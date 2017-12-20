<?php
Class debitreceiveController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->debitreceive) || json_decode($_SESSION['user_permission_action'])->debitreceive != "debitreceive") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Tổng hợp công nợ phải thu';

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
            $ngaytao = date('m-Y');
            $ngaytaobatdau = date('m-Y');
            $trangthai = "";
            $code = $this->registry->router->addition;
        }
        $ngayketthuc = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        $id = $this->registry->router->param_id;


        $customer_model = $this->model->get('customerModel');

        $customers = $customer_model->getAllCustomer(array('where' => 'type_customer=1','order_by'=>'customer_code ASC'));
        $this->view->data['customer_lists'] = $customers;

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'type_customer=1',
        );

        
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
        $this->view->data['ngaytao'] = $ngaytao;
        $this->view->data['ngaytaobatdau'] = $ngaytaobatdau;
        $this->view->data['trangthai'] = $trangthai;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'type_customer=1',
            );
        
      
        if ($keyword != '') {
            $search = '( customer_code LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%"
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        
        $customers = $customer_model->getAllcustomer($data);
        
        $this->view->data['customers'] = $customers;

        $debit_model = $this->model->get('debitModel');
        $data_debit = array();
        foreach ($customers as $customer) {
            $data_debit[$customer->customer_id]['dauky_no']=0;
            $data_debit[$customer->customer_id]['dauky_co']=0;
            $data_debit[$customer->customer_id]['no']=0;
            $data_debit[$customer->customer_id]['co']=0;

            $data_im = array(
                'where' => 'debit_money>0 AND debit_customer = '.$customer->customer_id.' AND debit_date < '.strtotime($batdau),
            );
            $debit_ims = $debit_model->getAllDebit($data_im);
            foreach ($debit_ims as $im) {
                $price = $im->debit_money;
                $data_debit[$customer->customer_id]['dauky_no'] = isset($data_debit[$customer->customer_id]['dauky_no'])?$data_debit[$customer->customer_id]['dauky_no']+$price:$price;
            }
            $data_ex = array(
                'where' => 'debit_money<0 AND debit_customer = '.$customer->customer_id.' AND debit_date < '.strtotime($batdau),
            );
            $debit_exs = $debit_model->getAllDebit($data_ex);
            foreach ($debit_exs as $ex) {
                $price = str_replace('-', '', $ex->debit_money);
                $data_debit[$customer->customer_id]['dauky_co'] = isset($data_debit[$customer->customer_id]['dauky_co'])?$data_debit[$customer->customer_id]['dauky_co']+$price:$price;
            }

            $data_im = array(
                'where' => 'debit_money>0 AND debit_customer = '.$customer->customer_id.' AND debit_date >= '.strtotime($batdau).' AND debit_date < '.strtotime($ngayketthuc),
            );
            $debit_ims = $debit_model->getAllDebit($data_im);
            foreach ($debit_ims as $im) {
                $price = $im->debit_money;
                $data_debit[$customer->customer_id]['no'] = isset($data_debit[$customer->customer_id]['no'])?$data_debit[$customer->customer_id]['no']+$price:$price;
            }

            $data_ex = array(
                'where' => 'debit_money<0 AND debit_customer = '.$customer->customer_id.' AND debit_date >= '.strtotime($batdau).' AND debit_date < '.strtotime($ngayketthuc),
            );
            $debit_exs = $debit_model->getAllDebit($data_ex);
            foreach ($debit_exs as $ex) {
                $price = str_replace('-', '', $ex->debit_money);
                $data_debit[$customer->customer_id]['co'] = isset($data_debit[$customer->customer_id]['co'])?$data_debit[$customer->customer_id]['co']+$price:$price;
            }
        }

        $this->view->data['data_debit'] = $data_debit;

        $this->view->data['lastID'] = isset($customer_model->getLastCustomer()->customer_id)?$customer_model->getLastCustomer()->customer_id:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('debitreceive/index');
    }


}
?>