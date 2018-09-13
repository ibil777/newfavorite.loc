<?php

if($ROW_PAGE['type'] == 'news')
    $res_str .= list_news();
else
    $res_str = one_news();

function list_news() {
    global $ini_arr, $ROW_PAGE, $content_arr;
    
    $res_str = "";
    $news_char_num = isset($ROW_PAGE['ini_arr']['adv_setting']['news_char_num']) ? (int)$ROW_PAGE['ini_arr']['adv_setting']['news_char_num'] : 300;
    $news_num = (isset($ROW_PAGE['ini_arr']['adv_setting']['news_num']) and (int)$ROW_PAGE['ini_arr']['adv_setting']['news_num'])
                ? (int)$ROW_PAGE['ini_arr']['adv_setting']['news_num']
                : $ini_arr['news_num'];

    $param = array('parent_id' => PAGE_ID, 'lang' => LANG);
    $query = "SELECT COUNT(*)
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id 
                AND ".prdb."menu_pages.parent_id = :parent_id AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.in_menu = '1' AND ".prdb."menu.type = 'news_1'
                AND LENGTH(".prdb."pages.name)";
    $num_all = db_get_value($query, $param);
    if(!$num_all) return $ROW_PAGE['content'];

    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, DATE_FORMAT(".prdb."menu.date_in, '%d.%m.%Y %H:%i') AS f_date_in,
                ".prdb."menu.user_only, ".prdb."menu.page_ini_arr, ".prdb."pages.name, ".prdb."pages.content
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id 
                AND ".prdb."menu_pages.parent_id = :parent_id AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.in_menu = '1' AND ".prdb."menu.type = 'news_1'
                AND LENGTH(".prdb."pages.name)
              ORDER BY ".prdb."menu.date_in DESC
              LIMIT ".PS.", ".$news_num;
    $row_arr = db_get_arr($query, $param, 'arr');
    foreach($row_arr as $row) {
        if($ini_arr['is_user_page'] and $row['user_only'] and !SESS_ID)
            continue;

        $row['prop_arr'] = empty($row['page_ini_arr']) ? array() : unserialize($row['page_ini_arr']);
        unset($row['page_ini_arr']);
        $res_str .= one_news_list($row, $news_char_num);
    }
    if(!empty($res_str))
        $res_str = $ROW_PAGE['content']."<ul class='list-news'>".$res_str."</ul>";
    
    if($num_all > $news_num) {
        $res_str .= "<br><div class='lineList' align='center'>".line_list_pages(
                        $num_all,
                        $news_num,
                        7,
                        PS,
                        $ini_arr['pr_lang'].ALIAS.".htm?ps="
                    )."</div>";
    }

    return $res_str;
}
function one_news() {
    global $ini_arr, $ROW_PAGE, $TYPE_ARR, $content_arr;
    $res_str = "";
    
    $sta = sizeof($GLOBALS['TREE_ARR']);
    
    $back_link = "";
    if(isset($GLOBALS['TREE_ARR'][$sta - 2]) and $GLOBALS['TREE_ARR'][$sta - 2]['type'] == 'news') {
        $back_link .= "<div class='back-link'>
            <a href='".$ini_arr['pr_lang'].$GLOBALS['TREE_ARR'][$sta - 2]['alias'].".htm".(PS ? "?ps=".PS : "")."'><span class='fa fa-arrow-circle-left'></span>".$ini_arr['lang']['back-to-news']."</a>
        </div>";
    }

    $img = "";
    if(!empty($ROW_PAGE['ini_arr']['adv_setting']['img_checkbox'])
       and !empty($ROW_PAGE['ini_arr']['adv_setting']['main_img'])
       and file_exists(IMAGE_PAGE_DIR."news_1/".$ROW_PAGE['ini_arr']['adv_setting']['main_img'])) {
        $img = "<div class='a-center bottom-space'>
            <img src='".IMAGE_PAGE_DIR."news_1/".$ROW_PAGE['ini_arr']['adv_setting']['main_img']."' />
        </div>";
    }

    $res_str .= $back_link;

    $res_str .= "
        <h1 class='a-center'>".$ROW_PAGE['name']."</h1>
        ".$img."
        ".$ROW_PAGE['content'];
    
    return $res_str;
}
?>