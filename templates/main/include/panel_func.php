<?php
function make_panel_content_arr() {
    global $content_arr, $ini_arr;

    $panel_arr_default = make_panel_arr_default();
    if(!is_array($panel_arr_default)) return;
    $page_panel_arr = make_panel_arr(PAGE_ID);

    foreach($panel_arr_default as $row) {
        $panel_arr[$row['id']] = $row;
        if(isset($page_panel_arr[$row['id']]['prop_arr']))
            $panel_arr[$row['id']]['prop_arr'] = $page_panel_arr[$row['id']]['prop_arr'];
    }

    foreach($panel_arr as $row) {
        $prop_arr = isset($row['prop_arr']) ? $row['prop_arr'] : array();
        switch ($row['type']) {
          case 'text':
            $content_arr["PANEL_".$row['alias']] = isset($prop_arr['text']) ? $prop_arr['text'] : "";
            break;
          case 'html':
            $content_arr["PANEL_".$row['alias']] = isset($prop_arr['text'][LANG]) ? $prop_arr['text'][LANG] : "";
            break;
          case 'banner':
            $content_arr["PANEL_".$row['alias']] = isset($prop_arr['banner_id']) ? make_banner($prop_arr['banner_id']) : "";
            break;
          case 'news':
            $prop_arr['num'] = isset($prop_arr['num']) ? (int)$prop_arr['num'] : 0;
            $prop_arr['len'] = isset($prop_arr['len']) ? (int)$prop_arr['len'] : 0;
            $prop_arr['photo'] = empty($prop_arr['photo']) ? 0 : 1;
            $prop_arr['date_in'] = empty($prop_arr['date_in']) ? 0 : 1;
            $content_arr["PANEL_".$row['alias']] = isset($prop_arr['news_id'])
                ? make_panel_news($prop_arr['news_id'], $prop_arr['num'], $prop_arr['len'], $prop_arr['photo'], $prop_arr['date_in'])
                : "";
            break;
          case 'list':
            $content_arr["PANEL_".$row['alias']] = empty($prop_arr['list_id']) ? "" : make_list_menu($prop_arr['list_id']);
            break;
          case 'cal':
            $prop_arr['all_alias'] = empty($prop_arr['all_alias']) ? "": $prop_arr['all_alias'];
            $content_arr["PANEL_".$row['alias']] = isset($prop_arr['news_id'])
                ? "<div id='news_cal'>".make_panel_calendar($prop_arr['news_id'], $prop_arr['all_alias'])."</div>"
                : "";
            break;
          case 'items_slider':
            $s = "";
            if(isset($prop_arr['item_id'])) {
                foreach($prop_arr['item_id'] as $item_id)
                    $s .= (int)$item_id.",";
                $s = substr($s, 0, -1);
            }
            $content_arr["PANEL_".$row['alias']] = $s;
            break;


          case 'vote':
            $content_arr["PANEL_".$panel_name] = make_vote($arr['que_id']);
            break;
          case 'tags':
            $content_arr["PANEL_".$panel_name] = make_tags_cloud($arr['num_tags'])."
            <div class='a-right'><a href='".$ini_arr['pr_lang']."tags.htm'>".$word[LANG][41]."</a></div>";
            break;
        }
    }
}
function make_tags_cloud($num_tags) {
    global $ini_arr, $word;
// ------------------------------------------- Старая
    if(!$num_tags) return;
    $query = "SELECT ".prdb."tags.name, COUNT(".prdb."tags.id) AS num_tags
              FROM ".prdb."tags, ".prdb."menu
              WHERE FIND_IN_SET(".prdb."tags.id, ".prdb."menu.tags)
              GROUP BY ".prdb."tags.id
              ORDER BY num_tags DESC
              LIMIT ".$num_tags."
              ";
    $result = mysql_query($query)    or die(mysql_error()."<br>".$query);
    while($row = mysql_fetch_assoc($result)) {
        $arr[] = $row['name'].",".$row['num_tags'];
    }
    if(!mysql_num_rows($result)) return;
    natcasesort($arr);
    $flag = true;
//echo "<pre>".print_r($arr, true)."</pre>";
    foreach($arr as $value) {
        $a = explode(",", $value);

        if($flag) {
            $max_num_tags = $a[1];
            $min_num_tags = $a[1];
            $flag = false;
        }
        $max_num_tags = $max_num_tags > $a[1] ? $max_num_tags : $a[1];
        $min_num_tags = $min_num_tags < $a[1] ? $min_num_tags : $a[1];

        $tags_arr[$a[0]] = $a[1];
    }

    $em1 = $ini_arr['tags_min_em'];
    $em2 = $ini_arr['tags_max_em'];

    $res_str = "<div class='a-center'>";
    foreach($tags_arr as $name => $num) {
        $em = $em2 - (($max_num_tags - $num) * ($em2 - $em1)) / ($max_num_tags - $min_num_tags);
        $res_str .= "<a href='".$ini_arr['pr_lang']."tags.htm?tag=".$name."'><span style='font-size: ".$em."em; '>".$name."</span></a> ";
    }
    $res_str .= "</div>

    ";

    return $res_str;
}
function make_panel_calendar($news_arr, $news_alias) {
    global $ini_arr;
    $res_str = "";

    if(!sizeof($news_arr)) return;

    $parent_arr = array_keys($news_arr);
    $month_arr = get_vars('', 'month', 'uid');

    $year = date("Y");
    if(isset($_REQUEST['cal_y'])) {
        $_REQUEST['cal_y'] = (int)$_REQUEST['cal_y'];
        if($_REQUEST['cal_y'] > $year - 100 and $_REQUEST['cal_y'] < $year + 100)
            $year = $_REQUEST['cal_y'];
    }

    $month = date("n");
    if(isset($_REQUEST['cal_m'])) {
        $_REQUEST['cal_m'] = (int)$_REQUEST['cal_m'];
        if($_REQUEST['cal_m'] > 0 and $_REQUEST['cal_m'] <= 12)
            $month = $_REQUEST['cal_m'];
    }

    $param = array(
        'date_min' => $year."-".$month."-1",
        'date_max' => $year."-".$month."-31 23:59:59",
        'lang' => LANG
    );
    $query_where = "";
    foreach($news_arr as $news_id => $v) {
        $param['news_id'.$news_id] = $news_id;
        $query_where .= " ".prdb."menu_pages.parent_id = :news_id".$news_id." OR ";
    }
    if(!empty($query_where))
        $query_where = " AND ( ".substr($query_where, 0, -3)." ) ";

    $query = "SELECT ".prdb."menu.alias, ".prdb."menu.id, DATE_FORMAT(".prdb."menu.date_in, '%d') AS f_day
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id AND
                ".prdb."menu.date_in >= :date_min AND ".prdb."menu.date_in <= :date_max
                AND ".prdb."menu.type = 'news_1' AND ".prdb."pages.lang = :lang AND in_menu = '1'
                ".$query_where."
              ORDER BY ".prdb."menu.date_in DESC
              ";
    $row_arr = db_get_arr($query, $param, 'arr');

    $res_arr = array();
    $alias_arr = array();
    foreach($row_arr as $row) {
        $day = (int)$row['f_day'];
        $res_arr[$day] = isset($res_arr[$day]) ? $res_arr[$day].",".$row['id'] : $row['id'];
        $alias_arr[$row['id']] = $row['alias'];
    }
    $num_day = date("t", mktime(0, 0, 0, $month, 1, $year));
    $res_str .= "
    <div class='title'>".$ini_arr['lang']['calendar']."</div>
    <table>
    <tr>
      <th colspan='4' class='a-left'>
        <select size='1' name='cal_m' id='cal_m' onChange='
          $(\"#news_cal\").load(\"".AJAX_SCRIPT."\",
          {
              action: \"make_calendar\",
              cal_m: this.value,
              cal_y: $(\"#cal_y\").val(),
              lang: \"".LANG."\"
          });
        '>";
    foreach($month_arr as $m => $m_name) {
        $res_str .= "<option value='".$m."' ".($m == $month ? "selected" : "").">".$m_name."</option>";
    }
    $res_str .= "
        </select>
      </th>
      <th colspan=3 class='a-right'>
        <select size='1' name='cal_y' id='cal_y' onChange='
          $(\"#news_cal\").load(\"".AJAX_SCRIPT."\",
          {
              action: \"make_calendar\",
              cal_m: $(\"#cal_m\").val(),
              cal_y: this.value,
              lang: \"".LANG."\"
          });
        '>";
    for($y = date("Y") + 1; $y >= 2004; $y--) {
        $res_str .= "<option value='".$y."' ".($y == $year ? "selected" : "").">".$y."</option>";
    }
    $res_str .= "
        </select>
      </th>
    </tr>";
    for($d = 1; $d <= $num_day; $d++) {
        $ds = mktime(0, 0, 0, $month, $d, $year);
        $w = date("w", $ds);
        if(!$w) $w = 7;

        if($d == 1) {
            $res_str .= "<tr>";
            for($i = 1; $i <= $w - 1; $i++)
                $res_str .= "<td>&nbsp;</td>";
        }
        elseif($w % 7 == 1)
            $res_str .= "<tr>";

        $today_class = "";
        if($year == date("Y") and $month == date("n") and $d == date("j"))
            $today_class = "today";
        if(array_key_exists($d, $res_arr)) {
            $id_arr = explode(",", $res_arr[$d]);
            if(sizeof($id_arr) > 1)
                $a = $news_alias.".htm?cal_m=".$month."&cal_y=".$year."&cal_list=".$res_arr[$d];
            else
                $a = $alias_arr[$id_arr[0]].".htm?cal_m=".$month."&cal_y=".$year;
            $res_str .= "<td class='".$today_class."'><a
              href='".$ini_arr['pr_lang'].$a."'
            >".$d."</a></td>";
        }
        else
            $res_str .= "<td class='".$today_class."'>".$d."</td>";

        if($d == $num_day) {
            for($i = $w + 1; $i <= 7; $i++)
                $res_str .= "<td>&nbsp;</td>";
            $res_str .= "</tr>";
        }
        elseif(!($w % 7))
            $res_str .= "</tr>";
    }
    $res_str .= "</table>";

    return "<div class='calendar'>".$res_str."</div>";
}
function make_panel_news($news_arr, $num, $len, $is_photo, $is_date) {
    global $ini_arr;
    $res_str = "";

    if(!sizeof($news_arr) or !$num) return;

    $parent_arr = array_keys($news_arr);

    $news_alias = '';
    $news_name = '';
    $news_link = '';

    if(sizeof($parent_arr) === 1) {
        $param = array('id' => $parent_arr[0], 'lang' => LANG);
        $query = "SELECT ".prdb."menu.alias, ".prdb."pages.name
                  FROM ".prdb."menu, ".prdb."pages
                  WHERE ".prdb."menu.id = ".prdb."pages.id
                    AND ".prdb."menu.id = :id AND ".prdb."pages.lang = :lang";
        $row = db_get_row($query, $param);
        if(!sizeof($row)) return;
    
        $news_alias = $row['alias'];
        $news_name = $row['name'];
    }
    if(!empty($news_name))
        $news_name = "<div class='title'>".$news_name."</div>";
    if(!empty($news_alias))
        $news_link = "<div class='a-right'>
          <a href='".$ini_arr['pr_lang'].$news_alias.".htm'>".$ini_arr['lang']['more']." <span class='fa fa-arrow-circle-right'></span></a>
        </div>";

    $param = array('lang' => LANG);
    $query_where = "";
    foreach($parent_arr as $parent_id) {
        $id = (int)$parent_id;
        if(!$id) continue;
        $param['id'.$id] = $id;
        $query_where .= " ".prdb."menu_pages.parent_id = :id".$id." OR ";
    }
    if(empty($query_where)) return;
    $query_where = substr($query_where, 0, -3);
    
    $query = "SELECT alias, name, content, page_ini_arr,
                DATE_FORMAT(date_in, '%d.%m.%Y') AS f_date_in
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id
                AND (".$query_where." ) AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.in_menu = 1 AND ".prdb."menu.type = 'news_1'
                AND LENGTH(".prdb."pages.name)
              ORDER BY date_in DESC
              LIMIT 0,".$num;
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr)) return;
    
    $res_str = "
    <div class='news-panel'>
      ".$news_name;
    foreach($row_arr as $row) {
        $prop_arr = empty($row['page_ini_arr']) ? array() : unserialize($row['page_ini_arr']);

        $img = "";
        if($is_photo) {
            if(!empty($prop_arr['adv_setting']['main_img']) and file_exists(IMAGE_PAGE_DIR."news_1/".$prop_arr['adv_setting']['main_img']))
                $img = "<img src='".IMAGE_PAGE_DIR."news_1/".$prop_arr['adv_setting']['main_img']."' />";
            else {
                preg_match("/<img [^>]*src=[\'\"]?([^\'\"]*)[\'\"]?[^>]*>/", $row['content'], $matches);
                if(isset($matches[1])) {
                    $img = "<img src='".$matches[1]."' />";
                }
            }
        }
        
        $date_in = "";
        if($is_date)
            $date_in = "<div class='date-in'>".(str_replace(" 00:00", "", $row['f_date_in']))."</div>";

        $res_str .= "
        <div class='news-panel-one'>
          <div class='name'><a href='".$ini_arr['pr_lang'].$row['alias'].".htm'>".$row['name']."</a></div>
          ".$date_in."
          <div class='news'>".$img.substr_space(strip_tags($row['content']), $len, " ...")."</div>
        </div>";
    }
    $res_str .= $news_link."</div>";

    return $res_str;
}
function make_banner($banner_arr) {
    if(!sizeof($banner_arr)) return;

    $arr = array_keys($banner_arr);
    shuffle($arr);
    $banner_id = current($arr);

    $param = array('banner_id' => $banner_id);
    $query = "SELECT * FROM ".prdb."banners WHERE id = :banner_id";
    $row = db_get_row($query, $param);
    if(!sizeof($row)) return;

    if(empty($row['code'])) {
        if(!empty($row['img']) and file_exists(BANNER_DIR.$row['img']))
            $img = "<img src='".BANNER_DIR.$row['img']."' />";
        else
            $img = $row['title'];
        $res_str = "<a href='".$row['link']."' ".($row['target'] ? "target='_blank'" : "").">".$img."</a>";
    }
    else
        $res_str = $row['code'];

    $param = array('stat_show' => $row['stat_show'] + 1);
    $param_where = array('banner_id' => $banner_id);
    $query = "UPDATE ".prdb."banners SET [QUERY_SET] WHERE id = :banner_id";
    db_update($query, $param, $param_where);
    
    return $res_str;
}
function page_tags() {
    // переделать
    global $row_page, $ini_arr;
    $res_str = "";

    if(empty($row_page['tags'])) return;
    $arr = explode(',', $row_page['tags']);
    $query = "SELECT name FROM ".prdb."tags WHERE ";
    foreach($arr as $id_tag) {
        $query .= " id=".$id_tag." OR ";
    }
    $query = substr($query, 0, -3);
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) return;

    $res_str .= "<div class='tags'>Теги: ";
    while($row = mysql_fetch_assoc($result)) {
        $res_str .= "<a href='".$ini_arr['pr_lang']."tags.htm?tag=".$row['name']."'>".$row['name']."</a> ";
    }
    $res_str .= "</div>";

    return $res_str;
}
?>