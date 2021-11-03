<?php

class ControllerExtensionPaymentWooppay extends Controller
{
	public function index()
	{
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['button_confirm_action'] = $this->url->link('extension/payment/wooppay/invoice', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/wooppay.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/wooppay.tpl', $data);
		} else {
			return $this->load->view('extension/payment/wooppay.tpl', $data);
		}
	}

	private function login() {
		$client = new WooppaySoapClient($this->config->get('wooppay_url'));
		$login_request = new CoreLoginRequest();
		$login_request->username = $this->config->get('wooppay_merchant');
		$login_request->password = $this->config->get('wooppay_password');
		return $client->login($login_request) ? $client : false;
	}

	public function invoice()
	{
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		try {
			if ($client = $this->login()) {
				$prefix = trim($this->config->get('wooppay_prefix'));
				$invoice_request = new CashCreateInvoiceByServiceRequest();
				$invoice_request->referenceId = $prefix . $order_info['order_id'];
				$invoice_request->backUrl = $this->url->link('checkout/success');
				$invoice_request->requestUrl = str_replace('&amp;', '&', $this->url->link('extension/payment/wooppay/callback', 'order=' . $order_info['order_id'] . '&key=' . md5($order_info['order_id']), 'SSL'));
				$invoice_request->addInfo = 'Оплата заказа №' . $order_info['order_id'];
				$invoice_request->amount = $order_info['total'];
				$invoice_request->serviceName = $this->config->get('wooppay_service');
				$invoice_request->deathDate = '';
				$invoice_request->description = 'Оплата заказа №' . $order_info['order_id'];
				$invoice_request->userEmail = $order_info['email'];
				$invoice_request->userPhone = $order_info['telephone'];
				$invoice_data = $client->createInvoice($invoice_request);

				$this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('wooppay_order_processing_status_id'));
				$this->load->model('extension/payment/wooppay');

				$this->model_extension_payment_wooppay->addTransaction(['order_id' => $order_info['order_id'], 'wooppay_transaction_id' => $invoice_data->response->operationId]);
				$this->response->redirect($invoice_data->response->operationUrl);
			}
		} catch (Exception $e) {
			$this->log->write(sprintf('Wooppay exception : %s order id (%s)', $e->getMessage(), $this->request->get['order']));
		}
		$this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
	}

	public function callback()
	{
		if ($this->request->get['key'] == md5($this->request->get['order'])) {
			try {
				if ($client = $this->login()) {
					$this->load->model('extension/payment/wooppay');
					$operationId = $this->model_extension_payment_wooppay->getTransactionRow($this->request->get['order']);
					if ($operationId) {
						$operationdata_request = new CashGetOperationDataRequest();
						$operationdata_request->operationId = array($operationId['wooppay_transaction_id']);
						$operation_data = $client->getOperationData($operationdata_request);
						if (!isset($operation_data->response->records[0]->status) || empty($operation_data->response->records[0]->status)) {
							exit;
						}

						if ($operation_data->response->records[0]->status == WooppayOperationStatus::OPERATION_STATUS_DONE || $operation_data->response->records[0]->status == WooppayOperationStatus::OPERATION_STATUS_WAITING) {
							$this->load->model('checkout/order');
							$this->model_checkout_order->addOrderHistory($this->request->get['order'], $this->config->get('wooppay_order_success_status_id'));
						} else
							$this->log->write(sprintf('Wooppay callback : счет не оплачен (%s) order id (%s)', $operation_data->response->records[0]->status, $this->request->get['order']));
					} else
						$this->log->write(sprintf('Wooppay order not found : %s order id (%s)', $this->request->get['order'], $this->request->get['order']));
				}
			} catch (Exception $e) {
				$this->log->write(sprintf('Wooppay exception : %s order id (%s)', $e->getMessage(), $this->request->get['order']));
			}
		} else
			$this->log->write('Wooppay callback : неверный key или order : ' . print_r($_REQUEST, true));
		echo json_encode(['data' => 1]);
	}
}

class WooppaySoapClient {

	private $c;

	public function __construct($url, $options = array()) {
		try {
			$this->c = new SoapClient($url, $options);
		}
		catch (Exception $e) {
			throw new WooppaySoapException($e->getMessage());
		}
		if(empty($this->c)) {
			throw new WooppaySoapException('Cannot create instance of Soap client');
		}
	}

	/**
	 * @param $method
	 * @param $data
	 * @return WooppaySoapResponse
	 * @throws BadCredentialsException
	 * @throws UnsuccessfulResponseException
	 * @throws WooppaySoapException
	 */
	public function __call($method, $data) {
		try {

			$response = $this->c->$method($data[0]);
		}
		catch (Exception $e) {
			throw new WooppaySoapException($e->getMessage());
		}
		$response = new WooppaySoapResponse($response);
		switch ($response->error_code) {
			case 0:
				return $response;
				break;
			case 5:
				throw new BadCredentialsException();
				break;
			default:
				throw new UnsuccessfulResponseException('Error code ' . $response->error_code);
		}

	}

	public function login(CoreLoginRequest $data) {
		$response = $this->core_login($data);

		if(isset($response->response->session)) {
			$this->c->__setCookie('session', $response->response->session);
			return true;
		}
		else {
			return false;
		}
	}

	public function getOperationData(CashGetOperationDataRequest $data) {
		return $this->cash_getOperationData($data);
	}
	public function createInvoice(CashCreateInvoiceByServiceRequest $data) {
		return $this->cash_createInvoiceByService($data);
	}
	public function getLastDialog() {
		return array('req' => $this->c->__getLastRequest(), 'res' => $this->c->__getLastResponse());
	}
}

class CoreLoginRequest {
	/**
	 * @var string $username
	 * @soap
	 */
	public $username;
	/**
	 * @var string $password
	 * @soap
	 */
	public $password;
	/**
	 * @var string $captcha
	 * @soap
	 */
	public $captcha = null;
}

class CashGetOperationDataRequest
{
	/**
	 * @var $operationId array
	 */
	public $operationId;

}

class CashCreateInvoiceRequest {
	/**
	 * @var string $referenceId
	 * @soap
	 */
	public $referenceId;
	/**
	 * @var string $backUrl
	 * @soap
	 */
	public $backUrl;
	/**
	 * @var string $requestUrl
	 * @soap
	 */
	public $requestUrl = '';
	/**
	 * @var string $addInfo
	 * @soap
	 */
	public $addInfo;
	/**
	 * @var float $amount
	 * @soap
	 */
	public $amount;
	/**
	 * @var string $deathDate
	 * @soap
	 */
	public $deathDate;
	/**
	 * @var int $serviceType
	 * @soap
	 */
	public $serviceType = 4;
	/**
	 * @var string $description
	 * @soap
	 */
	public $description = '';
	/**
	 * @var int $orderNumber
	 * @soap
	 */
	public $orderNumber = null;
	/**
	 * @var string $userEmail
	 * @soap
	 */
	public $userEmail = null;
	/**
	 * @var string $userPhone
	 * @soap
	 */
	public $userPhone = null;
}

class CashCreateInvoiceExtendedRequest extends CashCreateInvoiceRequest {
	/**
	 * @var string $userEmail
	 * @soap
	 */
	public $userEmail = '';
	/**
	 * @var string $userPhone
	 * @soap
	 */
	public $userPhone = '';
}

class CashCreateInvoiceExtended2Request extends CashCreateInvoiceExtendedRequest {
	/**
	 * @var int $cardForbidden
	 * @soap
	 */
	public $cardForbidden;
}

class CashCreateInvoiceByServiceRequest extends CashCreateInvoiceExtended2Request {
	/**
	 * @var string $serviceName
	 * @soap
	 */
	public $serviceName;
}

class WooppaySoapResponse {

	public $error_code;
	public $response;

	public function __construct($response) {

		if(!is_object($response)) {
			throw new BadResponseException('Response is not an object');
		}

		if(!isset($response->error_code)) {
			throw new BadResponseException('Response do not contains error code');
		}
		$this->error_code = $response->error_code;

		if(!property_exists($response, 'response')) {
			throw new BadResponseException('Response do not contains response body');
		}
		$this->response = $response->response;
	}
}

class WooppayOperationStatus
{
	/**
	 * Новая
	 */
	const OPERATION_STATUS_NEW = 1;
	/**
	 * На рассмотрении
	 */
	const OPERATION_STATUS_CONSIDER = 2;
	/**
	 * Отклонена
	 */
	const OPERATION_STATUS_REJECTED = 3;
	/**
	 * Проведена
	 */
	const OPERATION_STATUS_DONE = 4;
	/**
	 * Сторнирована
	 */
	const OPERATION_STATUS_CANCELED = 5;
	/**
	 * Сторнирующая
	 */
	const OPERATION_STATUS_CANCELING = 6;
	/**
	 * Удалена
	 */
	const OPERATION_STATUS_DELETED = 7;
	/**
	 * На квитовании
	 */
	const OPERATION_STATUS_KVITOVANIE = 4;
	/**
	 * На ожидании подверждения или отказа мерчанта
	 */
	const OPERATION_STATUS_WAITING = 9;
}

class WooppaySoapException          extends Exception {}
class BadResponseException          extends WooppaySoapException {}
class UnsuccessfulResponseException extends WooppaySoapException {}
class BadCredentialsException       extends UnsuccessfulResponseException {}
?>
