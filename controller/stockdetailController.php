<?php
Class stockdetailController Extends baseController {
    public function index() {
        $this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->stockdetail) || json_decode($_SESSION['user_permission_action'])->stockdetail != "stockdetail") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Chi tiết nhập xuất tồn';

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

        $item_lists = $items_model->getAllItems(array('where'=>'items_type=1','order_by'=>'items_code ASC'));
        $this->view->data['item_lists'] = $item_lists;

        $sonews = $limit;
        $x = ($page-1) * $sonews;
        $pagination_stages = 2;
        
        $data = array(
            'where' => 'items_type=1',
        );
        if ($trangthai>0) {
            $data['where'] .= ' AND items_id='.$trangthai;
        }

        
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
        if ($trangthai>0) {
            $data['where'] .= ' AND items_id='.$trangthai;
        }
        
      
        if ($keyword != '') {
            $search = '( items_code LIKE "%'.$keyword.'%" 
                    OR items_name LIKE "%'.$keyword.'%"
                )';
            
                $data['where'] = $data['where'].' AND '.$search;
        }

        
        $items = $items_model->getAllItems($data);
        
        $this->view->data['items'] = $items;

        $invoice_buy_item_model = $this->model->get('invoicebuyitemModel');
        $invoice_sell_item_model = $this->model->get('invoicesellitemModel');
        $invoice_purchase_item_model = $this->model->get('invoicepurchaseitemModel');

        $data_invoice_buy = array();
        $data_invoice_sell = array();
        $data_invoice_purchase = array();

        $qr_buy = 'SELECT invoice_buy_document_number, invoice_buy_document_date, invoice_buy_comment, invoice_buy_item_id FROM invoice_buy,invoice_buy_item WHERE invoice_buy=invoice_buy_id AND invoice_buy_item_id IN (SELECT invoice_buy_item FROM stock WHERE stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc).')';
        $invoice_buys = $invoice_buy_item_model->queryInvoice($qr_buy);
        foreach ($invoice_buys as $invoice_buy) {
            $data_invoice_buy[$invoice_buy->invoice_buy_item_id]['number'] = $invoice_buy->invoice_buy_document_number;
            $data_invoice_buy[$invoice_buy->invoice_buy_item_id]['date'] = $invoice_buy->invoice_buy_document_date;
            $data_invoice_buy[$invoice_buy->invoice_buy_item_id]['comment'] = $invoice_buy->invoice_buy_comment;
        }

        $qr_sell = 'SELECT invoice_sell_document_number, invoice_sell_document_date, invoice_sell_comment, invoice_sell_item_id FROM invoice_sell,invoice_sell_item WHERE invoice_sell=invoice_sell_id AND invoice_sell_item_id IN (SELECT invoice_sell_item FROM stock WHERE stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc).')';
        $invoice_sells = $invoice_sell_item_model->queryInvoice($qr_sell);
        foreach ($invoice_sells as $invoice_sell) {
            $data_invoice_sell[$invoice_sell->invoice_sell_item_id]['number'] = $invoice_sell->invoice_sell_document_number;
            $data_invoice_sell[$invoice_sell->invoice_sell_item_id]['date'] = $invoice_sell->invoice_sell_document_date;
            $data_invoice_sell[$invoice_sell->invoice_sell_item_id]['comment'] = $invoice_sell->invoice_sell_comment;
        }

        $qr_purchase = 'SELECT invoice_purchase_document_number, invoice_purchase_document_date, invoice_purchase_comment, invoice_purchase_item_id FROM invoice_purchase,invoice_purchase_item WHERE invoice_purchase=invoice_purchase_id AND invoice_purchase_item_id IN (SELECT invoice_purchase_item FROM stock WHERE stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc).')';
        $invoice_purchases = $invoice_purchase_item_model->queryInvoice($qr_purchase);
        foreach ($invoice_purchases as $invoice_purchase) {
            $data_invoice_purchase[$invoice_purchase->invoice_purchase_item_id]['number'] = $invoice_purchase->invoice_purchase_document_number;
            $data_invoice_purchase[$invoice_purchase->invoice_purchase_item_id]['date'] = $invoice_purchase->invoice_purchase_document_date;
            $data_invoice_purchase[$invoice_purchase->invoice_purchase_item_id]['comment'] = $invoice_purchase->invoice_purchase_comment;
        }

        $this->view->data['data_invoice_buy'] = $data_invoice_buy;
        $this->view->data['data_invoice_sell'] = $data_invoice_sell;
        $this->view->data['data_invoice_purchase'] = $data_invoice_purchase;

        $stock_model = $this->model->get('stockModel');
        $data_stock = array();
        $stock = array();
        foreach ($items as $item) {
            $data_i = array(
                'where' => 'stock_item = '.$item->items_id.' AND stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc),
                'order_by' => 'stock_date',
                'order' => 'ASC',
            );
            
            $stock[$item->items_id] = $stock_model->getAllStock($data_i);

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

            $data_ex = array(
                'where' => 'invoice_sell_item>0 AND stock_item = '.$item->items_id.' AND stock_date >= '.strtotime($batdau).' AND stock_date < '.strtotime($ngayketthuc),
            );
            
            $stock_exs = $stock_model->getAllStock($data_ex);
            foreach ($stock_exs as $ex) {
                $num = $ex->stock_number;
                $price = $ex->stock_price;
                $data_stock[$item->items_id]['export']['number'] = isset($data_stock[$item->items_id]['export']['number'])?$data_stock[$item->items_id]['export']['number']+$num:$num;
                $data_stock[$item->items_id]['export']['price'] = isset($data_stock[$item->items_id]['export']['price'])?$data_stock[$item->items_id]['export']['price']+$num*$price:$num*$price;
            }
        }

        $this->view->data['data_stock'] = $data_stock;
        $this->view->data['stocks'] = $stock;

        $this->view->data['lastID'] = isset($items_model->getLastItems()->items_id)?$items_model->getLastItems()->items_id:0;

        /* Lấy tổng doanh thu*/
        
        /*************/
        $this->view->show('stockdetail/index');
    }

}
?>