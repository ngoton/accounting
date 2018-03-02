<?php
Class debitpaydetailController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->debitpaydetail) || json_decode($_SESSION['user_permission_action'])->debitpaydetail != "debitpaydetail") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Chi tiết công nợ phải trả';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $nv = isset($_POST['nv']) ? $_POST['nv'] : null;
            $tha = isset($_POST['tha']) ? $_POST['tha'] : null;
            $na = isset($_POST['na']) ? $_POST['na'] : null;
            $trangthai = isset($_POST['trangthai']) ? $_POST['trangthai'] : null;
            $code = "";
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'customer_code';
            $order = $this->registry->router->order_by ? $this->registry->router->order : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $batdau = '01-'.date('m-Y');
            $ketthuc = date('t-m-Y');
            $nv = 1;
            $tha = date('m');
            $na = date('Y');
            $trangthai = "";
            $code = $this->registry->router->addition;
        }
        $ngayketthuc = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        $id = $this->registry->router->param_id;

        $customer_model = $this->model->get('customerModel');

        $customer_lists = $customer_model->getAllCustomer(array('where'=>'type_customer=2','order_by'=>'customer_code ASC'));
        $this->view->data['customer_lists'] = $customer_lists;

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'type_customer=2',
        );
        if ($trangthai>0) {
            $data['where'] .= ' AND customer_id='.$trangthai;
        }

        
        $tongsodong = count($customer_model->getAllCustomer($data));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['limit'] = $limit;
        $this->view->data['sonews'] = $sonews;
        $this->view->data['batdau'] = $batdau;
        $this->view->data['ketthuc'] = $ketthuc;
        $this->view->data['nv'] = $nv;
        $this->view->data['tha'] = $tha;
        $this->view->data['na'] = $na;
        $this->view->data['trangthai'] = $trangthai;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'type_customer=2',
            );
        if ($trangthai>0) {
            $data['where'] .= ' AND customer_id='.$trangthai;
        }
        
      
        if ($keyword != '') {
            $search = '( customer_code LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%"
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        
        $customers = $customer_model->getAllCustomer($data);
        
        $this->view->data['customers'] = $customers;

        $invoice_buy_model = $this->model->get('invoicebuyModel');
        $service_buy_model = $this->model->get('servicebuyModel');
        $invoice_purchase_model = $this->model->get('invoicepurchaseModel');
        $payment_item_model = $this->model->get('paymentitemModel');
        $additional_other_model = $this->model->get('additionalotherModel');

        $data_invoice_buy = array();
        $data_service_buy = array();
        $data_invoice_purchase = array();
        $data_payment = array();
        $data_additional_other = array();

        $invoice_buys = $invoice_buy_model->getAllInvoice(array('where'=>'invoice_buy_document_date>='.strtotime($batdau).' AND invoice_buy_document_date<'.strtotime($ngayketthuc)));
        foreach ($invoice_buys as $invoice_buy) {
            $data_invoice_buy[$invoice_buy->invoice_buy_id]['number'] = $invoice_buy->invoice_buy_document_number;
            $data_invoice_buy[$invoice_buy->invoice_buy_id]['date'] = $invoice_buy->invoice_buy_document_date;
            $data_invoice_buy[$invoice_buy->invoice_buy_id]['comment'] = $invoice_buy->invoice_buy_comment;
        }
        $service_buys = $service_buy_model->getAllService(array('where'=>'service_buy_document_date>='.strtotime($batdau).' AND service_buy_document_date<'.strtotime($ngayketthuc)));
        foreach ($service_buys as $service_buy) {
            $data_service_buy[$service_buy->service_buy_id]['number'] = $service_buy->service_buy_document_number;
            $data_service_buy[$service_buy->service_buy_id]['date'] = $service_buy->service_buy_document_date;
            $data_service_buy[$service_buy->service_buy_id]['comment'] = $service_buy->service_buy_comment;
        }
        $invoice_purchases = $invoice_purchase_model->getAllInvoice(array('where'=>'invoice_purchase_document_date>='.strtotime($batdau).' AND invoice_purchase_document_date<'.strtotime($ngayketthuc)));
        foreach ($invoice_purchases as $invoice_purchase) {
            $data_invoice_purchase[$invoice_purchase->invoice_purchase_id]['number'] = $invoice_purchase->invoice_purchase_document_number;
            $data_invoice_purchase[$invoice_purchase->invoice_purchase_id]['date'] = $invoice_purchase->invoice_purchase_document_date;
            $data_invoice_purchase[$invoice_purchase->invoice_purchase_id]['comment'] = $invoice_purchase->invoice_purchase_comment;
        }
        $join = array('table'=>'payment','where'=>'payment=payment_id');
        $payments = $payment_item_model->getAllPayment(array('where'=>'payment_document_date>='.strtotime($batdau).' AND payment_document_date<'.strtotime($ngayketthuc)),$join);
        foreach ($payments as $payment) {
            $data_payment[$payment->payment_item_id]['number'] = $payment->payment_document_number;
            $data_payment[$payment->payment_item_id]['date'] = $payment->payment_document_date;
            $data_payment[$payment->payment_item_id]['comment'] = $payment->payment_comment;
            $data_payment[$payment->payment_item_id]['check'] = $payment->payment_type;
        }
        $additional_others = $additional_other_model->getAllAdditional(array('where'=>'additional_other_document_date>='.strtotime($batdau).' AND additional_other_document_date<'.strtotime($ngayketthuc)));
        foreach ($additional_others as $additional_other) {
            $data_additional_other[$additional_other->additional_other_id]['check'] = $additional_other->additional_other_bank_check;
            $data_additional_other[$additional_other->additional_other_id]['number'] = $additional_other->additional_other_document_number;
            $data_additional_other[$additional_other->additional_other_id]['date'] = $additional_other->additional_other_document_date;
            $data_additional_other[$additional_other->additional_other_id]['comment'] = $additional_other->additional_other_comment;
        }

        $this->view->data['data_invoice_buy'] = $data_invoice_buy;
        $this->view->data['data_service_buy'] = $data_service_buy;
        $this->view->data['data_invoice_purchase'] = $data_invoice_purchase;
        $this->view->data['data_payment'] = $data_payment;
        $this->view->data['data_additional_other'] = $data_additional_other;

        $debit_model = $this->model->get('debitModel');
        $data_debit = array();
        $debits = array();
        foreach ($customers as $customer) {
            $data_debit[$customer->customer_id]['dauky_no']=0;
            $data_debit[$customer->customer_id]['dauky_co']=0;

            $data_im = array(
                'where' => 'debit_money>0 AND debit_customer = '.$customer->customer_id.' AND debit_date < '.strtotime($batdau),
            );
            $debit_ims = $debit_model->getAllDebit($data_im);
            foreach ($debit_ims as $im) {
                $price = $im->debit_money;
                $data_debit[$customer->customer_id]['dauky_co'] = isset($data_debit[$customer->customer_id]['dauky_co'])?$data_debit[$customer->customer_id]['dauky_co']+$price:$price;
            }
            $data_ex = array(
                'where' => 'debit_money<0 AND debit_customer = '.$customer->customer_id.' AND debit_date < '.strtotime($batdau),
            );
            $debit_exs = $debit_model->getAllDebit($data_ex);
            foreach ($debit_exs as $ex) {
                $price = str_replace('-', '', $ex->debit_money);
                $data_debit[$customer->customer_id]['dauky_no'] = isset($data_debit[$customer->customer_id]['dauky_no'])?$data_debit[$customer->customer_id]['dauky_no']+$price:$price;
            }

            $debits[$customer->customer_id]= $debit_model->getAllDebit(array('where'=>'debit_customer='.$customer->customer_id.' AND debit_date>='.strtotime($batdau).' AND debit_date<'.strtotime($ngayketthuc),'order_by'=>'debit_date ASC'));
            
            
        }
        $this->view->data['debits'] = $debits;
        $this->view->data['data_debit'] = $data_debit;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('debitpaydetail/index');
    }

}
?>