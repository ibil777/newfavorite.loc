<?php

$res_str .= page_list();

function page_list() {
    global $ini_arr, $ROW_PAGE;
    
    $list_char_num = isset($ROW_PAGE['ini_arr']['adv_setting']['list_char_num']) ? (int)$ROW_PAGE['ini_arr']['adv_setting']['list_char_num'] : 0;
    $res_str = "";

    $param = array('id' => PAGE_ID, 'lang'=> LANG);
    $query = "SELECT ".prdb."menu.alias, ".prdb."menu.user_only, ".prdb."pages.name, ".prdb."pages.content
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id
                AND ".prdb."menu_pages.parent_id = :id AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.in_menu = 1
              ORDER BY ".prdb."menu_pages.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    foreach($row_arr as $row) {
        if($ini_arr['is_user_page'] and $row['user_only'] and !SESS_ID)
            continue;

        $res_str .= "
        <li><a href='".$ini_arr['pr_lang'].$row['alias'].".htm'>".$row['name']."</a><br>
          ".substr_space(strip_tags(remove_h($row['content'])), $list_char_num, " ...")."
        </li>";
    }

    if(!empty($res_str))
        $res_str = "<ul>".$res_str."</ul>";

    return $res_str;
}
?>