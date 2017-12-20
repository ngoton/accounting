<?php
Class invoicesellController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->invoicesell) || json_decode($_SESSION['user_permission_action'])->invoicesell != "invoicesell") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Hóa đơn bán hàng';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'invoice_sell_document_date';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $invoice_sell_model = $this->model->get('invoicesellModel');

        $join = array('table'=>'customer','where'=>'invoice_sell_customer=customer_id');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );
        
        
        $tongsodong = count($invoice_sell_model->getAllInvoice($data,$join));
        $tongsotrang = ceil($tongsodong / $sonews);
        

        $this->view->data['page'] = $page;
        $this->view->data['order_by'] = $order_by;
        $this->view->data['order'] = $order;
        $this->view->data['keyword'] = $keyword;
        $this->view->data['pagination_stages'] = $pagination_stages;
        $this->view->data['tongsotrang'] = $tongsotrang;
        $this->view->data['limit'] = $limit;
        $this->view->data['sonews'] = $sonews;

        $data = array(
            'order_by'=>$order_by,
            'order'=>$order,
            'limit'=>$x.','.$sonews,
            'where' => '1=1',
            );
        
      
        if ($keyword != '') {
            $search = '( invoice_sell_number LIKE "%'.$keyword.'%" 
                    OR customer_name LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['invoice_sells'] = $invoice_sell_model->getAllInvoice($data,$join);
        $this->view->data['lastID'] = isset($invoice_sell_model->getLastInvoice()->invoice_sell_document_number)?$invoice_sell_model->getLastInvoice()->invoice_sell_document_number:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('invoicesell/index');
    }

    public function printview() {
        $this->view->disableLayout();
        $this->view->data['lib'] = $this->lib;

        $sell = $this->registry->router->param_id;

        $customer_model = $this->model->get('customerModel');
        $invoice_sell_model = $this->model->get('invoicesellModel');
        $invoice_sell_item_model = $this->model->get('invoicesellitemModel');

        $invoice_sells = $invoice_sell_model->getInvoice($sell);
        $customers = $customer_model->getCustomer($invoice_sells->invoice_sell_customer);

        $join = array('table'=>'items','where'=>'invoice_sell_item=items_id');

        $items = array();
        $i=1;
        $invoice_sell_items = $invoice_sell_item_model->getAllInvoice(array('where'=>'invoice_sell='.$sell),$join);
        foreach ($invoice_sell_items as $invoice_sell_item) {
            $items['stt'][] = $i++;
            $items['ten'][] = $invoice_sell_item->items_name;
            $items['dvt'][] = $invoice_sell_item->invoice_sell_item_unit;
            $items['sl'][] = $invoice_sell_item->invoice_sell_item_number;
            $items['dg'][] = floor($invoice_sell_item->invoice_sell_item_price);
            $items['tt'][] = floor($invoice_sell_item->invoice_sell_item_money);
        }

        $this->view->data['invoice_sells'] = $invoice_sells;
        $this->view->data['customers'] = $customers;
        $this->view->data['items'] = $items;

        $this->view->show('invoicesell/printview');
    }
    public function printpage() {
        $this->view->disableLayout();
        $this->view->data['lib'] = $this->lib;

        $sell = $this->registry->router->param_id;

        $customer_model = $this->model->get('customerModel');
        $invoice_sell_model = $this->model->get('invoicesellModel');
        $invoice_sell_item_model = $this->model->get('invoicesellitemModel');

        $invoice_sells = $invoice_sell_model->getInvoice($sell);
        $customers = $customer_model->getCustomer($invoice_sells->invoice_sell_customer);

        $join = array('table'=>'items','where'=>'invoice_sell_item=items_id');

        $items = array();
        $i=1;
        $invoice_sell_items = $invoice_sell_item_model->getAllInvoice(array('where'=>'invoice_sell='.$sell),$join);
        foreach ($invoice_sell_items as $invoice_sell_item) {
            $items['stt'][] = $i++;
            $items['ten'][] = $invoice_sell_item->items_name;
            $items['dvt'][] = $invoice_sell_item->invoice_sell_item_unit;
            $items['sl'][] = $invoice_sell_item->invoice_sell_item_number;
            $items['dg'][] = floor($invoice_sell_item->invoice_sell_item_price);
            $items['tt'][] = floor($invoice_sell_item->invoice_sell_item_money);
        }

        $this->view->data['invoice_sells'] = $invoice_sells;
        $this->view->data['customers'] = $customers;
        $this->view->data['items'] = $items;

        $this->view->show('invoicesell/printpage');
    }

   public function getItem(){
        $items_model = $this->model->get('itemsModel');
        $stock_model = $this->model->get('stockModel');

        $rowIndex = $_POST['rowIndex'];
        $thang = str_replace('/', '-', $_POST['val']);
        $batdau = '01-'.date('m-Y',strtotime($thang));
        $ketthuc = date('t-m-Y',strtotime($batdau));
        $ngayketthuc = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        $data_stock = array();

        $items = $items_model->getAllItems(array('where'=>'items_type=1','order_by'=>'items_code ASC, items_name ASC'));
        
        $str = '<table class="table_data" id="tblExport2">';
        $str .= '<thead><tr><th class="fix"><input type="checkbox" onclick="checkall(\'checkbox2\', this)" name="checkall"/></th><th class="fix">Mã hàng hóa</th><th class="fix">Tên hàng hóa</th><th class="fix">ĐVT</th><th class="fix">Thuế suất</th></tr></thead>';
        $str .= '<tbody>';

        foreach ($items as $item) {
            $data_stock[$item->items_id]['dauky_import']['number']=0;
            $data_stock[$item->items_id]['dauky_export']['number']=0;
            $data_stock[$item->items_id]['import']['number']=0;
            $data_stock[$item->items_id]['export']['number']=0;
            $data_stock[$item->items_id]['dauky_import']['price']=0;
            $data_stock[$item->items_id]['dauky_export']['price']=0;
            $data_stock[$item->items_id]['import']['price']=0;
            $data_stock[$item->items_id]['export']['price']=0;
            $data_stock[$item->items_id]['dauky']['number']=0;
            $data_stock[$item->items_id]['dauky']['price']=0;

            $data_im = array(
                'where' => '(invoice_buy_item>0 OR invoice_purchase_item>0 OR items>0) AND stock_item = '.$item->items_id.' AND stock_date < '.strtotime($batdau),
            );
            $stock_ims = $stock_model->getAllStock($data_im);
            foreach ($stock_ims as $im) {
                $num = $im->stock_number;
                $price = $im->stock_price;
                $data_stock[$item->items_id]['dauky_import']['number'] = isset($data_stock[$item->items_id]['dauky_import']['number'])?$data_stock[$item->items_id]['dauky_import']['number']+$num:$num;
                $data_stock[$item->items_id]['dauky_import']['price'] = isset($data_stock[$item->items_id]['dauky_import']['price'])?$data_stock[$item->items_id]['dauky_import']['price']+$num*$price:$num*$price;
            }
            $data_ex = array(
                'where' => 'invoice_sell_item>0 AND stock_item = '.$item->items_id.' AND stock_date < '.strtotime($batdau),
            );
            $stock_exs = $stock_model->getAllStock($data_ex);
            foreach ($stock_exs as $ex) {
                $num = $ex->stock_number;
                $price = $ex->stock_price;
                $data_stock[$item->items_id]['dauky_export']['number'] = isset($data_stock[$item->items_id]['dauky_export']['number'])?$data_stock[$item->items_id]['dauky_export']['number']+$num:$num;
                $data_stock[$item->items_id]['dauky_export']['price'] = isset($data_stock[$item->items_id]['dauky_export']['price'])?$data_stock[$item->items_id]['dauky_export']['price']+$num*$price:$num*$price;
            }

            $data_stock[$item->items_id]['dauky']['number'] = $data_stock[$item->items_id]['dauky_import']['number']-$data_stock[$item->items_id]['dauky_export']['number'];
            $data_stock[$item->items_id]['dauky']['price'] = $data_stock[$item->items_id]['dauky_import']['price']-$data_stock[$item->items_id]['dauky_export']['price'];


            $data_im = array(
                'where' => '(invoice_buy_item>0 OR invoice_purchase_item>0) AND stock_item = '.$item->items_id.' AND stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc),
            );
            $stock_ims = $stock_model->getAllStock($data_im);
            foreach ($stock_ims as $im) {
                $num = $im->stock_number;
                $price = $im->stock_price;
                $data_stock[$item->items_id]['import']['number'] = isset($data_stock[$item->items_id]['import']['number'])?$data_stock[$item->items_id]['import']['number']+$num:$num;
                $data_stock[$item->items_id]['import']['price'] = isset($data_stock[$item->items_id]['import']['price'])?$data_stock[$item->items_id]['import']['price']+$num*$price:$num*$price;
            }

            $sl_dauky = $data_stock[$item->items_id]['dauky']['number'];
            $tt_dauky = $data_stock[$item->items_id]['dauky']['price'];
            $sl_nhap = $data_stock[$item->items_id]['import']['number'];
            $tt_nhap = $data_stock[$item->items_id]['import']['price'];
            
            $sl_bq = $sl_dauky+$sl_nhap;
            $tt_bq = $tt_dauky+$tt_nhap;
            $dg_bq = round($tt_bq/$sl_bq,2);

            $str .= '<tr class="tr" data="'.$item->items_id.'"><td><input name="check_i[]" type="checkbox" class="checkbox2" value="'.$item->items_id.'" data="'.$rowIndex.'" data-price="'.$this->lib->formatMoney($dg_bq).'" data-code="'.$item->items_code.'" data-name="'.$item->items_name.'" data-unit="'.$item->items_unit.'" data-tax="'.$item->items_tax.'" ></td><td class="fix">'.$item->items_code.'</td><td class="fix">'.$item->items_name.'</td><td class="fix">'.$item->items_unit.'</td><td class="fix">'.$item->items_tax.'</td></tr>';
        }
        
        $str .= '</tbody></table>';
        echo $str;
   }
   public function getItemName(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $items_model = $this->model->get('itemsModel');
            $stock_model = $this->model->get('stockModel');
            if ($_POST['keyword'] == "*") {
                $list = $items_model->getAllItems();
            }
            else{
                $data = array(
                'where'=>'( items_code LIKE "%'.$_POST['keyword'].'%" OR items_name LIKE "%'.$_POST['keyword'].'%" ) AND items_type=1',
                );
                $list = $items_model->getAllItems($data);
            }

            $thang = str_replace('/', '-', $_POST['val']);
            $batdau = '01-'.date('m-Y',strtotime($thang));
            $ketthuc = date('t-m-Y',strtotime($batdau));
            $ngayketthuc = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

            $data_stock = array();

            foreach ($list as $rs) {
                $data_stock[$rs->items_id]['dauky_import']['number']=0;
                $data_stock[$rs->items_id]['dauky_export']['number']=0;
                $data_stock[$rs->items_id]['import']['number']=0;
                $data_stock[$rs->items_id]['export']['number']=0;
                $data_stock[$rs->items_id]['dauky_import']['price']=0;
                $data_stock[$rs->items_id]['dauky_export']['price']=0;
                $data_stock[$rs->items_id]['import']['price']=0;
                $data_stock[$rs->items_id]['export']['price']=0;
                $data_stock[$rs->items_id]['dauky']['number']=0;
                $data_stock[$rs->items_id]['dauky']['price']=0;

                $data_im = array(
                    'where' => '(invoice_buy_item>0 OR invoice_purchase_item>0) AND stock_item = '.$rs->items_id.' AND stock_date < '.strtotime($batdau),
                );
                $stock_ims = $stock_model->getAllStock($data_im);
                foreach ($stock_ims as $im) {
                    $num = $im->stock_number;
                    $price = $im->stock_price;
                    $data_stock[$rs->items_id]['dauky_import']['number'] = isset($data_stock[$rs->items_id]['dauky_import']['number'])?$data_stock[$rs->items_id]['dauky_import']['number']+$num:$num;
                    $data_stock[$rs->items_id]['dauky_import']['price'] = isset($data_stock[$rs->items_id]['dauky_import']['price'])?$data_stock[$rs->items_id]['dauky_import']['price']+$num*$price:$num*$price;
                }
                $data_ex = array(
                    'where' => 'invoice_sell_item>0 AND stock_item = '.$rs->items_id.' AND stock_date < '.strtotime($batdau),
                );
                $stock_exs = $stock_model->getAllStock($data_ex);
                foreach ($stock_exs as $ex) {
                    $num = $ex->stock_number;
                    $price = $ex->stock_price;
                    $data_stock[$rs->items_id]['dauky_export']['number'] = isset($data_stock[$rs->items_id]['dauky_export']['number'])?$data_stock[$rs->items_id]['dauky_export']['number']+$num:$num;
                    $data_stock[$rs->items_id]['dauky_export']['price'] = isset($data_stock[$rs->items_id]['dauky_export']['price'])?$data_stock[$rs->items_id]['dauky_export']['price']+$num*$price:$num*$price;
                }

                $data_stock[$rs->items_id]['dauky']['number'] = $data_stock[$rs->items_id]['dauky_import']['number']-$data_stock[$rs->items_id]['dauky_export']['number'];
                $data_stock[$rs->items_id]['dauky']['price'] = $data_stock[$rs->items_id]['dauky_import']['price']-$data_stock[$rs->items_id]['dauky_export']['price'];


                $data_im = array(
                    'where' => '(invoice_buy_item>0 OR invoice_purchase_item>0) AND stock_item = '.$rs->items_id.' AND stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc),
                );
                $stock_ims = $stock_model->getAllStock($data_im);
                foreach ($stock_ims as $im) {
                    $num = $im->stock_number;
                    $price = $im->stock_price;
                    $data_stock[$rs->items_id]['import']['number'] = isset($data_stock[$rs->items_id]['import']['number'])?$data_stock[$rs->items_id]['import']['number']+$num:$num;
                    $data_stock[$rs->items_id]['import']['price'] = isset($data_stock[$rs->items_id]['import']['price'])?$data_stock[$rs->items_id]['import']['price']+$num*$price:$num*$price;
                }

                $sl_dauky = $data_stock[$rs->items_id]['dauky']['number'];
                $tt_dauky = $data_stock[$rs->items_id]['dauky']['price'];
                $sl_nhap = $data_stock[$rs->items_id]['import']['number'];
                $tt_nhap = $data_stock[$rs->items_id]['import']['price'];
                
                $sl_bq = $sl_dauky+$sl_nhap;
                $tt_bq = $tt_dauky+$tt_nhap;
                $dg_bq = round($tt_bq/$sl_bq,2);

                $items_name = $rs->items_code.' | '.$rs->items_name;
                if ($_POST['keyword'] != "*") {
                    $items_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->items_code.' | '.$rs->items_name);
                }
                echo '<li onclick="set_item(\''.$rs->items_id.'\',\''.$rs->items_code.'\',\''.$rs->items_name.'\',\''.$rs->items_unit.'\',\''.$rs->items_tax.'\',\''.$this->lib->formatMoney($dg_bq).'\',\''.$_POST['offset'].'\')">'.$items_name.'</li>';
            }
        }
    }

    public function getHouse(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $house_model = $this->model->get('houseModel');
            if ($_POST['keyword'] == "*") {
                $list = $house_model->getAllHouse();
            }
            else{
                $data = array(
                'where'=>'( house_code LIKE "%'.$_POST['keyword'].'%" OR house_name LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $house_model->getAllHouse($data);
            }
            foreach ($list as $rs) {
                $house_name = $rs->house_code.' | '.$rs->house_name;
                if ($_POST['keyword'] != "*") {
                    $house_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->house_code.' | '.$rs->house_name);
                }
                echo '<li onclick="set_item_house(\''.$rs->house_id.'\',\''.$rs->house_code.'\',\''.$rs->house_name.'\',\''.$_POST['offset'].'\')">'.$house_name.'</li>';
            }
        }
    }
    public function getCustomer(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $customer_model = $this->model->get('customerModel');
            if ($_POST['keyword'] == "*") {
                $list = $customer_model->getAllCustomer();
            }
            else{
                $data = array(
                'where'=>'( customer_code LIKE "%'.$_POST['keyword'].'%" OR customer_name LIKE "%'.$_POST['keyword'].'%" ) AND type_customer=1',
                );
                $list = $customer_model->getAllCustomer($data);
            }
            foreach ($list as $rs) {
                $customer_name = $rs->customer_code.' | '.$rs->customer_name;
                if ($_POST['keyword'] != "*") {
                    $customer_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->customer_code.' | '.$rs->customer_name);
                }
                echo '<li onclick="set_item_customer(\''.$rs->customer_id.'\',\''.$rs->customer_code.'\',\''.$rs->customer_name.'\',\''.$rs->customer_company.'\',\''.$rs->customer_mst.'\',\''.$rs->customer_address.'\',\''.$_POST['offset'].'\')">'.$customer_name.'</li>';
            }
        }
    }

    public function getAccount(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $account_model = $this->model->get('accountModel');
            if ($_POST['keyword'] == "*") {
                $list = $account_model->getAllAccount();
            }
            else{
                $data = array(
                'where'=>'( account_number LIKE "%'.$_POST['keyword'].'%" )',
                );
                $list = $account_model->getAllAccount($data);
            }
            foreach ($list as $rs) {
                $account_name = $rs->account_number.' | '.$rs->account_name;
                if ($_POST['keyword'] != "*") {
                    $account_name = str_replace($_POST['keyword'], '<b>'.$_POST['keyword'].'</b>', $rs->account_number.' | '.$rs->account_name);
                }
                echo '<li onclick="set_item_account(\''.$rs->account_id.'\',\''.$rs->account_number.'\',\''.$_POST['name'].'\',\''.$_POST['offset'].'\')">'.$account_name.'</li>';
            }
        }
    }
   
    public function add(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            
            $invoice_sell_model = $this->model->get('invoicesellModel');
            $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $account_model = $this->model->get('accountModel');
            $invoice_model = $this->model->get('invoiceModel');
            $house_model = $this->model->get('houseModel');
            $stock_model = $this->model->get('stockModel');

            $items = $_POST['item'];

            $data = array(
                        
                        'invoice_sell_document_date' => strtotime(str_replace('/','-',$_POST['invoice_sell_document_date'])),
                        'invoice_sell_document_number' => trim($_POST['invoice_sell_document_number']),
                        'invoice_sell_additional_date' => strtotime(str_replace('/','-',$_POST['invoice_sell_additional_date'])),
                        'invoice_sell_customer' => trim($_POST['invoice_sell_customer']),
                        'invoice_sell_number' => trim($_POST['invoice_sell_number']),
                        'invoice_sell_date' => strtotime(str_replace('/','-',$_POST['invoice_sell_date'])),
                        'invoice_sell_symbol' => trim($_POST['invoice_sell_symbol']),
                        'invoice_sell_bill_number' => trim($_POST['invoice_sell_bill_number']),
                        'invoice_sell_contract_number' => trim($_POST['invoice_sell_contract_number']),
                        'invoice_sell_money_type' => trim($_POST['invoice_sell_money_type']),
                        'invoice_sell_money_rate' => str_replace(',','',trim($_POST['invoice_sell_money_rate'])),
                        'invoice_sell_origin_doc' => trim($_POST['invoice_sell_origin_doc']),
                        'invoice_sell_comment' => trim($_POST['invoice_sell_comment']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $invoice_sell_model->queryInvoice('SELECT * FROM invoice_sell WHERE (invoice_sell_document_number='.$data['invoice_sell_document_number'].') AND invoice_sell_id!='.$_POST['yes']);
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $invoice_sell_model->updateInvoice($data,array('invoice_sell_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_invoice_sell = $_POST['yes'];

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|invoice_sell|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $invoice_sell_model->queryInvoice('SELECT * FROM invoice_sell WHERE (invoice_sell_document_number='.$data['invoice_sell_document_number'].')');
                if($check){
                    echo "Chứng từ này đã tồn tại";
                    return false;
                }
                else{
                    $data['invoice_sell_create_user'] = $_SESSION['userid_logined'];
                    $data['invoice_sell_create_date'] = strtotime(date('d-m-Y'));

                    $invoice_sell_model->createInvoice($data);
                    echo "Thêm thành công";

                $id_invoice_sell = $invoice_sell_model->getLastInvoice()->invoice_sell_id;

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$invoice_sell_model->getLastInvoice()->invoice_sell_id."|invoice_sell|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }

            if (isset($id_invoice_sell)) {
                $invoice_sells = $invoice_sell_model->getInvoice($id_invoice_sell);

                $money = 0;
                $tax = 0;
                $number = 0;

                $arr_item = "";
                foreach ($items as $v) {
                    if($v['invoice_sell_item'] != ""){
                        if ($arr_item=="") {
                            $arr_item .= $v['invoice_sell_item'];
                        }
                        else{
                            $arr_item .= ','.$v['invoice_sell_item'];
                        }

                        $debit = 0;
                        $credit = 0;
                        $tax_debit = 0;
                        $house = 0;
                        $house_debit = 0;
                        $house_credit = 0;
                        if (trim($v['invoice_sell_item_debit']) != "") {
                            $debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_sell_item_debit'])));
                            if (!$debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_sell_item_debit'])));
                                $debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $debit = $debits->account_id;
                            }
                        }
                        if (trim($v['invoice_sell_item_credit']) != "") {
                            $credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_sell_item_credit'])));
                            if (!$credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_sell_item_credit'])));
                                $credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $credit = $credits->account_id;
                            }
                        }
                        if (trim($v['invoice_sell_item_tax_vat_debit']) != "") {
                            $tax_debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_sell_item_tax_vat_debit'])));
                            if (!$tax_debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_sell_item_tax_vat_debit'])));
                                $tax_debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $tax_debit = $tax_debits->account_id;
                            }
                        }
                        if (trim($v['invoice_sell_item_house']) != "") {
                            $houses = $house_model->getHouseByWhere(array('house_code'=>trim($v['invoice_sell_item_house'])));
                            if (!$houses) {
                                $house_model->createHouse(array('house_code'=>trim($v['invoice_sell_item_house'])));
                                $house = $house_model->getLasthouse()->house_id;
                            }
                            else{
                                $house = $houses->house_id;
                            }
                        }
                        if (trim($v['invoice_sell_item_house_debit']) != "") {
                            $house_debits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_sell_item_house_debit'])));
                            if (!$house_debits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_sell_item_house_debit'])));
                                $house_debit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $house_debit = $house_debits->account_id;
                            }
                        }
                        if (trim($v['invoice_sell_item_house_credit']) != "") {
                            $house_credits = $account_model->getAccountByWhere(array('account_number'=>trim($v['invoice_sell_item_house_credit'])));
                            if (!$house_credits) {
                                $account_model->createAccount(array('account_number'=>trim($v['invoice_sell_item_house_credit'])));
                                $house_credit = $account_model->getLastAccount()->account_id;
                            }
                            else{
                                $house_credit = $house_credits->account_id;
                            }
                        }
                        
                        

                        $data_item = array(
                            'invoice_sell' => $id_invoice_sell,
                            'invoice_sell_item' => $v['invoice_sell_item'],
                            'invoice_sell_item_unit' => trim($v['invoice_sell_item_unit']),
                            'invoice_sell_item_number' => trim($v['invoice_sell_item_number']),
                            'invoice_sell_item_price' => str_replace(',', '', $v['invoice_sell_item_price']),
                            'invoice_sell_item_money' => str_replace(',', '', $v['invoice_sell_item_money']),
                            'invoice_sell_item_debit' => $debit,
                            'invoice_sell_item_credit' => $credit,
                            'invoice_sell_item_tax_vat_percent' => trim($v['invoice_sell_item_tax_vat_percent']),
                            'invoice_sell_item_tax_vat' => str_replace(',', '', $v['invoice_sell_item_tax_vat']),
                            'invoice_sell_item_tax_vat_debit' => $tax_debit,
                            'invoice_sell_item_total' => str_replace(',', '', $v['invoice_sell_item_total']),
                            'invoice_sell_item_house_debit' => $house_debit,
                            'invoice_sell_item_house_credit' => $house_credit,
                            'invoice_sell_item_house' => $house,
                            'invoice_sell_item_house_money' => str_replace(',', '', $v['invoice_sell_item_house_money']),
                        );

                        $number += $data_item['invoice_sell_item_number'];
                        $money += $data_item['invoice_sell_item_money'];
                        $tax += $data_item['invoice_sell_item_tax_vat'];

                        $invoice_sell_items = $invoice_sell_item_model->getInvoiceByWhere(array('invoice_sell'=>$data_item['invoice_sell'],'invoice_sell_item'=>$data_item['invoice_sell_item']));
                        if ($invoice_sell_items) {
                            $invoice_sell_item_model->updateInvoice($data_item,array('invoice_sell_item_id'=>$invoice_sell_items->invoice_sell_item_id));
                            $id_invoice_sell_item = $invoice_sell_items->invoice_sell_item_id;

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
                            $additional_model->updateAdditional($data_additional,array('invoice_sell_item'=>$id_invoice_sell_item,'credit'=>$invoice_sell_items->invoice_sell_item_credit));

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
                            $additional_model->updateAdditional($data_additional,array('invoice_sell_item'=>$id_invoice_sell_item,'debit'=>$invoice_sell_items->invoice_sell_item_house_debit));

                            $data_stock = array(
                                'invoice_sell_item'=>$id_invoice_sell_item,
                                'stock_date'=>$invoice_sells->invoice_sell_document_date,
                                'stock_item'=>$data_item['invoice_sell_item'],
                                'stock_number'=>$data_item['invoice_sell_item_number'],
                                'stock_price'=>$data_item['invoice_sell_item_house_money'],
                                'stock_house'=>$data_item['invoice_sell_item_house'],
                            );
                            $stock_model->updateStock($data_stock,array('invoice_sell_item'=>$id_invoice_sell_item));

                            if($invoice_sell_items->invoice_sell_item_tax_vat_debit > 0 && $data_item['invoice_sell_item_tax_vat_debit'] > 0){
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
                                $additional_model->updateAdditional($data_additional,array('invoice_sell_item'=>$id_invoice_sell_item,'credit'=>$invoice_sell_items->invoice_sell_item_tax_vat_debit));
                            }
                            else if($invoice_sell_items->invoice_sell_item_tax_vat_debit == 0 && $data_item['invoice_sell_item_tax_vat_debit'] > 0){
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
                            else{
                                $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_sell_item='.$id_invoice_sell_item.' AND credit='.$invoice_sell_items->invoice_sell_item_tax_vat_debit);
                            }
                        }
                        else{
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
                            
                        }

                        
                    }
                }

                $item_olds = $invoice_sell_item_model->queryInvoice('SELECT * FROM invoice_sell_item WHERE invoice_sell='.$id_invoice_sell.' AND invoice_sell_item NOT IN ('.$arr_item.')');
                foreach ($item_olds as $item_old) {
                    $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_sell_item='.$item_old->invoice_sell_item_id);
                    $stock_model->queryStock('DELETE FROM stock WHERE invoice_sell_item='.$item_old->invoice_sell_item_id);
                    $invoice_sell_item_model->queryInvoice('DELETE FROM invoice_sell_item WHERE invoice_sell_item_id='.$item_old->invoice_sell_item_id);
                }
                
                $data_buy = array(
                    'invoice_sell_number_total'=>$number,
                    'invoice_sell_money'=>$money,
                    'invoice_sell_tax_vat'=>$tax,
                    'invoice_sell_total'=>($money+$tax),
                );
                $invoice_sell_model->updateInvoice($data_buy,array('invoice_sell_id'=>$id_invoice_sell));

                $data_debit = array(
                    'invoice_sell'=>$id_invoice_sell,
                    'debit_date'=>$invoice_sells->invoice_sell_document_date,
                    'debit_customer'=>$invoice_sells->invoice_sell_customer,
                    'debit_money'=>($money+$tax),
                    'debit_comment'=>$invoice_sells->invoice_sell_comment,
                );

                $invoice_sell_debits = $debit_model->getDebitByWhere(array('invoice_sell'=>$id_invoice_sell,'debit_money'=>$invoice_sells->invoice_sell_total));
                if ($invoice_sell_debits) {
                    $debit_model->updateDebit($data_debit,array('debit_id'=>$invoice_sell_debits->debit_id));
                }
                else{
                    $debit_model->createDebit($data_debit);
                }

                $data_invoice = array(
                    'invoice_sell'=>$id_invoice_sell,
                    'invoice_symbol'=>$invoice_sells->invoice_sell_symbol,
                    'invoice_date'=>$invoice_sells->invoice_sell_date,
                    'invoice_number'=>$invoice_sells->invoice_sell_number,
                    'invoice_customer'=>$invoice_sells->invoice_sell_customer,
                    'invoice_money'=>$money,
                    'invoice_tax'=>$tax,
                    'invoice_comment'=>$invoice_sells->invoice_sell_comment,
                    'invoice_type'=>2,
                );

                $invoice_sell_invoices = $invoice_model->getInvoiceByWhere(array('invoice_sell'=>$id_invoice_sell));
                if ($invoice_sell_invoices) {
                    $invoice_model->updateInvoice($data_invoice,array('invoice_id'=>$invoice_sell_invoices->invoice_id));
                }
                else{
                    $invoice_model->createInvoice($data_invoice);
                }
            }
                    
        }
    }

    public function getitemadd(){
        if (isset($_POST['invoice_sell'])) {
            $account_model = $this->model->get('accountModel');
            $house_model = $this->model->get('houseModel');
            $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
            $join = array('table'=>'items','where'=>'invoice_sell_item=items_id');
            $invoice_sell_items = $invoice_sell_item_model->getAllInvoice(array('where'=>'invoice_sell='.$_POST['invoice_sell']),$join);

            $str = "";
            $str2 = "";
            $str3 = "";
            $i = 1;
            foreach ($invoice_sell_items as $item) {
                $debit = $account_model->getAccount($item->invoice_sell_item_debit)->account_number;
                $credit = $account_model->getAccount($item->invoice_sell_item_credit)->account_number;
                $tax_debit = "";
                if ($item->invoice_sell_item_tax_vat_debit > 0) {
                    $tax_debit = $account_model->getAccount($item->invoice_sell_item_tax_vat_debit)->account_number;
                }
                $house = "";
                $house_debit = "";
                $house_credit = "";
                if ($item->invoice_sell_item_house > 0) {
                    $house = $house_model->getHouse($item->invoice_sell_item_house)->house_code;
                }
                if ($item->invoice_sell_item_house_debit > 0) {
                    $house_debit = $account_model->getAccount($item->invoice_sell_item_house_debit)->account_number;
                }
                if ($item->invoice_sell_item_house_credit) {
                    $house_credit = $account_model->getAccount($item->invoice_sell_item_house_credit)->account_number;
                }

                $str .= '<tr>';
                $str .= '<td class="width-3">'.$i.'</td>';
                $str .= '<td class="width-10">
                  <input data="'.$item->invoice_sell_item.'" value="'.$item->items_code.'" type="text" name="invoice_sell_item[]" class="invoice_sell_item left" required="required" placeholder="Nhập mã hoặc tên" autocomplete="off">
                  <button type="button" class="find_item right" title="Danh mục"><i class="fa fa-search"></i></button>
                </td>';
                $str .= '<td>
                  <input value="'.$item->items_name.'" type="text" name="invoice_sell_item_name[]" class="invoice_sell_item_name" required="required" autocomplete="off">
                  <ul class="name_list_id"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$debit.'" type="text" name="invoice_sell_item_debit[]" class="invoice_sell_item_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_2"></ul>
                </td>';
                $str .= '<td class="width-7">
                  <input value="'.$credit.'" type="text" name="invoice_sell_item_credit[]" class="invoice_sell_item_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_3"></ul>
                </td>';
                $str .= '<td class="width-7"><input value="'.$item->invoice_sell_item_unit.'" type="text" name="invoice_sell_item_unit[]" class="invoice_sell_item_unit" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-7"><input value="'.$item->invoice_sell_item_number.'" type="text" name="invoice_sell_item_number[]" class="invoice_sell_item_number text-right" required="required" autocomplete="off"></td>';
                $str .= '<td class="width-10"><input value="'.$this->lib->formatMoney($item->invoice_sell_item_price).'" type="text" name="invoice_sell_item_price[]" class="invoice_sell_item_price numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$this->lib->formatMoney($item->invoice_sell_item_money).'" type="text" name="invoice_sell_item_money[]" class="invoice_sell_item_money numbers text-right" required="required" autocomplete="off"></td>';
                $str .= '<td><input value="'.$this->lib->formatMoney($item->invoice_sell_item_total).'" disabled type="text" name="invoice_sell_item_total[]" class="invoice_sell_item_total numbers text-right" required="required" autocomplete="off"></td>';
              $str .= '</tr>';

              $str2 .= '<tr>';
                $str2 .= '<td class="width-3">'.$i.'</td>';
                $str2 .= '<td class="width-10"><input value="'.$item->items_code.'" type="text" name="invoice_sell_item2[]" class="invoice_sell_item2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->items_name.'" type="text" name="invoice_sell_item_name2[]" class="invoice_sell_item_name2" disabled required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$item->invoice_sell_item_tax_vat_percent.'" type="text" name="invoice_sell_item_tax_vat_percent[]" class="invoice_sell_item_tax_vat_percent keep-val text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td><input value="'.$this->lib->formatMoney($item->invoice_sell_item_tax_vat).'" type="text" name="invoice_sell_item_tax_vat[]" class="invoice_sell_item_tax_vat numbers text-right" required="required" autocomplete="off"></td>';
                $str2 .= '<td>
                  <input value="'.$tax_debit.'" type="text" name="invoice_sell_item_tax_vat_debit[]" class="invoice_sell_item_tax_vat_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_4"></ul>
                </td>';
                
              $str2 .= '</tr>';

              $str3 .= '<tr>';
                $str3 .= '<td class="width-3">'.$i.'</td>';
                $str3 .= '<td class="width-10"><input value="'.$item->items_code.'" type="text" name="invoice_sell_item3[]" class="invoice_sell_item3" disabled required="required" autocomplete="off"></td>';
                $str3.= '<td><input value="'.$item->items_name.'" type="text" name="invoice_sell_item_name3[]" class="invoice_sell_item_name3" disabled required="required" autocomplete="off"></td>';
                $str3 .= '<td>
                  <input value="'.$house.'" type="text" name="invoice_sell_item_house[]" class="invoice_sell_item_house keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_5"></ul>
                </td>';
                $str3 .= '<td>
                  <input value="'.$house_debit.'" type="text" name="invoice_sell_item_house_debit[]" class="invoice_sell_item_house_debit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_6"></ul>
                </td>';
                $str3 .= '<td>
                  <input value="'.$house_credit.'" type="text" name="invoice_sell_item_house_credit[]" class="invoice_sell_item_house_credit keep-val" required="required" autocomplete="off">
                  <ul class="name_list_id_7"></ul>
                </td>';
                $str3 .= '<td><input value="'.$this->lib->formatMoney($item->invoice_sell_item_house_money).'" type="text" name="invoice_sell_item_house_money[]" class="invoice_sell_item_house_money numbers text-right" required="required" autocomplete="off"></td>';
                $str3 .= '</tr>';

              $i++;
            }

            $arr = array(
                'hang'=>$str,
                'thue'=>$str2,
                'von'=>$str3,
            );
            echo json_encode($arr);
        }
    }

    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $invoice_sell_model = $this->model->get('invoicesellModel');
            $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
            $additional_model = $this->model->get('additionalModel');
            $debit_model = $this->model->get('debitModel');
            $invoice_model = $this->model->get('invoiceModel');
            $stock_model = $this->model->get('stockModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $invoice_sells = $invoice_sell_model->getInvoice($data);
                    $invoice_sell_items = $invoice_sell_item_model->getAllInvoice(array('where'=>'invoice_sell='.$data));
                    foreach ($invoice_sell_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_sell_item='.$item->invoice_sell_item_id);
                        $stock_model->queryStock('DELETE FROM stock WHERE invoice_sell_item='.$item->invoice_sell_item_id);
                    }
                    $invoice_sell_item_model->queryInvoice('DELETE FROM invoice_sell_item WHERE invoice_sell='.$data);
                    $debit_model->queryDebit('DELETE FROM debit WHERE invoice_sell='.$data.' AND debit_money='.$invoice_sells->invoice_sell_total);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE invoice_sell='.$data);
                       $invoice_sell_model->deleteInvoice($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|invoice_sell|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                    $invoice_sells = $invoice_sell_model->getInvoice($_POST['data']);
                    $invoice_sell_items = $invoice_sell_item_model->getAllInvoice(array('where'=>'invoice_sell='.$_POST['data']));
                    foreach ($invoice_sell_items as $item) {
                        $additional_model->queryAdditional('DELETE FROM additional WHERE invoice_sell_item='.$item->invoice_sell_item_id);
                        $stock_model->queryStock('DELETE FROM stock WHERE invoice_sell_item='.$item->invoice_sell_item_id);
                    }
                    $invoice_sell_item_model->queryInvoice('DELETE FROM invoice_sell_item WHERE invoice_sell='.$_POST['data']);
                    $debit_model->queryDebit('DELETE FROM debit WHERE invoice_sell='.$_POST['data'].' AND debit_money='.$invoice_sells->invoice_sell_total);
                    $invoice_model->queryInvoice('DELETE FROM invoice WHERE invoice_sell='.$_POST['data']);
                        $invoice_sell_model->deleteInvoice($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|invoice_sell|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
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

            $invoice_sell_model = $this->model->get('invoicesellModel');
            $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
            $debit_model = $this->model->get('debitModel');
            $additional_model = $this->model->get('additionalModel');
            $invoice_model = $this->model->get('invoiceModel');

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

                
                if ($nameWorksheet=="HDBH") {
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
                            
                            $invoice_sells = $invoice_sell_model->getInvoiceByWhere(array('invoice_sell_document_number'=>$val[3]));
                            if ($invoice_sells) {
                                $invoice_sell_model->updateInvoice(array('invoice_sell_comment'=>$val[10]),array('invoice_sell_id'=>$invoice_sells->invoice_sell_id));
                                $invoice_sell_items = $invoice_sell_item_model->getAllInvoice(array('where'=>'invoice_sell='.$invoice_sells->invoice_sell_id));
                                foreach ($invoice_sell_items as $invoice_sell_item) {
                                    $additional_model->updateAdditional(array('additional_comment'=>$val[10]),array('invoice_sell_item'=>$invoice_sell_item->invoice_sell_item_id));
                                }

                                $debit_model->updateDebit(array('debit_comment'=>$val[10]),array('invoice_sell'=>$invoice_sells->invoice_sell_id));
                                $invoice_model->updateInvoice(array('invoice_comment'=>$val[10]),array('invoice_sell'=>$invoice_sells->invoice_sell_id));
                            }
                        }
                        
                    }//
                }//HDBH
                
                

                $i++;
            }
            return $this->view->redirect('invoicesell');
        }
        $this->view->show('invoicesell/import');
    }


}
?>