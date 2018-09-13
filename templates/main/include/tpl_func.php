<?php
function make_content($text) {
    global $ini_arr, $content_arr, $ROW_PAGE, $TREE_ARR;
    $res_str = "";
    
    if(ALIAS == 'home')
        $res_str = $text;
    elseif(ALIAS == 'sellers') {
        $res_str = $text."[COMMENT_LIST]";
    }
    elseif($ROW_PAGE['type'] == 'seller' or $ROW_PAGE['type'] == 'shop')
        $res_str = $text;
    elseif(empty($text))
        $res_str = "<div class='page' style='padding-bottom: 30px;'><div class='content'><div class='alertText'>".$ini_arr['lang']['under-constr']."</div></div></div>";
    else {
        $res_str = '<div class="page" style="padding-bottom: 30px;"><div class="content">'.$text.'</div></div>[COMMENT_LIST]';
        if(!empty($ROW_PAGE['ini_arr']['adv_setting']['seo']))
            $res_str .= "<div class='mb-30'><div class='addition scroll-panel oa'>
                ".$ROW_PAGE['ini_arr']['adv_setting']['seo']."</div></div>";
    }
    
    return $res_str;
}
function make_main_menu() {
    global $ini_arr, $content_arr;
    $res_str = '';
    $content_arr['BOTTOM_MENU'] = '';

    $param = array('lang' => LANG);
    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, ".prdb."menu.user_only, ".prdb."pages.name, ".prdb."menu_pages.p_id
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id
                AND ".prdb."menu_pages.parent_id = 0 AND ".prdb."menu.id = ".prdb."pages.id
                AND ".prdb."menu.in_menu = '1' AND ".prdb."pages.lang = :lang
              ORDER BY ".prdb."menu_pages.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    $num_all = sizeof($row_arr);
    if(!$num_all) return "&nbsp;";

    $n = 1;
    $res_str .= "<ul class='nav main-menu'>";
    $content_arr['BOTTOM_MENU'] .= "<ul class='nav bottom-menu'>";
    foreach($row_arr as $row) {
        if($n == 1)
            $class = 'first';
        elseif($n == $num_all)
            $class = 'last';
        else
            $class = '';

        if(PAGE_ID == 1 and $row['id'] == 1 or $ini_arr['is_user_page'] and $row['user_only'] and !SESS_ID)
            continue;
        
        if($row['p_id'] == 1 and LANG == LANGUAGE_DEFAULT)
            $url = ".";
        else
            $url = $ini_arr['pr_lang'].$row['alias'].".htm";
        
        $res_str .= "<li class='".$class."'>
            <a href='".$url."' ".($row['alias'] == $GLOBALS['TREE_ARR'][0]['alias'] ? "class='mm-current'" : "").">
                ".$row['name']."
            </a>
        </li>";

        $content_arr['BOTTOM_MENU'] .= "<li class='".$class."'>
            <a href='".$url."'>".$row['name']."</a>
        </li>";

        $n++;
    }
    $res_str .= "</ul>";
    $content_arr['BOTTOM_MENU'] .= "</ul>";

    return $res_str;
}
function make_sub_menu($cur_id) {
    global $ini_arr, $ROW_PAGE, $TREE_ARR;
    $res_str = "";

    $sta = sizeof($TREE_ARR);

    $param = array('parent_id' => $cur_id, 'lang' => LANG);
    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, ".prdb."menu.user_only, ".prdb."pages.name
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id
                AND ".prdb."menu_pages.parent_id = :parent_id
                AND ".prdb."menu.in_menu = '1' AND ".prdb."pages.lang = :lang
                AND ".prdb."menu.type <> 'news_1'
              ORDER BY ".prdb."menu_pages.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    $num_all = sizeof($row_arr);
    if(!$num_all)
        return;

    $counter = 1;
    $res_str .= "<ul class='nav sub-menu'>";
    foreach($row_arr as $row) {
        if($ini_arr['is_user_page'] and $row['user_only'] and !SESS_ID)
            continue;

        $url = $ini_arr['pr_lang'].$row['alias'].".htm";

        $res_str .= "<li class='".($counter == $num_all ? "last" : "")."'><a href='".$url."' class='inv ".($row['alias'] == ALIAS ? "sm-current" : "")."'>
          ".$row['name']."
        </a></li>";
        
        $counter++;
    }
    $res_str .= "</ul>";

    return $res_str;
}
function make_list_menu($list_id) {
    global $ini_arr;
    $res_str = "";

    $param = array('parent_id' => $list_id, 'lang' => LANG);
    $query = "SELECT ".prdb."menu.id, ".prdb."menu.alias, ".prdb."menu.user_only, ".prdb."pages.name
              FROM ".prdb."menu, ".prdb."menu_pages, ".prdb."pages
              WHERE ".prdb."menu.id = ".prdb."menu_pages.id AND ".prdb."menu.id = ".prdb."pages.id
                AND ".prdb."menu_pages.parent_id = :parent_id
                AND ".prdb."menu.in_menu = '1' AND ".prdb."pages.lang = :lang
              ORDER BY ".prdb."menu_pages.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    $num_all = sizeof($row_arr);
    if(!$num_all)
        return;

    $counter = 1;
    $res_str .= "<ul class='nav sub-menu'>";
    foreach($row_arr as $row) {
        if($ini_arr['is_user_page'] and $row['user_only'] and !SESS_ID)
            continue;

        $url = $ini_arr['pr_lang'].$row['alias'].".htm";

        $res_str .= "<li class='".($counter == $num_all ? "last" : "")."'><a href='".$url."' class='inv ".($row['alias'] == ALIAS ? "sm-current" : "")."'>
          ".$row['name']."
        </a></li>";
        
        $counter++;
    }
    $res_str .= "</ul>";

    return $res_str;
}
function make_catalog_menu() {
    global $ini_arr;
    $res_str = "";
    
    $res_str = "
      <div class='cat-menu'>
        <ul class='nav'>";
    foreach($ini_arr['cat_part'] as $key => $value) {
        $class_cur = "";
        if(isset($_REQUEST['cat']) and $_REQUEST['cat'] == $key)
            $class_cur = "cur";
        $res_str .= "<li>
          <a href='".$ini_arr['pr_lang']."sellers-".$key.".htm' class='".$class_cur."'>".$value."
            <span class='fa fa-".$key."'></span>
          </a>
        </li>";
    }
    $res_str .= "</ul>
    </div>";
    
    return $res_str;
}
function make_catalog_firm_btn() {
    global $ini_arr;
    $res_str = "";
    
    $flag_sellers = (ALIAS == 'sellers' and empty($_REQUEST['part'])) ? true : false;
    
    $res_str .= "<div class='catalog_btn_container'>
      <a href='#'>Разделы каталога</a>
      ".($flag_sellers ? "" : "<div class='cat-menu-container'>".make_catalog_menu()."</div>")."
    </div>

    <script>
      $('.catalog_btn_container > a').click(function() {
        ".($flag_sellers ? "" : " $('.cat-menu-container').toggle(); ")."
        return false;
      })
    </script>";
    
    return $res_str;
}
function view_branch($parent_id) {
    global $ini_arr, $TREE_ARR;

    $sta = sizeof($TREE_ARR);
    if(ALIAS == 'home' or !$sta) return;

    $res_str = "";

    for($i = 0; $i < $sta - 1; $i++) {
        if($TREE_ARR[$i]['alias'] == 'service-pages' or $TREE_ARR[$i]['alias'] == 'top-menu') continue;
        $res_str .= "<a href='".$ini_arr['pr_lang'].$TREE_ARR[$i]['alias'].".htm'>
          ".$TREE_ARR[$i]['name']."
        </a><span class='fa fa-right-corner'></span>";
    }

    if($TREE_ARR[$sta - 1]['alias'] == 'sellers' and isset($_REQUEST['part']) and isset($ini_arr['cat_part'][$_REQUEST['part']])) {
        $res_str .= "<a href='".$ini_arr['pr_lang'].$TREE_ARR[$sta - 1]['alias'].".htm'>
          ".$TREE_ARR[$i]['name']."
        </a><span class='fa fa-right-corner'></span>".$ini_arr['cat_part'][$_REQUEST['part']];
    }
    else
        $res_str .= $TREE_ARR[$sta - 1]['name'];

    if(!empty($res_str))
        $res_str = "<div class='branchLine'><a href='.' class='fa fa-home'></a><span class='fa fa-right-corner'></span>".$res_str."</div>";

    return $res_str;
}
function make_search_form() {
    global $ini_arr;
    
    $search_query = empty($_SESSION['search_query']) ? "" : $_SESSION['search_query'];
    
    $res_str = "<div class='search-panel'>
      <form action='".$ini_arr['pr_lang']."search.htm' method='get'>
        <input name='search_query' type='text' value='".htmlspecialchars($search_query, ENT_QUOTES)."'
        ><button type='submit'><span class='fa fa-search'></span></button>
      </form>
    </div>";
    
    return $res_str;
}
function make_phones_bar() {
    global $ini_arr;
    $res_str = "<div class='phones'>
      <span class='fa fa-mobile'>
      </span><ul>
        <li>(050) 868-1804</li>
        <li>(096) 819-7134</li>
      </ul>
    </div>";
    
    return $res_str;
}
function make_cart_panel() {
    global $ini_arr;
    $res_str = "";
    $sum = isset($_SESSION['sess_cart_sum']) ? $_SESSION['sess_cart_sum'] : 0;
    $num = isset($_SESSION['sess_cart_num']) ? $_SESSION['sess_cart_num'] : 0;
    
    $num_str = "<div class='cpNum num ".($num ? "" : "hidden")."'>".$num."</div>";
    
    if($sum > 0)
        $sum_str = "<div class='sum'><span class='cpSum'>".number_format($sum, 2, '.', '')."</span> <span class='curr cpCur'>".$ini_arr['lang']['currency']."</span></div>";
    else
        $sum_str = "<div class='sum'><span class='cpSum'></span> <span class='curr cpCur'>".$ini_arr['lang']['empty']."</span></div>";

    $res_str .= "<a class='cart-panel cartPanel' href='#'>
      <span class='fa fa-cart rel'>
        ".$num_str."
      </span>
      ".$sum_str."
    </a>
    <script>
      cartNum = ".$num.";
    </script>";
    
    return $res_str;
}
function make_user_bar() {
    global $ini_arr;
    $res_str = "";
    
    if(SESS_ID) {
        $res_str .= "<div class='user-bar'>
          <a href='#'><span class='fa fa-login'></span>".$ini_arr['lang']['login']."</a>
        </div>";
    }
    else {
        $res_str .= "<div class='user-bar'>
          <a href='#'><span class='fa fa-login'></span>".$ini_arr['lang']['login']."</a>
        </div>";
    }
    
    return $res_str;
}
function make_admin_panel() {
    global $ini_arr;
    $res_str = "";
    
    if(!SESS_ID) return;
    
    $res_str .= "<div class='admin-panel'>
      I am ".SESS_WHO."<br>
      <a href='/".ADMIN_DIR."' target='_blank'>Admin Panel</a><br>
      <a href='/".ADMIN_DIR."menu.php?action=page_info&page_id=".PAGE_ID."' target='_blank'>Edit</a>
    </div>";
    
    return $res_str;
}
function make_lang_panel() {
    global $ini_arr;
    $res_str = "";
    
    $res_str .= "<ul class='lang-panel'>";
    foreach($ini_arr['lang_if'] as $key => $value) {
        $res_str .= "<li>";
        if($key == LANG)
            $res_str .= "<span class='ico ico-".$key."'></span>";
        else {
            $pr_lang = $key == LANGUAGE_DEFAULT ? "" : $key."-";
            $res_str .= "<a href='".$pr_lang.ALIAS.".htm'><span class='ico ico-".$key."'></span></a>";
        }
        $res_str .= "</li>";
    }
    $res_str .= "</ul>";
    
    return $res_str;
}
function login_form($error = "") {
    global $ini_arr;
    $res_str = "";

    $sex_arr = get_vars('', 'sex', 'uid', LANG);

    $login = isset($_REQUEST['login']) ? htmlspecialchars(trim(strip_tags($_REQUEST['login'])), ENT_QUOTES) : "";
    $sex = (isset($_REQUEST['sex']) and array_key_exists($_REQUEST['sex'], $sex_arr)) ? $_REQUEST['sex'] : "man";
    
      
    $res_str .= '
      <form name="form-login" class="form-login frmLogin" action="include/ajax.php" method="post">';
    if(!empty($error))
        $res_str .= '<div class="alert" id="login-error">'.$error.'</div>';
    $res_str .= '
      <input name="action" type="hidden" value="process_login">
      <input name="lang" type="hidden" value="'.LANG.'">
      <div class="form-row flex">';
    foreach($sex_arr as $key => $value) {
        $res_str .= '
        <div>
          <input name="sex" type="radio" id="radio-'.$key.'" value="'.$key.'" '.($sex == $key ? 'checked' : '').'>
          <label for="radio-'.$key.'" class="for-radio" style="margin-left: 5px">'.$value.'</label>
        </div>';
    }
    $res_str .= '
      </div>
      <div class="form-row">
        <input name="login" type="text" value="'.$login.'" autocomplete="off" placeholder="'.$ini_arr['lang']['user-name'].'/'.$ini_arr['lang']['email'].'">
      </div>
      <div class="form-row">
        <input name="pass" type="password" autocomplete="off" placeholder="'.$ini_arr['lang']['pass'].'">
      </div>
      <div class="form-row flex">
        <div>
          <div class="checkbox">
            <input name="flag_rem" type="checkbox" id="remember_me">
            <div class="check-button ico ico-off" data-id="remember_me"></div>
          </div>
          <label for="remember_me" class="for-radio" style="margin-left: 5px">'.$ini_arr['lang']['remember'].'</label>
        </div>
        <div>
          <a href="'.$ini_arr['pr_lang'].'forgot.htm">'.$ini_arr['lang']['remind'].'</a>
        </div>
      </div>
      <div align="center" class="form-row">
        <input type="submit" value="Sign in" class="button-1 btn-submit">
      </div>
      </form>
      '.enter_social().'

      <script>
        $(\'.btn-submit\').click(function () { 
            var query = $(\'.frmLogin\').serialize();
            $(\'#login-form\').load(\'include/ajax.php\', query);
            $(\'#over-container\').hide();
            $(document).scrollTop(0);
            return false;
        });
      </script>
    ';

    return $res_str;
}
function register_form($error = "") {
    global $ini_arr;
    $res_str = "";

    $sex_arr = get_vars('', 'sex', 'uid', LANG);

    $login = isset($_REQUEST['login']) ? htmlspecialchars(trim(strip_tags($_REQUEST['login'])), ENT_QUOTES) : "";
    $mail = isset($_REQUEST['mail']) ? htmlspecialchars(trim(strip_tags($_REQUEST['mail'])), ENT_QUOTES) : "";
    $sex = (isset($_REQUEST['sex']) and array_key_exists($_REQUEST['sex'], $sex_arr)) ? $_REQUEST['sex'] : "man";
    
    $res_str .= '
      <form name="form-reg" id="form-reg" class="form-login frmReg" action="include/ajax.php" method="post">';
    if(!empty($error))
        $res_str .= '<div class="alert" id="reg-error">'.$error.'</div>';
    $res_str .= '
      <input name="action" type="hidden" value="process_register">
      <input name="lang" type="hidden" value="'.LANG.'">
      <div class="form-row flex">';
    foreach($sex_arr as $key => $value) {
        $res_str .= '
        <div>
          <input name="sex" type="radio" id="reg-radio-'.$key.'" value="'.$key.'" '.($sex == $key ? 'checked' : '').'>
          <label for="reg-radio-'.$key.'" class="for-radio" style="margin-left: 5px">'.$value.'</label>
        </div>';
    }
    $res_str .= '
      </div>
      <div class="form-row">
        <input name="login" type="text" autocomplete="off" value="'.$login.'" placeholder="'.$ini_arr['lang']['user-name'].'">
      </div>
      <div class="form-row">
        <input name="mail" type="text" autocomplete="off" value="'.$mail.'" placeholder="'.$ini_arr['lang']['email'].'">
      </div>
      <div class="form-row">
        <input name="pass" type="password" autocomplete="off" placeholder="'.$ini_arr['lang']['pass'].'">
      </div>
      <div align="center" class="form-row">
        <input type="submit" id="btn-submit-reg" value="'.$ini_arr['lang']['send'].'" class="button-1 btn-submit-reg">
      </div>
      </form>
      '.enter_social().'

      <script>
        $(\'.btn-submit-reg\').click(function () { 
            var query = $(\'.frmReg\').serialize();
            $(\'#reg-form\').load(\'include/ajax.php\', query);
            $(\'#over-container\').hide();
            $(document).scrollTop(0);

            $(\'#login-sel\').removeClass(\'current\');
            $(\'#login-form\').hide();
            $(\'#reg-sel\').addClass(\'current\');
            $(\'#reg-form\').show();
            return false;
        });
        </script>
    ';

    return $res_str;
}
function enter_social() {
    global $ini_arr;
    $res_str = "";
    
    $res_str .= '
      <div class="flex social">
        <div>
          '.$ini_arr['lang']['login-with'].':
        </div>';
    $url = 'https://www.facebook.com/dialog/oauth';
    $param = array(
        'client_id'     => FB_APP_ID,
        'redirect_uri'  => FB_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'public_profile,email'
    );
    $link = $url."?".http_build_query($param);
    $res_str .= '
        <div>
          <a href="'.$link.'" class="ico ico-facebook"></a><a
            href="#" class="ico ico-google-plus"></a><a
            href="#" class="ico ico-instagram"></a>
        </div>
      </div>
    ';
    
    return $res_str;
}
function make_footer() {
    global $ini_arr;
    
    return '
    <footer>
        <div class="page flex">
            <a href="." class="logo-1"></a>
            [BOTTOM_MENU]
            <div>
              <ul class="contact">
                <li><span class="fa fa-mail"></span>manager@delozanami.com</li>
                <li><span class="fa fa-mobile"></span>+38 (073) 166-77-60</li>
              </ul>
              <div class="copyright"><a href="http://webpro.cimis.com.ua" target="_blank">[WORD_copyright]</a> &copy; 2004 - [YEAR]</div>
            </div>
        </div>
    </footer>
    ';
}
function make_dialog_html() {
    global $ini_arr;
    
    return '
<div class="over-container oh over-bg" style="display: none" id="over-container">
    <div class="dialog-container rel" id="dialog-container">
        <div class="dialog" id="dialog">
          <div class="dialog-bg oa">
            <div class="dialog-content" id="dialog-content"></div>
          </div>
        </div>
        <a href="#" class="dialog-close" onclick="
            $(\'#dialog-content\').empty();
            $(\'#over-container\').hide();
           return false;
        "><span class="fa fa-cancel"></span></a>
    </div>
</div>
    ';
}
?>