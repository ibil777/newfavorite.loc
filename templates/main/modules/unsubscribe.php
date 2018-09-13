<?php
// ------------------------------------------- Переделать

$res_str .= "<h1>Отмена подписки</h1>";

if(isset($_REQUEST['action']) and isset($_REQUEST['code']) and !empty($_REQUEST['code'])) {
    $query = "DELETE FROM ".prdb."mailer WHERE code='".mysql_real_escape_string($_REQUEST['code'])."'";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    $res_str .= "
    <div class='alertText'>Подписка отменена</div>";
}
elseif(isset($_REQUEST['code']) and !empty($_REQUEST['code'])) {
    $query = "SELECT mail, code FROM ".prdb."mailer WHERE code='".mysql_real_escape_string($_REQUEST['code'])."'";
    $result = mysql_query($query)   or die(mysql_error()."<br>".$query);
    if(!mysql_num_rows($result)) {
        $res_str .= "
        <div class='alertText'>Неправильный код отмены подписки</div>";
    }
    else {
        $row = mysql_fetch_assoc($result);
        $res_str .= "<div>Вы действительно хотите отменить подписку на почтовый ящик ".$row['mail']."?</div>
        <div class='a-center'>
          <a href='unsubscribe.htm?action=yes&code=".$row['code']."' class='button-link'>Да</a>
          <a href='.' class='button-link'>Нет</a>
        </div>";
    }
}


?>