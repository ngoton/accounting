<?php
Class indexController Extends baseController {
    public function index() {
        $this->view->disableLayout();
            $this->view->data['title'] = 'Accounting Management System';

            if (isset($_POST['new_mst']) && trim($_POST['new_mst']) != "") {

                $check=0;
                $doc = new DOMDocument();
                $doc->load( 'db.xml' );
                $companys = $doc->getElementsByTagName( "Company" );
                foreach ($companys as $item) { 
                    $tagMST = $item->getElementsByTagName( "MST" );
                    $mst = $tagMST->item(0)->nodeValue;
                    if ($mst==$_POST['new_mst']) {
                        $check = 1;
                    }
                }

                if ($check==0) {
                    $doc = new DOMDocument();
                    $doc->load( 'db.xml' );

                    $doc->formatOutput = true;
                    $r = $doc->getElementsByTagName("Database")->item(0);

                    $b = $doc->createElement("Company");

                    $loc = $doc->createElement("MST");
                    $loc->appendChild(
                        $doc->createTextNode($_POST['new_mst'])
                    );

                    $b->appendChild( $loc );

                    $r->appendChild( $b );
                        
                    $doc->save("db.xml"); 

                    $conn = Db::dbConnection();
                    $sql = "CREATE DATABASE ".DB_PREFIX.$_POST['new_mst']." CHARACTER SET utf8 COLLATE utf8_general_ci";
                    $conn->exec($sql);
                    
                    $conn2 = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_PREFIX.$_POST['new_mst'], DB_USERNAME, DB_PASSWORD);
                    $conn2->exec("SET CHARACTER SET utf8");
                    $sql = file_get_contents('db.sql');
                    $conn2->exec($sql);

                    setcookie("db", "",time() - 3600,"/");
                    session_destroy();
                }
                
            }
            $this->view->show('index');
    }

    public function view() {
        /*** set a template variable ***/
            $this->view->data['view'] = 'hehe';
        /*** load the index template ***/
            $this->view->show('index/view');
    }

}
?>