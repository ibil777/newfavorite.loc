<?php

if(!empty($_REQUEST['mail'])) {
    $res_str = process_form($_REQUEST['mail']);
}
else {
    $res_str .= form();

}

// -------------------------------- function
function form($error = "") {
    global $ini_arr;
    $res_str = "";
    
    if(strlen($error)) {
        $res_str .= "<div class='alertText'>".$error."</div>";
    }

    $res_str .= "
    <form action='".ALIAS.".htm' method='post' style='margin-top: 50px'>

      <div class='form-row'>
        <label>".$ini_arr['lang']['forgot-form'].":</label>
        <input type='text' name='mail' class='w-300px'>
      </div>

      <div class='submit'>
        <button type='submit' class='button button-2'>".$ini_arr['lang']['send']."</button>
      </div>
    </form>";

    return $res_str;
}
function process_form($mail) {
    global $ini_arr;

    $mail = strip_tags(trim($mail));

    $param = array('mail' => $mail);
    $query = "SELECT pass, mail
              FROM ".prdb."users
              WHERE mail = :mail";
    $row = db_get_row($query, $param);
    if(!sizeof($row)) {
        $query = "SELECT pass, login, mail
                  FROM ".prdb."login
                  WHERE mail = :mail OR login = :mail";
        $row = db_get_row($query, $param);
    }

    if(!sizeof($row))
        return form($ini_arr['lang']['err-forgot']);

    $repl_arr = array(
        '[LOGIN]' => isset($row['login']) ? $row['login'] : $row['mail'],
        '[PASS]' => $row['pass']
    );
    $mess_arr = make_letter('forgot', LANG, $repl_arr);
    if($mess_arr == "err")
        return form($ini_arr['lang']['err-forgot']." 1"); // "Нет такого шаблона"

    require_once(BASE_HREF.'include/class.phpmailer.php');
    $mail = new PHPMailer();
    $mail->CharSet = "UTF-8";
    $mail->SetFrom($ini_arr['noreplay-mail'], 'NOREPLAY '.DOMAIN);
    $mail->AddAddress($row['mail']);
    $mail->Subject = $mess_arr['subject'];
    $mail->Body = $mess_arr['text'];
    $mail->MsgHTML($mess_arr['html']);

    $mail->Send();

    return "<div class='alertText'>".$ini_arr['lang']['forgot-result']."</div>";
}
?>