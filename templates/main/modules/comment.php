<?php

if(isset($_REQUEST['action'])) {
    $action = trim($_REQUEST['action']);
    if($action == 'list_comment') {
        echo list_comment();
        exit;
    }
    elseif($action == 'process_comment' and isset($_REQUEST['form']) and is_array($_REQUEST['form'])) {
        echo process_comment($_REQUEST['form']);
        exit;
    }
}

$content_arr['COMMENT_LIST'] .= "
<div class='comment-container'>
  <div class='page'>
    <div class='comment-list'>".list_comment().form_comment()."</div>
  </div>
</div>";

// ---------------------------------------- function
function list_comment() {
    global $ini_arr, $ROW_PAGE;
    $res_str = "";

    $order = "";

    $param = array('id' => PAGE_ID);
    $query = "SELECT ".prdb."comment.name,
                DATE_FORMAT(".prdb."comment.date_in, '%d.%m.%Y %H:%i') AS f_date_in,
                ".prdb."comment.note, ".prdb."comment.admin_note
              FROM ".prdb."comment
              WHERE ".prdb."comment.visible = 1 AND ".prdb."comment.page_id = :id
              ORDER BY ".prdb."comment.date_in ".$order;
    $row_arr = db_get_arr($query, $param, 'arr');
    $num_all = sizeof($row_arr);
    $res_str .= "
      <h3 class='a-center'>".$ini_arr['lang']['comments']."</h3>";
    
    foreach($row_arr as $row) {
        $name = empty($row['name']) ? "Anonymous" : $row['name'];
        
        $res_str .= "
          <div class='comment'>
            <div class='title oa'>
              <div class='name'>".$name."</div>
              <div class='date-in'>".$row['f_date_in']."</div>
            </div>
            <div class='note'>".nl2br($row['note'])."</div>";
        if(!empty($row['admin_note'])) {
            $res_str .= "
            <div class='admin oa'>
              <div class='title'>Admin</div>
              <div class='note'>".nl2br($row['admin_note'])."</div>
            </div>";
        }
        $res_str .= "</div>";
    }

    return $res_str;
}
function form_comment($form = array(), $error = '') {
    global $ini_arr, $ROW_PAGE, $content_arr;
    $res_str = "";

    if(!empty($ini_arr['login_comment']) and !SESS_ID)
        return "<div class='alertText'>".$ini_arr['lang']['only_user_comment']."</div>";

    if(!isset($form['name'])) {
        if(isset($_SESSION['comment_name']))
            $form['name'] = $_SESSION['comment_name'];
        else
            $form['name'] = '';
    }
    if(!isset($form['mail'])) {
        if(isset($_SESSION['comment_mail']))
            $form['mail'] = $_SESSION['comment_mail'];
        else
            $form['mail'] = '';
    }
    
    $form['note'] = isset($form['note']) ? $form['note'] : '';
    
    $res_str .= "<div class='comment-form'>
    <form action='".$ini_arr['pr_lang'].ALIAS.".htm' method='post' id='comment-form'>
    <input name='action' type='hidden' value='process_comment'>
    <input name='form[page_id]' type='hidden' value='".PAGE_ID."'>
    ";

    $res_str .= "<h4>".$ini_arr['lang']['write-comment']."</h4>";

    if(!empty($error))
        $res_str .= "<div class='alertText'>".$error."</div>";

    $login = "";
    if(SESS_LOGIN <> '')
        $login = SESS_LOGIN;
    if(!empty($login)) {
        $res_str .= "<div class='form-row oa'>".$ini_arr['lang']['your-name'].": <span class='strong'>".$login."</span></div>
        <input name='form[name]' type='hidden' value='".htmlspecialchars($login, ENT_QUOTES)."'>";
    }
    else {
        $res_str .= "
        <div class='form-row'>
          <label for='comment-name'>".$ini_arr['lang']['your-name']."
          </label><input name='form[name]' type='text' id='comment-name' value='".htmlspecialchars($form['name'], ENT_QUOTES)."'>
        </div>
        <div class='form-row'>
          <label for='comment-mail'>".$ini_arr['lang']['e-mail_not_published']."
          </label><input name='form[mail]' type='text' id='comment-mail' value='".htmlspecialchars($form['mail'], ENT_QUOTES)."'>
        </div>";
    }
    $res_str .= "
    <div class='form-row'>
        <label for='comment-note'>".$ini_arr['lang']['message']."</label>
        <textarea id='comment-note' name='form[note]'>".$form['note']."</textarea>
    </div>
    <div class='form-row a-center' style='margin-top: 10px'>
        <input name='form[button]' type='submit' value='".$ini_arr['lang']['send']."' class='button-1' id='comment-submit'>
    </div>

    </form></div>

    <script>
      $('#comment-submit').click(function () { 
          var query = $('#comment-form').serialize();
          $('.comment-list').load('".$ini_arr['pr_lang'].ALIAS.".htm', query);
          return false;
      });
    </script>
    ";

    return $res_str;
}
function process_comment($form) {
    global $ini_arr;

    if(!empty($ini_arr['login_comment']) and !SESS_ID)
        return "<div class='alertText'>".$ini_arr['lang']['only_user_comment']."</div>";

    $res_str = "";
    $error = "";
    
    $form['name'] = isset($form['name']) ? strip_tags(trim($form['name'])) : "Anonimus";
    $form['mail'] = isset($form['mail']) ? strip_tags(trim($form['mail'])) : "";
    $form['note'] = isset($form['note']) ? strip_tags(trim($form['note'])) : "";
    
    if(empty($form['note'])) 
        $error .= "<div>".$ini_arr['lang']['err-empty-comment']."</div>";

    if(!empty($error)) {
        return form_comment($form, $error);
    }

    $param = array(
        'page_id' => PAGE_ID,
        'name' => $form['name'],
        'date_in' => date("Y-m-d H:i:s"),
        'visible' => !$ini_arr['moder_comment'],
        'mail' => $form['mail'],
        'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "emptyIP",
        'note' => $form['note']
    );
    if(SESS_ID) {
        $param['user_id'] = SESS_ID;
        $param['who'] = SESS_WHO;
    }
    $query = "INSERT INTO ".prdb."comment SET ";
    db_insert($query, $param);

    if(!SESS_ID) {
        $_SESSION['comment_name'] = $form['name'];
        $_SESSION['comment_mail'] = $form['mail'];
    }
    
    $res_str .= list_comment().form_comment();
    
    return $res_str;
}
?>