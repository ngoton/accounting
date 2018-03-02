<?php
Class invoiceoutController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->invoiceout) || json_decode($_SESSION['user_permission_action'])->invoiceout != "invoiceout") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Bảng kê hóa đơn bán ra';

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
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'invoice_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $batdau = '01-'.date('m-Y');
            $ketthuc = date('t-m-Y');
            $nv = 1;
            $tha = date('m');
            $na = date('Y');
        }
        $ngayketthuc = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        $invoice_model = $this->model->get('invoiceModel');

        $join = array('table'=>'customer','where'=>'invoice_customer=customer_id');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'invoice_type=2 AND invoice_date >= '.strtotime($batdau).' AND invoice_date < '.strtotime($ngayketthuc),
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
        $this->view->data['nv'] = $nv;
        $this->view->data['tha'] = $tha;
        $this->view->data['na'] = $na;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'invoice_type=2 AND invoice_date >= '.strtotime($batdau).' AND invoice_date < '.strtotime($ngayketthuc),
            );
        
      
        if ($keyword != '') {
            $search = '( invoice_number LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%"
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        
        $invoices = $invoice_model->getAllInvoice($data,$join);
        
        $this->view->data['invoices'] = $invoices;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('invoiceout/index');
    }



}
?>