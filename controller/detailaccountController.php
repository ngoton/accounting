<?php
Class detailaccountController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->detailaccount) || json_decode($_SESSION['user_permission_action'])->detailaccount != "detailaccount") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Sổ chi tiết';

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
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'additional_date ASC, document_number ASC, debit ';
            $order = $this->registry->router->order_by ? $this->registry->router->order : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
            $batdau = '01-'.date('m-Y');
            $ketthuc = date('t-m-Y');
            $nv = 1;
            $tha = date('m');
            $na = date('Y');
            $trangthai = 1;
            $code = $this->registry->router->addition;
        }
        $kt = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        $id = $this->registry->router->param_id;


        $additional_model = $this->model->get('additionalModel');

        $account_model = $this->model->get('accountModel');


        $account_parents = $account_model->getAllAccount(array('order_by'=>'account_number ASC'));
        $account_data = array();
        $account_parent_data = array();
        foreach ($account_parents as $account_parent) {
            $account_data[$account_parent->account_id] = $account_parent->account_number;
            if ($account_parent->account_parent>0) {
                $account_parent_data[$account_parent->account_parent][] = $account_parent->account_id;
            }
        }
        $this->view->data['account_parents'] = $account_parents;
        $this->view->data['account_data'] = $account_data;
        $this->view->data['account_parent_data'] = $account_parent_data;

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'additional_date >= '.strtotime($batdau).' AND additional_date < '.strtotime($kt),
        );

        if ($id>0) {
            $trangthai = $id;
        }

        if ($trangthai > 0) {
            $data['where'] .= ' AND ((debit = '.$trangthai.' OR credit = '.$trangthai.') OR (debit IN (SELECT account_id FROM account WHERE account_parent='.$trangthai.') OR credit IN (SELECT account_id FROM account WHERE account_parent='.$trangthai.')))';
        }
        
        
        $tongsodong = count($additional_model->getAllAdditional($data));
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
            'where' => 'additional_date >= '.strtotime($batdau).' AND additional_date < '.strtotime($kt),
            );
        
        if ($id>0) {
            $trangthai = $id;
        }

        if ($trangthai > 0) {
            $data['where'] .= ' AND ((debit = '.$trangthai.' OR credit = '.$trangthai.') OR (debit IN (SELECT account_id FROM account WHERE account_parent='.$trangthai.') OR credit IN (SELECT account_id FROM account WHERE account_parent='.$trangthai.')))';
        }

        
      
        if ($keyword != '') {
            $search = '( document_number LIKE "%'.$keyword.'%" 
                    OR additional_comment LIKE "%'.$keyword.'%"
                    OR money LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['additionals'] = $additional_model->getAllAdditional($data);
        

        $data_additional = array();

        $additionals = $additional_model->getAllAdditional(array('where'=>'((debit = '.$trangthai.' OR credit = '.$trangthai.') OR (debit IN (SELECT account_id FROM account WHERE account_parent='.$trangthai.') OR credit IN (SELECT account_id FROM account WHERE account_parent='.$trangthai.'))) AND additional_date < '.strtotime($batdau)));
        
        foreach ($additionals as $additional) {
            $data_additional[$additional->debit]['no']['dauky'] = isset($data_additional[$additional->debit]['no']['dauky'])?$data_additional[$additional->debit]['no']['dauky']+$additional->money:$additional->money;
            $data_additional[$additional->credit]['co']['dauky'] = isset($data_additional[$additional->credit]['co']['dauky'])?$data_additional[$additional->credit]['co']['dauky']+$additional->money:$additional->money;
        }
        $accounts = $account_model->getAllAccount(array('where'=>'account_id='.$trangthai.' OR account_parent = '.$trangthai));
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

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('detailaccount/index');
    }

    

}
?>