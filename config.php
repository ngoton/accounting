<?php
define('DB_PREFIX','accounting_');// Database name
//$db = isset($_SESSION['db'])?DB_PREFIX.$_SESSION['db']:null;
$db='accounting';
define('DB_DATABASE',$db);// Database name
define('DB_USERNAME','root');// User database
define('DB_PASSWORD','');// Password database
define('DB_SERVER','localhost');// IP host
define('BASE_URL',isset($_SERVER["HTTPS"]) ? 'https://'.$_SERVER["SERVER_NAME"] : 'http://'.$_SERVER["SERVER_NAME"]);// IP host

?>
