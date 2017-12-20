<?php
Class customerController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->customer) || json_decode($_SESSION['user_permission_action'])->customer != "customer") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Quản lý Khách hàng - Đối tác - Người thụ hưởng';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'customer_code';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        $customer_model = $this->model->get('customerModel');


        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;


        $data = array(
            'where' => 'type_customer=1',
        );

        if (isset($id) && $id > 0) {
            $data['where'] .= ' AND customer_id = '.$id;
        }
        
        $tongsodong = count($customer_model->getAllCustomer($data));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['sonews'] = $sonews;
        $this->view->data['limit'] = $limit;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => 'type_customer=1',
            );

        if (isset($id) && $id > 0) {
            $data['where'] .= ' AND customer_id = '.$id;
        }
        
        if ($keyword != '') {
            $search = ' AND ( customer_name LIKE "%'.$keyword.'%"
                OR customer_code LIKE "%'.$keyword.'%"  
                OR customer_phone LIKE "%'.$keyword.'%" 
                OR customer_mobile LIKE "%'.$keyword.'%" 
                OR customer_email LIKE "%'.$keyword.'%" 
                OR customer_mst LIKE "%'.$keyword.'%" )';
            $data['where'] .= $search;
        }
        $customers = $customer_model->getAllCustomer($data);
        $this->view->data['customers'] = $customers;

        $this->view->data['lastID'] = isset($customer_model->getLastCustomer()->customer_id)?$customer_model->getLastCustomer()->customer_id:0;
        
        $this->view->show('customer/index');
    }
    

    public function newcus(){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Thêm Khách hàng - Đối tác - Người thụ hưởng';
        $this->view->show('customer/newcus');
    }
    public function editcus($id){
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
        if (!$id) {
            return $this->view->redirect('customer');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Cập nhật khách hàng - Đối tác - Người thụ hưởng';

        $customer_model = $this->model->get('customerModel');
        $customers = $customer_model->getCustomer($id);
        $this->view->data['customers'] = $customers;

        $contact_person_model = $this->model->get('contactpersonModel');
        $contact_persons = $contact_person_model->getAllCustomer(array('where'=>'customer = '.$id));
        $this->view->data['contact_persons'] = $contact_persons;

        $customer_sub_model = $this->model->get('customersubModel');

        $customer_sub = "";
        $sts = explode(',', $customers->customer_sub);
        foreach ($sts as $key) {
            $subs = $customer_sub_model->getCustomer($key);
            if($subs){
                if ($customer_sub == "")
                    $customer_sub .= $subs->customer_sub_name;
                else
                    $customer_sub .= ','.$subs->customer_sub_name;
            }
            
        }
        $this->view->data['customer_sub'] = $customer_sub;
        

        if (!$customers) {
            return $this->view->redirect('customer');
        }

        $this->view->show('customer/editcus');
    }
    public function deletecontact(){
        if (isset($_POST['data'])) {
            $contact_person = $this->model->get('contactpersonModel');

            $contact_person->queryCustomer('DELETE FROM contact_person WHERE contact_person_id='.$_POST['data'].' AND customer='.$_POST['customer']);
        }
    }
    public function getSub(){
        header('Content-type: application/json');
        $q = $_GET["search"];

        $sub_model = $this->model->get('customersubModel');
        $data = array(
            'where' => 'customer_sub_name LIKE "%'.$q.'%"',
        );
        $subs = $sub_model->getAllCustomer($data);
        $arr = array();
        foreach ($subs as $sub) {
            $arr[] = $sub->customer_sub_name;
        }
        
        echo json_encode($arr);
    }

    public function add(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
        if (isset($_POST['yes'])) {
            $customer = $this->model->get('customerModel');
            $contact_person_model = $this->model->get('contactpersonModel');
            $debit_model = $this->model->get('debitModel');
            $additional_model = $this->model->get('additionalModel');
            $account_model = $this->model->get('accountModel');
            $acc_131 = $account_model->getAccountByWhere(array('account_number'=>'131'))->account_id;
            $acc_331 = $account_model->getAccountByWhere(array('account_number'=>'331'))->account_id;

            $contact_person = $_POST['contact_person'];

            $data = array(
                        'customer_code' => trim($_POST['customer_code']),
                        'customer_name' => trim($_POST['customer_name']),
                        'customer_company' => trim($_POST['customer_company']),
                        'customer_mst' => trim($_POST['customer_mst']),
                        'customer_address' => trim($_POST['customer_address']),
                        'customer_phone' => trim($_POST['customer_phone']),
                        'customer_mobile' => trim($_POST['customer_mobile']),
                        'customer_email' => trim($_POST['customer_email']),
                        'customer_bank_account' => trim($_POST['customer_bank_account']),
                        'customer_bank_name' => trim($_POST['customer_bank_name']),
                        'customer_bank_branch' => trim($_POST['customer_bank_branch']),
                        'type_customer' => trim($_POST['type_customer']),
                        'customer_debit_dauky' => str_replace(',','',$_POST['customer_debit_dauky']),
                        'customer_credit_dauky' => str_replace(',','',$_POST['customer_credit_dauky']),
                        );

            $customer_sub_model = $this->model->get('customersubModel');

            $contributor = "";
            if(trim($_POST['customer_sub']) != ""){
                $support = explode(',', trim($_POST['customer_sub']));

                if ($support) {
                    foreach ($support as $key) {
                        $name = $customer_sub_model->getCustomerByWhere(array('customer_sub_name'=>trim($key)));
                        if ($name) {
                            if ($contributor == "")
                                $contributor .= $name->customer_sub_id;
                            else
                                $contributor .= ','.$name->customer_sub_id;
                        }
                        else{
                            $customer_sub_model->createCustomer(array('customer_sub_name'=>trim($key)));
                            if ($contributor == "")
                                $contributor .= $customer_sub_model->getLastCustomer()->customer_sub_id;
                            else
                                $contributor .= ','.$customer_sub_model->getLastCustomer()->customer_sub_id;
                        }
                        
                    }
                }

            }
            $data['customer_sub'] = $contributor;


            if ($_POST['yes'] != "") {

                if ($customer->getAllCustomerByWhere($_POST['yes'].' AND customer_code = "'.$data['customer_code'].'"')) {
                    $mess = array(
                        'msg' => 'Mã khách hàng đã tồn tại',
                        'id' => $_POST['yes'],
                    );

                    echo json_encode($mess);
                }
                /*else if ($customer->getAllCustomerByWhere($_POST['yes'].' AND customer_name = "'.$data['customer_name'].'"')) {
                    $mess = array(
                        'msg' => 'Tên khách hàng đã tồn tại',
                        'id' => $_POST['yes'],
                    );

                    echo json_encode($mess);
                }
                else if ($customer->getAllCustomerByWhere($_POST['yes'].' AND customer_mst = "'.$data['customer_mst'].'"')) {
                    $mess = array(
                        'msg' => 'Khách hàng đã tồn tại',
                        'id' => $_POST['yes'],
                    );

                    echo json_encode($mess);
                }*/
                else{
                    $customer->updateCustomer($data,array('customer_id' => $_POST['yes']));

                    $id_customer = $_POST['yes'];
                    $customers = $customer->getCustomer($id_customer);

                    $qr = $debit_model->getDebitByWhere(array('customer'=>$id_customer,'debit_date'=>1));
                    if ($qr) {
                        if ($data['type_customer']==1) {
                            if($customers->customer_debit_dauky>0){
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>$data['customer_debit_dauky'],
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>$customers->customer_debit_dauky));

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>$acc_131,
                                    'credit'=>0,
                                    'money'=>$data['customer_debit_dauky'],
                                );
                                $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'debit'=>$acc_131));
                            }
                            else if($customers->customer_credit_dauky>0){
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>(0-$data['customer_credit_dauky']),
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>(0-$customers->customer_credit_dauky)));

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>0,
                                    'credit'=>$acc_131,
                                    'money'=>$data['customer_credit_dauky'],
                                );
                                $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'credit'=>$acc_131));
                            }
                        }
                        else if ($data['type_customer']==2) {
                            if($customers->customer_debit_dauky>0){
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>$data['customer_debit_dauky'],
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>$customers->customer_debit_dauky));

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>0,
                                    'credit'=>$acc_331,
                                    'money'=>$data['customer_debit_dauky'],
                                );
                                $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'credit'=>$acc_331));
                            }
                            else if($customers->customer_credit_dauky>0){
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>(0-$data['customer_credit_dauky']),
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>(0-$customers->customer_credit_dauky)));

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>$acc_331,
                                    'credit'=>0,
                                    'money'=>$data['customer_credit_dauky'],
                                );
                                $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'debit'=>$acc_331));
                            }
                        }
                        
                    }
                    else{
                        if ($data['type_customer']==1) {
                            if ($data['customer_debit_dauky']>0) {
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>$data['customer_debit_dauky'],
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->createDebit($data_debit);

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>$acc_131,
                                    'credit'=>0,
                                    'money'=>$data['customer_debit_dauky'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            if ($data['customer_credit_dauky']>0) {
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>(0-$data['customer_credit_dauky']),
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->createDebit($data_debit);

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>0,
                                    'credit'=>$acc_131,
                                    'money'=>$data['customer_credit_dauky'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                        }
                        else if ($data['type_customer']==2) {
                            if ($data['customer_debit_dauky']>0) {
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>$data['customer_debit_dauky'],
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->createDebit($data_debit);

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>0,
                                    'credit'=>$acc_331,
                                    'money'=>$data['customer_debit_dauky'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            if ($data['customer_credit_dauky']>0) {
                                $data_debit = array(
                                    'customer'=>$id_customer,
                                    'debit_date'=>1,
                                    'debit_customer'=>$id_customer,
                                    'debit_money'=>(0-$data['customer_credit_dauky']),
                                    'debit_comment'=>'Công nợ đầu kỳ',
                                );
                                $debit_model->createDebit($data_debit);

                                $data_additional = array(
                                    'customer'=>$id_customer,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                    'debit'=>$acc_331,
                                    'credit'=>0,
                                    'money'=>$data['customer_credit_dauky'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                        }
                    }
                    

                    

                    /*Log*/
                    /**/

                    $mess = array(
                        'msg' => 'Cập nhật thành công',
                        'id' => $_POST['yes'],
                    );

                    echo json_encode($mess);


                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|customer|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                    
                
                
            }
            else{

                if ($customer->getCustomerByWhere(array('customer_code'=>$data['customer_code']))) {
                    $mess = array(
                        'msg' => 'Mã khách hàng đã tồn tại',
                        'id' => "",
                    );

                    echo json_encode($mess);
                    
                }
                /*else if ($customer->getCustomerByWhere(array('customer_name'=>$data['customer_name']))) {
                    $mess = array(
                        'msg' => 'Tên khách hàng đã tồn tại',
                        'id' => "",
                    );

                    echo json_encode($mess);
                    
                }
                else if ($customer->getCustomerByWhere(array('customer_mst'=>$data['customer_mst']))) {
                    $mess = array(
                        'msg' => 'Khách hàng đã tồn tại',
                        'id' => "",
                    );

                    echo json_encode($mess);
                    
                }*/
                else{
                    $customer->createCustomer($data);

                    $id_customer = $customer->getLastCustomer()->customer_id;

                    if ($data['type_customer']==1) {
                        if ($data['customer_debit_dauky']>0) {
                            $data_debit = array(
                                'customer'=>$id_customer,
                                'debit_date'=>1,
                                'debit_customer'=>$id_customer,
                                'debit_money'=>$data['customer_debit_dauky'],
                                'debit_comment'=>'Công nợ đầu kỳ',
                            );
                            $debit_model->createDebit($data_debit);

                            $data_additional = array(
                                'customer'=>$id_customer,
                                'document_number'=>"",
                                'document_date'=>1,
                                'additional_date'=>1,
                                'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                'debit'=>$acc_131,
                                'credit'=>0,
                                'money'=>$data['customer_debit_dauky'],
                            );
                            $additional_model->createAdditional($data_additional);
                        }
                        if ($data['customer_credit_dauky']>0) {
                            $data_debit = array(
                                'customer'=>$id_customer,
                                'debit_date'=>1,
                                'debit_customer'=>$id_customer,
                                'debit_money'=>(0-$data['customer_credit_dauky']),
                                'debit_comment'=>'Công nợ đầu kỳ',
                            );
                            $debit_model->createDebit($data_debit);

                            $data_additional = array(
                                'customer'=>$id_customer,
                                'document_number'=>"",
                                'document_date'=>1,
                                'additional_date'=>1,
                                'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                'debit'=>0,
                                'credit'=>$acc_131,
                                'money'=>$data['customer_credit_dauky'],
                            );
                            $additional_model->createAdditional($data_additional);
                        }
                    }
                    else if ($data['type_customer']==2) {
                        if ($data['customer_debit_dauky']>0) {
                            $data_debit = array(
                                'customer'=>$id_customer,
                                'debit_date'=>1,
                                'debit_customer'=>$id_customer,
                                'debit_money'=>$data['customer_debit_dauky'],
                                'debit_comment'=>'Công nợ đầu kỳ',
                            );
                            $debit_model->createDebit($data_debit);

                            $data_additional = array(
                                'customer'=>$id_customer,
                                'document_number'=>"",
                                'document_date'=>1,
                                'additional_date'=>1,
                                'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                'debit'=>0,
                                'credit'=>$acc_331,
                                'money'=>$data['customer_debit_dauky'],
                            );
                            $additional_model->createAdditional($data_additional);
                        }
                        if ($data['customer_credit_dauky']>0) {
                            $data_debit = array(
                                'customer'=>$id_customer,
                                'debit_date'=>1,
                                'debit_customer'=>$id_customer,
                                'debit_money'=>(0-$data['customer_credit_dauky']),
                                'debit_comment'=>'Công nợ đầu kỳ',
                            );
                            $debit_model->createDebit($data_debit);

                            $data_additional = array(
                                'customer'=>$id_customer,
                                'document_number'=>"",
                                'document_date'=>1,
                                'additional_date'=>1,
                                'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                'debit'=>$acc_331,
                                'credit'=>0,
                                'money'=>$data['customer_credit_dauky'],
                            );
                            $additional_model->createAdditional($data_additional);
                        }
                    }
                    
                    

                    /*Log*/
                    /**/

                    $mess = array(
                        'msg' => 'Thêm thành công',
                        'id' => $customer->getLastCustomer()->customer_id,
                    );

                    echo json_encode($mess);


                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$customer->getLastCustomer()->customer_id."|customer|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                    
                
                
            }

            if (isset($id_customer)) {
                foreach ($contact_person as $v) {
                    $data_contact_person = array(
                        'contact_person_name' => trim($v['contact_person_name']),
                        'contact_person_phone' => trim($v['contact_person_phone']),
                        'contact_person_mobile' => trim($v['contact_person_mobile']),
                        'contact_person_email' => trim($v['contact_person_email']),
                        'contact_person_address' => trim($v['contact_person_address']),
                        'contact_person_position' => trim($v['contact_person_position']),
                        'contact_person_department' => trim($v['contact_person_department']),
                        'customer' => $id_customer,
                    );

                    

                    if ($contact_person_model->getCustomerByWhere(array('contact_person_name'=>$data_contact_person['contact_person_name'],'customer'=>$id_customer))) {
                        $id_contact_person = $contact_person_model->getCustomerByWhere(array('contact_person_name'=>$data_contact_person['contact_person_name'],'customer'=>$id_customer))->contact_person_id;
                        $contact_person_model->updateCustomer($data_contact_person,array('contact_person_id'=>$id_contact_person));
                    }
                    else if (!$contact_person_model->getCustomerByWhere(array('contact_person_name'=>$data_contact_person['contact_person_name'],'customer'=>$id_customer))) {
                        if ($data_contact_person['contact_person_name'] != "") {
                            $contact_person_model->createCustomer($data_contact_person);
                        }
                    }
                }
            }
            
                    
        }
    }
    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $customer = $this->model->get('customerModel');
            $debit_model = $this->model->get('debitModel');
            $additional_model = $this->model->get('additionalModel');
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE customer='.$data);
                    $debit_model->queryDebit('DELETE FROM debit WHERE customer='.$data);
                    $customer->deleteCustomer($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|customer|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }

                /*Log*/
                    /**/

                return true;
            }
            else{
                /*Log*/
                    /**/
                    $additional_model->queryAdditional('DELETE FROM additional WHERE customer='.$_POST['data']);
                    $debit_model->queryDebit('DELETE FROM debit WHERE customer='.$_POST['data']);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|customer|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);

                return $customer->deleteCustomer($_POST['data']);
            }
            
        }
    }
    public function import(){
        ini_set('max_execution_time', 2000); //300 seconds = 5 minutes
        $this->view->disableLayout();
        header('Content-Type: text/html; charset=utf-8');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $customer_model = $this->model->get('customerModel');
            $account_model = $this->model->get('accountModel');
            $debit_model = $this->model->get('debitModel');
            $additional_model = $this->model->get('additionalModel');

            $objPHPExcel = new PHPExcel();
            // Set properties
            if (pathinfo($_FILES['import']['name'], PATHINFO_EXTENSION) == "xls") {
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
            }
            else if (pathinfo($_FILES['import']['name'], PATHINFO_EXTENSION) == "xlsx") {
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            }
            
            $objReader->setReadDataOnly(false);

            $objPHPExcel = $objReader->load($_FILES['import']['tmp_name']);

            $i = 0;
            while ($objPHPExcel->setActiveSheetIndex($i)){
                $objWorksheet = $objPHPExcel->getActiveSheet();

                $nameWorksheet = trim($objWorksheet->getTitle()); // tên sheet là tháng lương (8.2014 => 08/2014)
                
                $highestRow = $objWorksheet->getHighestRow(); // e.g. 10
                $highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

                
                if ($nameWorksheet=="KHACHHANG") {
                    for ($row = 2; $row <= $highestRow; ++ $row) {
                        $val = array();
                        for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                            // Check if cell is merged
                            foreach ($objWorksheet->getMergeCells() as $cells) {
                                if ($cell->isInRange($cells)) {
                                    $currMergedCellsArray = PHPExcel_Cell::splitRange($cells);
                                    $cell = $objWorksheet->getCell($currMergedCellsArray[0][0]);
                                    break;
                                }
                            }
                            $val[] = trim($cell->getCalculatedValue());
                            //here's my prob..
                            //echo $val;
                        }

                        if ($val[1] != "" && $val[1] != NULL) {
                            
                            $acc_131 = $account_model->getAccountByWhere(array('account_number'=>'131'))->account_id;
                            $acc_331 = $account_model->getAccountByWhere(array('account_number'=>'331'))->account_id;

                            $data = array(
                            'customer_code' => $val[1],
                            'customer_name' => $val[2],
                            'customer_company' => $val[3],
                            'customer_mst' => $val[5],
                            'customer_address' => $val[4],
                            'customer_phone' => $val[6],
                            'customer_mobile' => $val[7],
                            'customer_email' => $val[8],
                            'customer_bank_account' => $val[9],
                            'customer_bank_name' => $val[10],
                            'customer_bank_branch' => $val[11],
                            'type_customer' => 1,
                            'customer_debit_dauky' => null,
                            'customer_credit_dauky' => null,
                            );

                            if ($val[12]>0) {
                                $data['customer_debit_dauky'] = $val[12];
                            }
                            if ($val[12]<0) {
                                $data['customer_credit_dauky'] = (0-$val[12]);
                            }

                            $customers = $customer_model->getCustomerByWhere(array('customer_code'=>$data['customer_code']));

                            if ($customers) {
                                $customer_model->updateCustomer($data,array('customer_id'=>$customers->customer_id));
                                $id_customer = $customers->customer_id;
                            }
                            else{
                                $customer_model->createCustomer($data);
                                $id_customer = $customer_model->getLastCustomer()->customer_id;
                            }

                            $qr = $debit_model->getDebitByWhere(array('customer'=>$id_customer,'debit_date'=>1));
                            if ($qr) {
                                if ($data['type_customer']==1) {
                                    if($customers->customer_debit_dauky>0){
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>$data['customer_debit_dauky'],
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>$customers->customer_debit_dauky));

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>$acc_131,
                                            'credit'=>0,
                                            'money'=>$data['customer_debit_dauky'],
                                        );
                                        $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'debit'=>$acc_131));
                                    }
                                    else if($customers->customer_credit_dauky>0){
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>(0-$data['customer_credit_dauky']),
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>(0-$customers->customer_credit_dauky)));

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>0,
                                            'credit'=>$acc_131,
                                            'money'=>$data['customer_credit_dauky'],
                                        );
                                        $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'credit'=>$acc_131));
                                    }
                                }
                            }
                            else{
                                if ($data['type_customer']==1) {
                                    if ($data['customer_debit_dauky']>0) {
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>$data['customer_debit_dauky'],
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->createDebit($data_debit);

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>$acc_131,
                                            'credit'=>0,
                                            'money'=>$data['customer_debit_dauky'],
                                        );
                                        $additional_model->createAdditional($data_additional);
                                    }
                                    if ($data['customer_credit_dauky']>0) {
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>(0-$data['customer_credit_dauky']),
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->createDebit($data_debit);

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>0,
                                            'credit'=>$acc_131,
                                            'money'=>$data['customer_credit_dauky'],
                                        );
                                        $additional_model->createAdditional($data_additional);
                                    }
                                }
                            }

                        }
                        
                    }//
                }//KHACHHANG
                else if ($nameWorksheet=="NHACUNGCAP") {
                    for ($row = 2; $row <= $highestRow; ++ $row) {
                        $val = array();
                        for ($col = 0; $col < $highestColumnIndex; ++ $col) {
                            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                            // Check if cell is merged
                            foreach ($objWorksheet->getMergeCells() as $cells) {
                                if ($cell->isInRange($cells)) {
                                    $currMergedCellsArray = PHPExcel_Cell::splitRange($cells);
                                    $cell = $objWorksheet->getCell($currMergedCellsArray[0][0]);
                                    break;
                                }
                            }
                            $val[] = trim($cell->getCalculatedValue());
                            //here's my prob..
                            //echo $val;
                        }

                        if ($val[1] != "" && $val[1] != NULL) {
                            
                            $acc_131 = $account_model->getAccountByWhere(array('account_number'=>'131'))->account_id;
                            $acc_331 = $account_model->getAccountByWhere(array('account_number'=>'331'))->account_id;

                            $data = array(
                            'customer_code' => $val[1],
                            'customer_name' => $val[2],
                            'customer_company' => $val[3],
                            'customer_mst' => $val[5],
                            'customer_address' => $val[4],
                            'customer_phone' => $val[6],
                            'customer_mobile' => $val[7],
                            'customer_email' => $val[8],
                            'customer_bank_account' => $val[9],
                            'customer_bank_name' => $val[10],
                            'customer_bank_branch' => $val[11],
                            'type_customer' => 2,
                            'customer_debit_dauky' => null,
                            'customer_credit_dauky' => null,
                            );

                            if ($val[12]>0) {
                                $data['customer_debit_dauky'] = $val[12];
                            }
                            if ($val[12]<0) {
                                $data['customer_credit_dauky'] = (0-$val[12]);
                            }

                            $customers = $customer_model->getCustomerByWhere(array('customer_code'=>$data['customer_code']));

                            if ($customers) {
                                $customer_model->updateCustomer($data,array('customer_id'=>$customers->customer_id));
                                $id_customer = $customers->customer_id;
                            }
                            else{
                                $customer_model->createCustomer($data);
                                $id_customer = $customer_model->getLastCustomer()->customer_id;
                            }

                            $qr = $debit_model->getDebitByWhere(array('customer'=>$id_customer,'debit_date'=>1));
                            if ($qr) {
                                if ($data['type_customer']==2) {
                                    if($customers->customer_debit_dauky>0){
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>$data['customer_debit_dauky'],
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>$customers->customer_debit_dauky));

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>0,
                                            'credit'=>$acc_331,
                                            'money'=>$data['customer_debit_dauky'],
                                        );
                                        $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'credit'=>$acc_331));
                                    }
                                    else if($customers->customer_credit_dauky>0){
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>(0-$data['customer_credit_dauky']),
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->updateDebit($data_debit,array('debit_date'=>1,'debit_customer'=>$id_customer,'debit_money'=>(0-$customers->customer_credit_dauky)));

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>$acc_331,
                                            'credit'=>0,
                                            'money'=>$data['customer_credit_dauky'],
                                        );
                                        $additional_model->updateAdditional($data_additional,array('additional_date'=>1,'customer'=>$id_customer,'debit'=>$acc_331));
                                    }
                                }
                            }
                            else{
                                if ($data['type_customer']==2) {
                                    if ($data['customer_debit_dauky']>0) {
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>$data['customer_debit_dauky'],
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->createDebit($data_debit);

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>0,
                                            'credit'=>$acc_331,
                                            'money'=>$data['customer_debit_dauky'],
                                        );
                                        $additional_model->createAdditional($data_additional);
                                    }
                                    if ($data['customer_credit_dauky']>0) {
                                        $data_debit = array(
                                            'customer'=>$id_customer,
                                            'debit_date'=>1,
                                            'debit_customer'=>$id_customer,
                                            'debit_money'=>(0-$data['customer_credit_dauky']),
                                            'debit_comment'=>'Công nợ đầu kỳ',
                                        );
                                        $debit_model->createDebit($data_debit);

                                        $data_additional = array(
                                            'customer'=>$id_customer,
                                            'document_number'=>"",
                                            'document_date'=>1,
                                            'additional_date'=>1,
                                            'additional_comment'=>"Công nợ đầu kỳ ".$data['customer_name'],
                                            'debit'=>$acc_331,
                                            'credit'=>0,
                                            'money'=>$data['customer_credit_dauky'],
                                        );
                                        $additional_model->createAdditional($data_additional);
                                    }
                                }
                            }

                            
                        }
                        
                    }//
                }//NHACUNGCAP
                
                

                $i++;
            }
            return $this->view->redirect('customer');
        }
        $this->view->show('customer/import');
    }


}
?>