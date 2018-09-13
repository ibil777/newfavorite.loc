<?php

function get_filters_fields($cat_type) {
    global $ini_arr;
    $res_arr = array('arr' => array(), 'id' => array());
    
    $item_type = str_replace('cat_', 'item_', $cat_type);
    $query_where = "";
    foreach($ini_arr['page_ini_arr'][$item_type] as $key => $arr) {
        if(!empty($arr['list_uid']) and !empty($arr['vars']) and ($arr['type'] == 'select' or $arr['type'] == 'checklist')) {
            $param['uid'.$key] = $arr['list_uid'];
            $query_where .= " parent_vars.uid = :uid".$key." OR ";
        }
    }
    $query_where = substr($query_where, 0, -3);
    
    $query = "SELECT vars.id, vars.".LANG." AS name, parent_vars.uid, parent_vars.".LANG." AS parent_name
              FROM ".prdb."vars AS vars, ".prdb."vars AS parent_vars
              WHERE vars.parent_id = parent_vars.id
                AND (".$query_where.")
              ORDER BY vars.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    foreach($row_arr as $row) {
        $vars_arr[$row['uid']][$row['id']] = $row;
    }

    foreach($ini_arr['page_ini_arr'][$item_type] as $key => $arr) {
        if(empty($arr['list_uid']) or !isset($vars_arr[$arr['list_uid']])) continue;
        foreach($vars_arr[$arr['list_uid']] as $k => $a) {
            $res_arr['arr'][$key]['name'] = $a['parent_name'];
            $res_arr['arr'][$key]['list_uid'][$k] = $a['name'];
            $res_arr['id'][$k] = $a['name'];
        }
    }

    return $res_arr;
}
function filters_panel() {
// Перенести в catalog.php
    global $ini_arr, $content_arr;
    $res_str = "";
    
    $query_where = "";
    $param = array('parent_id' => PAGE_ID);
    if(isset($_SESSION['items_set'])) {
        $query_where .= " AND ".prdb."menu.is_".$_SESSION['items_set']." ";
    }
    $query = "SELECT MIN(".prdb."prices.price) AS min_price, MAX(".prdb."prices.price) AS max_price
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."prices
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."prices.page_id
                AND ".prdb."menu_pages.parent_id = :parent_id
                AND ".prdb."menu.in_menu
                ".$query_where."
              ";
    $price_row = db_get_row($query, $param);
    $flag_price = true;
    if(empty($price_row['min_price']) and empty($price_row['max_price']))
        $flag_price = false;
    
    $url = ALIAS.".htm";
    
    $res_str .= "
    <form action='".$url."' method='get' id='fltr-form'>
    <div class='filters'>
      <div class='title'>".$ini_arr['lang']['price']."</div>";
    if($flag_price) {
        $min = isset($_SESSION['s-form']['min']) ? $_SESSION['s-form']['min'] : $price_row['min_price'];
        $max = isset($_SESSION['s-form']['max']) ? $_SESSION['s-form']['max'] : $price_row['max_price'];
        
        $res_str .= "
        <div class='flex'>
          <input type='text' class='min-price' name='min' value='".$min."'>
          <input type='text' class='max-price' name='max' value='".$max."'>
        </div>
        <input type='text' id='rangeSlider'>
        <div class='a-center'>
          <button type='submit'>".$ini_arr['lang']['apply']."</button>
        </div>";
    }
    foreach($ini_arr['filters_field'] as $part => $arr) {
        $var_arr = array();
        
        $res_str .= "<hr>
        <div class='title'>".$arr['name']."</div>";
        
        $counter_on = 0;
        foreach($arr['list_uid'] as $var_id => $var_name) { // сначала отмеченные
            if(isset($_SESSION['s-form']['vars'][$var_id])) {
                $var_arr[$var_id] = $var_name;
                $counter_on++;
            }
        }
        foreach($arr['list_uid'] as $var_id => $var_name) { // потом остальные
            if(!isset($var_arr[$var_id]))
                $var_arr[$var_id] = $var_name;
        }

        $num_show_filters = $ini_arr['num_show_filters'];
        if($counter_on > $num_show_filters)
            $num_show_filters = $counter_on;
        $counter = 0;
        $num_hidden = 0;
        foreach($var_arr as $var_id => $var_name) {
            if($counter == $num_show_filters)
                $res_str .= "<div id='hide-".$part."' style='display: none'>";
            if($counter >= $num_show_filters)
                $num_hidden++;
            $res_str .= "
            <label class='filter'>
              <div class='checkbox'>
                <input type='checkbox' name='fltr-".$var_id."' value='1' ".(isset($_SESSION['s-form']['vars'][$var_id]) ? "checked" : "").">
                <div class='check-button'>
                  <span class='fa fa-on'></span>
                </div>
              </div>
              <div class='check-label' data-id='id-".$var_id."'>".$var_name."</div>
            </label>";
            
            $counter++;
        }
        if($counter > $num_show_filters) {
            $res_str .= "</div>
            <a href='#' class='more more-fltr' id='more-".$part."' data-part='".$part."'>
              <span class='fa fa-angle-down'></span>".$ini_arr['lang']['more-lines']." (".$num_hidden.")
            </a>
            <a href='#' class='more collapse-fltr' id='collapse-".$part."' data-part='".$part."' style='display: none'>
              <span class='fa fa-angle-up'></span>".$ini_arr['lang']['collapse']."
            </a>
            ";
        }
        $res_str .= "<div class='a-center'>
            <button type='submit'>".$ini_arr['lang']['apply']."</button>
          </div>";
    }
    $res_str .= "
    </div>
    </form>
    
    <script>
    $('.more-fltr').click(function() {
      var data = $(this).data();
      $('#hide-' + data.part).slideDown('fast');
      $(this).hide();
      $('#collapse-' + data.part).show();
      return false;
    });

    $('.collapse-fltr').click(function() {
      var data = $(this).data();
      $('#hide-' + data.part).slideUp('fast');
      $(this).hide();
      $('#more-' + data.part).show();
      return false;
    });
    </script>";
    
// -------------- js
    if($flag_price) {
        $content_arr['HEAD'] .= "
        <link rel='stylesheet' href='".USER_TEMPLATE_DIR."css/ion.rangeSlider.css' type='text/css' media='screen' />
        <link rel='stylesheet' href='".USER_TEMPLATE_DIR."css/ion.rangeSlider.skinMatras.css?1' type='text/css' media='screen' />
        <script type='text/javascript' src='".USER_TEMPLATE_DIR."js/ion.rangeSlider.min.js'></script>
        ";
        
        $content_arr['ONLOAD'] .= '
        $range.ionRangeSlider({
            type: "double",
            min: min,
            max: max,
            from: '.$min.',
            to: '.$max.',
            step: 1,
            hide_min_max: true,
            hide_from_to: true,
            onStart: updateInputs,
            onChange: updateInputs
        });
        instance = $range.data("ionRangeSlider");
        ';
        
        $res_str .= '
        <script>
        var $range = $("#rangeSlider"),
            $minPrice = $(".min-price"),
            $maxPrice = $(".max-price"),
            instance,
            min = '.$price_row['min_price'].',
            max = '.$price_row['max_price'].',
            from = '.$min.',
            to = '.$min.';
        
        function updateInputs (data) {
            from = data.from;
            to = data.to;
            
            $minPrice.prop("value", from);
            $maxPrice.prop("value", to);	
        }
        
        $minPrice.on("input", function () {
            var val = $(this).prop("value");
            
            // validate
            if (val < min) {
                val = min;
            } else if (val > to) {
                val = to;
            }
            
            instance.update({
                from: val
            });
        });
        
        $maxPrice.on("input", function () {
            var val = $(this).prop("value");
            
            // validate
            if (val < from) {
                val = from;
            } else if (val > max) {
                val = max;
            }
            
            instance.update({
                to: val
            });
        });
        </script>
        ';
    }
    
    return $res_str;
}
function make_sort_panel() {
// Перенести в catalog.php
    global $ini_arr;
    $res_str = "";
    
    $set = isset($_SESSION['items_set']) ? $_SESSION['items_set'] : 'all';
    $sort = isset($_SESSION['items_sort']) ? $_SESSION['items_sort'] : $ini_arr['sort_items_default'];
    
    $res_str .= "
    <div class='sort-panel flex'>
      <div>
        <a href='".$ini_arr['pr_lang'].ALIAS.".htm?set=all' class='".($set == 'all' ? "selected" : "")."'>
            <span class='fa fa-on'></span> ".$ini_arr['lang']['all']."</a>
        <a href='".$ini_arr['pr_lang'].ALIAS.".htm?set=action' class='".($set == 'action' ? "selected" : "")."'>
            <span class='fa fa-on'></span> ".$ini_arr['lang']['item-action']."</a>
        <a href='".$ini_arr['pr_lang'].ALIAS.".htm?set=new' class='".($set == 'new' ? "selected" : "")."'>
            <span class='fa fa-on'></span> ".$ini_arr['lang']['item-new']."</a>
        <a href='".$ini_arr['pr_lang'].ALIAS.".htm?set=hit' class='".($set == 'hit' ? "selected" : "")."'>
            <span class='fa fa-on'></span> ".$ini_arr['lang']['item-hit']."</a>
      </div>
      <form action='".$ini_arr['pr_lang'].ALIAS.".htm' method='get' id='sort-form'>
        <label>".$ini_arr['lang']['sort'].": </label>
        <div class='select-container w-250px'>
          <select name='sort' size='1' onchange='$(\"#sort-form\").submit()'>";
    foreach($ini_arr['sort_items'] as $key => $value) {
        $res_str .= "<option value='".$key."' ".($sort == $key ? "selected" : "").">".$value."</option>";
    }
    $res_str .= "
          </select>
          <div class='select-button fa fa-angle-down'></div>
        </div>
      </form>
    </div>
    ";
    
    return $res_str;
}
function one_item() {
    global $ini_arr, $content_arr, $ROW_PAGE;
    $res_str = "";
    $ini_arr['prices'] = make_price_arr(PAGE_ID);

    $res_str .= item_main_panel();
    if(sizeof($ini_arr['prices']))
        $res_str .= prices_tbl();
    $res_str .= "
    <div class='bg mb-50 oh'>
      <div class='page content tbl-list mb-50'>
        ".$ROW_PAGE['content']."
      </div>
    </div>";
    
    return $res_str;
}
function item_main_panel() {
    global $ini_arr, $ROW_PAGE, $content_arr;
    $res_str = "";
    
    $lead = empty($ROW_PAGE['ini_arr']['adv_setting']['lead']) ? "" : $ROW_PAGE['ini_arr']['adv_setting']['lead'];
    $price_str = "";
    $cur_price = "";
    $cur_price_id = 0;
    if(sizeof($ini_arr['prices'])) {
        $counter = 1;
        $select_str = "";
        $size_price_str = "";
        foreach($ini_arr['prices'] as $var_id => $row) {
            $class = 'hidden';
            if($counter == 1) {
                $class = '';
                $cur_price = $row['price'];
                $cur_price_id = $var_id;
            }
            $select_str .= "<option value='".$var_id."'>".$row['name']."</option>";
            $size_price_str .= "<div id='size-price-".$var_id."' data-price='".$row['price']."' class='size-price ".$class."'>";
            if($row['price1'] > 0) {
                $size_price_str .= "<div>
                  <div class='text-red'>
                    <span class='price'>".$row['price']."</span> ".$ini_arr['lang']['currency']."
                  </div>
                  <div class='price-old'>
                    ".$row['price1']." ".$ini_arr['lang']['currency']."
                  </div>
                </div>";
            }
            else {
                $size_price_str .= "<div style='margin-top: 15px'><span class='price'>".$row['price']."</span> ".$ini_arr['lang']['currency']."</div>";
            }
            $size_price_str .= "</div>";
            
            $counter++;
        }
        
        $price_str .= "
        <div class='flex mb-20'>
          <div class='changePrice'>".$ini_arr['lang']['choose-size']."</div>
          <div class='select-container'>
            <select name='price' size='1' id='size-sel'>".$select_str."</select>
            <div class='select-button fa fa-down-dir'></div>
          </div>
          ".$size_price_str."
        </div>
        <script>
            $('#size-sel').change(function() {
                var id = $(this).val(),
                    priceDiv = $('#size-price-' + id),
                    price = priceDiv.data();
                
                $('.size-price').hide();
                priceDiv.show();
                curPrice = price.price;
                curPriceId = id;
            });
        </script>";
    }
    elseif($ROW_PAGE['price'] > 0) {
        $cur_price = $ROW_PAGE['price'];
        if($ROW_PAGE['price1'] > 0) {
            $price_str .= "<div class='flex w-70 m-center mb-20'>
              <div class='text-red'><span class='price'>".$ROW_PAGE['price']."</span> ".$ini_arr['lang']['currency']."</div>
              <div class='price-old'>".$ROW_PAGE['price1']." ".$ini_arr['lang']['currency']."</div>
            </div>";
        }
        else {
            $price_str .= "<div class='mb-20'><span class='price'>".$ROW_PAGE['price']."</span> ".$ini_arr['lang']['currency']."</div>";
        }
    }
    
    $res_str .= "
    <div class='flex page content mb-20'>
      <h1>".$ROW_PAGE['name']."</h1>
      <div class='f-15'>
        <span class='strong color-1'>".$ini_arr['lang']['availability']."</span>
      </div>
    </div>
    <div class='bg'>
      <div class='page content flex'>
        ".item_gallery()."
        <div class='item-main-panel'>
          <div class='mb-20 f-15 info oh'>".$lead."</div>
          <div class='buy-container mb-20'>
            ".$price_str."
            <div class='a-center'>
              <button class='button-buy button-1'>".$ini_arr['lang']['buy']."</button>
            </div>
          </div>
          <div class='flex f-15'>
            <div class='light'>
              <h4>".$ini_arr['lang']['delivery']."</h4>
              <ul>
                <li>".$ini_arr['lang']['in-kherson']."</li>
                <li>".$ini_arr['lang']['in-ukraine']."</li>
              </ul>
            </div>
            <div class='light'>
              <h4>".$ini_arr['lang']['payment']."</h4>
              <ul>
                <li>".$ini_arr['lang']['cash']."</li>
                <li>".$ini_arr['lang']['cashless']."</li>
                <li>".$ini_arr['lang']['upon-receipt']."</li>
              </ul>
            </div>
            <div class='light'>
              <h4>".$ini_arr['lang']['share']."</h4>
              <div class='share-soc'>
                <a href='#' title='Facebook'><span class='fa fa-facebook'></span></a>
                <a href='#' title='Instagram'><span class='fa fa-instagram'></span></a>
                <a href='#' title='Twitter'><span class='fa fa-twitter-bird'></span></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <script>
    var curPrice = ".$cur_price.",
        curPriceId = ".$cur_price_id.";
    $('.button-buy').click(function() {
        $('#dialog-content').load('".AJAX_SCRIPT."', {
          action: 'add_cart',
          item_id: '".PAGE_ID."',
          item_var_id: curPriceId,
          lang: '".LANG."'
        });

        $('#over-container').show();
        return false;
    });
    </script>";

    return $res_str;
}
function item_gallery() {
    global $ini_arr, $ROW_PAGE, $content_arr;

    $res_str = "";

    $param = array('id' => PAGE_ID);
    $query = "SELECT img, prop_arr
              FROM ".prdb."gallery
              WHERE page_id = :id 
                AND type = 'img'
                AND LENGTH(img) > 0
              ORDER BY p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr)) return;

    $content_arr['HEAD'] .= '
        <script src="[TPL_DIR]js/unitegallery.min.js"></script>
        <link href="[TPL_DIR]css/unite-gallery.css" rel="stylesheet" type="text/css" />
        <script src="[TPL_DIR]js/ug-theme-compact.js"></script>
    ';
    $content_arr['ONLOAD'] .= '
        jQuery(".gallery").unitegallery({
            gallery_theme: "compact",
            gallery_height:530,
            theme_panel_position: "right",
            theme_hide_panel_under_width: 480, // ширина окна, при которой скрыть превьюшки
            gallery_min_width: 400, // gallery minimal width when resizing
            gallery_min_height: 300, // gallery minimal height when resizing
            gallery_background_color: "rgb(255, 255, 255)",
            slider_transition: "fade",
            slider_enable_play_button: false,
            slider_enable_text_panel: false,
            slider_textpanel_always_on: false,
            thumb_width: 140,
            thumb_height: 110,
            thumb_fixed_size: false,
            strippanel_enable_buttons: false,
            strippanel_enable_handle: false,
            strippanel_background_color: "#F6F6F8",
            strippanel_padding_top: 0,
            strippanel_padding_bottom: 0
        });
    ';

    $res_str .= "
    <div class='igallery-container rel'>
      <div class='gallery' id='gallery'>
    ";
    foreach($row_arr as $row) {
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
        $name = empty($prop_arr['name'][LANG]) ? "" : $prop_arr['name'][LANG];
        if(!empty($row['img']) and file_exists(GALLERY_DIR."small/".$row['img']) and file_exists(GALLERY_DIR."big/".$row['img'])) {
            $res_str .= "<img alt=''
              src='".GALLERY_DIR."small/".$row['img']."'
              data-image='".GALLERY_DIR."big/".$row['img']."'
              data-description='".$name."'
            >";
        }
    }
    $res_str .= "</div>
    </div>";

    return $res_str;
}
function prices_tbl() {
    global $ini_arr, $ROW_PAGE, $content_arr;
    $res_str = "";
    
    $size0_arr = array();
    $size1_arr = array();
    foreach($ini_arr['prices'] as $var_id => $row) {
        $arr = explode(' x ', $row['name']);
        if(sizeof($arr) < 2) continue;
        
        $size_0 = (int)$arr[0];
        $size_1 = (int)$arr[1];
        
        $item_size[$size_0][$size_1] = $var_id;
        
        if(!in_array($size_0, $size0_arr))
            $size0_arr[] = $size_0;
        if(!in_array($size_1, $size1_arr))
            $size1_arr[] = $size_1;
    }

    $res_str .= "
    <div class='bg oh'>
      <div class='page content mb-50'>
        <h2>Ціни (грн)</h2>
        <table class='listing w-100'>
        <tr>
          <th>&nbsp;</th>";
    foreach($size0_arr as $value)
        $res_str .= "<th>".$value."</th>";
    $res_str .= "
        </tr>";
    foreach($size1_arr as $size1) {
        $res_str .= "<tr>
          <th>".$size1."</th>";
        foreach($size0_arr as $size0) {
            $res_str .= "<td class='a-center'>";
            if(isset($item_size[$size0][$size1]) and isset($ini_arr['prices'][$item_size[$size0][$size1]])) {
                $row = $ini_arr['prices'][$item_size[$size0][$size1]];
                if($row['price1'] > 0) {
                    $res_str .= "
                    <div class='text-red price'>".$row['price']."</div>
                    <div class='price-old'>".$row['price1']."</div>
                    ";
                }
                else
                    $res_str .= $row['price'];
            }
            else
                $res_str .= "&nbsp;";
            $res_str .= "</td>";
        }
        $res_str .= "</tr>";
    }
    $res_str .= "
        </table>
      </div>
    </div>";
    
    return $res_str;
}
function list_one_item($row) {
    global $ini_arr;
    $res_str = "";
    
    $img = USER_TEMPLATE_DIR."images/empty.jpg";
    $main_img = empty($row['adv_setting']['item_img']) ? "" : IMAGE_PAGE_DIR.$row['type']."/".$row['adv_setting']['item_img'];
    if(!empty($main_img) and file_exists($main_img))
        $img = $main_img;
        
    if($row['price'] > 0) {
        if($row['price1'] > 0)
            $price = "<div class='flex m-center'>
              <div class='text-red'><span class='price'>".$row['price']."</span> ".$ini_arr['lang']['currency']." </div>
              <div class='price-old'>".$row['price1']." ".$ini_arr['lang']['currency']."</div>
            </div>";
        else
            $price = "<span class='price'>".$row['price']."</span> ".$ini_arr['lang']['currency'];
    }
    else
        $price = "&nbsp;";
    
    $is_action = $row['is_action'] ? "<span class='badge action' data-text='".$ini_arr['lang']['item-action']."'></span>" : "";
    $is_hit = $row['is_hit'] ? "<span class='badge hit' data-text='".$ini_arr['lang']['item-hit']."'></span>" : "";
    $is_new = $row['is_new'] ? "<span class='badge new' data-text='".$ini_arr['lang']['item-new']."'></span>" : "";
    
    $title = $row['name'];
    if(!empty($row['content']))
        $title = "<a href='".$ini_arr['pr_lang'].$row['alias'].".htm'>".$row['name']."</a>";
    
    $res_str .= "<li>
        <div class='img rel' style='background-image: url(".$img.")'>
          <div class='badge-container'>".$is_action.$is_hit.$is_new."</div>
        </div>
        <div class='title'>".$title."</div>
        <div class='price-container'>".$price."</div>
        <div class='buy-container'>
          <div class='a-center'>
            <button class='button button-buy button-2' data-id='".$row['id']."'>".$ini_arr['lang']['buy']."</button>
          </div>
        </div>
    </li>";
    
    return $res_str;
}
function make_date_input($name_field, $selected_date, $error = false, $year_start = 0, $year_end = 0) {
    global $ini_arr;
    $res_str = "";
    if(!isset($ini_arr['month']))
        $ini_arr['month'] = get_vars('', 'month', 'uid');
        
    $year_start = $year_start ? $year_start : date('Y') - 80;
    $year_end = $year_end ? $year_end : date('Y');
    
    $arr = date_parse_from_format('Y-m-d', $selected_date);
    if($arr['error_count'])
        $arr = date_parse_from_format('d.m.Y', $selected_date);
    $year = empty($arr['year']) ? 0 : $arr['year'];
    $month = empty($arr['month']) ? 0 : $arr['month'];
    $day = empty($arr['day']) ? 0 : $arr['day'];
    
    $res_str .= "<div class='select-container day'>
        <select name='".$name_field."[day]' size='1' class='".($error ? "error" : "")."'>
          <option value='0'>day</option>";
    for($d = 1; $d <= 31; $d++) {
        $res_str .= "<option value='".$d."' ".($d == $day ? "selected" : "").">".$d."</option>";
    }
    $res_str .= "</select>
          <div class='select-button fa fa-angle-down'></div>
        </div><div class='select-container month'>
        <select name='".$name_field."[month]' size='1' class='".($error ? "error" : "")."'>
          <option value='0'>month</option>";
    for($m = 1; $m <= 12; $m++) {
        $res_str .= "<option value='".$m."' ".($m == $month ? "selected" : "").">".$ini_arr['month'][$m]."</option>";
    }
    $res_str .= "</select>
          <div class='select-button fa fa-angle-down'></div>
        </div><div class='select-container year'>
        <select name='".$name_field."[year]' size='1' class='".($error ? "error" : "")."'>
          <option value='0'>year</option>";
    for($y = $year_start; $y <= $year_end; $y++) {
        $res_str .= "<option value='".$y."' ".($y == $year ? "selected" : "").">".$y."</option>";
    }
    $res_str .= "</select>
          <div class='select-button fa fa-angle-down'></div>
        </div>
    ";
    
    return $res_str;
}
function make_g_map() {
    global $ini_arr, $content_arr;
    $res_str = "";

    $param = array('page_id' => PAGE_ID);
    $query = "SELECT id, gm_ll, prop_arr FROM ".prdb."gmap WHERE page_id = :page_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr))
        return $res_str;
    
    $content_arr['HEAD'] .= '
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key='.GOOGLE_KEY.'"></script>';
    $content_arr['ONLOAD'] .= '
        var initLatLng, myLatLng, myOptions, map;
        
        initLatLng = new google.maps.LatLng(46.65356036161195, 32.60244369506836);

        myOptions = {
          zoom: 12,
          center: initLatLng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(document.getElementById("gmap"), myOptions);
    ';
    $counter = 1;
    foreach($row_arr as $row) {
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
        $addr = isset($prop_arr['addr'][LANG]) ? $prop_arr['addr'][LANG] : "";
        $note = isset($prop_arr['note'][LANG]) ? $prop_arr['note'][LANG] : "";
        $win_str = "";
        if(!empty($addr))
            $win_str .= "<div class='title'>".$addr."</div>";
        if(!empty($note))
            $win_str .= "<div class='note'>".nl2br($note)."</div>";
        if(!empty($win_str))
            $win_str = make_js_str("<div class='gmWin'>".$win_str."</div>");
        
        $content_arr['ONLOAD'] .= '
        myLatlng = new google.maps.LatLng('.$row['gm_ll'].');

        var marker'.$counter.' = new google.maps.Marker({
            position: myLatlng,
            map: map
        });
        var infowindow'.$counter.' = new google.maps.InfoWindow({
            content: "'.$win_str.'"
        });
        marker'.$counter.'.addListener("click", function() {
            infowindow'.$counter.'.open(map, marker'.$counter.');
        });
        ';
        
        $counter++;
    }

    $res_str .= "<div id='gmap' class='gmap' name='#gmap'></div>
    ";
    
    return $res_str;
}
function make_price_arr($page_id) {
    global $ini_arr;
    $res_arr = array();
    
    $param = array('page_id' => $page_id);
    $query = "SELECT ".prdb."prices.var_id, ".prdb."prices.price, ".prdb."prices.price1,
                ".prdb."vars.".LANG." AS name
              FROM ".prdb."prices, ".prdb."vars
              WHERE ".prdb."prices.var_id = ".prdb."vars.id
                AND ".prdb."prices.page_id = :page_id AND ".prdb."prices.price > 0
                AND ".prdb."prices.var_id > 0
              ORDER BY ".prdb."vars.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    foreach($row_arr as $row) {
        $res_arr[$row['var_id']] = array(
            'name' => $row['name'],
            'price' => $row['price'],
            'price1' => $row['price1']
        );
    }
    
    return $res_arr;
}
function cart_list_items($last_item_key = 0) {
    global $ini_arr;
    $res_str = "<h2>".$ini_arr['lang']['cart']."</h2>";
    $empty_str = $res_str."<div class='alert' style='margin-top: 30px'>".$ini_arr['lang']['cart-empty']."</div>
    <script>
      cpSum.text('');
      cpNum.text('').hide();
      cpCur.text('".$ini_arr['lang']['empty']."');
    </script>";
    
    $arr = make_cart_list_items($last_item_key);
    $item_arr = $arr['items'];
    $var_arr = $arr['vars'];
    
    if(!sizeof($item_arr)) return $empty_str;
    
    $res_str .= "
    <ul class='cart-list'>";
    foreach($item_arr as $key => $row) {
        $item_key = $row['id'].'.'.$row['var_id'];
        
        if(!empty($row['page_ini_arr'])) {
            $arr = unserialize($row['page_ini_arr']);
            $row = array_merge($row, $arr);
        }
        
        if(isset($var_arr[$row['var_id']]))
            $row['var_name'] = "<br>".$ini_arr['lang']['size']." ".$var_arr[$row['var_id']];
        else
            $row['var_name'] = "";
        $row['num'] = $_SESSION['sess_cart'][$item_key];
        
        $img = USER_TEMPLATE_DIR."images/empty.jpg";
        $main_img = empty($row['adv_setting']['item_img']) ? "" : IMAGE_PAGE_DIR.$row['type']."/".$row['adv_setting']['item_img'];
        if(!empty($main_img) and file_exists($main_img))
            $img = $main_img;

        $res_str .= "<li class='flex'>
          <div class='img' style='background-image: url(".$img.")'></div>
          <div class='info'>
            ".$row['name']."
            ".$row['var_name']."
            <div class='price-container'><span class='price'>".$row['price']."</span> ".$ini_arr['lang']['currency']."</div>
          </div>
          <div class='num-control'>
            <a href='#' class='btn-sign btn-minus'
              data-item_id='".$row['id']."'
              data-item_var_id='".$row['var_id']."'
              data-price='".$row['price']."'
              data-sign='minus'
            ><span class='fa fa-minus'></span></a>
            <input type='text' value='".$row['num']."' id='num-".$row['id'].'-'.$row['var_id']."' readonly>
            <a href='#' class='btn-sign btn-plus'
              data-item_id='".$row['id']."'
              data-item_var_id='".$row['var_id']."'
              data-price='".$row['price']."'
              data-sign='plus'
            ><span class='fa fa-plus'></span></a>
          </div>
          <div class='row-sum'>
            <div class='mb-10'>".$ini_arr['lang']['sum']."</div>
            <div class='price-container'><span class='price' id='sum-".$row['id'].'-'.$row['var_id']."'>".number_format($row['price'] * $row['num'], 2, '.', '')."</span> ".$ini_arr['lang']['currency']."</div>
          </div>
          
          <a href='#' class='del-item' data-item_id='".$row['id']."' data-item_var_id='".$row['var_id']."' title='".$ini_arr['lang']['delete']."'><span class='fa fa-cancel'></span></a>
        </li>";
    }
    $res_str .= "</ul>
    <div class='flex cart-footer'>
      <div>
        <a class='button button-3' href='#' onclick='
            $(\"#dialog-content\").empty();
            $(\"#over-container\").hide();
           return false;
        '>".$ini_arr['lang']['continue-shopping']."</a>
      </div>
      <div class='cart-total'>
        <div class='mb-10 a-center'>
          ".$ini_arr['lang']['total'].": <span class='price' id='cartTotal'>".number_format($_SESSION['sess_cart_sum'], 2, '.', '')."</span> ".$ini_arr['lang']['currency']."
        </div>
        <a class='button button-1' href='".$ini_arr['pr_lang']."cart.htm'>".$ini_arr['lang']['checkout']."</a>
      </div>
    </div>
    
    <script>
      cpSum.text('".number_format($_SESSION['sess_cart_sum'], 2, '.', '')."');
      cpNum.text(".$_SESSION['sess_cart_num'].").show();
      cpCur.text('".$ini_arr['lang']['currency']."');
      cartNum = ".$_SESSION['sess_cart_num'].";
      
      $('.del-item').click(function() {
        var data = $(this).data();
        $('#dialog-content').load('".AJAX_SCRIPT."', {
          action: 'del_cart',
          item_id: data.item_id,
          item_var_id: data.item_var_id,
          lang: '".LANG."'
        });
        
        return false;
      });
      
      $('.btn-sign').click(function() {
        var data = $(this).data(),
            item_key = data.item_id + '-' + data.item_var_id,
            numInput = $('#num-' + item_key),
            num = numInput.val(),
            sumDiv = $('#sum-' + item_key),
            sum = sumDiv.text(),
            totalDiv = $('#cartTotal')
            total = totalDiv.text();
            
        if(data.sign == 'minus' && num == 1)
            return false;

        if(data.sign == 'minus') {
            numInput.val(num - 1);
            cartNum--;
            cpNum.text(cartNum);
            sumDiv.text(formatter.format(sum - data.price));
            totalDiv.text(formatter.format(total - data.price));
            cpSum.text(formatter.format(total - data.price));
        }
        else {
            numInput.val(1 * num + 1);
            cartNum++;
            cpNum.text(cartNum);
            sumDiv.text(formatter.format(1 * sum + data.price * 1));
            totalDiv.text(formatter.format(1 * total + data.price * 1));
            cpSum.text(formatter.format(1 * total + data.price * 1));
        }
        
        $.post('".AJAX_SCRIPT."', {
          action: 'sign_cart',
          sign: data.sign,
          item_id: data.item_id,
          item_var_id: data.item_var_id,
          lang: '".LANG."'
        });
        
        return false;
      });
      
    </script>";


//$res_str .= "<pre>".print_r($item_arr, true)."</pre>";    
    return $res_str;
}
function make_cart_list_items($last_item_key = 0) {
    global $ini_arr;
    $res_arr = array(
        'items' => array(),
        'vars' => array()
    );

    if(!isset($_SESSION['sess_cart']) or !sizeof($_SESSION['sess_cart']) or !isset($_SESSION['sess_cart_prices']))
        return $res_arr;

    $query_where = "";
    $param = array();
    foreach($_SESSION['sess_cart'] as $item_key => $num) {
        if(empty($_SESSION['sess_cart_prices'][$item_key])) {
            unset($_SESSION['sess_cart'][$item_key]);
            continue;
        }
        
        $arr = explode('.', $item_key);
        $item_id = $arr[0];
        $item_var_id = isset($arr[1]) ? $arr[1] : 0;
        $param['item_id_'.$item_id] = $item_id;
        $param['var_id_'.$item_var_id] = $item_var_id;
        $query_where .= " ".prdb."prices.page_id = :item_id_".$item_id." AND ".prdb."prices.var_id = :var_id_".$item_var_id." OR ";
    }
    $query_where = " AND (".substr($query_where, 0, -3).") ";
    
    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, ".prdb."menu.type, ".prdb."pages.name, ".prdb."menu.page_ini_arr,
                ".prdb."prices.var_id
              FROM ".prdb."menu, ".prdb."pages, ".prdb."prices
              WHERE ".prdb."menu.id = ".prdb."pages.id AND ".prdb."menu.id = ".prdb."prices.page_id
                ".$query_where;
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr))
        return $res_arr;
    
    $query_where = "";
    $param = array();
    $counter = 0;
    $item_arr = array();
    foreach($row_arr as $row) {
        $row['price'] = $_SESSION['sess_cart_prices'][$row['id'].'.'.$row['var_id']];

        if($row['id'].'.'.$row['var_id'] == $last_item_key) {
            array_unshift($item_arr, $row);
        }
        else
            $item_arr[$counter] = $row;

        $param['var_id_'.$row['var_id']] = $row['var_id'];
        $query_where .= " ".prdb."vars.id = :var_id_".$row['var_id']." OR ";

        $counter++;
    }
    $query_where = " ".substr($query_where, 0, -3)." ";
    $query = "SELECT ".prdb."vars.id, ".prdb."vars.".LANG."
              FROM ".prdb."vars
              WHERE ".$query_where;
    $var_arr = db_get_arr($query, $param, 'pair');

    $res_arr = array(
        'items' => $item_arr,
        'vars' => $var_arr
    );

    return $res_arr;
}
function one_news_list($row, $news_char_num) {
    global $ini_arr;

    $res_str = "";

    $img = "[IMAGE_DIR]empty.png";
    if(!empty($row['prop_arr']['adv_setting']['main_img']) and file_exists(IMAGE_PAGE_DIR."news_1/".$row['prop_arr']['adv_setting']['main_img']))
        $img = IMAGE_PAGE_DIR."news_1/".$row['prop_arr']['adv_setting']['main_img'];
    else {
        preg_match("/<img [^>]*src=[\'\"]?([^\'\"]*)[\'\"]?[^>]*>/", $row['content'], $matches);
        if(isset($matches[1])) {
            $img = $matches[1];
        }
    }
    
    if(!empty($row['prop_arr']['adv_setting']['lead']))
        $lead = substr_space(nl2br($row['prop_arr']['adv_setting']['lead']), $news_char_num, " ...");
    else
        $lead = substr_space(strip_tags($row['content']), $news_char_num, " ...");
    
    $res_str .= "<li>
      <a class='main-a' href='".$ini_arr['pr_lang'].$row['alias'].".htm".(PS ? "?ps=".PS : "")."'>
        <div class='img rel' style='background-image: url(".$img.")'>
        </div>
        <div class='text'>
          <h2>".$row['name']."</h2>
          <div class='date'>".(str_replace(" 00:00", "", $row['f_date_in']))."</div>
          <div class='lead'>".$lead."</div>
        </div>
      </a>
      <a class='more' href='".$ini_arr['pr_lang'].$row['alias'].".htm".(PS ? "?ps=".PS : "")."'>
        ".$ini_arr['lang']['more']."<span class='fa fa-angle-double-right'></span>
      </a>
    </li>";

    return $res_str;
}
?>