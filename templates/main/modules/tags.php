<?php
// ------------------------------------------- Переделать

if(isset($_REQUEST['tag'])) {
    $_REQUEST['tag'] = trim($_REQUEST['tag']);
    $res_str .= make_tags_cloud(1000).tags_page_list(urldecode($_REQUEST['tag']));
}
else {
    $res_str .= make_tags_cloud(1000);
}


// -------------------------------- function
function tags_page_list($tag) {
    global $ini_arr, $row_page;
    if(empty($_REQUEST['tag'])) return;

    $list_char_num = isset($row_page['ini_arr']['adv_setting']['list_char_num']) ? (int)$row_page['ini_arr']['adv_setting']['list_char_num'] : 200;
    $res_str = "";

    $query = "SELECT alias, ".prdb."pages.name, content
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages, ".prdb."tags
              WHERE ".prdb."menu.id=".prdb."menu_pages.id AND lang='".LANG."'
                AND ".prdb."menu.id=".prdb."pages.id AND in_menu='1'
                AND FIND_IN_SET(".prdb."tags.id, ".prdb."menu.tags) AND ".prdb."tags.name='".mysql_real_escape_string($tag)."'
              ORDER BY p_id";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    while($row = mysql_fetch_assoc($result))
        $res_str .= "
        <li><a href='".$ini_arr['pr_lang'].$row['alias'].".htm'>".$row['name']."</a><br>
          ".substr_space(strip_tags(remove_h($row['content'])), $list_char_num, " ...")."
        </li>";

    if(!empty($res_str))
        $res_str = "<ul>".$res_str."</ul>";

    return $res_str;
}
?>