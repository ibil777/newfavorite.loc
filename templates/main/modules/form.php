<?php
//echo "<pre>".print_r($_REQUEST, true)."</pre>";
if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'form' and isset($_REQUEST['form']) and is_array($_REQUEST['form']))
    $res_str = process_form($_REQUEST['form']);
else
    $res_str .= make_form();

function make_form($form = array(), $error = array()) {
    global $ini_arr;
    $star = "<span class='fa fa-star'></span>";
    $res_str = "";

    $param = array('page_id' => PAGE_ID);
    $query = "SELECT ".prdb."form.id, ".prdb."form.type, ".prdb."form.prop_arr
              FROM ".prdb."form
              WHERE ".prdb."form.page_id = :page_id
              ORDER BY ".prdb."form.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr))
        return;

    if(sizeof($error))
        $res_str .= "<div class='alertText'>".$ini_arr['lang']['err-submit']."</div>";

    $res_str .= "<div class='page-form'>
    <form action='".$ini_arr['pr_lang'].ALIAS.".htm' method='post'>
    <input name='action' type='hidden' value='form'>
    ";
    foreach($row_arr as $row) {
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);

        $row['required'] = isset($prop_arr['required']) ? $prop_arr['required'] : 0;
        $row['width'] = isset($prop_arr['width']) ? $prop_arr['width'] : 0;
        $row['height'] = isset($prop_arr['height']) ? $prop_arr['height'] : 0;
        $row['title'] = isset($prop_arr['title'][LANG]) ? $prop_arr['title'][LANG] : '';
        $row['note'] = isset($prop_arr['note'][LANG]) ? $prop_arr['note'][LANG] : '';
        $row['options'] = isset($prop_arr['options'][LANG]) ? $prop_arr['options'][LANG] : '';

        $row['title'] = ($row['required'] and $row['type'] <> 'note' and $row['type'] <> 'hidden') ? $star." ".$row['title'] : $row['title'];
        $row['note'] = empty($row['note']) ? "" : "<div class='form-note small a-left'>".nl2br($row['note'])."</div>";

        if($row['type'] == 'note') {
            $res_str .= "<h3>".$row['title'].$row['note']."</h3>";
        }
        elseif($row['type'] == 'hidden') {
            $res_str .= "<h3>".$row['title'].$row['note']."</h3>
            <input type='hidden' name='form[".$row['id']."]' value=''>";
        }
        elseif($row['type'] == 'text') {
            $res_str .= "
            <div class='form-row'>
                <label for='id".$row['id']."'
                  class='".(($row['required'] and isset($error[$row['id']])) ? "error" : "")."'
                >".$row['title'].$row['note']."</label><input
                    type='text' name='form[".$row['id']."]' id='id".$row['id']."'
                    style='".($row['width'] ? "width: ".$row['width']."%" : "")."'
                    class='".(($row['required'] and isset($error[$row['id']])) ? "error" : "")."'
                    value='".(isset($form[$row['id']]) ? htmlspecialchars($form[$row['id']], ENT_QUOTES) : '')."'
                >
            </div>";
        }
        elseif($row['type'] == 'textarea') {
            $res_str .= "
            <div class='form-row'>
                <label for='id".$row['id']."'
                  class='".(($row['required'] and isset($error[$row['id']])) ? "error" : "")."'
                >".$row['title'].$row['note']."</label>
                <textarea name='form[".$row['id']."]' id='id".$row['id']."'
                    style='
                        ".($row['width'] ? "width: ".$row['width']."%; " : "")."
                        ".($row['height'] ? "height: ".$row['height']."px; " : "")."
                    '
                    class='".(($row['required'] and isset($error[$row['id']])) ? "error" : "")."'
                    >".(isset($form[$row['id']]) ? htmlspecialchars($form[$row['id']], ENT_QUOTES) : '')."</textarea>
            </div>";
        }
        elseif($row['type'] == 'select') {
            $res_str .= "
            <div class='form-row'>
                <label for='id".$row['id']."'
                  class='".(($row['required'] and isset($error[$row['id']])) ? "error" : "")."'
                >".$row['title'].$row['note']."</label>
                <select size='1' name='form[".$row['id']."]'
                    style='".($row['width'] ? "width: ".$row['width']."%" : "")."'
                >";
            $arr = explode(';', $row['options']);
            foreach($arr as $key => $value) {
                $res_str .= "<option value='".$key."' ".((isset($form[$row['id']]) and $form[$row['id']] == $key) ? 'selected' : '').">".$value."</option>";
            }
            $res_str .= "</select>
            </div>";
        }
        elseif($row['type'] == 'checkbox') {
            $res_str .= "
            <div class='form-row'>
                <label for='id".$row['id']."' class='".(($row['required'] and isset($error[$row['id']])) ? "error" : "")."'>
                  <input type='checkbox' name='form[".$row['id']."]' id='id".$row['id']."'
                    ".(isset($form[$row['id']]) ? 'checked' : '').">
                  ".$row['title'].$row['note']."
                </label>
            </div>";
        }
        elseif($row['type'] == 'radio') {
            $res_str .= "
            <div class='form-row'>
                <label class='".(($row['required'] and isset($error[$row['id']])) ? "error" : "")."'>".$row['title'].$row['note']."</label>
                <div class='field-container'>";
            $arr = explode(';', $row['options']);
            foreach($arr as $key => $value) {
                $res_str .= "<label><input type='radio' name='form[".$row['id']."]'
                    ".((isset($form[$row['id']]) and $form[$row['id']] == $key) ? 'checked' : '')."
                    value='".$key."'
                > ".$value."</label>";
            }
            $res_str .= "</div>
            </div>";
        }
    }
    if(false and !isset($_SESSION['sess_id'])) {
        $res_str .= "
        <div class='form-row'>
            <label class='".(isset($error['secret']) ? "error" : "")."'>".$word[LANG][29]."<img src='".BASE_HREF."images/secret.php?t=".time()."'></label>
            <input name='form[secret]' type='text' class='w-100px'>
        </div>";
    }
    $res_str .= "
    <div class='form-row a-center'>
        <button type='submit' class='button button-1'>".$ini_arr['lang']['send']."</button>
    </div>

    </form>
    </div>
    ";

    return $res_str;
}
function process_form($form) {
    global $ini_arr, $content_arr, $ROW_PAGE;
    
    $add_mail = isset($ROW_PAGE['ini_arr']['adv_setting']['add_mail'])
        ? explode(';', $ROW_PAGE['ini_arr']['adv_setting']['add_mail'])
        : array();

    $param = array('page_id' => PAGE_ID);
    $query = "SELECT ".prdb."form.id, ".prdb."form.type, ".prdb."form.prop_arr
              FROM ".prdb."form
              WHERE ".prdb."form.page_id = :page_id
              ORDER BY ".prdb."form.p_id";
    $row_arr = db_get_arr($query, $param, 'arr');
    if(!sizeof($row_arr))
        return;

    $mail_str = "";
    $user_id = 0;
    $who = '';
    if(SESS_ID) {
        $mail_str .= "Пользователь: ".SESS_LOGIN."\n\n";
        $user_id = SESS_ID;
        $who = SESS_WHO;
    }

    $res_arr = array('name_form' => $ROW_PAGE['name'], 'form' => array());
    foreach($row_arr as $row) {
        $prop_arr = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);

        $row['required'] = isset($prop_arr['required']) ? $prop_arr['required'] : 0;
        $row['title'] = isset($prop_arr['title'][LANG]) ? $prop_arr['title'][LANG] : '';
        $row['note'] = isset($prop_arr['note'][LANG]) ? $prop_arr['note'][LANG] : '';
        $row['options'] = isset($prop_arr['options'][LANG]) ? $prop_arr['options'][LANG] : '';

        if($row['type'] == 'note' or $row['type'] == 'hidden') {
            $mail_str .= "\n---------------------------------------------\n";
            $mail_str .= $row['title'];
            $mail_str .= "\n---------------------------------------------\n";
            $res_arr['form'][$row['id']]['name'] = $row['title'];
            $res_arr['form'][$row['id']]['content'] = '';
        }
        elseif($row['type'] == 'text' or $row['type'] == 'textarea') {
            $form[$row['id']] = isset($form[$row['id']]) ? strip_tags(trim($form[$row['id']])) : "";
            if($row['required'] and empty($form[$row['id']]))
                $error[$row['id']] = 1;

            $mail_str .= $row['title'].": ".$form[$row['id']]."\n";

            $res_arr['form'][$row['id']]['name'] = $row['title'];
            $res_arr['form'][$row['id']]['content'] = $form[$row['id']];
        }
        elseif($row['type'] == 'select' or $row['type'] == 'radio') {
            $arr = explode(';', $row['options']);
            $form[$row['id']] = (isset($form[$row['id']]) and isset($arr[$form[$row['id']]])) ? $form[$row['id']] : -1;
            if($row['required'] and $form[$row['id']] == -1)
                $error[$row['id']] = 1;
            if(isset($arr[$form[$row['id']]])) {
                $mail_str .= $row['title'].": ".$arr[$form[$row['id']]]."\n";

                $res_arr['form'][$row['id']]['name'] = $row['title'];
                $res_arr['form'][$row['id']]['content'] = $arr[$form[$row['id']]];
            }
        }
        elseif($row['type'] == 'checkbox') {
            if($row['required'] and !isset($form[$row['id']]))
                $error[$row['id']] = 1;

            $res_arr['form'][$row['id']]['name'] = $row['title'];
            if(isset($form[$row['id']])) {
                $mail_str .= $row['title'].": Да\n";
                $res_arr['form'][$row['id']]['content'] = "Да";
            }
            else {
                $mail_str .= $row['title'].": Нет\n";
                $res_arr['form'][$row['id']]['content'] = "Нет";
            }
        }
    }

    if(false and !isset($_SESSION['sess_id']) and (
            !isset($form['secret']) or empty($form['secret']) or !isset($_SESSION['sess_secret']) or $form['secret'] <> $_SESSION['sess_secret']))
        $error['secret'] = 1;

    if(isset($error)) {
        return make_form($form, $error);
    }

    if(isset($ini_arr['admin_mail'])) {
        if(!isset($subject))
            $subject = "Сообщение со страницы <".$content_arr['TITLE'].">";

        $headers = "Content-Type: text/plain; charset=UTF-8\n";
        $headers .= "From: ".$ini_arr['admin_mail'];

        @mail($ini_arr['admin_mail'], $subject, $mail_str, $headers);
        if(sizeof($add_mail)) {
            foreach($add_mail as $mail)
                @mail($mail, $subject, $mail_str, $headers);
        }
    }

    $res_str = "";
    if(!empty($ROW_PAGE['ini_arr']['adv_setting']['result_html'][LANG]))
        $res_str .= $ROW_PAGE['ini_arr']['adv_setting']['result_html'][LANG];
    else
        $res_str .= "Thank you!";
    $res_str = "<div class='ok'>".$res_str."</div>";

    if(true or !empty($row_page['ini_arr']['adv_setting']['form_save'])) {
        $param = array(
            'page_id' => PAGE_ID,
            'date_in' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'who' => $who,
            'prop_arr' => serialize($res_arr)
        );
        $query = "INSERT INTO ".prdb."form_result SET ";
        db_insert($query, $param);
    }

    return $res_str;
}
?>