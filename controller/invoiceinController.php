<?php
Class invoiceinController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->invoicein) || json_decode($_SESSION['user_permission_action'])->invoicein != "invoicein") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Bảng kê hóa đơn mua vào';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
            $batdau = isset($_POST['batdau']) ? $_POST['batdau'] : null;
            $ketthuc = isset($_POST['ketthuc']) ? $_POST['ketthuc'] : null;
            $ngaytao = isset($_POST['ngaytao']) ? $_POST['ngaytao'] : null;
            $ngaytaobatdau = isset($_POST['ngaytaobatdau']) ? $_POST['ngaytaobatdau'] : null;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'invoice_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $batdau = '01-'.date('m-Y');
            $ketthuc = date('t-m-Y');
            $ngaytao = date('m-Y');
            $ngaytaobatdau = date('m-Y');
        }
        $ngayketthuc = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        $invoice_model = $this->model->get('invoiceModel');

        $join = array('table'=>'customer','where'=>'invoice_customer=customer_id');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'invoice_type=1 AND ((invoice_date >= '.strtotime($batdau).' AND invoice_date < '.strtotime($ngayketthuc).') OR invoice_tax IN (SELECT payment_item_money_2 FROM payment, payment_item WHERE payment=payment_id AND payment_check=2 AND payment_type=2 AND payment_document_date >= '.strtotime($batdau).' AND payment_document_date < '.strtotime($ngayketthuc).'))',
        );

        
        $tongsodong = count($invoice_model->getAllInvoice($data,$join));
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
        $this->view->data['ngaytao'] = $ngaytao;
        $this->view->data['ngaytaobatdau'] = $ngaytaobatdau;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'invoice_type=1 AND ((invoice_date >= '.strtotime($batdau).' AND invoice_date < '.strtotime($ngayketthuc).') OR invoice_tax IN (SELECT payment_item_money_2 FROM payment, payment_item WHERE payment=payment_id AND payment_check=2 AND payment_type=2 AND payment_document_date >= '.strtotime($batdau).' AND payment_document_date < '.strtotime($ngayketthuc).'))',
            );
        
      
        if ($keyword != '') {
            $search = '( invoice_number LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%"
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        
        $invoices = $invoice_model->getAllInvoice($data,$join);
        
        $this->view->data['invoices'] = $invoices;

        $invoice_data = array();

        $payment_item_model = $this->model->get('paymentitemModel');
        foreach ($invoices as $invoice) {
            if ($invoice->invoice_buy > 0) {
                $payments = $payment_item_model->queryPayment('SELECT * FROM payment, payment_item WHERE payment=payment_id AND payment_check=2 AND payment_type=2 AND payment_item_money_2='.$invoice->invoice_tax);
                foreach ($payments as $pay) {
                    $invoice_data[$invoice->invoice_id]['number'] = $pay->payment_document_number;
                    $invoice_data[$invoice->invoice_id]['date'] = $pay->payment_document_date;
                }
            }
            
        }
        $this->view->data['invoice_data'] = $invoice_data;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('invoicein/index');
    }



}
?>