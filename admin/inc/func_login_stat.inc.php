<?

function log_user_login($login,$time) {
    global $db;
    $q = 'insert into dwLoginStat set IDClientUser ='.
        ' ( select IDClientUser from dwClientUsers where IDUser ='.
        ' ( select ID_User from dwUsers where UserLogin="'.$login.'")),'.
        'LoginTime = '.$time;
    $db->Query($q);
}

?>
