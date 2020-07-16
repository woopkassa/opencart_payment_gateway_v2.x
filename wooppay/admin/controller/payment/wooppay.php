<?php

class ControllerPaymentWooppay extends Controller
{
    private $error = array();

    public function index()
    {

        $this->load->language('payment/wooppay');

        $this->document->setTitle = $this->language->get('heading_title');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('wooppay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_liqpay'] = $this->language->get('text_liqpay');
        $data['text_card'] = $this->language->get('text_card');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        // wooppay ENTER
        $data['entry_merchant'] = $this->language->get('entry_merchant');
        $data['entry_password'] = $this->language->get('entry_password');
        $data['entry_url'] = $this->language->get('entry_url');
        $data['entry_prefix'] = $this->language->get('entry_prefix');

        // URL
        $data['copy_result_url'] = HTTP_CATALOG . 'index.php?route=payment/wooppay/callback';
        $data['copy_success_url'] = HTTP_CATALOG . 'index.php?route=payment/wooppay/success';

        $data['entry_success_status'] = $this->language->get('entry_success_status');
        $data['entry_processing_status'] = $this->language->get('entry_processing_status');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['tab_general'] = $this->language->get('tab_general');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['merchant'])) {
            $data['error_merchant'] = $this->error['merchant'];
        } else {
            $data['error_merchant'] = '';
        }

        if (isset($this->error['password'])) {
            $data['error_password'] = $this->error['password'];
        } else {
            $data['error_password'] = '';
        }

        if (isset($this->error['url'])) {
            $data['error_url'] = $this->error['url'];
        } else {
            $data['error_url'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/wooppay', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link('payment/wooppay', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

        if (isset($this->request->post['wooppay_merchant'])) {
            $data['wooppay_merchant'] = $this->request->post['wooppay_merchant'];
        } else {
            $data['wooppay_merchant'] = $this->config->get('wooppay_merchant');
        }
        if (isset($this->request->post['wooppay_password'])) {
            $data['wooppay_password'] = $this->request->post['wooppay_password'];
        } else {
            $data['wooppay_password'] = $this->config->get('wooppay_password');
        }
        if (isset($this->request->post['wooppay_url'])) {
            $data['wooppay_url'] = $this->request->post['wooppay_url'];
        } else {
            $data['wooppay_url'] = $this->config->get('wooppay_url');
        }
        if (isset($this->request->post['wooppay_prefix'])) {
            $data['wooppay_prefix'] = $this->request->post['wooppay_prefix'];
        } else {
            $data['wooppay_prefix'] = $this->config->get('wooppay_prefix');
        }

        if (isset($this->request->post['wooppay_order_success_status_id'])) {
            $data['wooppay_order_success_status_id'] = $this->request->post['wooppay_order_success_status_id'];
        } else {
            $data['wooppay_order_success_status_id'] = $this->config->get('wooppay_order_success_status_id');
        }

        if (isset($this->request->post['wooppay_order_processing_status_id'])) {
            $data['wooppay_order_processing_status_id'] = $this->request->post['wooppay_order_processing_status_id'];
        } else {
            $data['wooppay_order_processing_status_id'] = $this->config->get('wooppay_order_processing_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['wooppay_status'])) {
            $data['wooppay_status'] = $this->request->post['wooppay_status'];
        } else {
            $data['wooppay_status'] = $this->config->get('wooppay_status');
        }

        if (isset($this->request->post['wooppay_sort_order'])) {
            $data['wooppay_sort_order'] = $this->request->post['wooppay_sort_order'];
        } else {
            $data['wooppay_sort_order'] = $this->config->get('wooppay_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('payment/wooppay.tpl', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'payment/wooppay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['wooppay_merchant']) {
            $this->error['merchant'] = $this->language->get('error_merchant');
        }

        if (!$this->request->post['wooppay_password']) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if (!$this->request->post['wooppay_url']) {
            $this->error['url'] = $this->language->get('error_url');
        }

        if (!$this->request->post['wooppay_prefix']) {
            $this->error['prefix'] = $this->language->get('error_prefix');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function install() {
        $this->load->model('payment/wooppay');
        $this->model_payment_wooppay->install();
    }

    public function uninstall() {
        $this->load->model('payment/wooppay');
        $this->model_payment_wooppay->uninstall();
    }
}

?>