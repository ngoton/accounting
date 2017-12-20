<?php
Class forwardController Extends baseController {
    public function index() {
    	$this->view->setLayout('admin');
        if (!isset($_SESSION['userid_logined'])) {
            return $this->view->redirect('user/login');
        }
        if (!isset(json_decode($_SESSION['user_permission_action'])->forward) || json_decode($_SESSION['user_permission_action'])->forward != "forward") {
            $this->view->data['disable_control'] = 1;
        }
        $this->view->data['lib'] = $this->lib;
        $this->view->data['title'] = 'Kết chuyển cuối kỳ';

        $ketthuc = isset($_POST['ngaythang'])?$_POST['ngaythang']:date('t-m-Y');
        $ngayketthuc = date('t-m-Y',strtotime('+1 day', strtotime($ketthuc)));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $additional_date = strtotime(str_replace('/', '-', $_POST['additional_date']));
            $ketthuc = date('t-m-Y',$additional_date);
            $ngayketthuc = date('t-m-Y',strtotime('+1 day', $additional_date));
        }

        $this->view->data['ketthuc'] = $ketthuc;

        $account_model = $this->model->get('accountModel');
        $accounts = $account_model->getAllAccount();

        $additional_model = $this->model->get('additionalModel');
        $data_additional = array();

        $additionals = $additional_model->getAllAdditional(array('where'=>'additional_date < '.strtotime($ngayketthuc)));
        
        foreach ($additionals as $additional) {
            $data_additional[$additional->debit]['no']['dauky'] = isset($data_additional[$additional->debit]['no']['dauky'])?$data_additional[$additional->debit]['no']['dauky']+$additional->money:$additional->money;
            $data_additional[$additional->credit]['co']['dauky'] = isset($data_additional[$additional->credit]['co']['dauky'])?$data_additional[$additional->credit]['co']['dauky']+$additional->money:$additional->money;
        }

        foreach ($accounts as $account) {
            if ($account->account_parent>0) {
                $nodauky[$account->account_id]=isset($data_additional[$account->account_id]['no']['dauky'])?$data_additional[$account->account_id]['no']['dauky']:0;
                $codauky[$account->account_id]=isset($data_additional[$account->account_id]['co']['dauky'])?$data_additional[$account->account_id]['co']['dauky']:0;
                
                $data_additional[$account->account_parent]['no']['dauky'] = isset($data_additional[$account->account_parent]['no']['dauky'])?$data_additional[$account->account_parent]['no']['dauky']+$nodauky[$account->account_id]:$nodauky[$account->account_id];
                $data_additional[$account->account_parent]['co']['dauky'] = isset($data_additional[$account->account_parent]['co']['dauky'])?$data_additional[$account->account_parent]['co']['dauky']+$codauky[$account->account_id]:$codauky[$account->account_id];
                
            }
        }

        $this->view->data['data_additional'] = $data_additional;
        $this->view->data['accounts'] = $accounts;

        $this->view->show('forward/index');
    }

    public function delete(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $additional_model = $this->model->get('additionalModel');
            $additional_date = strtotime(str_replace('/', '-', $_POST['additional_date']));
            $first = strtotime(date('01-m-Y',$additional_date));
            $last = strtotime(date('t-m-Y',strtotime('+1 day', $additional_date)));
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_413'].' AND credit='.$_POST['acc_515'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_635'].' AND credit='.$_POST['acc_413'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_5111'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_5112'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_5113'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_5118'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_515'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_632'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_635'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_6421'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_6422'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_711'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_811'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_821'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_4212'].' AND additional_date>='.$first.' AND additional_date<'.$last);
            $additional_model->queryAdditional('DELETE FROM additional WHERE debit='.$_POST['acc_4212'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last);

            echo "Đã xóa thành công!";
        }
    }

   public function complete(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $additional_model = $this->model->get('additionalModel');
            
            $document_date = strtotime(str_replace('/', '-', $_POST['document_date']));
            $additional_date = strtotime(str_replace('/', '-', $_POST['additional_date']));
            $document_number = trim($_POST['document_number']);
            $comment_413_515 = trim($_POST['comment_413_515']);
            $money_413_515 = str_replace(',', '', $_POST['money_413_515']);
            $comment_413_635 = trim($_POST['comment_413_635']);
            $money_413_635 = str_replace(',', '', $_POST['money_413_635']);
            $comment_511_911 = trim($_POST['comment_511_911']);
            $money_5111_911 = str_replace(',', '', $_POST['money_5111_911']);
            $money_5112_911 = str_replace(',', '', $_POST['money_5112_911']);
            $money_5113_911 = str_replace(',', '', $_POST['money_5113_911']);
            $money_5118_911 = str_replace(',', '', $_POST['money_5118_911']);
            $comment_515_911 = trim($_POST['comment_515_911']);
            $money_515_911 = str_replace(',', '', $_POST['money_515_911']);
            $comment_632_911 = trim($_POST['comment_632_911']);
            $money_632_911 = str_replace(',', '', $_POST['money_632_911']);
            $comment_635_911 = trim($_POST['comment_635_911']);
            $money_635_911 = str_replace(',', '', $_POST['money_635_911']);
            $comment_642_911 = trim($_POST['comment_642_911']);
            $money_6421_911 = str_replace(',', '', $_POST['money_6421_911']);
            $money_6422_911 = str_replace(',', '', $_POST['money_6422_911']);
            $comment_711_911 = trim($_POST['comment_711_911']);
            $money_711_911 = str_replace(',', '', $_POST['money_711_911']);
            $comment_811_911 = trim($_POST['comment_811_911']);
            $money_811_911 = str_replace(',', '', $_POST['money_811_911']);
            $comment_821_911 = trim($_POST['comment_821_911']);
            $money_821_911 = str_replace(',', '', $_POST['money_821_911']);
            $comment_911_4212 = trim($_POST['comment_911_4212']);
            $money_911_4212 = str_replace(',', '', $_POST['money_911_4212']);
            $money_4212_911 = str_replace(',', '', $_POST['money_4212_911']);

            $first = strtotime(date('01-m-Y',$additional_date));
            $last = strtotime(date('t-m-Y',strtotime('+1 day', $additional_date)));

            $result = array(
                'result_413_515'=>'Lỗi',
                'result_413_635'=>'Lỗi',
                'result_511_911'=>'Lỗi',
                'result_515_911'=>'Lỗi',
                'result_632_911'=>'Lỗi',
                'result_635_911'=>'Lỗi',
                'result_642_911'=>'Lỗi',
                'result_711_911'=>'Lỗi',
                'result_811_911'=>'Lỗi',
                'result_821_911'=>'Lỗi',
                'result_911_4212'=>'Lỗi',
            );

            $qr1 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_413'].' AND credit='.$_POST['acc_515'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr1) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_413_515,
                    'debit'=>$_POST['acc_413'],
                    'credit'=>$_POST['acc_515'],
                    'money'=>$money_413_515,
                    );
                $additional_model->createAdditional($data);
                $result['result_413_515'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_413_515,
                    'debit'=>$_POST['acc_413'],
                    'credit'=>$_POST['acc_515'],
                    'money'=>$money_413_515,
                    );
                foreach ($qr1 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_413_515'] = 'Cập nhật thành công';
            }
            $qr2 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_635'].' AND credit='.$_POST['acc_413'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr2) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_413_635,
                    'debit'=>$_POST['acc_635'],
                    'credit'=>$_POST['acc_413'],
                    'money'=>$money_413_635,
                    );
                $additional_model->createAdditional($data);
                $result['result_413_635'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_413_635,
                    'debit'=>$_POST['acc_635'],
                    'credit'=>$_POST['acc_413'],
                    'money'=>$money_413_635,
                    );
                foreach ($qr2 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_413_635'] = 'Cập nhật thành công';
            }
            $qr3 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_5111'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr3) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5111'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5111_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_511_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5111'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5111_911,
                    );
                foreach ($qr3 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_511_911'] = 'Cập nhật thành công';
            }
            $qr3 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_5112'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr3) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5112'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5112_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_511_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5112'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5112_911,
                    );
                foreach ($qr3 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_511_911'] = 'Cập nhật thành công';
            }
            $qr3 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_5113'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr3) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5113'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5113_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_511_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5113'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5113_911,
                    );
                foreach ($qr3 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_511_911'] = 'Cập nhật thành công';
            }
            $qr3 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_5118'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr3) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5118'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5118_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_511_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_511_911,
                    'debit'=>$_POST['acc_5118'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_5118_911,
                    );
                foreach ($qr3 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_511_911'] = 'Cập nhật thành công';
            }
            $qr4 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_515'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr4) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_515_911,
                    'debit'=>$_POST['acc_515'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_515_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_515_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_515_911,
                    'debit'=>$_POST['acc_515'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_515_911,
                    );
                foreach ($qr4 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_515_911'] = 'Cập nhật thành công';
            }
            $qr5 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_632'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr5) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_632_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_632'],
                    'money'=>$money_632_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_632_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_632_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_632'],
                    'money'=>$money_632_911,
                    );
                foreach ($qr5 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_632_911'] = 'Cập nhật thành công';
            }
            $qr6 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_635'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr6) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_635_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_635'],
                    'money'=>$money_635_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_635_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_635_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_635'],
                    'money'=>$money_635_911,
                    );
                foreach ($qr6 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_635_911'] = 'Cập nhật thành công';
            }
            $qr7 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_6421'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr7) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_642_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_6421'],
                    'money'=>$money_6421_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_642_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_642_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_6421'],
                    'money'=>$money_6421_911,
                    );
                foreach ($qr7 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_642_911'] = 'Cập nhật thành công';
            }
            $qr7 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_6422'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr7) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_642_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_6422'],
                    'money'=>$money_6422_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_642_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_642_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_6422'],
                    'money'=>$money_6422_911,
                    );
                foreach ($qr7 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_642_911'] = 'Cập nhật thành công';
            }
            $qr8 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_711'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr8) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_711_911,
                    'debit'=>$_POST['acc_711'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_711_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_711_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_711_911,
                    'debit'=>$_POST['acc_711'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_711_911,
                    );
                foreach ($qr8 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_711_911'] = 'Cập nhật thành công';
            }
            $qr9 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_811'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr9) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_811_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_811'],
                    'money'=>$money_811_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_811_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_811_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_811'],
                    'money'=>$money_811_911,
                    );
                foreach ($qr9 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_811_911'] = 'Cập nhật thành công';
            }
            $qr10 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_821'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr10) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_821_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_821'],
                    'money'=>$money_821_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_821_911'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_821_911,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_821'],
                    'money'=>$money_821_911,
                    );
                foreach ($qr10 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_821_911'] = 'Cập nhật thành công';
            }
            $qr11 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_911'].' AND credit='.$_POST['acc_4212'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr11) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_911_4212,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_4212'],
                    'money'=>$money_4212_911,
                    );
                $additional_model->createAdditional($data);
                $result['result_911_4212'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_911_4212,
                    'debit'=>$_POST['acc_911'],
                    'credit'=>$_POST['acc_4212'],
                    'money'=>$money_4212_911,
                    );
                foreach ($qr11 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_911_4212'] = 'Cập nhật thành công';
            }
            $qr12 = $additional_model->queryAdditional('SELECT * FROM additional WHERE debit='.$_POST['acc_4212'].' AND credit='.$_POST['acc_911'].' AND additional_date>='.$first.' AND additional_date<'.$last.' LIMIT 1');
            if (!$qr12) {
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_911_4212,
                    'debit'=>$_POST['acc_4212'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_911_4212,
                    );
                $additional_model->createAdditional($data);
                $result['result_911_4212'] = 'Thêm thành công';
            }
            else{
                $data = array(
                    'document_date'=>$document_date,
                    'document_number'=>$document_number,
                    'additional_date'=>$additional_date,
                    'additional_comment'=>$comment_911_4212,
                    'debit'=>$_POST['acc_4212'],
                    'credit'=>$_POST['acc_911'],
                    'money'=>$money_911_4212,
                    );
                foreach ($qr12 as $q) {
                    $additional_model->updateAdditional($data,array('additional_id'=>$q->additional_id));
                }
                $result['result_911_4212'] = 'Cập nhật thành công';
            }

            echo json_encode($result);

        }
    }
    

}
?>