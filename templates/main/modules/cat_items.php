<?php
$arr = get_filters_fields($ROW_PAGE['type']);
$ini_arr['filters_field'] = $arr['arr'];
$ini_arr['filters_list'] = $arr['id'];

$res_str = list_items();

// ----------------------------- function
function list_items() {
    global $ini_arr, $content_arr;
    $res_str = "";
    
// ------- make session s-form
    if(isset($_SESSION['s-page_id']) and $_SESSION['s-page_id'] <> PAGE_ID) {
        unset($_SESSION['s-page_id']);
        if(isset($_SESSION['s-form']))
            unset($_SESSION['s-form']);
        if(isset($_SESSION['items_set']))
            unset($_SESSION['items_set']);
        $_SESSION['items_sort'] = $ini_arr['sort_items_default'];
    }
    
    $_SESSION['s-page_id'] = PAGE_ID;
    
    if(isset($_REQUEST['set'])) {
        if(isset($_SESSION['s-form']))
            unset($_SESSION['s-form']);
        $_SESSION['items_sort'] = $ini_arr['sort_items_default'];

        if($_REQUEST['set'] == 'action' or $_REQUEST['set'] == 'hit' or $_REQUEST['set'] == 'new')
            $_SESSION['items_set'] = $_REQUEST['set'];
        elseif(isset($_SESSION['items_set']))
            unset($_SESSION['items_set']);
    }
    elseif(isset($_REQUEST['sort']) and isset($ini_arr['sort_items'][$_REQUEST['sort']])) {
        $_SESSION['items_sort'] = $_REQUEST['sort'];
    }
    elseif(isset($_REQUEST)) {
        if(isset($_REQUEST['min']) and (int)$_REQUEST['min'] > 0) {
            if(isset($_SESSION['s-form']))
                unset($_SESSION['s-form']);

            $_SESSION['s-form']['min'] = (int)$_REQUEST['min'];
        }
        if(isset($_REQUEST['max']) and (int)$_REQUEST['max'] > 0)
            $_SESSION['s-form']['max'] = (int)$_REQUEST['max'];
            
        foreach($_REQUEST as $key => $value) {
            $var_id = (int)str_replace('fltr-', '', $key);
            if(isset($ini_arr['filters_list'][$var_id]))
                $_SESSION['s-form']['vars'][$var_id] = $ini_arr['filters_list'][$var_id];
        }
    }
    
// ------- make query_where
    $query_where = "";
    
    $param = array(
        'parent_id' => PAGE_ID,
        'lang' => LANG
    );
    if(isset($_SESSION['items_set'])) {
        $query_where .= " AND ".prdb."menu.is_".$_SESSION['items_set']." ";
    }
    if(isset($_SESSION['s-form']['vars'])) {
        foreach($ini_arr['filters_field'] as $part => $arr) {
            $query_where_vars = "";
            foreach($arr['list_uid'] as $var_id => $var_name) {
                if(isset($_SESSION['s-form']['vars'][$var_id]))
                    $query_where_vars .= " FIND_IN_SET('".$var_id."', ".prdb."menu.vars) OR ";
            }
            if(!empty($query_where_vars))
                $query_where .= " AND (".substr($query_where_vars, 0, -3).") ";
        }
    }
    if(!empty($_SESSION['s-form']['min']) or !empty($_SESSION['s-form']['max'])) {
        if(!empty($_SESSION['s-form']['min'])) {
            $param['min'] = $_SESSION['s-form']['min'];
            $query_where .= " AND ".prdb."prices.price >= :min ";
        }
        if(!empty($_SESSION['s-form']['max'])) {
            $param['max'] = $_SESSION['s-form']['max'];
            $query_where .= " AND ".prdb."prices.price <= :max ";
        }
    }
    
    $query = "SELECT DISTINCT
                ".prdb."menu.id, ".prdb."menu.alias, ".prdb."menu.type, ".prdb."menu.price, ".prdb."menu.price1,
                ".prdb."menu.is_action, ".prdb."menu.is_new, ".prdb."menu.is_hit, ".prdb."menu.gallery, ".prdb."menu.comment,
                ".prdb."menu.user_only, ".prdb."pages.name, ".prdb."pages.content, ".prdb."menu.page_ini_arr
              FROM ".prdb."menu, ".prdb."pages, ".prdb."menu_pages, ".prdb."prices
              WHERE ".prdb."menu.id = ".prdb."pages.id AND ".prdb."menu.id = ".prdb."menu_pages.id
                AND ".prdb."menu.id = ".prdb."prices.page_id 
                AND ".prdb."menu_pages.parent_id = :parent_id
                AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.in_menu
                ".$query_where."
              ORDER BY ";
    $sort = isset($_SESSION['items_sort']) ? $_SESSION['items_sort'] : $ini_arr['sort_items_default'];
    switch ($sort) {
        case 'rating':
            $query .= prdb."menu_pages.p_id";
            break;
        case 'price-desc':
            $query .= prdb."menu.price DESC";
            break;
        case 'price-asc':
            $query .= prdb."menu.price";
            break;
    }
    
    $row_arr = db_get_arr($query, $param, 'arr');
    $num_all = sizeof($row_arr);
    
    $res_str .= "
    <div class='bg mb-50'>
      <div class='page oh' style='padding: 20px 0'>
        ".filters_panel()."
        <div class='main-column catalog'>
          ".make_sort_panel();
    if($num_all) {
        $res_str .= "<ul class='item-list'>";
        foreach($row_arr as $row) {
            if($ini_arr['is_user_page'] and $row['user_only'] and !SESS_ID)
                continue;

            $arr = empty($row['page_ini_arr']) ? array() : unserialize($row['page_ini_arr']);
            $row = array_merge($row, $arr);
            
            $res_str .= list_one_item($row);
        }
        $res_str .= "</ul>
        <script>

        $('.one-item').click(function() {
            var data = $(this).data();
            $('#dialog-content').load('".AJAX_SCRIPT."', {
              action: 'dialog_item',
              item_id: data.id,
              lang: '[LANG]'
            });
    
            $('#over-container').show();
            return false;
        });
        </script>";
    }
    else
        $res_str .= "<div class='alert'>".$ini_arr['lang']['no-result']."</div>";
    $res_str .= "
        </div>
      </div>
    </div>";
    
    return $res_str;
}
?>