<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// connect to database
connect($mysql_ml);

$set = get_settings($mysql_ml, $mysql_tables, $settings_name);
require_once 'lang/' . $set['language'];

if (!isset($_GET['login'])) {
    header('Location:login.php?login=user');
    die();
}

// Logout
if ($_GET['login'] === 'admin_logout') {
    unset($_SESSION['logged_admin']);
    header('Location:index.php');
    die();
}

// Check password
$output = '';

// admin
if ($_GET['login'] === 'admin') {
    $admin_check_sql = 'SELECT * FROM ' . $mysql_tables[2] . ' WHERE login = "admin"';
    $admin_check_result = mysql_query($admin_check_sql);
    while ($admin_check = mysql_fetch_array($admin_check_result)) {
        if (isset($_POST['admin_pass']) && md5($_POST['admin_pass']) == $admin_check['password']) {
            $_SESSION['logged_admin'] = true;
            header('Location:admin.php');
            die();
        } else {
            if (isset($_POST['admin_pass'])) {
                $output = $lang['l_wrong_pass'];
            }
            $login_info = $lang['l_pass_admin'];
            $input_action = 'login.php?login=admin';
            $input_name = 'admin_pass';
        }
    }
}

// user
if ($_GET['login'] === 'user') {
    $user_check_sql = 'SELECT * FROM ' . $mysql_tables[2] . ' WHERE login = "user"';
    $user_check_result = mysql_query($user_check_sql);
    while ($user_check = mysql_fetch_array($user_check_result)) {
        if (isset($_POST['pass']) && md5($_POST['pass']) == $user_check['password']) {
            $_SESSION['logged'] = true;
            header('Location:index.php');
        } else {
            if (isset($_POST['pass'])) {
                $output = $lang['l_wrong_pass'];
            }
            $login_info = $lang['l_pass'];
            $input_action = 'login.php?login=user';
            $input_name = 'pass';
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set['site_name'] ?> - <?PHP echo $lang['l_html_login'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="pass">
            <?PHP echo $output ?><br />
            <?PHP echo $login_info ?>
            <form action="<?PHP echo $input_action ?>" method="post">
                <input type="password" name="<?PHP echo $input_name ?>">
                <input type="submit" value="OK">
            </form>
        </div>
    </body>
</html>