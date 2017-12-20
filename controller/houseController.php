<?php

Class houseController Extends baseController {

    public function index() {

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->house) || json_decode($_SESSION['user_permission_action'])->house != "house") {
            $this->view->data['disable_control'] = 1;
        }

        $this->view->data['lib'] = $this->lib;

        $this->view->data['title'] = 'Quản lý kho vật tư';



        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : null;

            $order = isset($_POST['order']) ? $_POST['order'] : null;

            $page = isset($_POST['page']) ? $_POST['page'] : null;

            $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;

            $limit = isset($_POST['limit']) ? $_POST['limit'] : 18446744073709;

        }

        else{

            $order_by = $this->registry->router->order_by ? $this->registry->router->order_by : 'house_name';

            $order = $this->registry->router->order ? $this->registry->router->order : 'ASC';

            $page = $this->registry->router->page ? (int) $this->registry->router->page : 1;

            $keyword = "";

            $limit = 18446744073709;

        }

        $id = $this->registry->router->param_id;

        

        $house_model = $this->model->get('houseModel');

        $sonews = $limit;

        $x = ($page-1) * $sonews;

        $pagination_stages = 2;

        $data = array(
            'where' => '1=1',
        );

        if (isset($id) && $id > 0) {
            $data['where'] .= ' AND house_id = '.$id;
        }

        $tongsodong = count($house_model->getAllHouse($data));

        $tongsotrang = ceil($tongsodong / $sonews);

        



        $this->view->data['page'] = $page;

        $this->view->data['order_by'] = $order_by;

        $this->view->data['order'] = $order;

        $this->view->data['keyword'] = $keyword;

        $this->view->data['limit'] = $limit;

        $this->view->data['pagination_stages'] = $pagination_stages;

        $this->view->data['tongsotrang'] = $tongsotrang;

        $this->view->data['sonews'] = $sonews;



        $data = array(

            'order_by'=>$order_by,

            'order'=>$order,

            'limit'=>$x.','.$sonews,

            'where'=>'1=1',

            );

        if (isset($id) && $id > 0) {
            $data['where'] .= ' AND house_id = '.$id;
        }
        

        if ($keyword != '') {

            $search = '( house_name LIKE "%'.$keyword.'%" OR house_place LIKE "%'.$keyword.'%")';

            $data['where'] = $search;

        }

        

        

        

        $this->view->data['houses'] = $house_model->getAllHouse($data);



        $this->view->data['lastID'] = isset($house_model->getLastHouse()->house_id)?$house_model->getLastHouse()->house_id:0;

        

        $this->view->show('house/index');

    }



    



    public function add(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if (!isset(json_decode($_SESSION['user_permission_action'])->house) || json_decode($_SESSION['user_permission_action'])->house != "house") {

            return $this->view->redirect('user/login');

        }

        if (isset($_POST['yes'])) {

            $house = $this->model->get('houseModel');

            $data = array(
                        'house_code' => trim($_POST['house_code']),

                        'house_name' => trim($_POST['house_name']),

                        'house_place' => trim($_POST['house_place']),

                        );

            if ($_POST['yes'] != "") {

                //$data['house_update_user'] = $_SESSION['userid_logined'];

                //$data['house_update_time'] = time();

                //var_dump($data);
                if ($house->checkHouse($_POST['yes'],trim($_POST['house_code']))) {

                    echo "Thông tin này đã tồn tại";

                    return false;

                }

                else if ($house->checkHouse($_POST['yes'],trim($_POST['house_name']))) {

                    echo "Thông tin này đã tồn tại";

                    return false;

                }

                else{

                    $house->updateHouse($data,array('house_id' => $_POST['yes']));

                    echo "Cập nhật thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."edit"."|".$_POST['yes']."|house|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                    }

            }

            else{

                //$data['house_create_user'] = $_SESSION['userid_logined'];

                //$data['staff'] = $_POST['staff'];

                //var_dump($data);

                if ($house->getHouseByWhere(array('house_code'=>trim($_POST['house_code'])))) {

                    echo "Thông tin này đã tồn tại";

                    return false;

                }

                else if ($house->getHouseByWhere(array('house_name'=>trim($_POST['house_name'])))) {

                    echo "Thông tin này đã tồn tại";

                    return false;

                }

                else{

                    $house->createHouse($data);

                    echo "Thêm thành công";

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."add"."|".$house->getLastHouse()->house_id."|house|".implode("-",$data)."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                

            }

                    

        }

    }



    

    



    public function delete(){

        $this->view->setLayout('admin');

        if (!isset($_SESSION['userid_logined'])) {

            return $this->view->redirect('user/login');

        }

        if (!isset(json_decode($_SESSION['user_permission_action'])->house) || json_decode($_SESSION['user_permission_action'])->house != "house") {

            return $this->view->redirect('user/login');

        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $house = $this->model->get('houseModel');

            if (isset($_POST['xoa'])) {

                $data = explode(',', $_POST['xoa']);

                foreach ($data as $data) {

                    $house->deleteHouse($data);

                    date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$data."|house|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);

                }

                return true;

            }

            else{

                
                date_default_timezone_set("Asia/Ho_Chi_Minh"); 

                        $filename = "action_logs.txt";

                        $text = date('d/m/Y H:i:s')."|".$_SESSION['user_logined']."|"."delete"."|".$_POST['data']."|house|"."\n"."\r\n";

                        

                        $fh = fopen($filename, "a") or die("Could not open log file.");

                        fwrite($fh, $text) or die("Could not write file!");

                        fclose($fh);



                return $house->deleteHouse($_POST['data']);

            }

            

        }

    }




}

?>