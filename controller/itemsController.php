<?php
Class itemsController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->items) || json_decode($_SESSION['user_permission_action'])->items != "items") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Danh mục hàng hóa, dịch vụ';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;
            $order = isset($_POST['order']) ? $_POST['order'] : null;
            $page = isset($_POST['page']) ? $_POST['page'] : null;
            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;
        }
        else{
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'items_code ASC, items_name';
            $order = $this->registry->router->order_by ? $this->registry->router->order_by : 'ASC';
            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;
            $keyword = "";
            $limit = 18446744073709;
        }
        
        $items_model = $this->model->get('itemsModel');

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => '1=1',
        );
        
        
        $tongsodong = count($items_model->getAllItems($data));
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
            $search = '( items_code LIKE "%'.$keyword.'%" 
                    OR items_name LIKE "%'.$keyword.'%" 
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        

        
        $this->view->data['itemss'] = $items_model->getAllItems($data);
        $this->view->data['lastID'] = isset($items_model->getLastItems()->items_id)?$items_model->getLastItems()->items_id:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('items/index');
    }

   
   
    public function add(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (isset($_POST['yes'])) {
            $stock_model = $this->model->get('stockModel');
            $additional_model = $this->model->get('additionalModel');
            $items_model = $this->model->get('itemsModel');
            $account_model = $this->model->get('accountModel');
            $acc_156 = $account_model->getAccountByWhere(array('account_number'=>'156'))->account_id;

            $data = array(
                        
                        'items_code' => trim($_POST['items_code']),
                        'items_name' => trim($_POST['items_name']),
                        'items_unit' => trim($_POST['items_unit']),
                        'items_type' => trim($_POST['items_type']),
                        'items_tax' => trim($_POST['items_tax']),
                        'items_stuff' => trim($_POST['items_stuff']),
                        'items_number_dauky' => str_replace(',','',$_POST['items_number_dauky']),
                        'items_price_dauky' => str_replace(',','',$_POST['items_price_dauky']),
                        );
            

            if ($_POST['yes'] != "") {
                $check = $items_model->queryItems('SELECT * FROM items WHERE (items_code='.$data['items_code'].' OR items_name='.$data['items_name'].') AND items_id!='.$_POST['yes']);
                if($check){
                    echo "Thông tin đã tồn tại";
                    return false;
                }
                else{
                    $items_model->updateItems($data,array('items_id' => trim($_POST['yes'])));
                    echo "Cập nhật thành công";

                    $id_items = $_POST['yes'];

                    $qr = $additional_model->getAdditionalByWhere(array('items'=>$id_items,'debit'=>$acc_156,'additional_date'=>1));
                    if ($qr) {
                        $data_stock = array(
                            'items'=>$id_items,
                            'stock_date'=>1,
                            'stock_item'=>$id_items,
                            'stock_number'=>$data['items_number_dauky'],
                            'stock_price'=>round($data['items_price_dauky']/$data['items_number_dauky'],2),
                            'stock_house'=>1,
                        );
                        $stock_model->updateStock($data_stock,array('items'=>$id_items,'stock_date'=>1));

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
                        $additional_model->updateAdditional($data_additional,array('items'=>$id_items,'debit'=>$acc_156,'additional_date'=>1));
                    }
                    else{
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
                    

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|items|".implode("-",$data)."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                }
                
            }
            else{
                $check = $items_model->queryItems('SELECT * FROM items WHERE (items_code='.$data['items_code'].' OR items_name='.$data['items_name'].')');
                if($check){
                    echo "Thông tin đã tồn tại";
                    return false;
                }
                else{
                    $items_model->createItems($data);
                    echo "Thêm thành công";

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

                date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                    $filename = "action_logs.txt";
                    $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$items_model->getLastItems()->items_id."|items|".implode("-",$data)."\n"."\r\n";
                    
                    $fh = fopen($filename, "a") or die("Could not open log file.");
                    fwrite($fh, $text) or die("Could not write file!");
                    fclose($fh);
                }
                    
                
            }
                    
        }
    }

    public function delete(){
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $items_model = $this->model->get('itemsModel');
            $stock_model = $this->model->get('stockModel');
            $additional_model = $this->model->get('additionalModel');
           
            if (isset($_POST['xoa'])) {
                $data = explode(',', $_POST['xoa']);
                foreach ($data as $data) {
                    $stock_model->queryStock('DELETE FROM stock WHERE items='.$data);
                    $additional_model->queryAdditional('DELETE FROM additional WHERE items='.$data);
                       $items_model->deleteItems($data);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|items|"."\n"."\r\n";
                        
                        $fh = fopen($filename, "a") or die("Could not open log file.");
                        fwrite($fh, $text) or die("Could not write file!");
                        fclose($fh);
                    
                    
                }
                return true;
            }
            else{
                    $stock_model->queryStock('DELETE FROM stock WHERE items='.$_POST['data']);
                    $additional_model->queryAdditional('DELETE FROM additional WHERE items='.$_POST['data']);
                        $items_model->deleteItems($_POST['data']);
                        echo "Xóa thành công";
                        date_default_timezone_set("Asia/Ho_Chi_Minh"); 
                        $filename = "action_logs.txt";
                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|items|"."\n"."\r\n";
                        
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

            $items_model = $this->model->get('itemsModel');
            $account_model = $this->model->get('accountModel');
            $stock_model = $this->model->get('stockModel');
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

                
                if ($nameWorksheet=="HANGHOA") {
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

                            $items = $items_model->getItemsByWhere(array('items_code'=>$data['items_code']));
                            if ($items) {
                                $items_model->updateItems($data,array('items_id'=>$items->items_id));
                                $id_items = $items->items_id;
                            }
                            else{
                                $items_model->createItems($data);
                                $id_items = $items_model->getLastItems()->items_id;
                            }
                            
                            $qr = $additional_model->getAdditionalByWhere(array('items'=>$id_items,'debit'=>$acc_156,'additional_date'=>1));
                            if ($qr) {
                                $data_stock = array(
                                    'items'=>$id_items,
                                    'stock_date'=>1,
                                    'stock_item'=>$id_items,
                                    'stock_number'=>$data['items_number_dauky'],
                                    'stock_price'=>round($data['items_price_dauky']/$data['items_number_dauky'],2),
                                    'stock_house'=>1,
                                );
                                $stock_model->updateStock($data_stock,array('items'=>$id_items,'stock_date'=>1));

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
                                $additional_model->updateAdditional($data_additional,array('items'=>$id_items,'debit'=>$acc_156,'additional_date'=>1));
                            }
                            else{
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
                            
                        }
                        
                    }//
                }//HANGHOA
                
                

                $i++;
            }
            return $this->view->redirect('items');
        }
        $this->view->show('items/import');
    }

    
    

}
?>