<?php
$cal_list = isset($_REQUEST['cal_list']) ? trim(strip_tags($_REQUEST['cal_list'])) : "";
$res_str .= cal_list($cal_list);

function cal_list($cal_list) {
    global $ini_arr, $ROW_PAGE;

    $news_char_num = isset($ROW_PAGE['ini_arr']['adv_setting']['len']) ? (int)$ROW_PAGE['ini_arr']['adv_setting']['len'] : 300;

    $res_str = "";

    $param = array('lang' => LANG);
    $query_where = "";
    $news_arr = explode(",", $cal_list);
    foreach($news_arr as $news_id) {
        $id = (int)trim($news_id);
        if(!$id) continue;
        
        $param['id'.$id] = $id;
        $query_where .= " ".prdb."menu.id = :id".$id." OR ";
    }
    if(!empty($query_where))
        $query_where = " AND ( ".substr($query_where, 0, -3)." ) ";

    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, DATE_FORMAT(".prdb."menu.date_in, '%d.%m.%Y %H:%i') AS f_date_in,
                ".prdb."menu.user_only, ".prdb."menu.page_ini_arr, ".prdb."pages.name, ".prdb."pages.content
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."pages.id AND ".prdb."menu.id = ".prdb."menu_pages.id
                AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.in_menu = 1 AND ".prdb."menu.type = 'news_1'
                AND LENGTH(".prdb."pages.name)
                ".$query_where."
              ORDER BY ".prdb."menu.date_in DESC";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr)) return;
    
    foreach($row_arr as $row) {
        if($ini_arr['is_user_page'] and $row['user_only'] and !SESS_ID)
            continue;

        $row['prop_arr'] = empty($row['page_ini_arr']) ? array() : unserialize($row['page_ini_arr']);
        unset($row['page_ini_arr']);
        $res_str .= one_news_list($row, $news_char_num);
    }

    return "<ul class='list-news'>".$res_str."<ul>";
}
?>