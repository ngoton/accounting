<?php
Class adminController Extends baseController {
    public function index() {
    	$this->view->setLayout('admin');
        if (isset($_GET['mst'])) {
            $_SESSION['db'] = $_GET['mst'];
            $company = $this->model->get('infoModel');
            $_SESSION['company'] = $company->getLastInfo()->info_company;

            if (isset($_SESSION['user_logined'])) {
                $user = $this->model->get('userModel');
                $row = $user->getUserByUsername($_SESSION['user_logined']);
                if ($row) {

                    $_SESSION['user_logined'] = $row->username;

                    $_SESSION['userid_logined'] = $row->user_id;

                    $_SESSION['role_logined'] = $row->role;

                    $_SESSION['user_permission'] = $row->permission;

                    $_SESSION['user_permission_action'] = $row->permission_action;
                }
                else{
                    return $this->view->redirect('user/login');
                }
            }
            
        }
        else{
            $_SESSION['db'] = isset($_SESSION['db'])?$_SESSION['db']:NULL;
            $company = $this->model->get('infoModel');
            $_SESSION['company'] = $company->getLastInfo()->info_company;
        }

    	if (!isset($_SESSION['role_logined'])) {
            return $this->view->redirect('user/login');
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Dashboard';

        $this->view->show('admin/index');
    }

   public function checklockuser(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['data'] == 0) {
                echo 0;
            }
            else{
                $user_model = $this->model->get('userModel');
            
                $user = $user_model->getUserByWhere(array('user_id' => $_POST['data']));
                echo $user->user_lock;
            }
            
        }
    }

    public function importdata(){
        ini_set('max_execution_time', 2000); //300 seconds = 5 minutes
        $this->view->disableLayout();
        header('Content-Type: text/html; charset=utf-8');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_FILES['import']['name'] != null) {

            require("lib/Classes/PHPExcel/IOFactory.php");
            require("lib/Classes/PHPExcel.php");

            $additional_other_model = $this->model->get('additionalotherModel');
            $customer_model = $this->model->get('customerModel');
            $items_model = $this->model->get('itemsModel');
            $bank_model = $this->model->get('bankModel');
            $service_buy_model = $this->model->get('servicebuyModel');
            $service_buy_item_model = $this->model->get('servicebuyitemModel');
            $invoice_purchase_model = $this->model->get('invoicepurchaseModel');
            $invoice_purchase_item_model = $this->model->get('invoicepurchaseitemModel');
            $invoice_service_buy_model = $this->model->get('invoiceservicebuyModel');
            $invoice_sell_model = $this->model->get('invoicesellModel');
            $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
            $invoice_buy_model = $this->model->get('invoicebuyModel');
            $invoice_buy_item_model = $this->model->get('invoicebuyitemModel');
            $payment_model = $this->model->get('paymentModel');
            $payment_item_model = $this->model->get('paymentitemModel');
            $payment_cost_model = $this->model->get('paymentcostModel');
            $tax_model = $this->model->get('taxModel');
            $invoice_model = $this->model->get('invoiceModel');
            $account_model = $this->model->get('accountModel');
            $stock_model = $this->model->get('stockModel');
            $house_model = $this->model->get('houseModel');
            $bank_balance_model = $this->model->get('bankbalanceModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $internal_transfer_model = $this->model->get('internaltransferModel');
            $internal_transfer_item_model = $this->model->get('internaltransferitemModel');

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
                            );

                            if ($val[12]>0) {
                                $data['customer_debit_dauky'] = $val[12];
                            }
                            if ($val[12]<0) {
                                $data['customer_credit_dauky'] = (0-$val[12]);
                            }

                            $customer_model->createCustomer($data);

                            $id_customer = $customer_model->getLastCustomer()->customer_id;

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
                            );

                            if ($val[12]>0) {
                                $data['customer_debit_dauky'] = $val[12];
                            }
                            if ($val[12]<0) {
                                $data['customer_credit_dauky'] = (0-$val[12]);
                            }

                            $customer_model->createCustomer($data);

                            $id_customer = $customer_model->getLastCustomer()->customer_id;

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
                        
                    }//
                }//NHACUNGCAP
                else if ($nameWorksheet=="HANGHOA") {
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
                            $acc_156 = $account_model->getAccountByWhere(array('account_number'=>'156'))->account_id;

                            $data = array(
                                        
                            'items_code' => $val[1],
                            'items_name' => $val[2],
                            'items_unit' => $val[3],
                            'items_type' => 1,
                            'items_tax' => $val[4],
                            'items_stuff' => $val[5],
                            'items_number_dauky' => $val[6],
                            'items_price_dauky' => $val[7],
                            );

                            $items_model->createItems($data);
                            $id_items = $items_model->getLastItems()->items_id;

                            if ($data['items_number_dauky']>0) {
                                $data_stock = array(
                                    'items'=>$id_items,
                                    'stock_date'=>1,
                                    'stock_item'=>$id_items,
                                    'stock_number'=>$data['items_number_dauky'],
                                    'stock_price'=>round($data['items_price_dauky']/$data['items_number_dauky'],2),
                                    'stock_house'=>1,
                                );
                                $stock_model->createStock($data_stock);

                                $data_additional = array(
                                    'items'=>$id_items,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Số dư đầu kỳ",
                                    'debit'=>$acc_156,
                                    'credit'=>0,
                                    'money'=>$data['items_price_dauky'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            
                        }
                        
                    }//
                }//HANGHOA
                else if ($nameWorksheet=="DICHVU") {
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
                            $acc_156 = $account_model->getAccountByWhere(array('account_number'=>'156'))->account_id;

                            $data = array(
                                        
                            'items_code' => $val[1],
                            'items_name' => $val[2],
                            'items_unit' => $val[3],
                            'items_type' => 2,
                            'items_tax' => $val[4],
                            'items_stuff' => null,
                            'items_number_dauky' => null,
                            'items_price_dauky' => null,
                            );

                            $items_model->createItems($data);
                            $id_items = $items_model->getLastItems()->items_id;

                            if ($data['items_number_dauky']>0) {
                                $data_stock = array(
                                    'items'=>$id_items,
                                    'stock_date'=>1,
                                    'stock_item'=>$id_items,
                                    'stock_number'=>$data['items_number_dauky'],
                                    'stock_price'=>round($data['items_price_dauky']/$data['items_number_dauky'],2),
                                    'stock_house'=>1,
                                );
                                $stock_model->createStock($data_stock);

                                $data_additional = array(
                                    'items'=>$id_items,
                                    'document_number'=>"",
                                    'document_date'=>1,
                                    'additional_date'=>1,
                                    'additional_comment'=>"Số dư đầu kỳ",
                                    'debit'=>$acc_156,
                                    'credit'=>0,
                                    'money'=>$data['items_price_dauky'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }
                            
                        }
                        
                    }//
                }//DICHVU
                else if ($nameWorksheet=="STK") {
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
                            $data = array(
                        
                            'bank_code' => $val[1],
                            'bank_name' => $val[2],
                            'account_number' => $val[3],
                            'account_bank' => $val[4],
                            'account_bank_branch' => $val[5],
                            );
                            $bank_model->createBank($data);
                        }
                        
                    }//
                }//STK
                else if ($nameWorksheet=="PHIEUKETOAN") {
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

                        if ($val[6]>0) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[3]);
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $tk = null;
                            if ($val[9]!="" && $val[9]!=NULL) {
                                $tk = $val[9]=="TM"?0:$bank_model->getBankByWhere(array('bank_code'=>$val[9]))->bank_id;
                            }
                            $check = $val[8]=="Thu"?1:($val[8]=="Chi"?2:null);
                            $cus = null;
                            if ($val[10]!="" && $val[10]!=NULL) {
                                $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[10]))->customer_id;
                            }
                            $tax = null;
                            if ($val[13]!="" && $val[13]!=NULL) {
                                $tax = $account_model->getAccountByWhere(array('account_number'=>$val[13]))->account_id;
                            }
                            $tax_date = PHPExcel_Shared_Date::ExcelToPHP($val[15]);
                            
                            $data = array(
                                'additional_other_document_number' =>$val[1],
                                'additional_other_document_date' => $doc_date,
                                'additional_other_additional_date' => $add_date,
                                'additional_other_comment' => $val[7],
                                'additional_other_debit' => $debit,
                                'additional_other_credit' => $credit,
                                'additional_other_money' => $val[6],
                                'additional_other_customer' => $cus,
                                'additional_other_bank' => $tk,
                                'additional_other_bank_check' => $check,
                                'additional_other_tax_percent' => $val[11],
                                'additional_other_tax' => $val[12],
                                'additional_other_tax_debit' => $tax,
                                'additional_other_invoice_number' => $val[14],
                                'additional_other_invoice_date' => $tax_date,
                                'additional_other_invoice_symbol' => $val[16],
                            );
                            $additional_other_model->createAdditional($data);
                            $id_additional = $additional_other_model->getLastAdditional()->additional_other_id;

                            $data_additional = array(
                                'additional_other'=>$id_additional,
                                'document_number'=>$data['additional_other_document_number'],
                                'document_date'=>$data['additional_other_document_date'],
                                'additional_date'=>$data['additional_other_additional_date'],
                                'additional_comment'=>$data['additional_other_comment'],
                                'debit'=>$data['additional_other_debit'],
                                'credit'=>$data['additional_other_credit'],
                                'money'=>$data['additional_other_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            if ($data['additional_other_bank_check']==1 && $data['additional_other_bank']>=0) {
                                $data_bank = array(
                                    'additional_other'=>$id_additional,
                                    'bank_balance_date'=>$data['additional_other_document_date'],
                                    'bank'=>$data['additional_other_bank'],
                                    'bank_balance_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                                );
                                $bank_balance_model->createBank($data_bank);
                            }
                            else if ($data['additional_other_bank_check']==2 && $data['additional_other_bank']>=0) {
                                $data_bank = array(
                                    'additional_other'=>$id_additional,
                                    'bank_balance_date'=>$data['additional_other_document_date'],
                                    'bank'=>$data['additional_other_bank'],
                                    'bank_balance_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                                );
                                $bank_balance_model->createBank($data_bank);
                            }
                            
                            if ($data['additional_other_customer']>0) {
                                if ($data['additional_other_bank_check']>0) {
                                    $data_debit = array(
                                        'additional_other'=>$id_additional,
                                        'debit_date'=>$data['additional_other_document_date'],
                                        'debit_customer'=>$data['additional_other_customer'],
                                        'debit_money'=>(0-$data['additional_other_money']-$data['additional_other_tax']),
                                        'debit_comment'=>$data['additional_other_comment'],
                                    );
                                }
                                else{
                                    $data_debit = array(
                                        'additional_other'=>$id_additional,
                                        'debit_date'=>$data['additional_other_document_date'],
                                        'debit_customer'=>$data['additional_other_customer'],
                                        'debit_money'=>$data['additional_other_money']+$data['additional_other_tax'],
                                        'debit_comment'=>$data['additional_other_comment'],
                                    );
                                }
                                $debit_model->createDebit($data_debit);
                            }

                            if ($data['additional_other_tax_debit']>0) {
                                $data_additional = array(
                                    'additional_other'=>$id_additional,
                                    'document_number'=>$data['additional_other_document_number'],
                                    'document_date'=>$data['additional_other_document_date'],
                                    'additional_date'=>$data['additional_other_additional_date'],
                                    'additional_comment'=>$data['additional_other_comment'],
                                    'debit'=>$data['additional_other_tax_debit'],
                                    'credit'=>$data['additional_other_credit'],
                                    'money'=>$data['additional_other_tax'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_invoice = array(
                                    'additional_other'=>$id_additional,
                                    'invoice_date'=>$data['additional_other_document_date'],
                                    'invoice_symbol'=>$data['additional_other_invoice_symbol'],
                                    'invoice_number'=>$data['additional_other_invoice_number'],
                                    'invoice_customer'=>$data['additional_other_customer'],
                                    'invoice_money'=>$data['additional_other_money'],
                                    'invoice_tax'=>$data['additional_other_tax'],
                                    'invoice_comment'=>$data['additional_other_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);
                            }
                        }
                        
                    }//
                }//PHIEUKETOAN
                else if ($nameWorksheet=="MUAHANGDV") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $hd_date = PHPExcel_Shared_Date::ExcelToPHP($val[4]);
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[9]))->customer_id;
                            $type = $val[12]=="Khác"?2:1;

                            $data = array(
                            'service_buy_document_date' => $doc_date,
                            'service_buy_document_number' => $val[3],
                            'service_buy_additional_date' => $add_date,
                            'service_buy_customer' => $cus,
                            'service_buy_number' => $val[6],
                            'service_buy_date' => $hd_date,
                            'service_buy_symbol' => $val[5],
                            'service_buy_bill_number' => $val[7],
                            'service_buy_contract_number' => $val[8],
                            'service_buy_money_type' => null,
                            'service_buy_money_rate' => null,
                            'service_buy_origin_doc' => $val[11],
                            'service_buy_comment' => $val[10],
                            'service_buy_type' => $type,
                            'service_buy_create_user'=>$_SESSION['userid_logined'],
                            'service_buy_create_date'=>strtotime(date('d-m-Y')),
                            );

                            $service_buy_model->createService($data);
                        }
                        
                    }//
                }//MUAHANGDV
                else if ($nameWorksheet=="CHITIET-MHDV") {
                    $money_foreign = array();
                    $money = array();
                    $tax = array();
                    $ids = "0";

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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $service_buys = $service_buy_model->getServiceByWhere(array('service_buy_document_number'=>$val[1]));
                            $id_service_buy = $service_buys->service_buy_id;
                            $ids .= ",".$id_service_buy;
                            $service_buy_item = $items_model->getItemsByWhere(array('items_code'=>$val[2]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[3]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $tax_debit = $account_model->getAccountByWhere(array('account_number'=>$val[11]))->account_id;
                            $data_item = array(
                                'service_buy' => $id_service_buy,
                                'service_buy_item' => $service_buy_item->items_id,
                                'service_buy_item_unit' => $service_buy_item->items_unit,
                                'service_buy_item_number' => $val[6],
                                'service_buy_item_price' => $val[7],
                                'service_buy_item_money' => $val[8],
                                'service_buy_item_debit' => $debit,
                                'service_buy_item_credit' => $credit,
                                'service_buy_item_tax_vat_percent' => $val[9],
                                'service_buy_item_tax_vat' => $val[10],
                                'service_buy_item_tax_vat_debit' => $tax_debit,
                                'service_buy_item_total' => $val[12],
                            );
                            $service_buy_item_model->createService($data_item);
                            $id_service_buy_item = $service_buy_item_model->getLastService()->service_buy_item_id;

                            if ($data['service_buy_type'] != 1) {
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_money'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }

                            if($data_item['service_buy_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'service_buy_item'=>$id_service_buy_item,
                                    'document_number'=>$service_buys->service_buy_document_number,
                                    'document_date'=>$service_buys->service_buy_document_date,
                                    'additional_date'=>$service_buys->service_buy_additional_date,
                                    'additional_comment'=>$service_buys->service_buy_comment,
                                    'debit'=>$data_item['service_buy_item_tax_vat_debit'],
                                    'credit'=>$data_item['service_buy_item_credit'],
                                    'money'=>$data_item['service_buy_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }


                            if ($val[5]==1) {
                                $money_type[$id_service_buy]=1;
                                $money_rate[$id_service_buy]=$val[5];
                            }
                            else{
                                $money_type[$id_service_buy]=2;
                                $money_rate[$id_service_buy]=$val[5];
                            }

                            $money_foreign[$id_service_buy] = isset($money_foreign[$id_service_buy])?$money_foreign[$id_service_buy]+$data_item['service_buy_item_price']*$data_item['service_buy_item_number']:$data_item['service_buy_item_price']*$data_item['service_buy_item_number'];
                            $money[$id_service_buy] = isset($money[$id_service_buy])?$money[$id_service_buy]+$data_item['service_buy_item_money']:$data_item['service_buy_item_money'];
                            $tax[$id_service_buy] = isset($tax[$id_service_buy])?$tax[$id_service_buy]+$data_item['service_buy_item_tax_vat']:$data_item['service_buy_item_tax_vat'];

                        }
                        
                    }//
                    $service_buys = $service_buy_model->getAllService(array('where'=>'service_buy_id IN ('.$ids.')'));
                    foreach ($service_buys as $service_buy) {
                        $data_buy = array(
                            'service_buy_money_type' => $money_type[$service_buy->service_buy_id],
                            'service_buy_money_rate' => $money_rate[$service_buy->service_buy_id],
                            'service_buy_money'=>$money[$service_buy->service_buy_id],
                            'service_buy_money_foreign'=>$money_foreign[$service_buy->service_buy_id],
                            'service_buy_tax_vat'=>$tax[$service_buy->service_buy_id],
                            'service_buy_total'=>($money[$service_buy->service_buy_id]+$tax[$service_buy->service_buy_id]),
                        );
                        $service_buy_model->updateService($data_buy,array('service_buy_id'=>$service_buy->service_buy_id));

                        $data_debit = array(
                            'service_buy'=>$service_buy->service_buy_id,
                            'debit_date'=>$service_buy->service_buy_document_date,
                            'debit_customer'=>$service_buy->service_buy_customer,
                            'debit_money'=>($money[$service_buy->service_buy_id]+$tax[$service_buy->service_buy_id]),
                            'debit_comment'=>$service_buy->service_buy_comment,
                        );

                        if ($money_rate[$service_buy->service_buy_id] > 1) {
                            $data_debit['debit_money_foreign'] = $money_foreign[$service_buy->service_buy_id];
                        }

                        $debit_model->createDebit($data_debit);

                        $data_invoice = array(
                            'service_buy'=>$service_buy->service_buy_id,
                            'invoice_symbol'=>$service_buy->service_buy_symbol,
                            'invoice_date'=>$service_buy->service_buy_date,
                            'invoice_number'=>$service_buy->service_buy_number,
                            'invoice_customer'=>$service_buy->service_buy_customer,
                            'invoice_money'=>$money[$service_buy->service_buy_id],
                            'invoice_tax'=>$tax[$service_buy->service_buy_id],
                            'invoice_comment'=>$service_buy->service_buy_comment,
                            'invoice_type'=>1,
                        );

                        $invoice_model->createInvoice($data_invoice);
                        
                    }
                    
                }//CHITIET-MHDV
                else if ($nameWorksheet=="HDMH") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $hd_date = PHPExcel_Shared_Date::ExcelToPHP($val[4]);
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[9]))->customer_id;

                            $data = array(
                        
                            'invoice_purchase_document_date' => $doc_date,
                            'invoice_purchase_document_number' => $val[3],
                            'invoice_purchase_additional_date' => $add_date,
                            'invoice_purchase_customer' => $cus,
                            'invoice_purchase_number' => $val[6],
                            'invoice_purchase_date' => $hd_date,
                            'invoice_purchase_symbol' => $val[5],
                            'invoice_purchase_bill_number' => $val[7],
                            'invoice_purchase_contract_number' => $val[8],
                            'invoice_purchase_money_type' => null,
                            'invoice_purchase_money_rate' => null,
                            'invoice_purchase_origin_doc' => $val[11],
                            'invoice_purchase_comment' => $val[10],
                            'invoice_purchase_allocation' => 2,
                            'invoice_purchase_create_user'=>$_SESSION['userid_logined'],
                            'invoice_purchase_create_date'=>strtotime(date('d-m-Y')),
                            );
                            $invoice_purchase_model->createInvoice($data);
                        }
                        
                    }//
                }//HDMH
                else if ($nameWorksheet=="CHITIET-HDMH") {
                    $cost = array();
                    $money = array();
                    $tax = array();
                    $number = array();
                    $ids = "0";

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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $invoice_purchases = $invoice_purchase_model->getInvoiceByWhere(array('invoice_purchase_document_number'=>$val[1]));
                            $id_invoice_purchase = $invoice_purchases->invoice_purchase_id;
                            $ids .= ",".$id_invoice_purchase;
                            $invoice_purchase_item = $items_model->getItemsByWhere(array('items_code'=>$val[2]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $tax_debit = $account_model->getAccountByWhere(array('account_number'=>$val[12]))->account_id;
                            $house = $house_model->getHouseByWhere(array('house_code'=>$val[3]))->house_id;
                            $data_item = array(
                                'invoice_purchase' => $id_invoice_purchase,
                                'invoice_purchase_item' => $invoice_purchase_item->items_id,
                                'invoice_purchase_item_unit' => $invoice_purchase_item->items_unit,
                                'invoice_purchase_item_number' => $val[7],
                                'invoice_purchase_item_price' => $val[8],
                                'invoice_purchase_item_money' => $val[9],
                                'invoice_purchase_item_debit' => $debit,
                                'invoice_purchase_item_credit' => $credit,
                                'invoice_purchase_item_tax_vat_percent' => $val[10],
                                'invoice_purchase_item_tax_vat' => $val[11],
                                'invoice_purchase_item_tax_vat_debit' => $tax_debit,
                                'invoice_purchase_item_total' => $val[14],
                                'invoice_purchase_item_cost' => $val[13],
                                'invoice_purchase_item_house' => $house,
                            );
                            $invoice_purchase_item_model->createInvoice($data_item);
                            $id_invoice_purchase_item = $invoice_purchase_item_model->getLastInvoice()->invoice_purchase_item_id;

                            $data_additional = array(
                                'invoice_purchase_item'=>$id_invoice_purchase_item,
                                'document_number'=>$invoice_purchases->invoice_purchase_document_number,
                                'document_date'=>$invoice_purchases->invoice_purchase_document_date,
                                'additional_date'=>$invoice_purchases->invoice_purchase_additional_date,
                                'additional_comment'=>$invoice_purchases->invoice_purchase_comment,
                                'debit'=>$data_item['invoice_purchase_item_debit'],
                                'credit'=>$data_item['invoice_purchase_item_credit'],
                                'money'=>$data_item['invoice_purchase_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_stock = array(
                                'invoice_purchase_item'=>$id_invoice_purchase_item,
                                'stock_date'=>$invoice_purchases->invoice_purchase_document_date,
                                'stock_item'=>$data_item['invoice_purchase_item'],
                                'stock_number'=>$data_item['invoice_purchase_item_number'],
                                'stock_price'=>$data_item['invoice_purchase_item_total'],
                                'stock_house'=>$data_item['invoice_purchase_item_house'],
                            );
                            $stock_model->createStock($data_stock);

                            if($data_item['invoice_purchase_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'invoice_purchase_item'=>$id_invoice_purchase_item,
                                    'document_number'=>$invoice_purchases->invoice_purchase_document_number,
                                    'document_date'=>$invoice_purchases->invoice_purchase_document_date,
                                    'additional_date'=>$invoice_purchases->invoice_purchase_additional_date,
                                    'additional_comment'=>$invoice_purchases->invoice_purchase_comment,
                                    'debit'=>$data_item['invoice_purchase_item_tax_vat_debit'],
                                    'credit'=>$data_item['invoice_purchase_item_credit'],
                                    'money'=>$data_item['invoice_purchase_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }


                            if ($val[6]==1) {
                                $money_type[$id_invoice_purchase]=1;
                                $money_rate[$id_invoice_purchase]=$val[6];
                            }
                            else{
                                $money_type[$id_invoice_purchase]=2;
                                $money_rate[$id_invoice_purchase]=$val[6];
                            }

                            $money[$id_invoice_purchase] = isset($money[$id_invoice_purchase])?$money[$id_invoice_purchase]+$data_item['invoice_purchase_item_money']:$data_item['invoice_purchase_item_money'];
                            $tax[$id_invoice_purchase] = isset($tax[$id_invoice_purchase])?$tax[$id_invoice_purchase]+$data_item['invoice_purchase_item_tax_vat']:$data_item['invoice_purchase_item_tax_vat'];
                            $cost[$id_invoice_purchase] = isset($cost[$id_invoice_purchase])?$cost[$id_invoice_purchase]+$data_item['invoice_purchase_item_cost']:$data_item['invoice_purchase_item_cost'];
                            $number[$id_invoice_purchase] = isset($number[$id_invoice_purchase])?$number[$id_invoice_purchase]+$data_item['invoice_purchase_item_number']:$data_item['invoice_purchase_item_number'];

                        }
                        
                    }//
                    $invoice_purchases = $invoice_purchase_model->getAllInvoice(array('where'=>'invoice_purchase_id IN ('.$ids.')'));
                    foreach ($invoice_purchases as $invoice_purchase) {
                        $data_buy = array(
                            'invoice_purchase_money_type' => $money_type[$invoice_purchase->invoice_purchase_id],
                            'invoice_purchase_money_rate' => $money_rate[$invoice_purchase->invoice_purchase_id],
                            'invoice_purchase_number_total'=>$number[$invoice_purchase->invoice_purchase_id],
                            'invoice_purchase_money'=>$money[$invoice_purchase->invoice_purchase_id],
                            'invoice_purchase_tax_vat'=>$tax[$invoice_purchase->invoice_purchase_id],
                            'invoice_purchase_cost'=>$cost[$invoice_purchase->invoice_purchase_id],
                            'invoice_purchase_total'=>($money[$invoice_purchase->invoice_purchase_id]+$tax[$invoice_purchase->invoice_purchase_id]+$cost[$invoice_purchase->invoice_purchase_id]),
                        );
                        $invoice_purchase_model->updateInvoice($data_buy,array('invoice_purchase_id'=>$invoice_purchase->invoice_purchase_id));

                        $data_debit = array(
                            'invoice_purchase'=>$invoice_purchase->invoice_purchase_id,
                            'debit_date'=>$invoice_purchase->invoice_purchase_document_date,
                            'debit_customer'=>$invoice_purchase->invoice_purchase_customer,
                            'debit_money'=>($money[$invoice_purchase->invoice_purchase_id]+$tax[$invoice_purchase->invoice_purchase_id]),
                            'debit_comment'=>$invoice_purchase->invoice_purchase_comment,
                        );

                        $debit_model->createDebit($data_debit);

                        $data_invoice = array(
                            'invoice_purchase'=>$invoice_purchase->invoice_purchase_id,
                            'invoice_symbol'=>$invoice_purchase->invoice_purchase_symbol,
                            'invoice_date'=>$invoice_purchase->invoice_purchase_date,
                            'invoice_number'=>$invoice_purchase->invoice_purchase_number,
                            'invoice_customer'=>$invoice_purchase->invoice_purchase_customer,
                            'invoice_money'=>$money[$invoice_purchase->invoice_purchase_id],
                            'invoice_tax'=>$tax[$invoice_purchase->invoice_purchase_id],
                            'invoice_comment'=>$invoice_purchase->invoice_purchase_comment,
                            'invoice_type'=>1,
                        );

                        $invoice_model->createInvoice($data_invoice);
                        
                    }
                    
                }//CHITIET-HDMH
                else if ($nameWorksheet=="PHANBO-CPMH") {
                    $cost_invoice = array();

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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $invoice_purchases = $invoice_purchase_model->getInvoiceByWhere(array('invoice_purchase_number'=>$val[1]));
                            $service_buys = $service_buy_model->getServiceByWhere(array('service_buy_number'=>$val[2]));

                            $money = $val[3];
                            $cost_invoice[$invoice_purchases->invoice_purchase_id] = isset($cost_invoice[$invoice_purchases->invoice_purchase_id])?$cost_invoice[$invoice_purchases->invoice_purchase_id]+$money:$money;

                            $data_service = array(
                                'invoice_purchase'=>$invoice_purchases->invoice_purchase_id,
                                'service_buy'=>$service_buys->service_buy_id,
                                'invoice_service_buy_money'=>$money,
                            );
                            $invoice_service_buy_model->createInvoice($data_service);
                        }
                        
                    }//

                    if(count($cost_invoice)>0){
                        $invoice_purchases = $invoice_purchase_model->getAllInvoice();
                        foreach ($invoice_purchases as $invoice_purchase) {
                            if ($invoice_purchase->invoice_purchase_cost<1) {
                                $num = 0;
                                $invoice_purchase_items = $invoice_purchase_item_model->getAllInvoice(array('where'=>'invoice_purchase='.$invoice_purchase->invoice_purchase_id));
                                foreach ($invoice_purchase_items as $invoice_purchase_item) {
                                    $num += $invoice_purchase_item->invoice_purchase_item_number;
                                }

                                $cost = round($cost_invoice[$invoice_purchase->invoice_purchase_id]/$num,2);

                                foreach ($invoice_purchase_items as $invoice_purchase_item) {
                                    $data_item = array(
                                        'invoice_purchase_item_cost'=>$cost,
                                        'invoice_purchase_item_total'=>$invoice_purchase_item->invoice_purchase_item_total+$cost,
                                    );
                                    $invoice_purchase_item_model->updateInvoice($data_item,array('invoice_purchase_item_id'=>$invoice_purchase_item->invoice_purchase_item_id));

                                    $stock_model->updateStock(array('stock_price'=>$data_item['invoice_purchase_item_total']),array('invoice_purchase_item'=>$invoice_purchase_item->invoice_purchase_item_id));
                                }

                                $data_buy = array(
                                    'invoice_purchase_cost'=>$money[$invoice_purchase->invoice_purchase_id],
                                    'invoice_purchase_total'=>($invoice_purchase->invoice_purchase_money+$invoice_purchase->invoice_purchase_tax_vat+$money[$invoice_purchase->invoice_purchase_id]),
                                );
                                $invoice_purchase_model->updateInvoice($data_buy,array('invoice_purchase_id'=>$invoice_purchase->invoice_purchase_id));
                            }
                            
                        }
                    }
                }//PHANBO-CPMH
                else if ($nameWorksheet=="HDBH") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $hd_date = PHPExcel_Shared_Date::ExcelToPHP($val[4]);
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[9]))->customer_id;

                            $data = array(
                        
                            'invoice_sell_document_date' => $doc_date,
                            'invoice_sell_document_number' => $val[3],
                            'invoice_sell_additional_date' => $add_date,
                            'invoice_sell_customer' => $cus,
                            'invoice_sell_number' => $val[6],
                            'invoice_sell_date' => $hd_date,
                            'invoice_sell_symbol' => $val[5],
                            'invoice_sell_bill_number' => $val[7],
                            'invoice_sell_contract_number' => $val[8],
                            'invoice_sell_money_type' => null,
                            'invoice_sell_money_rate' => null,
                            'invoice_sell_origin_doc' => $val[11],
                            'invoice_sell_comment' => $val[10],
                            'invoice_sell_create_user' => $_SESSION['userid_logined'],
                            'invoice_sell_create_date' => strtotime(date('d-m-Y')),
                            );
                            $invoice_sell_model->createInvoice($data);
                        }
                        
                    }//
                }//HDBH
                else if ($nameWorksheet=="CHITIET-HDBH") {
                    $money = array();
                    $tax = array();
                    $number = array();
                    $ids = "0";

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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $invoice_sells = $invoice_sell_model->getInvoiceByWhere(array('invoice_sell_document_number'=>$val[1]));
                            $id_invoice_sell = $invoice_sells->invoice_sell_id;
                            $ids .= ",".$id_invoice_sell;
                            $invoice_sell_item = $items_model->getItemsByWhere(array('items_code'=>$val[2]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[3]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $tax_debit = $account_model->getAccountByWhere(array('account_number'=>$val[11]))->account_id;
                            $house = $house_model->getHouseByWhere(array('house_code'=>$val[13]))->house_id;
                            $house_debit = $account_model->getAccountByWhere(array('account_number'=>$val[14]))->account_id;
                            $house_credit = $account_model->getAccountByWhere(array('account_number'=>$val[15]))->account_id;
                            $data_item = array(
                                'invoice_sell' => $id_invoice_sell,
                                'invoice_sell_item' => $invoice_sell_item->items_id,
                                'invoice_sell_item_unit' => $invoice_sell_item->items_unit,
                                'invoice_sell_item_number' => $val[6],
                                'invoice_sell_item_price' => $val[7],
                                'invoice_sell_item_money' => $val[8],
                                'invoice_sell_item_debit' => $debit,
                                'invoice_sell_item_credit' => $credit,
                                'invoice_sell_item_tax_vat_percent' => $val[9],
                                'invoice_sell_item_tax_vat' => $val[10],
                                'invoice_sell_item_tax_vat_debit' => $tax_debit,
                                'invoice_sell_item_total' => $val[12],
                                'invoice_sell_item_house_debit' => $house_debit,
                                'invoice_sell_item_house_credit' => $house_credit,
                                'invoice_sell_item_house' => $house,
                                'invoice_sell_item_house_money' => $val[16],
                            );
                            $invoice_sell_item_model->createInvoice($data_item);
                            $id_invoice_sell_item = $invoice_sell_item_model->getLastInvoice()->invoice_sell_item_id;

                            $data_additional = array(
                                'invoice_sell_item'=>$id_invoice_sell_item,
                                'document_number'=>$invoice_sells->invoice_sell_document_number,
                                'document_date'=>$invoice_sells->invoice_sell_document_date,
                                'additional_date'=>$invoice_sells->invoice_sell_additional_date,
                                'additional_comment'=>$invoice_sells->invoice_sell_comment,
                                'debit'=>$data_item['invoice_sell_item_debit'],
                                'credit'=>$data_item['invoice_sell_item_credit'],
                                'money'=>$data_item['invoice_sell_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_additional = array(
                                'invoice_sell_item'=>$id_invoice_sell_item,
                                'document_number'=>$invoice_sells->invoice_sell_document_number,
                                'document_date'=>$invoice_sells->invoice_sell_document_date,
                                'additional_date'=>$invoice_sells->invoice_sell_additional_date,
                                'additional_comment'=>$invoice_sells->invoice_sell_comment,
                                'debit'=>$data_item['invoice_sell_item_house_debit'],
                                'credit'=>$data_item['invoice_sell_item_house_credit'],
                                'money'=>($data_item['invoice_sell_item_house_money']*$data_item['invoice_sell_item_number']),
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_stock = array(
                                'invoice_sell_item'=>$id_invoice_sell_item,
                                'stock_date'=>$invoice_sells->invoice_sell_document_date,
                                'stock_item'=>$data_item['invoice_sell_item'],
                                'stock_number'=>$data_item['invoice_sell_item_number'],
                                'stock_price'=>$data_item['invoice_sell_item_house_money'],
                                'stock_house'=>$data_item['invoice_sell_item_house'],
                            );
                            $stock_model->createStock($data_stock);

                            if($data_item['invoice_sell_item_tax_vat_debit'] > 0){
                                $data_additional = array(
                                    'invoice_sell_item'=>$id_invoice_sell_item,
                                    'document_number'=>$invoice_sells->invoice_sell_document_number,
                                    'document_date'=>$invoice_sells->invoice_sell_document_date,
                                    'additional_date'=>$invoice_sells->invoice_sell_additional_date,
                                    'additional_comment'=>$invoice_sells->invoice_sell_comment,
                                    'debit'=>$data_item['invoice_sell_item_debit'],
                                    'credit'=>$data_item['invoice_sell_item_tax_vat_debit'],
                                    'money'=>$data_item['invoice_sell_item_tax_vat'],
                                );
                                $additional_model->createAdditional($data_additional);
                            }


                            if ($val[5]==1) {
                                $money_type[$id_invoice_sell]=1;
                                $money_rate[$id_invoice_sell]=$val[5];
                            }
                            else{
                                $money_type[$id_invoice_sell]=2;
                                $money_rate[$id_invoice_sell]=$val[5];
                            }

                            $money[$id_invoice_sell] = isset($money[$id_invoice_sell])?$money[$id_invoice_sell]+$data_item['invoice_sell_item_money']:$data_item['invoice_sell_item_money'];
                            $tax[$id_invoice_sell] = isset($tax[$id_invoice_sell])?$tax[$id_invoice_sell]+$data_item['invoice_sell_item_tax_vat']:$data_item['invoice_sell_item_tax_vat'];
                            $number[$id_invoice_sell] = isset($number[$id_invoice_sell])?$number[$id_invoice_sell]+$data_item['invoice_sell_item_number']:$data_item['invoice_sell_item_number'];

                        }
                        
                    }//
                    $invoice_sells = $invoice_sell_model->getAllInvoice(array('where'=>'invoice_sell_id IN ('.$ids.')'));
                    foreach ($invoice_sells as $invoice_sell) {
                        $data_buy = array(
                            'invoice_sell_money_type' => $money_type[$invoice_sell->invoice_sell_id],
                            'invoice_sell_money_rate' => $money_rate[$invoice_sell->invoice_sell_id],
                            'invoice_sell_number_total'=>$number[$invoice_sell->invoice_sell_id],
                            'invoice_sell_money'=>$money[$invoice_sell->invoice_sell_id],
                            'invoice_sell_tax_vat'=>$tax[$invoice_sell->invoice_sell_id],
                            'invoice_sell_total'=>($money[$invoice_sell->invoice_sell_id]+$tax[$invoice_sell->invoice_sell_id]),
                        );
                        $invoice_sell_model->updateInvoice($data_buy,array('invoice_sell_id'=>$invoice_sell->invoice_sell_id));

                        $data_debit = array(
                            'invoice_sell'=>$invoice_sell->invoice_sell_id,
                            'debit_date'=>$invoice_sell->invoice_sell_document_date,
                            'debit_customer'=>$invoice_sell->invoice_sell_customer,
                            'debit_money'=>($money[$invoice_sell->invoice_sell_id]+$tax[$invoice_sell->invoice_sell_id]),
                            'debit_comment'=>$invoice_sell->invoice_sell_comment,
                        );

                        $debit_model->createDebit($data_debit);

                        $data_invoice = array(
                            'invoice_sell'=>$invoice_sell->invoice_sell_id,
                            'invoice_symbol'=>$invoice_sell->invoice_sell_symbol,
                            'invoice_date'=>$invoice_sell->invoice_sell_date,
                            'invoice_number'=>$invoice_sell->invoice_sell_number,
                            'invoice_customer'=>$invoice_sell->invoice_sell_customer,
                            'invoice_money'=>$money[$invoice_sell->invoice_sell_id],
                            'invoice_tax'=>$tax[$invoice_sell->invoice_sell_id],
                            'invoice_comment'=>$invoice_sell->invoice_sell_comment,
                            'invoice_type'=>2,
                        );

                        $invoice_model->createInvoice($data_invoice);
                        
                    }
                    
                }//CHITIET-HDBH
                else if ($nameWorksheet=="NHAPKHAU") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $hd_date = PHPExcel_Shared_Date::ExcelToPHP($val[4]);
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[9]))->customer_id;

                            $data = array(
                        
                            'invoice_buy_document_date' => $doc_date,
                            'invoice_buy_document_number' => $val[3],
                            'invoice_buy_additional_date' => $add_date,
                            'invoice_buy_customer' => $cus,
                            'invoice_buy_number' => $val[6],
                            'invoice_buy_date' => $hd_date,
                            'invoice_buy_symbol' => $val[5],
                            'invoice_buy_bill_number' => $val[7],
                            'invoice_buy_contract_number' => $val[8],
                            'invoice_buy_money_type' => null,
                            'invoice_buy_money_rate' => null,
                            'invoice_buy_origin_doc' => $val[11],
                            'invoice_buy_comment' => $val[10],
                            'invoice_buy_allocation' => 2,
                            'invoice_buy_allocation2' => 2,
                            'invoice_buy_create_user'=>$_SESSION['userid_logined'],
                            'invoice_buy_create_date'=>strtotime(date('d-m-Y')),
                            );
                            $invoice_buy_model->createInvoice($data);
                        }
                        
                    }//
                }//NHAPKHAU
                else if ($nameWorksheet=="CHITIET-NHAPKHAU") {
                    $cost = array();
                    $money = array();
                    $tax = array();
                    $money_foreign = array();
                    $import = array();
                    $custom_cost = array();
                    $other_cost = array();
                    $number = array();
                    $ids = "0";

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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $invoice_buys = $invoice_buy_model->getInvoiceByWhere(array('invoice_buy_document_number'=>$val[1]));
                            $id_invoice_buy = $invoice_buys->invoice_buy_id;
                            $ids .= ",".$id_invoice_buy;
                            $invoice_buy_item = $items_model->getItemsByWhere(array('items_code'=>$val[2]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $tax_debit = $account_model->getAccountByWhere(array('account_number'=>$val[19]))->account_id;
                            $tax_credit = $account_model->getAccountByWhere(array('account_number'=>$val[20]))->account_id;
                            $import_debit = $account_model->getAccountByWhere(array('account_number'=>$val[16]))->account_id;
                            $house = $house_model->getHouseByWhere(array('house_code'=>$val[3]))->house_id;
                            $data_item = array(
                                'invoice_buy' => $id_invoice_buy,
                                'invoice_buy_item' => $invoice_buy_item->items_id,
                                'invoice_buy_item_house' => $house,
                                'invoice_buy_item_unit' => $invoice_buy_item->items_unit,
                                'invoice_buy_item_number' => $val[7],
                                'invoice_buy_item_price' => $val[8],
                                'invoice_buy_item_money' => $val[9],
                                'invoice_buy_item_debit' => $debit,
                                'invoice_buy_item_credit' => $credit,
                                'invoice_buy_item_custom_cost' => $val[10],
                                'invoice_buy_item_custom_cost_money' => $val[11],
                                'invoice_buy_item_other_cost' => $val[12],
                                'invoice_buy_item_custom_rate' => $val[13],
                                'invoice_buy_item_tax_import_percent' => $val[14],
                                'invoice_buy_item_tax_import' => $val[15],
                                'invoice_buy_item_tax_import_debit' => $import_debit,
                                'invoice_buy_item_tax_vat_percent' => $val[17],
                                'invoice_buy_item_tax_vat' => $val[18],
                                'invoice_buy_item_tax_vat_debit' => $tax_debit,
                                'invoice_buy_item_tax_vat_credit' => $tax_credit,
                                'invoice_buy_item_tax_total' => $val[18]+$val[15],
                                'invoice_buy_item_total' => $val[21],
                            );
                            $invoice_buy_item_model->createInvoice($data_item);
                            $id_invoice_buy_item = $invoice_buy_item_model->getLastInvoice()->invoice_buy_item_id;

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_debit'],
                                'credit'=>$data_item['invoice_buy_item_credit'],
                                'money'=>($data_item['invoice_buy_item_money']+round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2)+round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2)),
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_debit'],
                                'credit'=>$data_item['invoice_buy_item_tax_import_debit'],
                                'money'=>$data_item['invoice_buy_item_tax_import'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_additional = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'document_number'=>$invoice_buys->invoice_buy_document_number,
                                'document_date'=>$invoice_buys->invoice_buy_document_date,
                                'additional_date'=>$invoice_buys->invoice_buy_additional_date,
                                'additional_comment'=>$invoice_buys->invoice_buy_comment,
                                'debit'=>$data_item['invoice_buy_item_tax_vat_debit'],
                                'credit'=>$data_item['invoice_buy_item_tax_vat_credit'],
                                'money'=>$data_item['invoice_buy_item_tax_vat'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_stock = array(
                                'invoice_buy_item'=>$id_invoice_buy_item,
                                'stock_date'=>$invoice_buys->invoice_buy_document_date,
                                'stock_item'=>$data_item['invoice_buy_item'],
                                'stock_number'=>$data_item['invoice_buy_item_number'],
                                'stock_price'=>$data_item['invoice_buy_item_total'],
                                'stock_house'=>$data_item['invoice_buy_item_house'],
                            );
                            $stock_model->createStock($data_stock);


                            if ($val[6]==1) {
                                $money_type[$id_invoice_buy]=1;
                                $money_rate[$id_invoice_buy]=$val[6];
                            }
                            else{
                                $money_type[$id_invoice_buy]=2;
                                $money_rate[$id_invoice_buy]=$val[6];
                            }

                            $money[$id_invoice_buy] = isset($money[$id_invoice_buy])?$money[$id_invoice_buy]+$data_item['invoice_buy_item_money']:$data_item['invoice_buy_item_money'];
                            $tax[$id_invoice_buy] = isset($tax[$id_invoice_buy])?$tax[$id_invoice_buy]+$data_item['invoice_buy_item_tax_vat']:$data_item['invoice_buy_item_tax_vat'];
                            $money_foreign[$id_invoice_buy] = isset($money_foreign[$id_invoice_buy])?$money_foreign[$id_invoice_buy]+$data_item['invoice_buy_item_price']*$data_item['invoice_buy_item_number']:$data_item['invoice_buy_item_price']*$data_item['invoice_buy_item_number'];
                            $import[$id_invoice_buy] = isset($import[$id_invoice_buy])?$import[$id_invoice_buy]+$data_item['invoice_buy_item_tax_import']:$data_item['invoice_buy_item_tax_import'];
                            $custom_cost[$id_invoice_buy] = isset($custom_cost[$id_invoice_buy])?$custom_cost[$id_invoice_buy]+round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2):round($data_item['invoice_buy_item_custom_cost_money']*$data_item['invoice_buy_item_number'],2);
                            $other_cost[$id_invoice_buy] = isset($other_cost[$id_invoice_buy])?$other_cost[$id_invoice_buy]+round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2):round($data_item['invoice_buy_item_other_cost']*$data_item['invoice_buy_item_number'],2);
                            $number[$id_invoice_buy] = isset($number[$id_invoice_buy])?$number[$id_invoice_buy]+$data_item['invoice_buy_item_number']:$data_item['invoice_buy_item_number'];
                        }
                        
                    }//
                    $invoice_buys = $invoice_buy_model->getAllInvoice(array('where'=>'invoice_buy_id IN ('.$ids.')'));
                    foreach ($invoice_buys as $invoice_buy) {
                        $data_buy = array(
                            'invoice_buy_money_type'=>$money_type[$invoice_buy->invoice_buy_id],
                            'invoice_buy_money_rate'=>$money_rate[$invoice_buy->invoice_buy_id],
                            'invoice_buy_number_total'=>$number[$invoice_buy->invoice_buy_id],
                            'invoice_buy_money'=>$money[$invoice_buy->invoice_buy_id],
                            'invoice_buy_money_foreign'=>$money_foreign[$invoice_buy->invoice_buy_id],
                            'invoice_buy_tax_vat'=>$tax[$invoice_buy->invoice_buy_id],
                            'invoice_buy_tax_import'=>$import[$invoice_buy->invoice_buy_id],
                            'invoice_buy_custom_cost'=>$custom_cost[$invoice_buy->invoice_buy_id],
                            'invoice_buy_other_cost'=>$other_cost[$invoice_buy->invoice_buy_id],
                            'invoice_buy_total'=>($money[$invoice_buy->invoice_buy_id]+$tax[$invoice_buy->invoice_buy_id]+$import[$invoice_buy->invoice_buy_id]+$custom_cost[$invoice_buy->invoice_buy_id]+$other_cost[$invoice_buy->invoice_buy_id]),
                        );
                        $invoice_buy_model->updateInvoice($data_buy,array('invoice_buy_id'=>$invoice_buy->invoice_buy_id));

                        $data_debit = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'debit_date'=>$invoice_buy->invoice_buy_document_date,
                            'debit_customer'=>$invoice_buy->invoice_buy_customer,
                            'debit_money'=>$money[$invoice_buy->invoice_buy_id],
                            'debit_money_foreign'=>$money_foreign[$invoice_buy->invoice_buy_id],
                            'debit_comment'=>$invoice_buy->invoice_buy_comment,
                        );

                        $debit_model->createDebit($data_debit);

                        $data_invoice = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'invoice_symbol'=>$invoice_buy->invoice_buy_symbol,
                            'invoice_date'=>$invoice_buy->invoice_buy_date,
                            'invoice_number'=>$invoice_buy->invoice_buy_number,
                            'invoice_customer'=>$invoice_buy->invoice_buy_customer,
                            'invoice_money'=>($money[$invoice_buy->invoice_buy_id]+$custom_cost[$invoice_buy->invoice_buy_id]+$import[$invoice_buy->invoice_buy_id]),
                            'invoice_tax'=>$tax[$invoice_buy->invoice_buy_id],
                            'invoice_comment'=>$invoice_buy->invoice_buy_comment,
                            'invoice_type'=>1,
                        );

                        $invoice_model->createInvoice($data_invoice);

                        $data_tax_import = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'tax_date'=>$invoice_buy->invoice_buy_date,
                            'tax_money'=>$import[$invoice_buy->invoice_buy_id],
                            'tax_comment'=>$invoice_buy->invoice_buy_comment,
                            'tax_type'=>1,
                        );
                        $tax_model->createTax($data_tax_import);
                        $data_tax_vat = array(
                            'invoice_buy'=>$invoice_buy->invoice_buy_id,
                            'tax_date'=>$invoice_buy->invoice_buy_date,
                            'tax_money'=>$tax[$invoice_buy->invoice_buy_id],
                            'tax_comment'=>$invoice_buy->invoice_buy_comment,
                            'tax_type'=>2,
                        );
                        $tax_model->createTax($data_tax_vat);
                        
                    }
                    
                }//CHITIET-NHAPKHAU
                else if ($nameWorksheet=="PHANBO-TRUOCHQ") {
                    $cost_invoice = array();
                    $cost_invoice_foreign = array();

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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $invoice_buys = $invoice_buy_model->getInvoiceByWhere(array('invoice_buy_number'=>$val[1]));
                            $service_buys = $service_buy_model->getServiceByWhere(array('service_buy_number'=>$val[2]));

                            $money = $val[4];
                            $money_foreign = $val[3];
                            $cost_invoice[$invoice_buys->invoice_buy_id] = isset($cost_invoice[$invoice_buys->invoice_buy_id])?$cost_invoice[$invoice_buys->invoice_buy_id]+$money:$money;
                            $cost_invoice_foreign[$invoice_buys->invoice_buy_id] = isset($cost_invoice_foreign[$invoice_buys->invoice_buy_id])?$cost_invoice_foreign[$invoice_buys->invoice_buy_id]+$money_foreign:$money_foreign;

                            $data_service = array(
                                'invoice_buy'=>$invoice_buys->invoice_buy_id,
                                'service_buy'=>$service_buys->service_buy_id,
                                'invoice_service_buy_money'=>$money,
                                'invoice_service_buy_money_foreign'=>$money_foreign,
                                'invoice_buy_type'=>1,
                            );
                            $invoice_service_buy_model->createInvoice($data_service);
                        }
                        
                    }//

                    if(count($cost_invoice)>0){
                        $invoice_buys = $invoice_buy_model->getAllInvoice();
                        foreach ($invoice_buys as $invoice_buy) {
                            if ($invoice_buy->invoice_buy_custom_cost<1) {
                                $num = 0;
                                $invoice_buy_items = $invoice_buy_item_model->getAllInvoice(array('where'=>'invoice_buy='.$invoice_buy->invoice_buy_id));
                                foreach ($invoice_buy_items as $invoice_buy_item) {
                                    $num += $invoice_buy_item->invoice_buy_item_number;
                                }

                                $cost = round($cost_invoice[$invoice_buy->invoice_buy_id]/$num,2);
                                $cost_foreign = round($cost_invoice_foreign[$invoice_buy->invoice_buy_id]/$num,2);

                                foreach ($invoice_buy_items as $invoice_buy_item) {
                                    $data_item = array(
                                        'invoice_buy_item_custom_cost_money'=>$cost,
                                        'invoice_buy_item_custom_cost'=>$cost_foreign,
                                        'invoice_buy_item_total'=>$invoice_buy_item->invoice_buy_item_total+$cost,
                                    );
                                    $invoice_buy_item_model->updateInvoice($data_item,array('invoice_buy_item_id'=>$invoice_buy_item->invoice_buy_item_id));

                                    $stock_model->updateStock(array('stock_price'=>$data_item['invoice_buy_item_total']),array('invoice_buy_item'=>$invoice_buy_item->invoice_buy_item_id));
                                }

                                $data_buy = array(
                                    'invoice_buy_custom_cost'=>$money[$invoice_buy->invoice_buy_id],
                                    'invoice_buy_total'=>($invoice_buy->invoice_buy_money+$invoice_buy->invoice_buy_tax_vat+$invoice_buy->invoice_buy_tax_import+$money[$invoice_buy->invoice_buy_id]),
                                );
                                $invoice_buy_model->updateInvoice($data_buy,array('invoice_buy_id'=>$invoice_buy->invoice_buy_id));
                            }
                            
                        }    
                    }
                    
                }//PHANBO-TRUOCHQ
                else if ($nameWorksheet=="PHANBO-VEKHO") {
                    $cost_invoice = array();

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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $invoice_buys = $invoice_buy_model->getInvoiceByWhere(array('invoice_buy_number'=>$val[1]));
                            $service_buys = $service_buy_model->getServiceByWhere(array('service_buy_number'=>$val[2]));

                            $money = $val[4];
                            $cost_invoice[$invoice_buys->invoice_buy_id] = isset($cost_invoice[$invoice_buys->invoice_buy_id])?$cost_invoice[$invoice_buys->invoice_buy_id]+$money:$money;
                           
                            $data_service = array(
                                'invoice_buy'=>$invoice_buys->invoice_buy_id,
                                'service_buy'=>$service_buys->service_buy_id,
                                'invoice_service_buy_money'=>$money,
                                'invoice_buy_type'=>2,
                            );
                            $invoice_service_buy_model->createInvoice($data_service);
                        }
                        
                    }//

                    if(count($cost_invoice)>0){
                        $invoice_buys = $invoice_buy_model->getAllInvoice();
                        foreach ($invoice_buys as $invoice_buy) {
                            if ($invoice_buy->invoice_buy_custom_cost<1) {
                                $num = 0;
                                $invoice_buy_items = $invoice_buy_item_model->getAllInvoice(array('where'=>'invoice_buy='.$invoice_buy->invoice_buy_id));
                                foreach ($invoice_buy_items as $invoice_buy_item) {
                                    $num += $invoice_buy_item->invoice_buy_item_number;
                                }

                                $cost = round($cost_invoice[$invoice_buy->invoice_buy_id]/$num,2);

                                foreach ($invoice_buy_items as $invoice_buy_item) {
                                    $data_item = array(
                                        'invoice_buy_item_other_cost'=>$cost,
                                        'invoice_buy_item_total'=>$invoice_buy_item->invoice_buy_item_total+$cost,
                                    );
                                    $invoice_buy_item_model->updateInvoice($data_item,array('invoice_buy_item_id'=>$invoice_buy_item->invoice_buy_item_id));

                                    $stock_model->updateStock(array('stock_price'=>$data_item['invoice_buy_item_total']),array('invoice_buy_item'=>$invoice_buy_item->invoice_buy_item_id));
                                }

                                $data_buy = array(
                                    'invoice_buy_other_cost'=>$money[$invoice_buy->invoice_buy_id],
                                    'invoice_buy_total'=>($invoice_buy->invoice_buy_money+$invoice_buy->invoice_buy_tax_vat+$invoice_buy->invoice_buy_item_tax_import+$invoice_buy->invoice_buy_custom_cost+$money[$invoice_buy->invoice_buy_id]),
                                );
                                $invoice_buy_model->updateInvoice($data_buy,array('invoice_buy_id'=>$invoice_buy->invoice_buy_id));
                            }
                            
                        }
                    }
                }//PHANBO-VEKHO
                else if ($nameWorksheet=="PHIEUTHU") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);

                            $data = array(
                        
                            'payment_document_date' => $doc_date,
                            'payment_document_number' => $val[3],
                            'payment_additional_date' => $add_date,
                            'payment_person' => $val[4],
                            'payment_origin_doc' => $val[6],
                            'payment_comment' => $val[5],
                            'payment_bank' => 0,
                            'payment_bank_type' => 1,
                            'payment_type' => 1,
                            'payment_check' => 1,
                            'payment_in_ex' => 1,
                            'payment_create_user' => $_SESSION['userid_logined'],
                            'payment_create_date' => strtotime(date('d-m-Y')),
                            );
                            $payment_model->createPayment($data);
                        }
                        
                    }//
                }//PHIEUTHU
                else if ($nameWorksheet=="CHITIET-PHIEUTHU") {
                    $money = array();
                    $ids = "0";
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $payments = $payment_model->getPaymentByWhere(array('payment_document_number'=>$val[1]));
                            $id_payment = $payments->payment_id;
                            $ids .= ",".$id_payment;
                            $invoice_sells = $invoice_sell_model->getInvoiceByWhere(array('invoice_sell_document_number'=>$val[2]));
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[3]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $id_invoice = null;
                            $recei = null;
                            $used=0;
                            if(isset($invoice_sells->invoice_sell_id)){
                                $id_invoice = $invoice_sells->invoice_sell_id;
                                $recei = $invoice_sells->invoice_sell_total;
                                $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND invoice_sell='.$invoice_sells->invoice_sell_id));
                                
                                foreach ($pays as $pay) {
                                    $used += $pay->debit_money;
                                }
                            }
                            

                            $data_item = array(
                                'payment' => $id_payment,
                                'payment_item_invoice' => $id_invoice,
                                'payment_item_customer' => isset($cus->customer_id)?$cus->customer_id:null,
                                'payment_item_receivable_money' => $recei,
                                'payment_item_pay_money' => $used,
                                'payment_item_money' => $val[6],
                                'payment_item_debit' => $debit,
                                'payment_item_credit' => $credit,
                                'payment_item_comment' => $val[7],
                            );
                            $payment_item_model->createPayment($data_item);
                            $id_payment_item = $payment_item_model->getLastPayment()->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_debit = array(
                                'payment_item'=>$id_payment_item,
                                'debit_date'=>$payments->payment_document_date,
                                'debit_customer'=>$data_item['payment_item_customer'],
                                'debit_money'=>(0-$data_item['payment_item_money']),
                                'debit_comment'=>$data_item['payment_item_comment'],
                                'invoice_sell'=>$data_item['payment_item_invoice'],
                            );

                            $debit_model->createDebit($data_debit);

                            $money[$id_payment] = isset($money[$id_payment])?$money[$id_payment]+$data_item['payment_item_money']:$data_item['payment_item_money'];
                        }
                        
                    }//
                    $payments = $payment_model->getAllPayment(array('where'=>'payment_bank_type=1 AND payment_type=1 AND payment_check=1 AND payment_in_ex=1 AND payment_id IN ('.$ids.')'));
                    foreach ($payments as $payment) {
                        $data_pay = array(
                            'payment_money'=>$money[$payment->payment_id],
                        );
                        $payment_model->updatePayment($data_pay,array('payment_id'=>$payment->payment_id));

                        $data_bank = array(
                            'payment'=>$payment->payment_id,
                            'bank_balance_date'=>$payment->payment_document_date,
                            'bank'=>$payment->payment_bank,
                            'bank_balance_money'=>$data_pay['payment_money'],
                        );
                        $bank_balance_model->createBank($data_bank);
                        
                    }
                    
                }//CHITIET-PHIEUTHU
                else if ($nameWorksheet=="PHIEUCHI") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);

                            $data = array(
                        
                            'payment_document_date' => $doc_date,
                            'payment_document_number' => $val[3],
                            'payment_additional_date' => $add_date,
                            'payment_person' => $val[4],
                            'payment_origin_doc' => $val[6],
                            'payment_comment' => $val[5],
                            'payment_bank' => 0,
                            'payment_bank_type' => 1,
                            'payment_type' => 2,
                            'payment_check' => 1,
                            'payment_in_ex' => 1,
                            'payment_create_user' => $_SESSION['userid_logined'],
                            'payment_create_date' => strtotime(date('d-m-Y')),
                            );
                            $payment_model->createPayment($data);
                        }
                        
                    }//
                }//PHIEUCHI
                else if ($nameWorksheet=="CHITIET-PHIEUCHI") {
                    $money = array();
                    $ids = "0";
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $payments = $payment_model->getPaymentByWhere(array('payment_document_number'=>$val[1]));
                            $id_payment = $payments->payment_id;
                            $ids .= ",".$id_payment;
                            $service_buys = $service_buy_model->getServiceByWhere(array('service_buy_document_number'=>$val[2]));
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[3]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $tax_debits = $account_model->getAccountByWhere(array('account_number'=>$val[10]));
                            $id_invoice = null;
                            $recei = null;
                            $used=0;
                            if(isset($service_buys->service_buy_id)){
                                $id_invoice = $service_buys->service_buy_id;
                                $recei = $service_buys->service_buy_total;
                                $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND service_buy='.$service_buys->service_buy_id));
                                
                                foreach ($pays as $pay) {
                                    $used += $pay->debit_money;
                                }
                            }

                            $tax_debit = null;
                            if ($tax_debits) {
                                $tax_debit = $tax_debits->account_id;
                            }
                            $invoice_date = PHPExcel_Shared_Date::ExcelToPHP($val[12]);
                            

                            $data_item = array(
                                'payment' => $id_payment,
                                'payment_item_invoice' => $id_invoice,
                                'payment_item_customer' => isset($cus->customer_id)?$cus->customer_id:null,
                                'payment_item_receivable_money' => $recei,
                                'payment_item_pay_money' => $used,
                                'payment_item_money' => $val[6],
                                'payment_item_debit' => $debit,
                                'payment_item_credit' => $credit,
                                'payment_item_comment' => $val[7],
                                'payment_item_tax_percent' => $val[8],
                                'payment_item_tax' => $val[9],
                                'payment_item_tax_debit' => $tax_debit,
                                'payment_item_invoice_number' => $val[11],
                                'payment_item_invoice_date' => $invoice_date,
                                'payment_item_invoice_symbol' => $val[13],
                            );
                            $payment_item_model->createPayment($data_item);
                            $id_payment_item = $payment_item_model->getLastPayment()->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            if ($data_item['payment_item_tax_debit']>0) {
                                $data_additional = array(
                                    'payment_item'=>$id_payment_item,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_item['payment_item_comment'],
                                    'debit'=>$data_item['payment_item_tax_debit'],
                                    'credit'=>$data_item['payment_item_credit'],
                                    'money'=>$data_item['payment_item_tax'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_invoice = array(
                                    'payment_item'=>$id_payment_item,
                                    'invoice_date'=>$data_item['payment_item_invoice_date'],
                                    'invoice_symbol'=>$data_item['payment_item_invoice_symbol'],
                                    'invoice_number'=>$data_item['payment_item_invoice_number'],
                                    'invoice_customer'=>$data_item['payment_item_customer'],
                                    'invoice_money'=>$data_item['payment_item_money'],
                                    'invoice_tax'=>$data_item['payment_item_tax'],
                                    'invoice_comment'=>$data_item['payment_item_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);
                            }

                            $data_debit = array(
                                'payment_item'=>$id_payment_item,
                                'debit_date'=>$payments->payment_document_date,
                                'debit_customer'=>$data_item['payment_item_customer'],
                                'debit_money'=>(0-$data_item['payment_item_money']-$data_item['payment_item_tax']),
                                'debit_comment'=>$data_item['payment_item_comment'],
                                'service_buy'=>$data_item['payment_item_invoice'],
                            );

                            $debit_model->createDebit($data_debit);

                            $money[$id_payment] = isset($money[$id_payment])?$money[$id_payment]+$data_item['payment_item_money']+$data_item['payment_item_tax']:$data_item['payment_item_money']+$data_item['payment_item_tax'];
                        }
                        
                    }//
                    $payments = $payment_model->getAllPayment(array('where'=>'payment_bank_type=1 AND payment_type=2 AND payment_check=1 AND payment_in_ex=1 AND payment_id IN ('.$ids.')'));
                    foreach ($payments as $payment) {
                        $data_pay = array(
                            'payment_money'=>$money[$payment->payment_id],
                        );
                        $payment_model->updatePayment($data_pay,array('payment_id'=>$payment->payment_id));

                        $data_bank = array(
                            'payment'=>$payment->payment_id,
                            'bank_balance_date'=>$payment->payment_document_date,
                            'bank'=>$payment->payment_bank,
                            'bank_balance_money'=>(0-$data_pay['payment_money']),
                        );
                        $bank_balance_model->createBank($data_bank);
                        
                    }
                    
                }//CHITIET-PHIEUCHI
                else if ($nameWorksheet=="CTNOIBO") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);

                            $data = array(
                        
                            'internal_transfer_document_date' => $doc_date,
                            'internal_transfer_document_number' => $val[3],
                            'internal_transfer_additional_date' => $add_date,
                            'internal_transfer_comment' => $val[9],
                            'internal_transfer_create_user' => $_SESSION['userid_logined'],
                            'internal_transfer_create_date' => strtotime(date('d-m-Y')),
                            );
                            $internal_transfer_model->createBank($data);

                            $id_internal_transfer = $internal_transfer_model->getLastBank()->internal_transfer_id;
                            $internal_transfers = $internal_transfer_model->getBank($id_internal_transfer);

                            $money = 0;

                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[6]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[7]))->account_id;

                            $out = $val[4]=="TM"?0:$bank_model->getBankByWhere(array('bank_code'=>$val[4]))->bank_id;
                            $in = $val[5]=="TM"?0:$bank_model->getBankByWhere(array('bank_code'=>$val[5]))->bank_id;

                            $data_item = array(
                                'internal_transfer' => $id_internal_transfer,
                                'internal_transfer_item_out' => $out,
                                'internal_transfer_item_in' => $in,
                                'internal_transfer_item_money' => $val[8],
                                'internal_transfer_item_debit' => $debit,
                                'internal_transfer_item_credit' => $credit,
                            );
                            $internal_transfer_item_model->createBank($data_item);
                            $id_internal_transfer_item = $internal_transfer_item_model->getLastBank()->internal_transfer_item_id;

                            $data_additional = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'document_number'=>$internal_transfers->internal_transfer_document_number,
                                'document_date'=>$internal_transfers->internal_transfer_document_date,
                                'additional_date'=>$internal_transfers->internal_transfer_additional_date,
                                'additional_comment'=>$internal_transfers->internal_transfer_comment,
                                'debit'=>$data_item['internal_transfer_item_debit'],
                                'credit'=>$data_item['internal_transfer_item_credit'],
                                'money'=>$data_item['internal_transfer_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_bank_out = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'bank_balance_date'=>$internal_transfers->internal_transfer_document_date,
                                'bank'=>$data_item['internal_transfer_item_out'],
                                'bank_balance_money'=>(0-$data_item['internal_transfer_item_money']),
                            );
                            $data_bank_in = array(
                                'internal_transfer_item'=>$id_internal_transfer_item,
                                'bank_balance_date'=>$internal_transfers->internal_transfer_document_date,
                                'bank'=>$data_item['internal_transfer_item_in'],
                                'bank_balance_money'=>$data_item['internal_transfer_item_money'],
                            );
                            
                            $bank_balance_model->createBank($data_bank_out);
                            $bank_balance_model->createBank($data_bank_in);

                            $money += $data_item['internal_transfer_item_money'];
                            $data_pay = array(
                                'internal_transfer_money'=>$money,
                            );
                            $internal_transfer_model->updateBank($data_pay,array('internal_transfer_id'=>$id_internal_transfer));
                        }
                        
                    }//
                }//CTNOIBO
                else if ($nameWorksheet=="THUTIENNH") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $bank = $bank_model->getBankByWhere(array('bank_code'=>$val[4]))->bank_id;

                            $data = array(
                        
                            'payment_document_date' => $doc_date,
                            'payment_document_number' => $val[3],
                            'payment_additional_date' => $add_date,
                            'payment_person' => null,
                            'payment_origin_doc' => $val[6],
                            'payment_comment' => $val[5],
                            'payment_bank' => $bank,
                            'payment_bank_type' => 2,
                            'payment_type' => 1,
                            'payment_check' => 1,
                            'payment_in_ex' => 1,
                            'payment_create_user' => $_SESSION['userid_logined'],
                            'payment_create_date' => strtotime(date('d-m-Y')),
                            );
                            $payment_model->createPayment($data);
                        }
                        
                    }//
                }//THUTIENNH
                else if ($nameWorksheet=="CHITIET-THUTIENNH") {
                    $money = array();
                    $ids = "0";
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $payments = $payment_model->getPaymentByWhere(array('payment_document_number'=>$val[1]));
                            $id_payment = $payments->payment_id;
                            $ids .= ",".$id_payment;
                            $invoice_sells = $invoice_sell_model->getInvoiceByWhere(array('invoice_sell_document_number'=>$val[2]));
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[3]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $id_invoice = null;
                            $recei = null;
                            $used=0;
                            if(isset($invoice_sells->invoice_sell_id)){
                                $id_invoice = $invoice_sells->invoice_sell_id;
                                $recei = $invoice_sells->invoice_sell_total;
                                $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND invoice_sell='.$invoice_sells->invoice_sell_id));
                                
                                foreach ($pays as $pay) {
                                    $used += $pay->debit_money;
                                }
                            }
                            

                            $data_item = array(
                                'payment' => $id_payment,
                                'payment_item_invoice' => $id_invoice,
                                'payment_item_customer' => isset($cus->customer_id)?$cus->customer_id:null,
                                'payment_item_receivable_money' => $recei,
                                'payment_item_pay_money' => $used,
                                'payment_item_money' => $val[6],
                                'payment_item_debit' => $debit,
                                'payment_item_credit' => $credit,
                                'payment_item_comment' => $val[7],
                            );
                            $payment_item_model->createPayment($data_item);
                            $id_payment_item = $payment_item_model->getLastPayment()->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_debit = array(
                                'payment_item'=>$id_payment_item,
                                'debit_date'=>$payments->payment_document_date,
                                'debit_customer'=>$data_item['payment_item_customer'],
                                'debit_money'=>(0-$data_item['payment_item_money']),
                                'debit_comment'=>$data_item['payment_item_comment'],
                                'invoice_sell'=>$data_item['payment_item_invoice'],
                            );

                            $debit_model->createDebit($data_debit);

                            $money[$id_payment] = isset($money[$id_payment])?$money[$id_payment]+$data_item['payment_item_money']:$data_item['payment_item_money'];
                        }
                        
                    }//
                    $payments = $payment_model->getAllPayment(array('where'=>'payment_bank_type=2 AND payment_type=1 AND payment_check=1 AND payment_in_ex=1 AND payment_id IN ('.$ids.')'));
                    foreach ($payments as $payment) {
                        $data_pay = array(
                            'payment_money'=>$money[$payment->payment_id],
                        );
                        $payment_model->updatePayment($data_pay,array('payment_id'=>$payment->payment_id));

                        $data_bank = array(
                            'payment'=>$payment->payment_id,
                            'bank_balance_date'=>$payment->payment_document_date,
                            'bank'=>$payment->payment_bank,
                            'bank_balance_money'=>$data_pay['payment_money'],
                        );
                        $bank_balance_model->createBank($data_bank);
                        
                    }
                    
                }//CHITIET-THUTIENNH
                else if ($nameWorksheet=="TTTRONGNUOC") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $bank = $bank_model->getBankByWhere(array('bank_code'=>$val[4]))->bank_id;

                            $data = array(
                        
                            'payment_document_date' => $doc_date,
                            'payment_document_number' => $val[3],
                            'payment_additional_date' => $add_date,
                            'payment_person' => null,
                            'payment_origin_doc' => $val[6],
                            'payment_comment' => $val[5],
                            'payment_bank' => $bank,
                            'payment_bank_type' => 2,
                            'payment_type' => 2,
                            'payment_check' => 1,
                            'payment_in_ex' => 1,
                            'payment_create_user' => $_SESSION['userid_logined'],
                            'payment_create_date' => strtotime(date('d-m-Y')),
                            );
                            $payment_model->createPayment($data);
                            $id_payment = $payment_model->getLastPayment()->payment_id;
                            $payments = $payment_model->getPayment($id_payment);

                            if ($val[7]>0) {
                                $cost_debit = $account_model->getAccountByWhere(array('account_number'=>$val[10]))->account_id;
                                $cost_credit = $account_model->getAccountByWhere(array('account_number'=>$val[11]))->account_id;
                                $cost_debit_vat = $account_model->getAccountByWhere(array('account_number'=>$val[13]))->account_id;
                                $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[8]))->customer_id;
                                $data_cost = array(
                                    'payment'=>$id_payment,
                                    'payment_cost_comment'=>$val[9],
                                    'payment_cost_money'=>$val[7],
                                    'payment_cost_debit'=>$cost_debit,
                                    'payment_cost_credit'=>$cost_credit,
                                    'payment_cost_customer'=>$cus,
                                    'payment_cost_vat'=>$val[12],
                                    'payment_cost_debit_vat'=>$cost_debit_vat,
                                );
                                $payment_cost_model->createPayment($data_cost);
                                $id_payment_cost = $payment_cost_model->getLastPayment()->payment_cost_id;

                                $data_additional = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_cost['payment_cost_comment'],
                                    'debit'=>$data_cost['payment_cost_debit'],
                                    'credit'=>$data_cost['payment_cost_credit'],
                                    'money'=>$data_cost['payment_cost_money'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_additional_vat = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_cost['payment_cost_comment'],
                                    'debit'=>$data_cost['payment_cost_debit_vat'],
                                    'credit'=>$data_cost['payment_cost_credit'],
                                    'money'=>$data_cost['payment_cost_vat'],
                                );
                                $additional_model->createAdditional($data_additional_vat);

                                $data_invoice = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'invoice_date'=>$payments->payment_document_date,
                                    'invoice_number'=>$payments->payment_document_number,
                                    'invoice_customer'=>$data_cost['payment_cost_customer'],
                                    'invoice_money'=>$data_cost['payment_cost_money'],
                                    'invoice_tax'=>$data_cost['payment_cost_vat'],
                                    'invoice_comment'=>$data_cost['payment_cost_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);

                                $data_pay = array(
                                    'payment_money_cost'=>$data_cost['payment_cost_money']+$data_cost['payment_cost_vat'],
                                );
                                $payment_model->updatePayment($data_pay,array('payment_id'=>$id_payment));
                            }
                            
                        }
                        
                    }//
                }//TTTRONGNUOC
                else if ($nameWorksheet=="CHITIET-TTTRONGNUOC") {
                    $money = array();
                    $ids = "0";
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $payments = $payment_model->getPaymentByWhere(array('payment_document_number'=>$val[1]));
                            $id_payment = $payments->payment_id;
                            $ids .= ",".$id_payment;
                            $service_buys = $service_buy_model->getServiceByWhere(array('service_buy_document_number'=>$val[2]));
                            $invoice_purchases = $invoice_purchase_model->getInvoiceByWhere(array('invoice_purchase_document_number'=>$val[2]));
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[3]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $tax_debits = $account_model->getAccountByWhere(array('account_number'=>$val[10]));
                            $id_invoice = null;
                            $id_invoice2 = null;
                            $recei = null;
                            $used=0;
                            if(isset($service_buys->service_buy_id)){
                                $id_invoice = $service_buys->service_buy_id;
                                $recei = $service_buys->service_buy_total;
                                $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND service_buy='.$service_buys->service_buy_id));
                                
                                foreach ($pays as $pay) {
                                    $used += $pay->debit_money;
                                }
                            }
                            else if(isset($invoice_purchases->invoice_purchase_id)){
                                $id_invoice2 = $invoice_purchases->invoice_purchase_id;
                                $recei = $invoice_purchases->invoice_purchase_total;
                                $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND invoice_purchase='.$invoice_purchases->invoice_purchase_id));
                                
                                foreach ($pays as $pay) {
                                    $used += $pay->debit_money;
                                }
                            }

                            $tax_debit = null;
                            if ($tax_debits) {
                                $tax_debit = $tax_debits->account_id;
                            }
                            $invoice_date = PHPExcel_Shared_Date::ExcelToPHP($val[12]);
                            

                            $data_item = array(
                                'payment' => $id_payment,
                                'payment_item_invoice' => $id_invoice,
                                'payment_item_invoice_2' => $id_invoice2,
                                'payment_item_customer' => isset($cus->customer_id)?$cus->customer_id:null,
                                'payment_item_receivable_money' => $recei,
                                'payment_item_pay_money' => $used,
                                'payment_item_money' => $val[6],
                                'payment_item_debit' => $debit,
                                'payment_item_credit' => $credit,
                                'payment_item_comment' => $val[7],
                                'payment_item_tax_percent' => $val[8],
                                'payment_item_tax' => $val[9],
                                'payment_item_tax_debit' => $tax_debit,
                                'payment_item_invoice_number' => $val[11],
                                'payment_item_invoice_date' => $invoice_date,
                                'payment_item_invoice_symbol' => $val[13],
                            );
                            $payment_item_model->createPayment($data_item);
                            $id_payment_item = $payment_item_model->getLastPayment()->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            if ($data_item['payment_item_tax_debit']>0) {
                                $data_additional = array(
                                    'payment_item'=>$id_payment_item,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_item['payment_item_comment'],
                                    'debit'=>$data_item['payment_item_tax_debit'],
                                    'credit'=>$data_item['payment_item_credit'],
                                    'money'=>$data_item['payment_item_tax'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_invoice = array(
                                    'payment_item'=>$id_payment_item,
                                    'invoice_date'=>$data_item['payment_item_invoice_date'],
                                    'invoice_symbol'=>$data_item['payment_item_invoice_symbol'],
                                    'invoice_number'=>$data_item['payment_item_invoice_number'],
                                    'invoice_customer'=>$data_item['payment_item_customer'],
                                    'invoice_money'=>$data_item['payment_item_money'],
                                    'invoice_tax'=>$data_item['payment_item_tax'],
                                    'invoice_comment'=>$data_item['payment_item_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);
                            }

                            $data_debit = array(
                                'payment_item'=>$id_payment_item,
                                'debit_date'=>$payments->payment_document_date,
                                'debit_customer'=>$data_item['payment_item_customer'],
                                'debit_money'=>(0-$data_item['payment_item_money']-$data_item['payment_item_tax']),
                                'debit_comment'=>$data_item['payment_item_comment'],
                                'service_buy'=>$data_item['payment_item_invoice'],
                            );

                            if ($data_item['payment_item_invoice_2']>0) {
                                $data_debit = array(
                                    'payment_item'=>$id_payment_item,
                                    'debit_date'=>$payments->payment_document_date,
                                    'debit_customer'=>$data_item['payment_item_customer'],
                                    'debit_money'=>(0-$data_item['payment_item_money']-$data_item['payment_item_tax']),
                                    'debit_comment'=>$data_item['payment_item_comment'],
                                    'invoice_purchase'=>$data_item['payment_item_invoice_2'],
                                );
                            }

                            $debit_model->createDebit($data_debit);

                            $money[$id_payment] = isset($money[$id_payment])?$money[$id_payment]+$data_item['payment_item_money']+$data_item['payment_item_tax']:$data_item['payment_item_money']+$data_item['payment_item_tax'];
                        }
                        
                    }//
                    $payments = $payment_model->getAllPayment(array('where'=>'payment_bank_type=2 AND payment_type=2 AND payment_check=1 AND payment_in_ex=1 AND payment_id IN ('.$ids.')'));
                    foreach ($payments as $payment) {
                        $data_pay = array(
                            'payment_money'=>$money[$payment->payment_id],
                        );
                        $payment_model->updatePayment($data_pay,array('payment_id'=>$payment->payment_id));

                        $data_bank = array(
                            'payment'=>$payment->payment_id,
                            'bank_balance_date'=>$payment->payment_document_date,
                            'bank'=>$payment->payment_bank,
                            'bank_balance_money'=>(0-$data_pay['payment_money']-$payment->payment_money_cost),
                        );
                        $bank_balance_model->createBank($data_bank);
                        
                    }
                    
                }//CHITIET-TTTRONGNUOC
                else if ($nameWorksheet=="TTNUOCNGOAI") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $bank = $bank_model->getBankByWhere(array('bank_code'=>$val[4]))->bank_id;

                            $data = array(
                        
                            'payment_document_date' => $doc_date,
                            'payment_document_number' => $val[3],
                            'payment_additional_date' => $add_date,
                            'payment_person' => null,
                            'payment_origin_doc' => $val[6],
                            'payment_comment' => $val[5],
                            'payment_bank' => $bank,
                            'payment_bank_type' => 2,
                            'payment_type' => 2,
                            'payment_check' => 1,
                            'payment_in_ex' => 2,
                            'payment_create_user' => $_SESSION['userid_logined'],
                            'payment_create_date' => strtotime(date('d-m-Y')),
                            );
                            $payment_model->createPayment($data);
                            $id_payment = $payment_model->getLastPayment()->payment_id;
                            $payments = $payment_model->getPayment($id_payment);

                            if ($val[7]>0) {
                                $cost_debit = $account_model->getAccountByWhere(array('account_number'=>$val[10]))->account_id;
                                $cost_credit = $account_model->getAccountByWhere(array('account_number'=>$val[11]))->account_id;
                                $cost_debit_vat = $account_model->getAccountByWhere(array('account_number'=>$val[13]))->account_id;
                                $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[8]))->customer_id;
                                $data_cost = array(
                                    'payment'=>$id_payment,
                                    'payment_cost_comment'=>$val[9],
                                    'payment_cost_money'=>$val[7],
                                    'payment_cost_debit'=>$cost_debit,
                                    'payment_cost_credit'=>$cost_credit,
                                    'payment_cost_customer'=>$cus,
                                    'payment_cost_vat'=>$val[12],
                                    'payment_cost_debit_vat'=>$cost_debit_vat,
                                );
                                $payment_cost_model->createPayment($data_cost);
                                $id_payment_cost = $payment_cost_model->getLastPayment()->payment_cost_id;

                                $data_additional = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_cost['payment_cost_comment'],
                                    'debit'=>$data_cost['payment_cost_debit'],
                                    'credit'=>$data_cost['payment_cost_credit'],
                                    'money'=>$data_cost['payment_cost_money'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_additional_vat = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_cost['payment_cost_comment'],
                                    'debit'=>$data_cost['payment_cost_debit_vat'],
                                    'credit'=>$data_cost['payment_cost_credit'],
                                    'money'=>$data_cost['payment_cost_vat'],
                                );
                                $additional_model->createAdditional($data_additional_vat);

                                $data_invoice = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'invoice_date'=>$payments->payment_document_date,
                                    'invoice_number'=>$payments->payment_document_number,
                                    'invoice_customer'=>$data_cost['payment_cost_customer'],
                                    'invoice_money'=>$data_cost['payment_cost_money'],
                                    'invoice_tax'=>$data_cost['payment_cost_vat'],
                                    'invoice_comment'=>$data_cost['payment_cost_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);

                                $data_pay = array(
                                    'payment_money_cost'=>$data_cost['payment_cost_money']+$data_cost['payment_cost_vat'],
                                );
                                $payment_model->updatePayment($data_pay,array('payment_id'=>$id_payment));
                            }
                            
                        }
                        
                    }//
                }//TTNUOCNGOAI
                else if ($nameWorksheet=="CHITIET-TTNUOCNGOAI") {
                    $money = array();
                    $money_foreign = array();
                    $ids = "0";
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $payments = $payment_model->getPaymentByWhere(array('payment_document_number'=>$val[1]));
                            $id_payment = $payments->payment_id;
                            $ids .= ",".$id_payment;
                            $service_buys = $service_buy_model->getServiceByWhere(array('service_buy_document_number'=>$val[2]));
                            $invoice_buys = $invoice_buy_model->getInvoiceByWhere(array('invoice_buy_document_number'=>$val[2]));
                            $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[3]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[4]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $id_invoice = null;
                            $id_invoice2 = null;
                            $recei = null;
                            $used=0;
                            $recei_foreign = null;
                            $used_foreign=0;
                            if(isset($service_buys->service_buy_id)){
                                $id_invoice2 = $service_buys->service_buy_id;
                                $recei = $service_buys->service_buy_total;
                                $recei_foreign = $service_buys->service_buy_money_foreign;
                                $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND service_buy='.$service_buys->service_buy_id));
                                
                                foreach ($pays as $pay) {
                                    $used += $pay->debit_money;
                                    $used_foreign += $pay->debit_money_foreign;
                                }
                            }
                            else if(isset($invoice_buys->invoice_buy_id)){
                                $id_invoice = $invoice_buys->invoice_buy_id;
                                $recei = $invoice_buys->invoice_buy_money;
                                $recei_foreign = $invoice_buys->invoice_buy_money_foreign;
                                $pays = $debit_model->getAllDebit(array('where'=>'debit_money<0 AND invoice_buy='.$invoice_buys->invoice_buy_id));
                                
                                foreach ($pays as $pay) {
                                    $used += $pay->debit_money;
                                    $used_foreign += $pay->debit_money_foreign;
                                }
                            }
                            

                            $data_item = array(
                                'payment' => $id_payment,
                                'payment_item_invoice' => $id_invoice,
                                'payment_item_invoice_2' => $id_invoice2,
                                'payment_item_customer' => isset($cus->customer_id)?$cus->customer_id:null,
                                'payment_item_receivable_money' => $recei,
                                'payment_item_pay_money' => $used,
                                'payment_item_receivable_money_foreign' => $recei_foreign,
                                'payment_item_pay_money_foreign' => $used_foreign,
                                'payment_item_price' => $val[7],
                                'payment_item_money' => $val[8],
                                'payment_item_debit' => $debit,
                                'payment_item_credit' => $credit,
                                'payment_item_comment' => $val[9],
                            );
                            $payment_item_model->createPayment($data_item);
                            $id_payment_item = $payment_item_model->getLastPayment()->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_debit = array(
                                'payment_item'=>$id_payment_item,
                                'debit_date'=>$payments->payment_document_date,
                                'debit_customer'=>$data_item['payment_item_customer'],
                                'debit_money'=>(0-$data_item['payment_item_money']),
                                'debit_comment'=>$data_item['payment_item_comment'],
                                'invoice_buy'=>$data_item['payment_item_invoice'],
                            );

                            if ($data_item['payment_item_invoice_2']>0) {
                                $data_debit = array(
                                    'payment_item'=>$id_payment_item,
                                    'debit_date'=>$payments->payment_document_date,
                                    'debit_customer'=>$data_item['payment_item_customer'],
                                    'debit_money'=>(0-$data_item['payment_item_money']),
                                    'debit_comment'=>$data_item['payment_item_comment'],
                                    'service_buy'=>$data_item['payment_item_invoice_2'],
                                );
                            }

                            $debit_model->createDebit($data_debit);

                            if ($val[6]==1) {
                                $money_type[$id_service_buy]=1;
                                $money_rate[$id_service_buy]=$val[6];
                            }
                            else{
                                $money_type[$id_service_buy]=2;
                                $money_rate[$id_service_buy]=$val[6];
                            }

                            $money[$id_payment] = isset($money[$id_payment])?$money[$id_payment]+$data_item['payment_item_money']:$data_item['payment_item_money'];
                            $money_foreign[$id_payment] = isset($money_foreign[$id_payment])?$money_foreign[$id_payment]+$data_item['payment_item_price']:$data_item['payment_item_price'];
                        }
                        
                    }//
                    $payments = $payment_model->getAllPayment(array('where'=>'payment_bank_type=2 AND payment_type=2 AND payment_check=1 AND payment_in_ex=2 AND payment_id IN ('.$ids.')'));
                    foreach ($payments as $payment) {
                        $data_pay = array(
                            'payment_money_type' => $money_type[$payment->payment_id],
                            'payment_money_rate' => $money_rate[$payment->payment_id],
                            'payment_money'=>$money[$payment->payment_id],
                            'payment_money_foreign'=>$money_foreign[$payment->payment_id],
                        );
                        $payment_model->updatePayment($data_pay,array('payment_id'=>$payment->payment_id));

                        $data_bank = array(
                            'payment'=>$payment->payment_id,
                            'bank_balance_date'=>$payment->payment_document_date,
                            'bank'=>$payment->payment_bank,
                            'bank_balance_money'=>(0-$data_pay['payment_money']-$payment->payment_money_cost),
                        );
                        $bank_balance_model->createBank($data_bank);
                        
                    }
                    
                }//CHITIET-TTNUOCNGOAI
                else if ($nameWorksheet=="NOPTHUE") {
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $doc_date = PHPExcel_Shared_Date::ExcelToPHP($val[1]);
                            $add_date = PHPExcel_Shared_Date::ExcelToPHP($val[2]);
                            $bank = $bank_model->getBankByWhere(array('bank_code'=>$val[4]))->bank_id;

                            $data = array(
                        
                            'payment_document_date' => $doc_date,
                            'payment_document_number' => $val[3],
                            'payment_additional_date' => $add_date,
                            'payment_person' => null,
                            'payment_origin_doc' => $val[7],
                            'payment_comment' => $val[6],
                            'payment_bank' => $bank,
                            'payment_bank_type' => 2,
                            'payment_type' => 2,
                            'payment_check' => 2,
                            'payment_in_ex' => 1,
                            'payment_KBNN' => $val[5],
                            'payment_create_user' => $_SESSION['userid_logined'],
                            'payment_create_date' => strtotime(date('d-m-Y')),
                            );
                            $payment_model->createPayment($data);
                            $id_payment = $payment_model->getLastPayment()->payment_id;
                            $payments = $payment_model->getPayment($id_payment);

                            if ($val[8]>0) {
                                $cost_debit = $account_model->getAccountByWhere(array('account_number'=>$val[11]))->account_id;
                                $cost_credit = $account_model->getAccountByWhere(array('account_number'=>$val[12]))->account_id;
                                $cost_debit_vat = $account_model->getAccountByWhere(array('account_number'=>$val[14]))->account_id;
                                $cus = $customer_model->getCustomerByWhere(array('customer_code'=>$val[9]))->customer_id;
                                $data_cost = array(
                                    'payment'=>$id_payment,
                                    'payment_cost_comment'=>$val[10],
                                    'payment_cost_money'=>$val[8],
                                    'payment_cost_debit'=>$cost_debit,
                                    'payment_cost_credit'=>$cost_credit,
                                    'payment_cost_customer'=>$cus,
                                    'payment_cost_vat'=>$val[13],
                                    'payment_cost_debit_vat'=>$cost_debit_vat,
                                );
                                $payment_cost_model->createPayment($data_cost);
                                $id_payment_cost = $payment_cost_model->getLastPayment()->payment_cost_id;

                                $data_additional = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_cost['payment_cost_comment'],
                                    'debit'=>$data_cost['payment_cost_debit'],
                                    'credit'=>$data_cost['payment_cost_credit'],
                                    'money'=>$data_cost['payment_cost_money'],
                                );
                                $additional_model->createAdditional($data_additional);

                                $data_additional_vat = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'document_number'=>$payments->payment_document_number,
                                    'document_date'=>$payments->payment_document_date,
                                    'additional_date'=>$payments->payment_additional_date,
                                    'additional_comment'=>$data_cost['payment_cost_comment'],
                                    'debit'=>$data_cost['payment_cost_debit_vat'],
                                    'credit'=>$data_cost['payment_cost_credit'],
                                    'money'=>$data_cost['payment_cost_vat'],
                                );
                                $additional_model->createAdditional($data_additional_vat);

                                $data_invoice = array(
                                    'payment_cost'=>$id_payment_cost,
                                    'invoice_date'=>$payments->payment_document_date,
                                    'invoice_number'=>$payments->payment_document_number,
                                    'invoice_customer'=>$data_cost['payment_cost_customer'],
                                    'invoice_money'=>$data_cost['payment_cost_money'],
                                    'invoice_tax'=>$data_cost['payment_cost_vat'],
                                    'invoice_comment'=>$data_cost['payment_cost_comment'],
                                    'invoice_type'=>1,
                                );
                                $invoice_model->createInvoice($data_invoice);

                                $data_pay = array(
                                    'payment_money_cost'=>$data_cost['payment_cost_money']+$data_cost['payment_cost_vat'],
                                );
                                $payment_model->updatePayment($data_pay,array('payment_id'=>$id_payment));
                            }
                            
                        }
                        
                    }//
                }//NOPTHUE
                else if ($nameWorksheet=="CHITIET-NOPTHUE") {
                    $money = array();
                    $ids = "0";
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

                        if ($val[1]!="" && $val[1]!=NULL) {
                            $payments = $payment_model->getPaymentByWhere(array('payment_document_number'=>$val[1]));
                            $id_payment = $payments->payment_id;
                            $ids .= ",".$id_payment;
                            $invoice_buys = $invoice_buy_model->getInvoiceByWhere(array('invoice_buy_document_number'=>$val[2]));
                            $debit = $account_model->getAccountByWhere(array('account_number'=>$val[5]))->account_id;
                            $debit_2 = $account_model->getAccountByWhere(array('account_number'=>$val[6]))->account_id;
                            $credit = $account_model->getAccountByWhere(array('account_number'=>$val[7]))->account_id;
                            $id_invoice = null;
                            $money_import = null;
                            $money_vat = null;
                            $used_vat=0;
                            $used_import=0;
                            if(isset($invoice_buys->invoice_buy_id)){
                                $pay_imports = $tax_model->getAllTax(array('where'=>'tax_money<0 AND tax_type=1 AND invoice_buy='.$invoice_buys->invoice_buy_id));
                                $pay_vats = $tax_model->getAllTax(array('where'=>'tax_money<0 AND tax_type=2 AND invoice_buy='.$invoice_buys->invoice_buy_id));
                                $used_import=0;
                                $used_vat=0;
                                foreach ($pay_imports as $pay_import) {
                                    $used_import += $pay_import->tax_money;
                                }
                                foreach ($pay_vats as $pay_vat) {
                                    $used_vat += $pay_vat->tax_money;
                                }

                                $money_import = $invoice_buys->invoice_buy_tax_import;
                                $money_vat = $invoice_buys->invoice_buy_tax_vat;
                            }
                            

                            $data_item = array(
                                'payment' => $id_payment,
                                'payment_item_invoice' => $id_invoice,
                                'payment_item_receivable_money' => $money_import+$money_vat,
                                'payment_item_pay_money' => $used_import+$used_vat,
                                'payment_item_money' => $val[3],
                                'payment_item_money_2' => $val[4],
                                'payment_item_debit' => $debit,
                                'payment_item_debit_2' => $debit_2,
                                'payment_item_credit' => $credit,
                                'payment_item_comment' => $val[8],
                            );
                            $payment_item_model->createPayment($data_item);
                            $id_payment_item = $payment_item_model->getLastPayment()->payment_item_id;

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_additional = array(
                                'payment_item'=>$id_payment_item,
                                'document_number'=>$payments->payment_document_number,
                                'document_date'=>$payments->payment_document_date,
                                'additional_date'=>$payments->payment_additional_date,
                                'additional_comment'=>$data_item['payment_item_comment'],
                                'debit'=>$data_item['payment_item_debit_2'],
                                'credit'=>$data_item['payment_item_credit'],
                                'money'=>$data_item['payment_item_money_2'],
                            );
                            $additional_model->createAdditional($data_additional);

                            $data_tax = array(
                                'payment_item'=>$id_payment_item,
                                'tax_date'=>$payments->payment_document_date,
                                'tax_money'=>(0-$data_item['payment_item_money']),
                                'tax_comment'=>$data_item['payment_item_comment'],
                                'tax_type'=>1,
                                'invoice_buy'=>$data_item['payment_item_invoice'],
                            );

                            $tax_model->createTax($data_tax);

                            $data_tax_2 = array(
                                'payment_item'=>$id_payment_item,
                                'tax_date'=>$payments->payment_document_date,
                                'tax_money'=>(0-$data_item['payment_item_money_2']),
                                'tax_comment'=>$data_item['payment_item_comment'],
                                'tax_type'=>2,
                                'invoice_buy'=>$data_item['payment_item_invoice'],
                            );

                            $tax_model->createTax($data_tax_2);

                            $money[$id_payment] = isset($money[$id_payment])?$money[$id_payment]+$data_item['payment_item_money']+$data_item['payment_item_money_2']:$data_item['payment_item_money']+$data_item['payment_item_money_2'];
                        }
                        
                    }//
                    $payments = $payment_model->getAllPayment(array('where'=>'payment_bank_type=2 AND payment_type=2 AND payment_check=2 AND payment_in_ex=1 AND payment_id IN ('.$ids.')'));
                    foreach ($payments as $payment) {
                        $data_pay = array(
                            'payment_money'=>$money[$payment->payment_id],
                        );
                        $payment_model->updatePayment($data_pay,array('payment_id'=>$payment->payment_id));

                        $data_bank = array(
                            'payment'=>$payment->payment_id,
                            'bank_balance_date'=>$payment->payment_document_date,
                            'bank'=>$payment->payment_bank,
                            'bank_balance_money'=>(0-$data_pay['payment_money']-$payment->payment_money_cost),
                        );
                        $bank_balance_model->createBank($data_bank);
                        
                    }
                    
                }//CHITIET-NOPTHUE
                

                $i++;
            }
            return $this->view->redirect('admin');
        }
        $this->view->show('admin/importdata');
    }
    

}
?>