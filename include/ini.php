<?php
/* Цена
1. Все хранится в таблице prices, включая единичную цену для товара (в таком случае var_id = 0) - это для фильтра
2. В таблице menu хранится минимальное значение price с соответствующей price1 - это для вывода на плашке в списке товаров
3. В page_ini_arr хранится единичная цена. Она поменяна местами price и price1. Это только для общих настроек товара
*/

session_start();

setlocale (LC_ALL, "ru_RU.UTF-8");
header('Content-type: text/html; charset=UTF-8');
date_default_timezone_set('Europe/Kiev');

$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
$url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "";
define('URI', $uri);
define('DOMAIN', $url);
define('URL', "http://".DOMAIN);
define('ERROR_LOG_FILE', BASE_HREF.'data/error.txt');
define('GALLERY_DIR', BASE_HREF.'images/gallery/');
define('IMAGE_PAGE_DIR', BASE_HREF.'images/pages/');
define('BANNER_DIR', BASE_HREF.'images/banners/');
define('SLIDER_DIR', BASE_HREF.'images/slider/');
define('USER_TEMPLATE', 'main');
define('USER_TEMPLATE_DIR', BASE_HREF.'templates/'.USER_TEMPLATE.'/');
define('ADMIN_DIR', 'jpadmin/');
define('DATA_DIR', BASE_HREF."data/");
define('LANG_FILE', DATA_DIR."!lang.dat");
define('TYPES_FILE', DATA_DIR."!types.dat");
define('LETTERS_FILE', DATA_DIR."!letters.dat");
define('LETTERS_DIR', BASE_HREF."files/Letters/");
define('IS_GUEST', true); // Собирать в таблицу guest данные о незарег. посетителях

define('CKEDITOR_JS', '[BASE_HREF]jped/ckeditor.js');
define('AJAX_SCRIPT', '/include/ajax.php');

define('MAIL_CONFIRM', 'confirm-mail.txt');
define('MAIL_FORGOT', 'forgot.txt');


$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "IP error";
define('USER_IP', $ip);

$arr = pathinfo($_SERVER['SCRIPT_NAME']);
define('SCRIPT', $arr['basename']);

$ip_server = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : "IP error";
if($ip_server == '127.0.0.1')
    define('IS_LOCAL', true);
else
    define('IS_LOCAL', false);

if(str_replace("/", "", $arr['dirname']) == str_replace("/", "", ADMIN_DIR))
    define('IS_ADMIN', true);
else
    define('IS_ADMIN', false);
    
$ps = isset($_REQUEST['ps']) ? (int)$_REQUEST['ps'] : 0;
define('PS', $ps);


define('FB_APP_ID', '');
define('FB_APP_SECRET', '');
define('FB_REDIRECT_URI', URL.'/?fb_auth');
$ini_arr['who']['admin'] = 'Администратор';
$ini_arr['who']['editor'] = 'Редактор';
$ini_arr['admins'] = array('admin', 'editor');

if(IS_ADMIN) {
}

include(BASE_HREF."include/main_func.php");
include(BASE_HREF."include/auth_func.php");
set_exception_handler('exception_handler');
set_error_handler("error_handler");
include(BASE_HREF."include/some_func.php");
if(IS_ADMIN) {
    include(BASE_HREF.ADMIN_DIR."ini.php");
}

if(!isset($_REQUEST['action']) or $_REQUEST['action'] <> 'process_login') {
    $ping = isset($_REQUEST['ping']) ? true : false;
    $login_arr = protect_page($ping);

    if(IS_ADMIN and !$login_arr['admins'] and SCRIPT <> 'index.php') {
        header("Location: ".BASE_HREF.ADMIN_DIR);
        exit;
    }
    
    define("SESS_ID", $login_arr['admins'] ? $_SESSION['sess_id'] : 0);
    define("SESS_S_ID", $login_arr['admins'] ? $_SESSION['sess_s_id'] : 0);
    define("SESS_LOGIN", $login_arr['admins'] ? $_SESSION['sess_login'] : "");
    define("SESS_WHO", $login_arr['admins'] ? $_SESSION['sess_who'] : "");

    define("SESS_G_ID", $login_arr['guest'] ? $_SESSION['sess_g_id'] : 0);

    define("SESS_U_ID", $login_arr['user'] ? $_SESSION['sess_u_id'] : 0);
    define("SESS_U_S_ID", $login_arr['user'] ? $_SESSION['sess_u_s_id'] : 0);
    define("SESS_U_LOGIN", $login_arr['user'] ? $_SESSION['sess_u_login'] : "");
}

define('LANGUAGE_DEFAULT', 'ru');
$ini_arr['language']['ua'] = "Українська";
$ini_arr['language']['ru'] = "Русский";
$ini_arr['language']['en'] = "English";

$ini_arr['lang_if'] = $ini_arr['language'];
unset($ini_arr['lang_if']['ua'], $ini_arr['lang_if']['en']);

if(!isset($_REQUEST['lang']) or !in_array($_REQUEST['lang'], array_keys($ini_arr['language'])))
    $lang = LANGUAGE_DEFAULT;
else
    $lang = $_REQUEST['lang'];

$ini_arr['pr_lang'] = $lang == LANGUAGE_DEFAULT ? "" : $lang."-";
define('LANG', $lang);
unset($lang);

$s = file_get_contents(LANG_FILE);
$arr = unserialize($s);
if(IS_ADMIN)
    $ini_arr['lang'] = $arr['ru'];
else
    $ini_arr['lang'] = $arr[LANG];

$arr = get_vars('', 'settings', 'uid');
if(is_array($arr))
    $ini_arr = array_merge($ini_arr, $arr);

$ini_arr['www'] = isset($_SERVER['HTTP_HOST']) ? "http://".$_SERVER['HTTP_HOST']."/" : "cron:";

$ini_arr['max_image_width'] = 3000;
$ini_arr['max_image_height'] = 3000;
$ini_arr['max_image_size'] = 4000;
$ini_arr['size_small'] = 220;
$ini_arr['size_big'] = 800;
$ini_arr['main_img_size'] = 600; // Главное фото новости

$ini_arr['is_comment'] = true;
$ini_arr['is_user_page'] = true;
$ini_arr['is_mailer'] = false;
$ini_arr['google_map'] = false;
$ini_arr['is_tags'] = false;
$ini_arr['tags_min_em'] = .9;
$ini_arr['tags_max_em'] = 2.2;

$ini_arr['sec_online'] = 300;

$ini_arr['sort_items'] = array(
    'rating' => 'По рейтингу',
    'price-desc' => 'От дорогих к дешевым',
    'price-asc' => 'От дешевых к дорогим'
);
$ini_arr['sort_items_default'] = 'rating';
$ini_arr['num_show_filters'] = 5;

$ini_arr['cat_part'] = get_vars('', 'catalog', 'uid');

$arr = unserialize(file_get_contents(TYPES_FILE));
$TYPE_ARR = $arr['TYPE_ARR'];
$ini_arr['page_ini_arr'] = $arr['page_ini_arr'];
$MODULE_ARR = $arr['MODULE_ARR'];

/*
$TYPE_ARR['test'] = "Тестовый тип страницы";
$ini_arr['page_ini_arr']['test'] = array(
    'price_tbl' => array(
        'name' => "Таблица цен",
        'type' => "price_tbl",
        'list_uid' => "price_vars",
        'add' => true // не показывать на странице настроек
    ),
    'test_text' => array(
        'name' => "Text",
        'type' => "text",
        'search' => true
    ),
    'test_textarea' => array(
        'name' => "Textarea",
        'type' => "textarea",
        'search' => true,
        'add' => true,
        'editor' => true // показывать на странице редактора, только textarea
    ),
    'test_price' => array(
        'name' => "Price",
        'type' => "price"
    ),
    'test_checkbox' => array(
        'name' => "Checkbox",
        'type' => "checkbox"
    ),
    'test_checklist' => array(
        'name' => "Checklist",
        'type' => "checklist",
        'list_uid' => "check_vars",
        'vars' => true
    ),
    'test_select' => array(
        'name' => "Select",
        'type' => "select",
        'list_uid' => "select_vars",
        'vars' => true
    ),
    'test_file' => array(
        'name' => "File",
        'type' => "file"
    ),
    'test_html' => array(
        'name' => "Editor",
        'type' => "editor",
        'search' => true,
        'add' => true
    )
);
*/

$PANEL_ARR['text'] = "Простой текст";
$PANEL_ARR['html'] = "Форматированный текст";
$PANEL_ARR['list'] = "Список разделов (меню)";
$PANEL_ARR['items_slider'] = "Слайдер товаров";
$PANEL_ARR['banner'] = "Баннер";
if(isset($TYPE_ARR['news'])) 
    $PANEL_ARR['news'] = "Последние новости";
if(isset($TYPE_ARR['news']))
    $PANEL_ARR['cal'] = "Календарь";
/*
if(isset($TYPE_ARR['vote']))
    $PANEL_ARR['vote'] = "Голосование";
if($ini_arr['is_tags'])
    $PANEL_ARR['tags'] = "Облако тегов";
*/


//$arr = array('TYPE_ARR' => $TYPE_ARR, 'page_ini_arr' => $ini_arr['page_ini_arr'], 'MODULE_ARR' => $MODULE_ARR);
//$s = serialize($arr);
//file_put_contents(TYPES_FILE, $s);

$res_str = "";

$content_arr['HEAD'] = "";
$content_arr['ONLOAD'] = "";
$content_arr['USER_NAME'] = isset($ini_arr['user_data']['name']) ? $ini_arr['user_data']['name'] : "";
?>