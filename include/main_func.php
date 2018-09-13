<?php

function make_page($tpl_dir, $content) {
    global $ini_arr;
    
    if(file_exists($tpl_dir."tpl/".$content['TPL_FILE'].".tpl"))
        $tpl_file = $tpl_dir."tpl/".$content['TPL_FILE'].".tpl";
    elseif(file_exists($tpl_dir."tpl/main.tpl"))
        $tpl_file = $tpl_dir."tpl/main.tpl";
    else
        exit('Sorry. Site coming soon');
    $res_str = file_get_contents($tpl_file);
    unset($content['TPL_FILE']);

    if(isset($content['LAST'])) {
        $last = $content['LAST'];
        unset($content['LAST']);
    }

    if(!isset($content['HEAD']))
        $res_str = str_replace("[HEAD]", '', $res_str);

    if(isset($content['CONTENT'])) {
        $res_str = str_replace("[CONTENT]", $content['CONTENT'], $res_str);
        unset($content['CONTENT']);
    }

    foreach($content as $key => $value) {
        $res_str = str_replace("[".$key."]", $value, $res_str);
    }

    $res_str = str_replace("[BASE_HREF]", BASE_HREF, $res_str);
    $res_str = str_replace("[IMAGE_DIR]", $tpl_dir.'images/', $res_str);
    $res_str = str_replace("[TPL_DIR]", $tpl_dir, $res_str);
//    $res_str = str_replace("[ALIAS]", $alias, $res_str);
    $res_str = str_replace("[GALLERY_DIR]", GALLERY_DIR, $res_str);

    if(defined("LANG")) {
        $res_str = str_replace("[LANG]", LANG, $res_str);
        foreach($ini_arr['lang'] as $key => $value) {
            $res_str = str_replace("[WORD_".$key."]", $value, $res_str);
        }
    }
    
    if(isset($last))
        $res_str = str_replace("[LAST]", $last, $res_str);

    return $res_str;
}
function get_page_id($alias) {
// for no-access & page-404
    $param = array('alias' => $alias);
    $query = "SELECT id
              FROM ".prdb."menu
              WHERE alias = :alias";
    $id = db_get_value($query, $param);
    if($id !== false) {
        $GLOBALS['page_id'] = $id;
        $GLOBALS['alias'] = $alias;
    }
}
function make_branch_arr($parent_id, $res_arr="") {
    if(defined('LANG'))
        $lang = LANG;
    else
        $lang = LANGUAGE_DEFAULT;

    if(!is_array($res_arr)) {
        unset($res_arr);
        $res_arr = array();
    }
    $param = array('id' => $parent_id, 'lang' => $lang);
    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, ".prdb."menu.type, ".prdb."menu.page_ini_arr,
                ".prdb."pages.name, 
                ".prdb."menu_pages.parent_id, ".prdb."menu_pages.key_db
              FROM ".prdb."menu, ".prdb."pages, ".prdb."menu_pages
              WHERE ".prdb."menu.id = :id
                AND ".prdb."menu.id = ".prdb."pages.id AND ".prdb."menu.id = ".prdb."menu_pages.id
                AND lang = :lang";
    $row = db_get_row($query, $param);
    if(sizeof($row)) {
        $prop_arr = empty($row['page_ini_arr']) ? array() : unserialize($row['page_ini_arr']);
        if(empty($row['name'])) {
            $param = array('id' => $row['id']);
            $query = "SELECT name FROM ".prdb."pages WHERE id = :id AND LENGTH(name)";
            $row['name'] = db_get_value($query, $param);
        }
        $res_arr[] = array(
            'key_db' => $row['key_db'],
            'id' => $row['id'],
            'parent_id' => $row['parent_id'],
            'type' => $row['type'],
            'alias' => $row['alias'],
            'name' => $row['name'],
            'prop_arr' => isset($prop_arr['adv_setting']) ? $prop_arr['adv_setting'] : array()
        );
        if($parent_id) {
            $res_arr = make_branch_arr($row['parent_id'], $res_arr);
            return $res_arr;
        }
        else {
            return $res_arr;
        }
    }
    else {
        return $res_arr;
    }
}
function make_panel_arr_default() {
    global $ini_arr;
    
    $query = "SELECT *
              FROM ".prdb."panels";
    $row_arr = db_get_arr($query, array(), 'arr');
    if(!sizeof($row_arr))
        return "";
    
    foreach($row_arr as $row) {
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
        $row['prop_arr'] = $prop_arr;
        $res_arr[$row['id']] = $row;
    }
    
    return $res_arr;
}
function make_panel_arr($page_id) {
    $param = array('page_id' => $page_id);
    $query = "SELECT id, panel_id, prop_arr FROM ".prdb."panels_pages WHERE page_id = :page_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr))
        return array();

    foreach($row_arr as $row) {
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
        $row['prop_arr'] = $prop_arr;
        $res_arr[$row['panel_id']] = $row;
    }

    return $res_arr;
}
function calc_sess_cart_sum() {
    $sum = 0;
    foreach($_SESSION['sess_cart'] as $key => $num) {
        if(empty($_SESSION['sess_cart_prices'][$key])) {
            unset($_SESSION['sess_cart'][$key]);
        }
        else {
            $sum += $num * $_SESSION['sess_cart_prices'][$key];
        }
    }
    
    return $sum;
}
function substr_space($str, $len, $dots = "") {
    if($len <= 0) return;

    if(strlen($str) > $len) {
        $len_new = strpos($str, " ", $len);
        if(!$len_new) return $str;
        $str = substr($str, 0, $len_new).$dots;
    }

    return $str;
}
function get_vars($id, $uid, $res_type, $lang = '') {
// $res_type => id | uid | arr
    $res_arr = array();
    
    if(empty($lang))
        $lang = defined("LANG") ? LANG : LANGUAGE_DEFAULT;
        
    if($id) {
        $param = array('parent_id' => $id);
        $query = "FROM ".prdb."vars AS vars WHERE vars.parent_id = :parent_id ORDER BY vars.p_id";
    }
    else {
        $param = array('uid' => $uid);
        $query = "FROM ".prdb."vars AS vars, ".prdb."vars AS parent_vars
                  WHERE vars.parent_id = parent_vars.id and parent_vars.uid = :uid ORDER BY vars.p_id";
    }
    switch ($res_type) {
        case "id":
            $query = "SELECT vars.id, vars.".$lang." ".$query;
            $res_arr = db_get_arr($query, $param, 'pair');
            break;
        case "uid":
            $query = "SELECT vars.uid, vars.".$lang." ".$query;
            $res_arr = db_get_arr($query, $param, 'pair');
            break;
        case "arr":
            $query = "SELECT vars.id, vars.uid, vars.".$lang.", vars.note ".$query;
            $arr = db_get_arr($query, $param, 'arr');
            foreach($arr as $key => $row)
                $res_arr[$row['id']] = $row;
            break;
    }

    if(!sizeof($res_arr))
        return false;

    return $res_arr;
}
function get_list_vars($lang = '') {
    if(empty($lang))
        $lang = defined("LANG") ? LANG : LANGUAGE_DEFAULT;

    $param = array('uid' => 'form');
    $query = "SELECT ".prdb."vars.uid, child.id, child.uid AS ch_uid, child.".$lang."
              FROM ".prdb."vars, ".prdb."vars AS parent, ".prdb."vars AS child
              WHERE ".prdb."vars.parent_id = parent.id AND child.parent_id = ".prdb."vars.id
                AND parent.uid = :uid
              ORDER BY parent.id, ".prdb."vars.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    foreach($row_arr as $row) {
        if($row['uid'] == 'month')
            $res_arr[$row['uid']][$row['ch_uid']] = $row[$lang];
        else
            $res_arr[$row['uid']][$row['id']] = $row[$lang];
    }
    
    return $res_arr;
}
function line_list_pages($num_all, $num_per_page, $list_per_page, $page_start, $action) {
// $num_all - Всего строк в таблице
// $num_per_page - сколько вывести на страницу
// $list_per_page - сколько ссылок в строке содержания
// $page_start - номер строки в таблице, которая первой выводится на странице. Начинается с 0
    $res_str = "";
    if(strpos($action, "?") === false)
        $action .= "?";
    $num_pages = $num_all / $num_per_page;
    if($num_pages > (int)$num_pages) $num_pages = (int)$num_pages + 1;
    $start_list = (int)($page_start/$num_per_page/$list_per_page)*$num_per_page*$list_per_page;
    $end_list = $start_list+$num_per_page*$list_per_page;
    if($page_start >= $num_per_page*$list_per_page)
        $res_str .= "<a href=\"".str_replace("ps=", "ps=".($start_list-$num_per_page), $action)."\" class=lineList><< Назад</a> ";
    else
        $res_str .= " ";

    for($i=$start_list; $i<$end_list; $i+=$num_per_page) {
        if($page_start >= $i and $page_start < $i+$num_per_page) {
            $a_href = "";
            $a = "";
        }
        else {
            $a_href = "<a href=\"".str_replace("ps=", "ps=".$i, $action)."\" class=lineList>";
            $a = "</a>";
        }
        if(($i+$num_per_page) < $num_all)
            $res_str .= $a_href.($i+1)."..".($i+$num_per_page).$a." ";
        else {
            $res_str .= $a_href.($i+1)."..".$num_all.$a." ";
            break;
        }
    }

    if($num_all > $end_list)
        $res_str .= " <a href=\"".str_replace("ps=", "ps=".$end_list, $action)."\" class=lineList>Дальше >></a>";

    return $res_str;
}
function protect_js($text) {
    $arr = array("<script", "onclick", "onload", "onblur", "onchange", "ondblclick", "onfocus", "onkey", "onmouse",
    "onreset", "onselect", "onsubmit", "onunload");
    foreach($arr as $value) {
        $text = preg_replace("/".$value."/i", nospam($value), $text);
    }
    $text = preg_replace("/javascript:/i", "Java Script:", $text);

    return $text;
}
function nospam($text) {
    $getval = strval($text);
    $nospammail = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $asciicode = ord($getval[$i]);
        $nospammail .= "&#$asciicode;";
    }
    return $nospammail;
}
function utf8decode($str, $encode="windows-1251"){
    return iconv('UTF-8', $encode."//IGNORE", $str);
}
function utf8encode($str, $encode="windows-1251"){
    return iconv($encode."//IGNORE", 'UTF-8', $str);
}
function age_from_bday($birthdate) {
    $arr = date_parse_from_format('Y-m-d', $birthdate);
    if($arr['error_count'])
        return false;

    $birth = date_create($birthdate);
    $now = date_create('now');
    $interval = date_diff($birth, $now);
    $age = $interval->format('%y');

    return $age;
}
function translit($cyr_str) {
    $cyr_str = mb_strtolower($cyr_str, 'UTF-8');
    $tr = array(
        " "=>"-","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"shch","ъ"=>"",
        "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
        "і"=>"i","ї"=>"i","є"=>"e","ый"=>"iy"
    );
    $str = strtr($cyr_str, $tr);
    $str = preg_replace('~[^0-9a-zA-Z\-]~', '', $str);
    return $str;
}
function remove_h($text) {
    $text = preg_replace("/<[hH](\d)[^>]*>[^<]*<\/[hH](\d)>/", "", $text);
    return $text;
}
function make_js_str($text) {
    $text = str_replace(chr(10), ' ', $text);
    $text = str_replace(chr(13), ' ', $text);
    $text = str_replace('"', '&quot;', $text);
    return $text;
}
function load_ckeditor($id_textarea, $toolbar, $width, $height) {
    $res_str = "
    CKEDITOR.replace( \"".$id_textarea."\",
        {
    ";
    
    if(isset($_SESSION['sess_code'])) {
        $akey = md5(SESS_ID."JP - SP".SESS_LOGIN);
        $res_str .= "
            filebrowserBrowseUrl : \"/jped/filemanager/dialog.php?type=2&editor=ckeditor&fldr=&akey=".$akey."\",
            filebrowserUploadUrl : \"/jped/filemanager/dialog.php?type=2&editor=ckeditor&fldr=&akey=".$akey."\",
            filebrowserImageBrowseUrl : \"/jped/filemanager/dialog.php?type=1&editor=ckeditor&fldr=&akey=".$akey."\",
        ";
    }

    $res_str .= "
            toolbar: \"".$toolbar."\",
            width: \"".$width."\",
            height: \"".$height."\"
        }
    );
    ";
    
    return $res_str;
}
function make_pass($len) {
    $symbols = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789123456789123456789123456789";
    srand((double)microtime() * 1000000);
    $pass = "";
    for($i=0; $i<$len; $i++)
        $pass .= $symbols[rand(0, strlen($symbols)-1)];
    
    return $pass;
}
function get_uniqid($lenght = 13) {
    $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));

    return substr(bin2hex($bytes), 0, $lenght);
}
function check_img($userfile, $max_image_width, $max_image_height, $max_image_size) {
    $max_image_size *= 1024;
    $valid_types = array(1, 2, 3);
/*
array getimagesize()
Index 2 is a flag indicating the type of the image:
1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order),
9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM
*/
    if(is_uploaded_file($_FILES[$userfile]['tmp_name'])) {
        $filename = $_FILES[$userfile]['tmp_name'];
        $size = @GetImageSize($filename);
        if($size) {
            if(filesize($filename) > $max_image_size) {
                return $error = 1;  // 'Error: File size > 64K.'
            }
            elseif(!in_array($size[2], $valid_types)) {
                return $error = 2;  // 'Error: Invalid file type.'
            }
            else {
                if($size[0] <= $max_image_width && $size[1] <= $max_image_height) {
                        return $error = 0;  // OK'
                }
                else {
                    return $error = 3;  // 'Error: invalid image properties.'
                }
            }
        }
        else {
            return $error = 2;  // "Error: empty file."
        }
    }
    else {
        return $error = 4;  // "Error: empty file."
    }
}
function make_image($userfile, $size, $img_dir, $img_name) {
    $upload_file = $_FILES[$userfile]['tmp_name'];
    $img_size = @getimagesize($upload_file);
    if(!is_array($img_size)) return false;
    $width = (int)$img_size[0];                // from
    $height = (int)$img_size[1];
    $ext = (int)$img_size[2];
    switch ($ext) {
      case 1:
        $extt = "gif";
        break;
      case 2:
        $extt = "jpg";
        break;
      case 3:
        $extt = "png";
        break;
    }

    $max_size = max($width, $height);

    if($max_size <= $size) {
        @move_uploaded_file($upload_file, $img_dir.$img_name.".".$extt);
        return $img_name.".".$extt;
    }

    $koef = $size/$max_size;
    $w = (int)$img_size[0]*$koef;         // to
    $h = (int)$img_size[1]*$koef;
    switch ($ext) {
      case 1:
        $im = imagecreatefromgif($upload_file);
        break;
      case 2:
        $im = imagecreatefromjpeg($upload_file);
        break;
      case 3:
        $im = imagecreatefrompng($upload_file);
        break;
    }
    $im_new = imagecreatetruecolor($w, $h);
    imagecopyresampled($im_new, $im, 0, 0, 0, 0, $w, $h, $width, $height);
    switch ($ext) {
      case 1:
        imagegif($im_new, $img_dir.$img_name.".".$extt);
        break;
      case 2:
        imagejpeg($im_new, $img_dir.$img_name.".".$extt);
        break;
      case 3:
        imagepng($im_new, $img_dir.$img_name.".".$extt);
        break;
    }

    return $img_name.".".$extt;
}
function dialog_open($text) {
    return "
    $('.dialog-container').css({ width: '400px', height: '200px', 'margin-top': '70px' });
    $('#dialog-content').html(\"".make_js_str($text)."\");
    $('#over-container').show();
    ";
}
function make_user_id($tbl) {
    global $ini_arr;

    do {
        $new_id = rand(1000000, 9999999);
        $query = "SELECT COUNT(*) FROM ".prdb.$tbl." WHERE id = ".$new_id;
        
    } while(db_get_value($query));
    
    return $new_id;
}
function check_meta($text) {
    $text = strip_tags(trim($text));
    $text = str_replace("'", "&apos;", $text);
    $text = str_replace('"', '&quot;', $text);
    $text = str_replace(chr(10), '', $text);
    $text = str_replace(chr(13), ' ', $text);
    return $text;
}
function is_search_bot() {
    global $ini_arr;
    
    $bots = array(
        'GoogleBot' => 'Googlebot',
        'Yandex' => 'Yandex',
        'MSN Bot' => 'msn',
        'Yahoo' => 'Yahoo',
        'Rambler' => 'Rambler',
        'bing.com' => 'bingbot',
        'ahrefs.com' => 'AhrefsBot',
        'www.trendiction.de' => 'trendictionbot',
        'www.diffbot.com' => 'Diffbot',
        'www.datagnion.com' => 'datagnionbot'
    );
    
    if(empty($_SERVER['HTTP_USER_AGENT']))
        return false;
    
    foreach($bots as $bot) {
        if(stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
            return true;
    }
    
    return false;
}
function make_letter($letter_uid, $lang, $repl_arr = array()) {
    global $ini_arr;
    $res_arr = array(
        'name' => "",
        'subject' => "",
        'text' => "",
        'html' => ""
    );

    $s = file_get_contents(LETTERS_FILE);
    $letters_arr = empty($s) ? array() : unserialize($s);
    foreach($letters_arr as $key => $row) {
        if($row['uid'] == $letter_uid) {
            $l_arr = $row;
            break;
        }
    }
    
    if(!isset($l_arr))
        return "err";
    
    $main_tpl = "";
    $main_tpl_file = LETTERS_DIR.'main-'.$lang.'.tpl';
    if(file_exists($main_tpl_file))
        $main_tpl = file_get_contents($main_tpl_file);
        
    
    if(!empty($l_arr['subject'][$lang]))
        $subject = $l_arr['subject'][$lang];
    else
        $subject = $l_arr['name'];

    $message = "";
    $letter_tpl_file = LETTERS_DIR.$l_arr['uid'].'-'.$lang.'.tpl';
    if(file_exists($letter_tpl_file))
        $message = file_get_contents($letter_tpl_file);
        
    if(!sizeof($repl_arr) and sizeof($l_arr['repl_alias'])) {
    // Подстановки по умолчанию
        foreach($l_arr['repl_alias'] as $key => $alias) {
            $repl_arr[$alias] = isset($l_arr['repl_value'][$key]) ? $l_arr['repl_value'][$key] : "";
        }
    }
    
    foreach($repl_arr as $alias => $value) {
        $subject = str_replace($alias, $value, $subject);
        $message = str_replace($alias, $value, $message);
    }
    
    $res_arr['name'] = $l_arr['name'];
    $res_arr['subject'] = $subject;
    $res_arr['text'] = strip_tags($message);
    if(empty($main_tpl) or $letter_uid == 'main')
        $res_arr['html'] = $message;
    else
        $res_arr['html'] = str_replace('[MAIL_CONTENT]', $message, $main_tpl);
    
    return $res_arr;
}
// --------------------- errors
function exception_handler($exception) {
    $err = print_r($exception, true);
    
    if(isset($_SERVER['SERVER_ADDR']) and $_SERVER['SERVER_ADDR'] == "127.0.0.1") {
        echo "<div class='systemErr'><pre>".$err."</pre></div>";
        return;
    }

    require_once(BASE_HREF.'include/class.phpmailer.php');

    error_log($err . "--------------------------\n", 3, ERROR_LOG_FILE);

    $mail = new PHPMailer();
    $mail->CharSet = "UTF-8";
    $mail->SetFrom('errors@'.DOMAIN, 'ERRORS '.DOMAIN);
    $mail->AddAddress('jaroslav.ks@gmail.com');
    $mail->Subject = 'ERROR! '.substr($exception->getMessage(), 0, 100);
    $mail->Body = $err;

    $mail->Send();
}
function error_handler($errno, $errmsg, $filename, $linenum, $vars) {
    $dt = @date("Y-m-d H:i:s");

    $errortype = array (
                E_ERROR           => "Error",
                E_WARNING         => "Warning",
                E_PARSE           => "Parsing Error",
                E_NOTICE          => "Notice",
                E_CORE_ERROR      => "Core Error",
                E_CORE_WARNING    => "Core Warning",
                E_COMPILE_ERROR   => "Compile Error",
                E_COMPILE_WARNING => "Compile Warning",
                E_USER_ERROR      => "User Error",
                E_USER_WARNING    => "User Warning",
                E_USER_NOTICE     => "User Notice",
                E_STRICT          => "Runtime Notice"
                );

    $err = "datetime: " . $dt . "\n";
    $err .= "errortype: " . $errortype[$errno] . "\n";
    $err .= "errormsg: " . $errmsg . "\n";
    $err .= "scriptname: " . $filename . "\n";
    $err .= "line: " . $linenum . "\n\n";

    if(isset($_SERVER['SERVER_ADDR']) and $_SERVER['SERVER_ADDR'] == "127.0.0.1" and !defined('IS_BOT')) {
        echo "<div class='systemErr'>".nl2br($err)."</div>";
        return;
    }

    if($filename == 'Unknown' or !$linenum) {
	$error_log_file = $_SERVER['DOCUMENT_ROOT']."/data/error.txt";
	$base_href = $_SERVER['DOCUMENT_ROOT']."/";
    }
    else {
	$error_log_file = ERROR_LOG_FILE;
	$base_href = BASE_HREF;
    }
    
    require_once($base_href.'include/class.phpmailer.php');

    error_log($err . "--------------------------\n", 3, $error_log_file);

    $file_body = $err."--------------------------\n vars: " . print_r($vars, true) . "\n";
    $file_name = $base_href.'data/tmp/'.time().'-'.make_pass(5).'.txt';
    file_put_contents($file_name, $file_body);

    $mail = new PHPMailer();
    $mail->CharSet = "UTF-8";
    $mail->SetFrom('errors@'.DOMAIN, 'ERRORS '.DOMAIN);
    $mail->AddAddress('jaroslav.ks@gmail.com');
    $mail->Subject = 'ERROR! '.substr($errmsg, 0, 100);
    $mail->Body = $err;
    $mail->AddAttachment($file_name);

    $mail->Send();

    unlink($file_name);
}
// --------------------- pdo
function db_get_value($query, $params_arr = array()) {
// if empty result return false

    if(sizeof($params_arr)) {
        $param = pdo_prepare_arr($params_arr);
        $result = $GLOBALS['pdo']->prepare($query);
        $result->execute($param);
        $res = $result->fetchColumn();
    }
    else {
        $res = $GLOBALS['pdo']->query($query)->fetchColumn();
    }
    
    return $res;
}
function db_get_row($query, $params_arr = array()) {
    if(sizeof($params_arr)) {
        $param = pdo_prepare_arr($params_arr);
        $result = $GLOBALS['pdo']->prepare($query);
        $result->execute($param);
    }
    else {
        $result = $GLOBALS['pdo']->query($query);
    }
    
    $res_arr = $result->fetchAll();
    
    if(isset($res_arr[0]))
        return $res_arr[0];
    
    return array();
}
function db_get_arr($query, $params_arr = array(), $res_type = 'row') {
// $res_type => one, pair, arr, row(for 'while')

    if(sizeof($params_arr)) {
        $param = pdo_prepare_arr($params_arr);
        $result = $GLOBALS['pdo']->prepare($query);
        $result->execute($param);
    }
    else {
        $result = $GLOBALS['pdo']->query($query);
    }
    
    switch ($res_type) {
        case "one":
            $res_arr = $result->fetchAll(PDO::FETCH_COLUMN);
            break;
        case "pair":
            $res_arr = $result->fetchAll(PDO::FETCH_KEY_PAIR);
            break;
        case "arr":
            $res_arr = $result->fetchAll();
            break;
        case "row":
            $res_arr = $result;
            break;
    }

    return $res_arr;
}
function db_insert($query, $params_arr, $res_type = 'count') {
// $res_type => count | insert_id

    foreach($params_arr as $key => $value)
        $query .= $key." = :".$key.", ";
    $query = substr($query, 0, -2);

    $param = pdo_prepare_arr($params_arr);
    $result = $GLOBALS['pdo']->prepare($query);
    $result->execute($param);
    
    if($res_type == 'insert_id')
        return $GLOBALS['pdo']->lastInsertId();
    
    return $result->rowCount();
}
function db_update($query, $params_arr, $params_arr_where) {
// [QUERY_SET]

    $query_set = "";
    
    foreach($params_arr as $key => $value)
        $query_set .= $key." = :".$key.", ";
    $query_set = substr($query_set, 0, -2);

    $query = str_replace("[QUERY_SET]", $query_set, $query);
    $param = pdo_prepare_arr($params_arr);
    $param_where = pdo_prepare_arr($params_arr_where);
    $param = array_merge($param, $param_where);
    $result = $GLOBALS['pdo']->prepare($query);
    $result->execute($param);
    
    return $result->rowCount();
}
function db_delete($query, $params_arr) {
    $param = pdo_prepare_arr($params_arr);
    $result = $GLOBALS['pdo']->prepare($query);
    $result->execute($param);

    return $result->rowCount();
}
function pdo_prepare_arr($arr) {
    $res_arr = array();
    foreach($arr as $key => $value)
        $res_arr[":".$key] = $value;
    
    return $res_arr;
}
?>