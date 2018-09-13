<?php
define('BASE_HREF', '../');
include(BASE_HREF."include/ini.php");

// ------------------------- main code
if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    if($action == 'translit' and isset($_REQUEST['text'])) {
        $res_str = translit($_REQUEST['text']);
    }
    elseif($action == 'view_cart') {
        include(USER_TEMPLATE_DIR."include/modules_func.php");
        $res_str = cart_list_items();
    }
    elseif($action == 'add_cart') {
        include(USER_TEMPLATE_DIR."include/modules_func.php");
        $res_str = process_add_cart();
    }
    elseif($action == 'del_cart') {
        include(USER_TEMPLATE_DIR."include/modules_func.php");
        $res_str = process_del_cart();
    }
    elseif($action == 'sign_cart') {
        process_sign_cart();
    }
    elseif($action == 'make_calendar') {
        $panel_arr_default = make_panel_arr_default();
        foreach($panel_arr_default as $arr) {
            if(!isset($arr['type']) or $arr['type'] <> 'cal') continue;
            $news_arr = $arr['prop_arr']['news_id'];
            $news_alias = $arr['prop_arr']['all_alias'];
        }
        if(isset($news_arr)) {
            include(USER_TEMPLATE_DIR."include/panel_func.php");
            $res_str = make_panel_calendar($news_arr, $news_alias);
        }
    }
}

echo $res_str;

// ------------------------- function
function process_add_cart() {
    global $ini_arr;

    $item_id = isset($_REQUEST['item_id']) ? (int)$_REQUEST['item_id'] : 0;
    $item_var_id = isset($_REQUEST['item_var_id']) ? (int)$_REQUEST['item_var_id'] : 0;
    if(!$item_id) return "Error";
    
    $item_key = $item_id.'.'.$item_var_id;
    
    if(isset($_SESSION['sess_cart'][$item_key]))
        $_SESSION['sess_cart'][$item_key]++;
    else {
        $param = array('page_id' => $item_id, 'var_id' => $item_var_id);
        $query = "SELECT price FROM ".prdb."prices WHERE page_id = :page_id AND var_id = :var_id";
        $price = db_get_value($query, $param);
        if($price == 0)
            return "Error";
        
        $_SESSION['sess_cart'][$item_key] = 1;
        $_SESSION['sess_cart_prices'][$item_key] = $price;
    }

    save_cart();
    
    return cart_list_items($item_key);
}
function process_del_cart() {
    global $ini_arr;

    $item_id = isset($_REQUEST['item_id']) ? (int)$_REQUEST['item_id'] : 0;
    $item_var_id = isset($_REQUEST['item_var_id']) ? (int)$_REQUEST['item_var_id'] : 0;
    if(!$item_id) return "Error";
    
    $item_key = $item_id.'.'.$item_var_id;
    
    if(isset($_SESSION['sess_cart'][$item_key])) {
        unset($_SESSION['sess_cart'][$item_key]);
    }
    if(isset($_SESSION['sess_cart_prices'][$item_key])) {
        unset($_SESSION['sess_cart_prices'][$item_key]);
    }
    
    save_cart();
    
    return cart_list_items($item_key);
}
function process_sign_cart() {
    global $ini_arr;

    $item_id = isset($_REQUEST['item_id']) ? (int)$_REQUEST['item_id'] : 0;
    $item_var_id = isset($_REQUEST['item_var_id']) ? (int)$_REQUEST['item_var_id'] : 0;
    $sign = (isset($_REQUEST['sign']) and ($_REQUEST['sign'] == 'minus' or $_REQUEST['sign'] == 'plus')) ? $_REQUEST['sign'] : "";
    $item_key = $item_id.'.'.$item_var_id;
    if(empty($sign) or !$item_id or !isset($_SESSION['sess_cart'][$item_key])) return;
    
    if($sign == 'plus')
        $_SESSION['sess_cart'][$item_key]++;
    elseif($sign == 'minus' and $_SESSION['sess_cart'][$item_key] > 1)
        $_SESSION['sess_cart'][$item_key]--;
    
    save_cart();
}
function save_cart() {
    global $ini_arr;
    
    $_SESSION['sess_cart_sum'] = calc_sess_cart_sum();
    $_SESSION['sess_cart_num'] = array_sum($_SESSION['sess_cart']);

    $ini_arr['user_data']['cart'] = array(
        'items' => $_SESSION['sess_cart'],
        'prices' => $_SESSION['sess_cart_prices'],
    );

    if(SESS_G_ID) {
        $param = array('prop_arr' => serialize($ini_arr['user_data']));
        $param_where = array('id' => SESS_G_ID);
        $query = "UPDATE ".prdb."guests SET [QUERY_SET] WHERE id = :id";
        db_update($query, $param, $param_where);
    }
    elseif(SESS_U_ID) {
        $param = array('prop_arr' => serialize($ini_arr['user_data']));
        $param_where = array('id' => SESS_U_ID);
        $query = "UPDATE ".prdb."users SET [QUERY_SET] WHERE id = :id";
        db_update($query, $param, $param_where);
    }
}
?>