<?php
$ini_arr['search_num_res'] = isset($ROW_PAGE['ini_arr']['adv_setting']['num_res']) ? (int)$ROW_PAGE['ini_arr']['adv_setting']['num_res'] : 10;
$ini_arr['search_str_len'] = isset($ROW_PAGE['ini_arr']['adv_setting']['str_len']) ? (int)$ROW_PAGE['ini_arr']['adv_setting']['str_len'] : 600;

if(isset($_REQUEST['search_query'])) {
    $_SESSION['search_query'] = urldecode(strip_tags(trim($_REQUEST['search_query'])));
    if(strlen($_SESSION['search_query']) < 3)
        $_SESSION['search_query'] = "";
}
if(!isset($_SESSION['search_query']))
    $_SESSION['search_query'] = "";

$content_arr['SEARCH_QUERY'] = $_SESSION['search_query'];

$res_str = site_search($ps);

// ---------------------------- function
function site_search($ps) {
    global $ini_arr;

    $res_str = '
    <div style="margin-bottom: 30px">
      <form action="'.$ini_arr['pr_lang'].'search.htm" method="get" class="">
        <input name="search_query" type="text" class="" value="'.htmlspecialchars($_SESSION['search_query'], ENT_QUOTES).'"
          placeholder="Пошук по сайту" />
        <button type="submit" value="Пошук" class="button"><img src="[IMAGE_DIR]search_icon.png"></button>
      </form>
    </div>';

    if(empty($_SESSION['search_query']))
        return $res_str.'<div class="alert">'.$ini_arr['lang']['no-result'].'</div>';

    $search_arr = page_search();

    $num_all = sizeof($search_arr);
    $res_arr = array_slice($search_arr, $ps, $ini_arr['search_num_res']);

    if(!$num_all)
        return $res_str.'<div class="alert">'.$ini_arr['lang']['no-result'].'</div>';

    foreach($res_arr as $row) {
        $res_str .= '
          <div class="list-result">
            <h3><a href="'.$ini_arr['pr_lang'].$row['alias'].'.htm">'.$row['name'].'</a></h3>
            '.substr_space(strip_tags($row['content']), $ini_arr['search_str_len'], " ...").'
          </div>
        ';
    }

    if($num_all > $ini_arr['search_num_res']) {
        $res_str .= "<br><div class='lineList' align='center'>".line_list_pages(
                        $num_all,
                        $ini_arr['search_num_res'],
                        7,
                        $ps,
                        $ini_arr['pr_lang']."search.htm?search_query=".$_SESSION['search_query']."&ps="
                    )."</div>";
    }

    return $res_str;
}
function page_search() {
    global $ini_arr;

    $res_arr = array();
    $param = array('lang' => LANG, 'query' => '%'.$_SESSION['search_query'].'%');
    $query = "SELECT ".prdb."menu.alias, ".prdb."pages.name, ".prdb."pages.content
              FROM ".prdb."menu, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."pages.id
                AND ".prdb."menu.in_menu='1'
                AND ".prdb."pages.lang = :lang 
                AND (".prdb."pages.name LIKE :query
                     OR ".prdb."pages.content LIKE :query
                     OR ".prdb."pages.search LIKE :query)
              ORDER BY date_in DESC
              LIMIT 50";
    $res_arr = db_get_arr($query, $param, 'arr');

    return $res_arr;
}
?>