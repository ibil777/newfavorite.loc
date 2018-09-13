<?php

if(isset($_REQUEST['fb_auth'])) {
    $error = fb_auth();
    if($error) {
        $text = "<h2>".$ini_arr['lang']['login']."</h2><div class='alert'>".$ini_arr['lang']['err-login-fb-'.$error]."</div>";
        $content_arr['ONLOAD'] .= dialog_open($text);
    }
    else {
        header("Location: cabinet.htm");
        exit;
    }
}

// --------------------- function
function fb_auth() {
    global $ini_arr;
    
    if(!isset($_REQUEST['code']))
        return 1;

    $url = "https://graph.facebook.com/v2.3/oauth/access_token";
    $param = array(
        'client_id' => FB_APP_ID,
        'redirect_uri'  => FB_REDIRECT_URI,
        'client_secret' => FB_APP_SECRET,
        'code' => $_REQUEST['code']
    );
    $link = $url."?".http_build_query($param);
    $s = @file_get_contents($link);
    $obj = json_decode($s);
    if(empty($obj->access_token)) return 1;
    $token = $obj->access_token;
    
    $url = "https://graph.facebook.com/v2.7/me";
    $param = array(
        'access_token' => $token,
        'fields' => 'id,first_name,last_name,email,gender'
    );
    $link = $url."?".http_build_query($param);
    $s = @file_get_contents($link);
    $obj = json_decode($s);

    if(!isset($obj->id) or !isset($obj->gender) or $obj->gender <> 'female' and $obj->gender <> 'male')
        return 2;
    
    $param = array('fb_id' => $obj->id);
    if($obj->gender == 'female') {
        $who = 'girl';
        $query = "SELECT ".prdb."girls.id
                  FROM ".prdb."girls
                  WHERE ".prdb."girls.fb_id = :fb_id";
    }
    else {
        $who = 'man';
        $query = "SELECT ".prdb."men.id
                  FROM ".prdb."men
                  WHERE ".prdb."men.fb_id = :fb_id";
    }
    $user_id = db_get_value($query, $param);
    if($user_id) {
        save_new_session($user_id, $who);
        return 0;
    }
    
    if(!empty($obj->email)) {
        $param = array('mail' => $obj->email);
        if($who == 'girl') {
            $query = "SELECT ".prdb."girls.id
                      FROM ".prdb."girls
                      WHERE ".prdb."girls.mail = :mail";
        }
        else {  
            $query = "SELECT ".prdb."men.id
                      FROM ".prdb."men
                      WHERE ".prdb."men.mail = :mail";
        }
        $user_id = db_get_value($query, $param);
        if($user_id) {
            $param = array(
                'fb_id' => $obj->id
            );
            $param_where = array(
                'id' => $user_id
            );
            if($who == 'girl') {
                $query = "UPDATE ".prdb."girls SET
                            [QUERY_SET]
                          WHERE ".prdb."girls.id = :id";
            }
            else {
                $query = "UPDATE ".prdb."men SET
                            [QUERY_SET]
                          WHERE ".prdb."men.id = :id";
            }
            db_update($query, $param, $param_where);

            save_new_session($user_id, $who);
            return 0;
        }
    }
    
    $mail = empty($obj->email) ? "" : $obj->email;
    $f_name = empty($obj->first_name) ? "" : $obj->first_name;
    $l_name = empty($obj->last_name) ? "" : $obj->last_name;
    if($who == 'girl') {
        $user_id = make_user_id('girls');
        $param = array(
            'id' => $user_id,
            'date_in' => date('Y-m-d'),
            'mail' => $mail,
            'fb_id' => $obj->id
        );
        $query = "INSERT INTO ".prdb."girls SET ";
        db_insert($query, $param);

        foreach($ini_arr['lang_if'] as $lang => $v) {
            $param = array(
                'girl_id' => $user_id,
                'lang' => $lang,
                'f_name' => $f_name,
                'l_name' => $l_name
            );
            $query = "INSERT INTO ".prdb."girls_text SET ";
            db_insert($query, $param);
        }

        save_new_session($user_id, 'girl');
        return 0;
    }
    
    $user_id = make_user_id('men');
    $param = array(
        'id' => $user_id,
        'date_in' => date('Y-m-d'),
        'mail' => $mail,
        'fb_id' => $obj->id,
        'f_name' => $f_name,
        'l_name' => $l_name
    );
    $query = "INSERT INTO ".prdb."men SET ";
    db_insert($query, $param);

    save_new_session($user_id, 'man');
    return 0;
}
?>