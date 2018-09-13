<?php
define('BASE_HREF', "");
include(BASE_HREF."include/301.php");
include(BASE_HREF."include/ini.php");
include(BASE_HREF."include/social_auth.php");
include(USER_TEMPLATE_DIR."include/tpl_func.php");
include(USER_TEMPLATE_DIR."include/panel_func.php");
include(USER_TEMPLATE_DIR."include/modules_func.php");

// -------------------- Log Out ----------------
if(isset($_REQUEST['logout'])) {
    user_logout();
    header("Location: .");
    exit;
}

// -------------------- HTTP_POST_VARS ----------------
if((isset($_POST['form']) and is_array($_POST['form'])) or (isset($_POST['s_form']) and is_array($_POST['s_form']))) {
    $_SESSION['request'] = $_REQUEST;
    header("HTTP/1.1 301 Moved Permanently");
    header ("Location: ".$_SERVER['REQUEST_URI']);
    exit;
}
elseif(isset($_SESSION['request'])) {
    $_REQUEST = $_SESSION['request'];
    unset($_SESSION['request']);
}

// ----------------------------------- var init
if(isset($_REQUEST['page'])) {
    $p = $_REQUEST['page'];
    if($p == "ladies" and SESS_G_ID) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ".URL.'/men.htm');
        exit;
    }
    elseif($p == "men" and !SESS_G_ID) {
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ".URL.'/ladies.htm');
        exit;
    }

    $param = array('alias' => $p);
    $query = "SELECT id, alias, user_only
              FROM ".prdb."menu
              WHERE alias = :alias";
    $row = db_get_row($query, $param);
    if(sizeof($row)) {
        if($row['user_only'] and !SESS_ID) {
            get_page_id("no-access");
        }
        else {
            $page_id = $row['id'];
            $alias = $row['alias'];
        }
    }
    else {
        header("HTTP/1.0 404 Not Found");
        get_page_id("page-404");
    }
}

if(!isset($page_id)) {
    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias
              FROM ".prdb."menu, ".prdb."menu_pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id
                AND ".prdb."menu_pages.parent_id = 0
                AND ".prdb."menu.in_menu = '1'
              ORDER BY p_id
              LIMIT 1";
    $row = db_get_row($query);
    if(sizeof($row)) {
        $page_id = $row['id'];
        $alias = $row['alias'];
    }
    else
        exit('Sorry. Site coming soon');
}
define('PAGE_ID', $page_id);
define('ALIAS', $alias);

$TREE_ARR = array_reverse(make_branch_arr(PAGE_ID));
//echo "<pre>".print_r($TREE_ARR, true)."</pre>";

// ----------------------------------- make page
$param = array('id' => PAGE_ID);
$query = "SELECT parent_id, type, template, name, title, description, keywords, content, lang, comment,
            list_pages, gallery, form, slider, price, price1, page_ini_arr,
            ".($ini_arr['is_tags'] ? "tags, " : "")."
            DATE_FORMAT(".prdb."menu.date_in, '%d.%m.%Y %H:%i') AS f_date_in,
            DATE_FORMAT(".prdb."menu.date_in, '%d.%m.%Y') AS f_date
          FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
          WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = :id
            AND ".prdb."menu.id = ".prdb."pages.id";
$line_arr = db_get_arr($query, $param, 'arr');
if($line_arr === false) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: page-404.htm");
    exit;
}
foreach($line_arr as $row)
    $row_arr[$row['lang']] = $row;


if(empty($row_arr[LANG]['content'])) {
    foreach($ini_arr['language'] as $key => $value) {
        if(!empty($row_arr[$key]['content'])) {
            $ROW_PAGE = $row_arr[$key];
            break;
        }
    }
}
if(!isset($ROW_PAGE)) {
    if(isset($row_arr[LANG]))
        $ROW_PAGE = $row_arr[LANG];
    else
        $ROW_PAGE = $row_arr[LANGUAGE_DEFAULT];
}

$ROW_PAGE['ini_arr'] = empty($ROW_PAGE['page_ini_arr']) ? array() : unserialize($ROW_PAGE['page_ini_arr']);
$res_str = $ROW_PAGE['content'];

if(strpos($ROW_PAGE['type'], 'cat_') === 0)
    define('IS_CAT_ITEMS', true);
else
    define('IS_CAT_ITEMS', false);
if(strpos($ROW_PAGE['type'], 'item_') === 0)
    define('IS_ITEM', true);
else
    define('IS_ITEM', false);

$content_arr['TPL_FILE'] = isset($_REQUEST['prn']) ? "print" : $ROW_PAGE['template'];
$content_arr['TITLE'] = (empty($ROW_PAGE['title']) ? $ROW_PAGE['name'].". ".$ini_arr['title'] : $ROW_PAGE['title']);
$content_arr['NAME_SITE'] = $ini_arr['title'];
$content_arr['DESCRIPTION'] = empty($ROW_PAGE['description']) ? $ini_arr['description'] : $ROW_PAGE['description'];
$content_arr['KEYWORDS'] = empty($ROW_PAGE['keywords']) ? $ini_arr['keywords'] : $ROW_PAGE['keywords'];

$content_arr['HEADER'] = "";
$content_arr['G_MAP'] = "";
$content_arr['COMMENT_LIST'] = "";
//$content_arr['CATALOG_FIRM_BTN'] = make_catalog_firm_btn();
$content_arr['BRANCH'] = view_branch(PAGE_ID);
$content_arr['FOOTER'] = make_footer();
$content_arr['DIALOG'] = make_dialog_html();
$content_arr['IS_ADMIN_LOGIN'] = make_admin_panel();
$content_arr['MAIN_MENU'] = make_main_menu();
$content_arr['SUB_MENU'] = make_sub_menu($TREE_ARR[0]['id']);
$content_arr['SEARCH_FORM'] = make_search_form();
$content_arr['USER_BAR'] = make_user_bar();
$content_arr['PHONES'] = make_phones_bar();
$content_arr['CART_PANEL'] = make_cart_panel();
$content_arr['YEAR'] = date("Y");
$content_arr['LANG'] = LANG;

$content_arr['GALLERY'] = "";


make_panel_content_arr();

/*
$content_arr['BRANCH'] = view_branch(PAGE_ID);

*/

$url = parse_url($_SERVER['REQUEST_URI']);
if(isset($url['query']) and !empty($url['query']))
    $content_arr['QUERY'] = "?".$url['query'];
else
    $content_arr['QUERY'] = "";

//$is_comment = false;

// ------------------------------------- include modules
if($ROW_PAGE['type'] == 'news' or $ROW_PAGE['type'] == 'news_1') {
    include(USER_TEMPLATE_DIR."modules/news.php");
}
elseif($ROW_PAGE['type'] == 'seller') {
    include(USER_TEMPLATE_DIR."modules/seller.php");
}

elseif($ROW_PAGE['type'] == 'shop') {
    include(USER_TEMPLATE_DIR."modules/shop.php");
}
elseif(IS_CAT_ITEMS) {
    include(USER_TEMPLATE_DIR."modules/cat_items.php");
}
elseif(IS_ITEM) {
    include(USER_TEMPLATE_DIR."modules/items.php");
}

if(isset($MODULE_ARR) and array_key_exists(ALIAS, $MODULE_ARR) and file_exists(USER_TEMPLATE_DIR."modules/".ALIAS.".php"))
    include(USER_TEMPLATE_DIR."modules/".ALIAS.".php");

// ------------------------------------- include add modules
if($ROW_PAGE['list_pages'])
    include(USER_TEMPLATE_DIR."modules/list.php");

if($ROW_PAGE['gallery'] and !IS_ITEM)
    include(USER_TEMPLATE_DIR."modules/gallery.php");

if($ROW_PAGE['form'])
    include(USER_TEMPLATE_DIR."modules/form.php");

if($ROW_PAGE['slider'])
    include(USER_TEMPLATE_DIR."modules/slider.php");

if($ini_arr['is_comment'] and $ROW_PAGE['comment']) {
    include(USER_TEMPLATE_DIR."modules/comment.php");
}

// ----------------------------------- make stat
if(!is_search_bot()) {
    $param = array(
        'dns' => USER_IP,
        'minute' => isset($ini_arr['min_stat']) ? $ini_arr['min_stat'] : 10,
        'alias' => ALIAS
    );
    $query = "SELECT COUNT(*)
              FROM ".prdb."stat
              WHERE dns = :dns
                AND DATE_ADD(NOW(), INTERVAL -:minute MINUTE) < date_in
                AND alias = :alias";
    if(!db_get_value($query, $param)) {
        $param = array();
        $param_where = array('alias' => ALIAS);
        $query = "UPDATE ".prdb."menu SET
                    counter=counter+1
                  WHERE alias = :alias";
        db_update($query, $param, $param_where);
    
        $param = array(
            'alias' => ALIAS,
            'date_in' => date('Y-m-d H:i:s'),
            'dns' => USER_IP,
            'referer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ""
        );
        $query = "INSERT INTO ".prdb."stat SET ";
        db_insert($query, $param);
    }
}

// ----------------------------------- last
$content_arr['CONTENT'] = make_content($res_str);

echo make_page(USER_TEMPLATE_DIR, $content_arr);
?>