<?php
Class balanceresultController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->balanceresult) || json_decode($_SESSION['user_permission_action'])->balanceresult != "balanceresult") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Báo cáo kết quả hoạt động kinh doanh';

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
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'account_number';
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

        $account_model = $this->model->get('accountModel');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );

        
        $tongsodong = count($account_model->getAllAccount($data));
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
            'where' => '1=1',
            );
        
      
        if ($keyword != '') {
            $search = '( account_number LIKE "%'.$keyword.'%" 
                    OR account_name LIKE "%'.$keyword.'%"
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        
        $accounts = $account_model->getAllAccount($data);
        
        $this->view->data['accounts'] = $accounts;

        $additional_model = $this->model->get('additionalModel');
        $data_additional = array();

        $additionals = $additional_model->getAllAdditional(array('where'=>'additional_date >= '.strtotime($batdau).' AND additional_date < '.strtotime($ngayketthuc)));
        
        foreach ($additionals as $additional) {
            $data_additional[$additional->debit]['no']['phatsinh'] = isset($data_additional[$additional->debit]['no']['phatsinh'])?$data_additional[$additional->debit]['no']['phatsinh']+$additional->money:$additional->money;
            $data_additional[$additional->credit]['co']['phatsinh'] = isset($data_additional[$additional->credit]['co']['phatsinh'])?$data_additional[$additional->credit]['co']['phatsinh']+$additional->money:$additional->money;
        }

        $additionals = $additional_model->getAllAdditional(array('where'=>'additional_date < '.strtotime($batdau)));
        
        foreach ($additionals as $additional) {
            $data_additional[$additional->debit]['no']['dauky'] = isset($data_additional[$additional->debit]['no']['dauky'])?$data_additional[$additional->debit]['no']['dauky']+$additional->money:$additional->money;
            $data_additional[$additional->credit]['co']['dauky'] = isset($data_additional[$additional->credit]['co']['dauky'])?$data_additional[$additional->credit]['co']['dauky']+$additional->money:$additional->money;
        }

        foreach ($accounts as $account) {
            if ($account->account_parent>0) {
                $nodauky[$account->account_id]=isset($data_additional[$account->account_id]['no']['dauky'])?$data_additional[$account->account_id]['no']['dauky']:0;
                $codauky[$account->account_id]=isset($data_additional[$account->account_id]['co']['dauky'])?$data_additional[$account->account_id]['co']['dauky']:0;
                $nophatsinh[$account->account_id]=isset($data_additional[$account->account_id]['no']['phatsinh'])?$data_additional[$account->account_id]['no']['phatsinh']:0;
                $cophatsinh[$account->account_id]=isset($data_additional[$account->account_id]['co']['phatsinh'])?$data_additional[$account->account_id]['co']['phatsinh']:0;

                $data_additional[$account->account_parent]['no']['dauky'] = isset($data_additional[$account->account_parent]['no']['dauky'])?$data_additional[$account->account_parent]['no']['dauky']+$nodauky[$account->account_id]:$nodauky[$account->account_id];
                $data_additional[$account->account_parent]['co']['dauky'] = isset($data_additional[$account->account_parent]['co']['dauky'])?$data_additional[$account->account_parent]['co']['dauky']+$codauky[$account->account_id]:$codauky[$account->account_id];
                $data_additional[$account->account_parent]['no']['phatsinh'] = isset($data_additional[$account->account_parent]['no']['phatsinh'])?$data_additional[$account->account_parent]['no']['phatsinh']+$nophatsinh[$account->account_id]:$nophatsinh[$account->account_id];
                $data_additional[$account->account_parent]['co']['phatsinh'] = isset($data_additional[$account->account_parent]['co']['phatsinh'])?$data_additional[$account->account_parent]['co']['phatsinh']+$cophatsinh[$account->account_id]:$cophatsinh[$account->account_id];
            }
        }

        $this->view->data['data_additional'] = $data_additional;

        $this->view->data['lastID'] = isset($account_model->getLastAccount()->account_id)?$account_model->getLastAccount()->account_id:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('balanceresult/index');
    }



}
?>