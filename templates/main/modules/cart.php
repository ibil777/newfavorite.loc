<?php

$ini_arr['delivery'] = get_vars('', 'delivery', 'id');
$ini_arr['delivery_ks'] = get_vars('', 'delivery_ks', 'id');
$ini_arr['payment'] = get_vars('', 'payment', 'id');
$res_str = "";

if(isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    if($action == 'form' and isset($_REQUEST['form']) and is_array($_REQUEST['form'])) {
        $res_str = process_order_form($_REQUEST['form']);
    }
}

if(empty($res_str))
    $res_str = cart();

// -------------------------------- function
function cart($form = array(), $error = array()) {
    global $ini_arr;
    $res_str = "";
    $empty_str = "
    <div class='content page oh' style='padding: 20px 0'>
      <h2>".$ini_arr['lang']['order-form']."</h2>
      <div class='alert' style='margin: 30px 0'>".$ini_arr['lang']['cart-empty']."</div>
    </div>";
    
    if(!isset($_SESSION['sess_cart']) or !sizeof($_SESSION['sess_cart']) or !isset($_SESSION['sess_cart_prices']))
        return $empty_str;

    $res_str .= "
    <div class='bg mb-50'>
      <div class='content page oh' style='padding: 20px 0'>
        <div class='order-list'>
          ".order_list_items()."
        </div>
        <div class='order-form'>
          ".order_form($form, $error)."
        </div>
      </div>
    </div>";
    
    return $res_str;
}
function order_form($form, $error) {
    global $ini_arr;
    $res_str = "";
    
    if(!sizeof($form) and isset($_SESSION['sess_cart_user']) and is_array($_SESSION['sess_cart_user']))
        $form = $_SESSION['sess_cart_user'];
    
    $form['f_name'] = isset($form['f_name']) ? $form['f_name'] : "";
    $form['l_name'] = isset($form['l_name']) ? $form['l_name'] : "";
    $form['mail'] = isset($form['mail']) ? $form['mail'] : "";
    $form['phone'] = isset($form['phone']) ? $form['phone'] : "";
    $form['is_kherson'] = isset($form['is_kherson']) ? $form['is_kherson'] : 1;
    $form['delivery_ks'] = isset($form['delivery_ks']) ? $form['delivery_ks'] : 0;
    $form['addr_ks'] = isset($form['addr_ks']) ? $form['addr_ks'] : "";
    $form['delivery'] = isset($form['delivery']) ? $form['delivery'] : 0;
    $form['city'] = isset($form['city']) ? $form['city'] : "";
    $form['addr'] = isset($form['addr']) ? $form['addr'] : "";
    $form['payment'] = isset($form['payment']) ? $form['payment'] : 0;
    $form['note'] = isset($form['note']) ? $form['note'] : "";
    
    if($form['is_kherson'] == 1) {
        $style_ks = "";
        $style_ua = "display: none";
    }
    elseif($form['is_kherson'] == 0) {
        $style_ks = "display: none";
        $style_ua = "";
    }
    else {
        $style_ks = "display: none";
        $style_ua = "display: none";
    }
    
    $res_str .= "<h2>".$ini_arr['lang']['order-form']."</h2>";
    
    if(sizeof($error))
        $res_str .= "<div class='alert'>".$ini_arr['lang']['err-submit']."</div>";
    
    $res_str .= "<form action='".ALIAS.".htm' method='post'>
      <input name='action' type='hidden' value='form'>
  
      <h3 class='a-center'>".$ini_arr['lang']['person-info']."</h3>
      <div class='form-row'>
        <label>".$ini_arr['lang']['first-name']."
        </label><input
          class='".(isset($error['f_name']) ? "error" : "")."'
          type='text' name='form[f_name]'
          value='".htmlspecialchars($form['f_name'], ENT_QUOTES)."'
        >
      </div>
      <div class='form-row'>
        <label>".$ini_arr['lang']['last-name']."
        </label><input
          class='".(isset($error['l_name']) ? "error" : "")."'
          type='text' name='form[l_name]'
          value='".htmlspecialchars($form['l_name'], ENT_QUOTES)."'
        >
      </div>
      <div class='form-row'>
        <label>".$ini_arr['lang']['email']."
        </label><input
          class='".(isset($error['mail']) ? "error" : "")."'
          type='text' name='form[mail]'
          value='".htmlspecialchars($form['mail'], ENT_QUOTES)."'
        >
      </div>
      <div class='form-row'>
        <label>".$ini_arr['lang']['phone']."
        </label><input
          class='".(isset($error['phone']) ? "error" : "")."'
          type='text' name='form[phone]'
          value='".htmlspecialchars($form['phone'], ENT_QUOTES)."'
        >
      </div>

      <h3 class='a-center'>".$ini_arr['lang']['delivery-info']."</h3>
      
      <div class='form-row flex'>
        <label>".$ini_arr['lang']['in-kherson']."
        </label><input
          class='ks-ua'
          data-id='in-kherson'
          type='radio' name='form[is_kherson]'
          value='1'
          ".($form['is_kherson'] == 1 ? "checked" : "")."
        >
        <label>".$ini_arr['lang']['in-ukraine']."
        </label><input
          class='ks-ua'
          data-id='in-ukraine'
          type='radio' name='form[is_kherson]'
          value='0'
          ".($form['is_kherson'] == 0 ? "checked" : "")."
        >
        <label>".$ini_arr['lang']['pickup']."
        </label><input
          class='ks-ua'
          data-id='pickup'
          type='radio' name='form[is_kherson]'
          value='2'
          ".($form['is_kherson'] == 2 ? "checked" : "")."
        >
      </div>
      <div id='in-kherson' style='".$style_ks."'>
        <div class='form-row'>
          <label>".$ini_arr['lang']['delivery']."
          </label><div class='select-container ".(isset($error['delivery_ks']) ? "error" : "")."'>
            <select name='form[delivery_ks]' size='1'>";
    foreach($ini_arr['delivery_ks'] as $key => $value) {
        $res_str .= "<option value='".$key."' ".($form['delivery_ks'] == $key ? "selected" : "").">".$value."</option>";
    }
    $res_str .= "</select>
            <div class='select-button fa fa-down-dir'></div>
          </div>
        </div>
        <div class='form-row'>
          <label>".$ini_arr['lang']['addr']."
          </label><textarea
            class='".(isset($error['addr_ks']) ? "error" : "")."'
            name='form[addr_ks]'
            style='height: 50px'
          >".$form['addr_ks']."</textarea>
        </div>
      </div>

      <div id='in-ukraine' style='".$style_ua."'>
        <div class='form-row'>
          <label>".$ini_arr['lang']['delivery']."
          </label><div class='select-container ".(isset($error['delivery']) ? "error" : "")."'>
            <select name='form[delivery]' size='1'>";
    foreach($ini_arr['delivery'] as $key => $value) {
        $res_str .= "<option value='".$key."' ".($form['delivery'] == $key ? "selected" : "").">".$value."</option>";
    }
    $res_str .= "</select>
            <div class='select-button fa fa-down-dir'></div>
          </div>
        </div>
        <div class='form-row'>
          <label>".$ini_arr['lang']['city']."
          </label><input
            class='".(isset($error['city']) ? "error" : "")."'
            type='text' name='form[city]'
            value='".htmlspecialchars($form['city'], ENT_QUOTES)."'
          >
        </div>
        <div class='form-row'>
          <label>".$ini_arr['lang']['addr']."
          </label><textarea
            class='".(isset($error['addr']) ? "error" : "")."'
            name='form[addr]'
            style='height: 50px'
          >".$form['addr']."</textarea>
        </div>
      </div>

      <div class='form-row'>
        <label>".$ini_arr['lang']['payment']."
        </label><div class='select-container ".(isset($error['payment']) ? "error" : "")."'>
          <select name='form[payment]' size='1'>";
    foreach($ini_arr['payment'] as $key => $value) {
        $res_str .= "<option value='".$key."' ".($form['payment'] == $key ? "selected" : "").">".$value."</option>";
    }
    $res_str .= "</select>
          <div class='select-button fa fa-down-dir'></div>
        </div>
      </div>

      <h3 class='a-center'>".$ini_arr['lang']['additional-information']."</h3>
      <div class='form-row'>
        <label>".$ini_arr['lang']['comment']."
        </label><textarea
          name='form[note]'
          style='height: 80px'
        >".$form['note']."</textarea>
      </div>

      <div class='form-row a-center' style='margin-top: 30px'>
          <button type='submit' class='button button-1'>".$ini_arr['lang']['checkout']."</button>
      </div>
    </form>
    
    <script>
    $('.ks-ua').click(function() {
        var data = $(this).data();
        if(data.id === 'in-kherson') {
            $('#in-kherson').slideDown();
            $('#in-ukraine').slideUp();
        }
        else if(data.id === 'in-ukraine') {
            $('#in-kherson').slideUp();
            $('#in-ukraine').slideDown();
        }
        else {
            $('#in-kherson').slideUp();
            $('#in-ukraine').slideUp();
        }
    });
    </script>";
    
    return $res_str;
}
function order_list_items() {
    global $ini_arr;
    $res_str = "<h2 class='a-center'>".$ini_arr['lang']['cart']."</h2>";
    $empty_str = $res_str."<div class='alert' style='margin-top: 10px'>".$ini_arr['lang']['cart-empty']."</div>";
    
    $arr = make_cart_list_items();
    $item_arr = $arr['items'];
    $var_arr = $arr['vars'];
    
    if(!sizeof($item_arr)) return $empty_str;

    $res_str .= "
    <ul>";
    foreach($item_arr as $key => $row) {
        $item_key = $row['id'].'.'.$row['var_id'];
        
        if(!empty($row['page_ini_arr'])) {
            $arr = unserialize($row['page_ini_arr']);
            $row = array_merge($row, $arr);
        }
        
        if(isset($var_arr[$row['var_id']]))
            $row['var_name'] = "<p>".$ini_arr['lang']['size'].": <span class='strong'>".$var_arr[$row['var_id']]."</span></p>";
        else
            $row['var_name'] = "";
        $row['num'] = $_SESSION['sess_cart'][$item_key];
        
        $img = USER_TEMPLATE_DIR."images/empty.jpg";
        $main_img = empty($row['adv_setting']['item_img']) ? "" : IMAGE_PAGE_DIR.$row['type']."/".$row['adv_setting']['item_img'];
        if(!empty($main_img) and file_exists($main_img))
            $img = $main_img;

        $res_str .= "<li class='oh'>
          <div class='img' style='background-image: url(".$img.")'></div>
          <div class='info'>
            <div class='strong'>".$row['name']."</div>
            ".$row['var_name']."
            <p>".$ini_arr['lang']['price'].": <span class='strong'>".number_format($row['price'], 2, '.', '')."</span> ".$ini_arr['lang']['currency']."</p>
            <p>".$ini_arr['lang']['num'].": <span class='strong'>".$row['num']."</span></p>
            <p>".$ini_arr['lang']['sum'].": <span class='strong'>".number_format($row['price'] * $row['num'], 2, '.', '')."</span> ".$ini_arr['lang']['currency']."</p>
          </div>
        </li>";
    }
    $res_str .= "
      <li class='flex'>
        <div>".$ini_arr['lang']['delivery-cost']."</div>
        <div class='strong'>".$ini_arr['lang']['question']."</div>
      </li>
    </ul>
    <div class='order-footer mb-10'>
      <div class='flex'>
        <div>".$ini_arr['lang']['total']."</div>
        <div><span class='price'>".number_format($_SESSION['sess_cart_sum'], 2, '.', '')."</span> ".$ini_arr['lang']['currency']."</div>
      </div>
    </div>
    <div class='a-center'>
      <a href='#' id='cartLink' id=>".$ini_arr['lang']['order-edit']."</a>
    </div>
    <script>
    $('#cartLink').click(function() {
        $('#dialog-content').load('".AJAX_SCRIPT."', {
          action: 'view_cart',
          lang: '".LANG."'
        });

        $('#over-container').show();
        return false;
    });
    </script>";

    return $res_str;
}
function process_order_form($form) {
    global $ini_arr;
    $res_str = "";

    $form['f_name'] = isset($form['f_name']) ? trim(strip_tags($form['f_name'])) : "";
    $form['l_name'] = isset($form['l_name']) ? trim(strip_tags($form['l_name'])) : "";
    $form['mail'] = isset($form['mail']) ? trim(strip_tags($form['mail'])) : "";
    $form['phone'] = isset($form['phone']) ? trim(strip_tags($form['phone'])) : "";
    $form['is_kherson'] = (isset($form['is_kherson']) and ((int)$form['is_kherson'] == 1 or (int)$form['is_kherson'] == 0 or (int)$form['is_kherson'] == 2))
        ? (int)$form['is_kherson'] : 1;
    $form['delivery_ks'] = isset($form['delivery_ks']) ? (int)$form['delivery_ks'] : 0;
    $form['addr_ks'] = isset($form['addr_ks']) ? trim(strip_tags($form['addr_ks'])) : "";
    $form['delivery'] = isset($form['delivery']) ? (int)$form['delivery'] : 0;
    $form['city'] = isset($form['city']) ? trim(strip_tags($form['city'])) : "";
    $form['addr'] = isset($form['addr']) ? trim(strip_tags($form['addr'])) : "";
    $form['payment'] = isset($form['payment']) ? (int)$form['payment'] : 0;
    $form['note'] = isset($form['note']) ? trim(strip_tags($form['note'])) : "";
    
    if(empty($form['f_name']))
        $error['f_name'] = true;
    if(empty($form['l_name']))
        $error['l_name'] = true;
    if(empty($form['mail']))
        $error['mail'] = true;
    if(empty($form['phone']))
        $error['phone'] = true;
    if(!isset($ini_arr['payment'][$form['payment']]))
        $error['payment'] = true;
        
    if($form['is_kherson'] == 1) {
        unset($form['delivery'], $form['city'], $form['addr']);
        if(!isset($ini_arr['delivery_ks'][$form['delivery_ks']]))
            $error['delivery_ks'] = true;
        if(empty($form['addr_ks']))
            $error['addr_ks'] = true;
    }
    elseif($form['is_kherson'] == 0) {
        unset($form['delivery_ks'], $form['addr_ks']);
        if(!isset($ini_arr['delivery'][$form['delivery']]))
            $error['delivery'] = true;
        if(empty($form['city']))
            $error['city'] = true;
        if(empty($form['addr']))
            $error['addr'] = true;
    }
    else {
        unset($form['delivery'], $form['city'], $form['addr'], $form['delivery_ks'], $form['addr_ks']);
    }
    
    $_SESSION['sess_cart_user'] = $form;

    if(isset($error))
        return cart($form, $error);

    $ini_arr['user_data']['order'] = $form;
    unset($ini_arr['user_data']['cart']);

    if(SESS_G_ID) {
        $param = array('prop_arr' => serialize($ini_arr['user_data']));
        $param_where = array('id' => SESS_G_ID);
        $query = "UPDATE ".prdb."guests SET [QUERY_SET] WHERE id = :id";
        db_update($query, $param, $param_where);
    }
    elseif(SESS_U_ID) {
        $param = array('prop_arr' => serialize($ini_arr['user_data']));
        $param_where = array('id' => SESS_U_ID);
        $query = "UPDATE ".prdb."users SET [QUERY_SET] WHERE id = :id";
        db_update($query, $param, $param_where);
    }

    $arr = make_cart_list_items();
    $item_arr = $arr['items'];
    $var_arr = $arr['vars'];

    $param = array(
        'user_id' => SESS_G_ID, // когда будут юзеры (SESS_G_ID ? SESS_G_ID : SESS_U_ID),
        'date_in' => date('Y-m-d H:i:s'),
        'prop_arr' => serialize($form)
    );
    $query = "INSERT INTO ".prdb."order SET ";
    $order_id = db_insert($query, $param, 'insert_id');
    
    foreach($_SESSION['sess_cart'] as $item_key => $num) {
        $arr = explode('.', $item_key);
        $item_id = $arr[0];
        $item_var_id = isset($arr[1]) ? $arr[1] : 0;
        
        $param = array(
            'order_id' => $order_id,
            'item_id' => $item_id,
            'var_id' => $item_var_id,
            'price' => $_SESSION['sess_cart_prices'][$item_key],
            'num' => $num
        );
        $query = "INSERT INTO ".prdb."cart SET ";
        db_insert($query, $param);
    }
    
    $order_str = "
    ".$ini_arr['lang']['first-name'].", ".$ini_arr['lang']['last-name'].": <strong>".$form['f_name']." ".$form['l_name']."</strong><br>
    ".$ini_arr['lang']['email'].": <strong>".$form['mail']."</strong><br>
    ".$ini_arr['lang']['phone'].": <strong>".$form['phone']."</strong><br>
    ";
    if($form['is_kherson'] == 1) {
        $order_str .= "
        ".$ini_arr['lang']['delivery'].": <strong>".$ini_arr['lang']['in-kherson'].", ".$ini_arr['delivery_ks'][$form['delivery_ks']]."</strong><br>
        ".$ini_arr['lang']['addr'].": <strong>".$form['addr_ks']."</strong><br>
        ";
    }
    elseif($form['is_kherson'] == 0) {
        $order_str .= "
        ".$ini_arr['lang']['delivery'].": <strong>".$ini_arr['lang']['in-ukraine'].", ".$ini_arr['delivery'][$form['delivery']]."</strong><br>
        ".$ini_arr['lang']['city'].": <strong>".$form['city']."</strong><br>
        ".$ini_arr['lang']['addr'].": <strong>".$form['addr']."</strong><br>
        ";
    }
    else {
        $order_str .= "
        ".$ini_arr['lang']['delivery'].": <strong>".$ini_arr['lang']['pickup']."</strong><br>
        ";
    }
    $order_str .= "
    ".$ini_arr['lang']['payment'].": <strong>".$ini_arr['payment'][$form['payment']]."</strong><br>";
    if(!empty($form['note']))
        $order_str .= $ini_arr['lang']['additional-information'].":<br><strong>".nl2br($form['note'])."</strong>";
    
    $item_str = '
    <table border="1" cellpadding="3" cellspacing="0" style="width: 100%; margin:0; padding:0">
    <tbody>
    <tr>
      <th>'.$ini_arr['lang']['payment'].'</th>
      <th>'.$ini_arr['lang']['price'].'</th>
      <th>'.$ini_arr['lang']['num'].'</th>
      <th>'.$ini_arr['lang']['sum'].'</th>
    </tr>';
    foreach($item_arr as $key => $row) {
        $item_key = $row['id'].'.'.$row['var_id'];
        $row['var_name'] = isset($var_arr[$row['var_id']])
            ? "<br>".$ini_arr['lang']['size'].": <span class='strong'>".$var_arr[$row['var_id']]."</span>"
            : "";
        $row['num'] = $_SESSION['sess_cart'][$item_key];
        $item_str .= '
        <tr>
          <td>'.$row['name'].$row['var_name'].'</td>
          <td>'.number_format($row['price'], 2, '.', '').' '.$ini_arr['lang']['currency'].'</td>
          <td align="center">'.$row['num'].'</td>
          <td>'.number_format($row['price'] * $row['num'], 2, '.', '').' '.$ini_arr['lang']['currency'].'</td>
        </tr>';
    }
    $item_str .= '
    <tr>
      <td colspan="3" align="right">'.$ini_arr['lang']['delivery-cost'].'</td>
      <td>'.$ini_arr['lang']['question'].'</td>
    </tr>
    <tr>
      <td colspan="3" align="right">'.$ini_arr['lang']['total'].'</td>
      <td>'.number_format($_SESSION['sess_cart_sum'], 2, '.', '').' '.$ini_arr['lang']['currency'].'</td>
    </tr>
    </tbody>
    </table>';
    
    $repl_arr = array(
        '[FIRST_NAME]' => $form['f_name'],
        '[LAST_NAME]' => $form['l_name'],
        '[ORDER_INFO]' => "<h3>Ваш заказ № ".$order_id."</h3>".$order_str,
        '[CART_LIST]' => $item_str
    );
    $mess_arr = make_letter('cart', LANG, $repl_arr);
    if($mess_arr == "err")
        return; // "Нет такого шаблона"

    require_once(BASE_HREF.'include/class.phpmailer.php');
    $mail = new PHPMailer();
    $mail->CharSet = "UTF-8";
    $mail->SetFrom($ini_arr['noreplay-mail'], 'NOREPLY '.DOMAIN);
    $mail->AddAddress($form['mail']);
    $mail->Subject = $mess_arr['subject'];
    $mail->Body = $mess_arr['text'];
    $mail->MsgHTML($mess_arr['html']);

    $mail->Send();

    $repl_arr = array(
        '[ORDER_INFO]' => "<h3>Заказ № ".$order_id."</h3>".$order_str,
        '[CART_LIST]' => $item_str
    );
    $mess_arr = make_letter('cart-admin', LANG, $repl_arr);
    if($mess_arr == "err")
        return; // "Нет такого шаблона"

    require_once(BASE_HREF.'include/class.phpmailer.php');
    $mail = new PHPMailer();
    $mail->CharSet = "UTF-8";
    $mail->SetFrom($ini_arr['noreplay-mail'], 'NOREPLY '.DOMAIN);
    $mail->AddAddress($ini_arr['admin_mail']);
    $mail->Subject = $mess_arr['subject'];
    $mail->Body = $mess_arr['text'];
    $mail->MsgHTML($mess_arr['html']);

    $mail->Send();
    
    unset($_SESSION['sess_cart'],
          $_SESSION['sess_cart_prices'],
          $_SESSION['sess_cart_sum'],
          $_SESSION['sess_cart_num']);

    return "
    <div class='content page oh' style='padding: 20px 0'>
      <h2>".$ini_arr['lang']['order-form']."</h2>
      <div class='alert' style='margin: 30px 0'>".$ini_arr['lang']['cart-result']."</div>
    </div>
    <script>
      cpSum.text('');
      cpNum.text('').hide();
      cpCur.text('".$ini_arr['lang']['empty']."');
    </script>";
}
?>