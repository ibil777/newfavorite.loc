<?php
function protect_page($only_ping = false) {
    global $ini_arr;

    $res_arr = array('admins' => false, 'guest' => false, 'user' => false);
    if(isset($_SESSION['sess_id']) and isset($_SESSION['sess_s_id']) and isset($_SESSION['sess_code']) and isset($_SESSION['sess_who'])) {
        $is_login = check_admin_session();
        if($is_login) {
            save_session($only_ping, $_SESSION['sess_id'], $_SESSION['sess_s_id'], $_SESSION['sess_who']);
            $res_arr['admins'] = true;
            return $res_arr;
        }
    }
    
    if(isset($_SESSION['sess_u_id']) and isset($_SESSION['sess_u_s_id']) and isset($_SESSION['sess_u_code'])) {
        $is_login = check_user_session();
        if($is_login) {
            save_session($only_ping, $_SESSION['sess_u_id'], $_SESSION['sess_u_s_id'], "user");
            $res_arr['user'] = true;
            return $res_arr;
        }
    }
    
    if(IS_GUEST and !is_search_bot()) {
        if(isset($_COOKIE['jpc_guest'])) {
            check_guest_session();
            $res_arr['guest'] = true;
        }
        else {
            $log_date = date("Y-m-d H:i:s");
            $cookie = md5($log_date.SECRET_CODE.USER_IP);
            setcookie('jpc_guest', $cookie, strtotime('+30 days'), "/");
        }
    }

    return $res_arr;
}
function check_admin_session() {
    global $ini_arr;

    $return = false;
    $param = array(
        'login_id' => $_SESSION['sess_id'],
        'who' => $_SESSION['sess_who'],
        'sessions_id' => $_SESSION['sess_s_id']
    );
    $query = "SELECT ".prdb."login.login, ".prdb."login.name, ".prdb."login.prop_arr,
                ".prdb."sessions.ping_date
              FROM ".prdb."login, ".prdb."sessions
              WHERE ".prdb."login.id = ".prdb."sessions.user_id
                AND ".prdb."login.id = :login_id
                AND ".prdb."sessions.who = :who
                AND ".prdb."sessions.id = :sessions_id
                ";
    $row = db_get_row($query, $param);
    if(sizeof($row)) {
        $_SESSION['sess_login'] = $row['login'];
        $log_date = $row['ping_date'];
        $code = md5($log_date.SECRET_CODE.USER_IP.$_SESSION['sess_who']);
        if($_SESSION['sess_code'] == $code) {
            $return = true;

            $ini_arr['user_data'] = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
            $ini_arr['user_data']['name'] = $row['name'];
            if(!isset($ini_arr['user_data']['priv']))
                $ini_arr['user_data']['priv'] = isset($ini_arr['priv'][$_SESSION['sess_who']]) ? $ini_arr['priv'][$_SESSION['sess_who']] : array();
        }
    }
    
    return $return;
}
function check_user_session() {
    global $ini_arr;

    $return = false;
    $param = array(
        'login_id' => $_SESSION['sess_u_id'],
        'sessions_id' => $_SESSION['sess_u_s_id'],
        'who' => 'user'
    );
    $query = "SELECT ".prdb."users.mail, ".prdb."users.code, ".prdb."users.status, ".prdb."users.f_name, ".prdb."users.l_name,
                ".prdb."users.prop_arr, ".prdb."sessions.ping_date
              FROM ".prdb."users, ".prdb."sessions
              WHERE ".prdb."users.id = ".prdb."sessions.user_id
                AND ".prdb."users.id = :login_id
                AND ".prdb."sessions.who = :who
                AND ".prdb."sessions.id = :sessions_id
                ";
    $row = db_get_row($query, $param);
    if(sizeof($row)) {
        $_SESSION['sess_u_login'] = $row['mail'];
        $log_date = $row['ping_date'];
        $code = md5($log_date.SECRET_CODE.USER_IP."user");
        if($_SESSION['sess_u_code'] == $code) {
            $return = true;

            $ini_arr['user_data'] = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
            $ini_arr['user_data']['f_name'] = $row['f_name'];
            $ini_arr['user_data']['l_name'] = $row['l_name'];
            $ini_arr['user_data']['code'] = $row['code'];
            $ini_arr['user_data']['status'] = $row['status'];

            if(!isset($_SESSION['sess_cart']) and isset($ini_arr['user_data']['cart'])) {
                $_SESSION['sess_cart'] = isset($ini_arr['user_data']['cart']['items']) ? $ini_arr['user_data']['cart']['items'] : array();
                $_SESSION['sess_cart_prices'] = isset($ini_arr['user_data']['cart']['prices']) ? $ini_arr['user_data']['cart']['prices'] : array();
                $_SESSION['sess_cart_sum'] = calc_sess_cart_sum();
                $_SESSION['sess_cart_num'] = array_sum($_SESSION['sess_cart']);
            }
        }
    }
    
    return $return;
}
function check_guest_session() {
    global $ini_arr;

    $param = array(
        'code' => $_COOKIE['jpc_guest']
    );
    $query = "SELECT ".prdb."guests.id, ".prdb."guests.prop_arr
              FROM ".prdb."guests
              WHERE ".prdb."guests.code = :code";
    $row = db_get_row($query, $param);
    if(sizeof($row)) {
        $_SESSION['sess_g_id'] = $row['id'];
        $ini_arr['user_data'] = empty($row['prop_arr']) ? array() : unserialize($row['prop_arr']);
        
        $param = array(
            'ip' => USER_IP,
            'active_date' => date('Y-m-d H:i:s')
        );
        $param_where = array('id' => $row['id']);
        $query = "UPDATE ".prdb."guests SET [QUERY_SET] WHERE id = :id";
        db_update($query, $param, $param_where);
    }
    else {
        $param = array(
            'code' => $_COOKIE['jpc_guest'],
            'date_in' => date('Y-m-d H:i:s'),
            'ip' => USER_IP,
            'active_date' => date('Y-m-d H:i:s')
        );
        $query = "INSERT INTO ".prdb."guests SET ";
        $_SESSION['sess_g_id'] = db_insert($query, $param, 'insert_id');
        $ini_arr['user_data'] = array();
    }
    
    if(!isset($_SESSION['sess_cart']) and isset($ini_arr['user_data']['cart'])) {
        $_SESSION['sess_cart'] = isset($ini_arr['user_data']['cart']['items']) ? $ini_arr['user_data']['cart']['items'] : array();
        $_SESSION['sess_cart_prices'] = isset($ini_arr['user_data']['cart']['prices']) ? $ini_arr['user_data']['cart']['prices'] : array();
        $_SESSION['sess_cart_sum'] = calc_sess_cart_sum();
        $_SESSION['sess_cart_num'] = array_sum($_SESSION['sess_cart']);
    }
}
function check_cookie_session() {
    global $ini_arr;
    
    $param = array('code' => $_COOKIE['jpc_user']);
    $query = "SELECT id, user_id, who
              FROM ".prdb."sessions
              WHERE (who = 'girl' OR who='man')
                AND code = :code";
    $row = db_get_row($query, $param);
    if(!sizeof($row))
        return false;
    
    if($row['who'] == 'girl') {
        $_SESSION['sess_g_id'] = $row['user_id'];
        $_SESSION['sess_g_s_id'] = $row['id'];
        $param = array(
            'login_id' => $_SESSION['sess_g_id']
        );
        $query = "SELECT ".prdb."girls.login, ".prdb."girls_text.f_name, ".prdb."girls.prop_arr
                  FROM ".prdb."girls, ".prdb."girls_text
                  WHERE ".prdb."girls.id = ".prdb."girls_text.girl_id
                    AND ".prdb."girls.id = :login_id
                    ";
        $row_1 = db_get_row($query, $param);
        if(!sizeof($row_1))
            return false;

        $_SESSION['sess_g_login'] = $row_1['login'];
        $ini_arr['girl_data'] = empty($row_1['prop_arr']) ? array() : unserialize($row_1['prop_arr']);
        $ini_arr['girl_data']['name'] = $row_1['f_name'];
    }
    elseif($row['who'] == 'man') {
        $_SESSION['sess_m_id'] = $row['user_id'];
        $_SESSION['sess_m_s_id'] = $row['id'];
        $param = array(
            'login_id' => $_SESSION['sess_m_id']
        );
        $query = "SELECT ".prdb."men.login, ".prdb."men.code, ".prdb."men.status, ".prdb."men.f_name, ".prdb."men.fin, ".prdb."men.prop_arr
                  FROM ".prdb."men
                  WHERE ".prdb."men.id = :login_id
                    ";
        $row_1 = db_get_row($query, $param);
        if(!sizeof($row_1))
            return false;

        $_SESSION['sess_m_login'] = $row_1['login'];
        $ini_arr['man_data'] = empty($row_1['prop_arr']) ? array() : unserialize($row_1['prop_arr']);
        $ini_arr['man_data']['name'] = $row_1['f_name'];
        $ini_arr['man_data']['code'] = $row_1['code'];
        $ini_arr['man_data']['status'] = $row_1['status'];
        $ini_arr['man_data']['fin'] = $row_1['fin'];
    }
    
    return $row['who'];
}
function save_session($only_ping, $user_id, $user_s_id, $user_who) {
    global $ini_arr;
    
    $log_date = date("Y-m-d H:i:s");
    $param = array(
        'ping_date' => $log_date
    );
    if(!$only_ping)
        $param['active_date'] = $log_date;
    $param_where = array(
        'id' => $user_s_id,
        'user_id' => $user_id
    );
    $query = "UPDATE ".prdb."sessions SET
                [QUERY_SET]
              WHERE id = :id AND user_id = :user_id";
    db_update($query, $param, $param_where);
    
    if($user_who == 'user')
        $_SESSION['sess_u_code'] = md5($log_date.SECRET_CODE.USER_IP.$user_who);
    else
        $_SESSION['sess_code'] = md5($log_date.SECRET_CODE.USER_IP.$user_who);
}
function save_new_session($user_id, $who) {
    global $ini_arr;
    
    $log_date = date("Y-m-d H:i:s");

    $param = array(
        'user_id' => $user_id,
        'who' => $who,
        'log_date' => $log_date,
        'active_date' => $log_date,
        'ping_date' => $log_date,
        'ip' => USER_IP
    );

    if(isset($_REQUEST['flag_rem'])) {
        $cookie = md5($log_date.SECRET_CODE.$user_id);
        setcookie('jpc_user', $cookie, strtotime('+30 days'), "/");
        $param['code'] = $cookie;
    }

    $query = "INSERT INTO ".prdb."sessions SET ";
    $s_id = db_insert($query, $param, 'insert_id');
    $_SESSION['sess_code'] = md5($log_date.SECRET_CODE.USER_IP.$who);
    $_SESSION['sess_id'] = $user_id;
    $_SESSION['sess_who'] = $who;
    $_SESSION['sess_s_id'] = $s_id;
}
function user_logout() {
    global $ini_arr;
    
    if(defined('SESS_ID') and defined('SESS_S_ID')) {
        $param = array(
            'id' => SESS_S_ID,
            'user_id' => SESS_ID
        );
        $query = "DELETE FROM ".prdb."sessions
                  WHERE id = :id AND user_id = :user_id";
        db_delete($query, $param);
    }

    $_SESSION = array();
    
    if(isset($_COOKIE['jpc_user']))
        setcookie('jpc_user', '', 1, "/");
}
?>