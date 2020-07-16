<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-wooppay" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> Edit Wooppay</h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-wooppay"
                      class="form-horizontal">

                    <div class="form-group required">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_merchant"><?php echo $entry_merchant; ?></label>

                        <div class="col-sm-10">
                            <input type="text" name="wooppay_merchant" value="<?php echo $wooppay_merchant; ?>"
                                   placeholder="<?php echo $entry_merchant; ?>" id="input-wooppay_merchant"
                                   class="form-control"/>
                        </div>
                        <?php if ($error_merchant) { ?>
                        <div class="text-danger"><?php echo $error_merchant; ?></div>
                        <?php } ?>
                    </div>

                    <div class="form-group required">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_password"><?php echo $entry_password; ?></label>

                        <div class="col-sm-10">
                            <input type="password" name="wooppay_password" value="<?php echo $wooppay_password; ?>"
                                   placeholder="<?php echo $entry_password; ?>" id="input-wooppay_password"
                                   class="form-control"/>
                        </div>
                        <?php if ($error_password) { ?>
                        <div class="text-danger"><?php echo $error_password; ?></div>
                        <?php } ?>
                    </div>

                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="input-wooppay_url"><?php echo $entry_url; ?></label>

                        <div class="col-sm-10">
                            <input type="text" name="wooppay_url" value="<?php echo $wooppay_url; ?>"
                                   placeholder="<?php echo $entry_url; ?>" id="input-wooppay_url" class="form-control"/>
                        </div>
                        <?php if ($error_url) { ?>
                        <div class="text-danger"><?php echo $error_url; ?></div>
                        <?php } ?>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_prefix"><?php echo $entry_prefix; ?></label>

                        <div class="col-sm-10">
                            <input type="text" name="wooppay_prefix" value="<?php echo $wooppay_prefix; ?>"
                                   placeholder="<?php echo $entry_prefix; ?>" id="input-wooppay_prefix"
                                   class="form-control"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_service"><?php echo $entry_service; ?></label>

                        <div class="col-sm-10">
                            <input type="text" name="wooppay_service" value="<?php echo $wooppay_service; ?>"
                                   placeholder="<?php echo $entry_service; ?>" id="input-wooppay_service"
                                   class="form-control"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-wooppay_result_url">Result URL:</label>

                        <div class="col-sm-10">
                            <input type="text" name="wooppay_result_url" readonly="readonly"
                                   value="<?php echo $copy_result_url; ?>" id="input-wooppay_result_url"
                                   class="form-control"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-wooppay_success_url">Success URL:</label>

                        <div class="col-sm-10">
                            <input type="text" name="wooppay_success_url" readonly="readonly"
                                   value="<?php echo $copy_success_url; ?>" id="input-wooppay_success_url"
                                   class="form-control"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_order_success_status_id"><?php echo $entry_success_status; ?></label>

                        <div class="col-sm-10">
                            <select name="wooppay_order_success_status_id" id="input-wooppay_order_success_status_id"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $wooppay_order_success_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_order_processing_status_id"><?php echo $entry_processing_status; ?></label>

                        <div class="col-sm-10">
                            <select name="wooppay_order_processing_status_id"
                                    id="input-wooppay_order_processing_status_id"
                                    class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $wooppay_order_processing_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"
                                        selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_status"><?php echo $entry_status; ?></label>

                        <div class="col-sm-10">
                            <select name="wooppay_status" id="input-wooppay_status" class="form-control">
                                <?php if ($wooppay_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label"
                               for="input-wooppay_sort_order"><?php echo $entry_sort_order; ?></label>

                        <div class="col-sm-10">
                            <input type="text" name="wooppay_sort_order" value="<?php echo $wooppay_sort_order; ?>"
                                   placeholder="<?php echo $entry_sort_order; ?>" id="input-wooppay_sort_order"
                                   class="form-control"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>