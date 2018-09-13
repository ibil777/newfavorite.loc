<?php
define('BASE_HREF', '../');
include(BASE_HREF."include/ini.php");

if(!isset($_REQUEST['code']) or strlen($_REQUEST['code']) <> 32) {
    header("Location: .");
    exit;
}

// ---------------------- man
$param_where = array('old_code' => $_REQUEST['code']);
$query = "SELECT id FROM ".prdb."men WHERE code = :old_code";
$man_id = db_get_value($query, $param_where);

$param = array('code' => '', 'status' => 1);
$query = "UPDATE ".prdb."men SET [QUERY_SET] WHERE code = :old_code";
$num = db_update($query, $param, $param_where);

if($num) {
    if(SESS_M_ID == $man_id) {
        header("Location: cabinet.htm");
        exit;
    }
    header("Location: .?logout");
    exit;
}

// ---------------------- girl
$param_where = array('old_code' => $_REQUEST['code']);
$query = "SELECT id FROM ".prdb."girls WHERE code = :old_code";
$girl_id = db_get_value($query, $param_where);

$param = array('code' => '', 'status' => 1);
$query = "UPDATE ".prdb."girls SET [QUERY_SET] WHERE code = :old_code";
$num = db_update($query, $param, $param_where);

if($num and SESS_G_ID == $girl_id) {
    header("Location: cabinet.htm");
    exit;
}

header("Location: .?logout");
?>