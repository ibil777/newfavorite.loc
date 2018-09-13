<?php

$res_str .= creat_map(0);

// ------------------------ function
function creat_map($parent_id) {
    global $ini_arr;

    $res_str = "";
    $param = array('page_id' => $parent_id, 'lang' => LANG);
    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, ".prdb."pages.name, ".prdb."menu.type
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id
                AND ".prdb."menu_pages.parent_id = :page_id AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.in_menu
              ORDER BY ".prdb."menu_pages.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr))
        return;

    $res_str .= "<div class='div_map'>";
    foreach($row_arr as $row) {
        $res_str .= "<a href='".$ini_arr['pr_lang'].$row['alias'].".htm'>".$row['name']."</a>";
        if($row['type'] == 'news') continue;

        $res_str .= creat_map($row['id']);
    }
    $res_str .= "</div>";

    return $res_str;
}
?>