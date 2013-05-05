<?php
session_start();
require_once 'config.php';

// Check password
if (isset($_GET['login']) && $_GET['login'] === 'admin') {
    if (isset($_POST['admin_pass']) && md5($_POST['admin_pass']) === $set_admin_panel_pass) {
        $_SESSION['logged_admin'] = true;
        header('Location:admin.php');
        die();
    } else {
        $login_info = $lang['l_pass_admin'];
        $input_action = 'login.php?login=admin';
        $input_name = 'admin_pass';
    }
} else {
    if (isset($_POST['pass']) && md5($_POST['pass']) === $set_protect_site_pass) {
        $_SESSION['logged'] = true;
        header('Location:index.php');
        die();
    } else{
        $login_info = $lang['l_pass'];
        $input_action = 'login.php';
        $input_name = 'pass';
    }
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set_site_name ?> - <?PHP echo $lang['l_html_login'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="pass">
            <?PHP echo $login_info ?>
            <form action="<?PHP echo $input_action ?>" method="post">
                <input type="password" name="<?PHP echo $input_name ?>">
                <input type="submit" value="OK">
            </form>
        </div>
    </body>
</html>