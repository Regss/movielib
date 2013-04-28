<?php
session_start();
require_once 'config.php';

// Sprawdzenie hasła i dostęp do menu
if (isset($_POST['pass']) && md5($_POST['pass']) === $pass) {
    $_SESSION['logged'] = true;
    header('Location:index.php');
    die;
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $site_name ?> - <?PHP echo $lang['l_html_login'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="pass">
            <form action="login.php" method="post">
                <input type="password" name="pass">
                <input type="submit" value="OK">
            </form>
        </div>
    </body>
</html>