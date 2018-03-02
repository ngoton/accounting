<?php
Class stockController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->stock) || json_decode($_SESSION['user_permission_action'])->stock != "stock") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Tổng hợp nhập xuất tồn';

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
            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'items_code';
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


        $items_model = $this->model->get('itemsModel');
        $house_model = $this->model->get('houseModel');

        $houses = $house_model->getAllHouse(array('order_by'=>'house_code ASC'));
        $this->view->data['houses'] = $houses;

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'items_type=1',
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
            'where' => 'items_type=1',
            );
        
      
        if ($keyword != '') {
            $search = '( items_code LIKE "%'.$keyword.'%" 
                    OR items_name LIKE "%'.$keyword.'%"
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        
        $items = $items_model->getAllItems($data);
        
        $this->view->data['items'] = $items;

        $stock_model = $this->model->get('stockModel');
        $data_stock = array();
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
            if ($trangthai>0) {
                $data_im['where'] .= ' AND stock_house='.$trangthai;
            }
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
            if ($trangthai>0) {
                $data_ex['where'] .= ' AND stock_house='.$trangthai;
            }
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
            if ($trangthai>0) {
                $data_im['where'] .= ' AND stock_house='.$trangthai;
            }
            $stock_ims = $stock_model->getAllStock($data_im);
            foreach ($stock_ims as $im) {
                $num = $im->stock_number;
                $price = $im->stock_price;
                $data_stock[$item->items_id]['import']['number'] = isset($data_stock[$item->items_id]['import']['number'])?$data_stock[$item->items_id]['import']['number']+$num:$num;
                $data_stock[$item->items_id]['import']['price'] = isset($data_stock[$item->items_id]['import']['price'])?$data_stock[$item->items_id]['import']['price']+$num*$price:$num*$price;
            }

            $data_ex = array(
                'where' => 'invoice_sell_item>0 AND stock_item = '.$item->items_id.' AND stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc),
            );
            if ($trangthai>0) {
                $data_ex['where'] .= ' AND stock_house='.$trangthai;
            }
            $stock_exs = $stock_model->getAllStock($data_ex);
            foreach ($stock_exs as $ex) {
                $num = $ex->stock_number;
                $price = $ex->stock_price;
                $data_stock[$item->items_id]['export']['number'] = isset($data_stock[$item->items_id]['export']['number'])?$data_stock[$item->items_id]['export']['number']+$num:$num;
                $data_stock[$item->items_id]['export']['price'] = isset($data_stock[$item->items_id]['export']['price'])?$data_stock[$item->items_id]['export']['price']+$num*$price:$num*$price;
            }
        }

        $this->view->data['data_stock'] = $data_stock;

        $this->view->data['lastID'] = isset($items_model->getLastItems()->items_id)?$items_model->getLastItems()->items_id:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('stock/index');
    }


    public function updateprice(){
        if (isset($_POST['val'])) {
            $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
            $stock_model = $this->model->get('stockModel');
            $additional_model = $this->model->get('additionalModel');
            $thang = str_replace('/', '-', $_POST['val']);
            $batdau = '01-'.$thang;
            $ketthuc = date('t-m-Y',strtotime($batdau));
            $ngayketthuc = date('d-m-Y',strtotime('+1 day', strtotime($ketthuc)));

            $items_model = $this->model->get('itemsModel');
            $items = $items_model->getAllItems(array('where'=>'items_type=1'));
            $data_stock = array();
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

            }

            $join = array('table'=>'invoice_sell','where'=>'invoice_sell=invoice_sell_id');
            $data = array(
                'where'=>'invoice_sell_document_date >= '.strtotime($batdau).' AND invoice_sell_document_date < '.strtotime($ngayketthuc),
            );

            $invoices = $invoice_sell_item_model->getAllInvoice($data,$join);
            
            foreach ($invoices as $invoice) {
                $sl_dauky = $data_stock[$invoice->invoice_sell_item]['dauky']['number'];
                $tt_dauky = $data_stock[$invoice->invoice_sell_item]['dauky']['price'];
                $sl_nhap = $data_stock[$invoice->invoice_sell_item]['import']['number'];
                $tt_nhap = $data_stock[$invoice->invoice_sell_item]['import']['price'];
                $sl_xuat = $data_stock[$invoice->invoice_sell_item]['export']['number'];
                $tt_xuat = $data_stock[$invoice->invoice_sell_item]['export']['price'];
                
                $sl_bq = $sl_dauky+$sl_nhap;
                $tt_bq = $tt_dauky+$tt_nhap;
                $dg_bq = round($tt_bq/$sl_bq,2);

                //$tt_xuat = $tt_xuat==0?$sl_xuat*$dg_bq:$tt_xuat;

                //$dg_xuat = round($tt_xuat/$sl_xuat);
                $dg_xuat=$dg_bq;

                $invoice_sell_item_model->updateInvoice(array('invoice_sell_item_house_money'=>$dg_xuat),array('invoice_sell_item_id'=>$invoice->invoice_sell_item_id));
                $stock_model->updateStock(array('stock_price'=>$dg_xuat),array('invoice_sell_item'=>$invoice->invoice_sell_item_id));
                $additional_model->updateAdditional(array('money'=>($dg_xuat*$invoice->invoice_sell_item_number)),array('invoice_sell_item'=>$invoice->invoice_sell_item_id,'debit'=>$invoice->invoice_sell_item_house_debit,'credit'=>$invoice->invoice_sell_item_house_credit));
            }

            echo "Cập nhật giá thành công";
        }
    }

}
?>