<?PHP
session_start();
require('config.php');
require('function.php');

// connect to database
connect($mysql_ml);

$set = get_settings($mysql_ml, $mysql_tables);
require('lang/' . $set['language'] . '/lang.php');

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
        if (isset($_POST['movielib_admin_pass']) && md5($_POST['movielib_admin_pass']) == $admin_check['password']) {
            $_SESSION['logged_admin'] = true;
            header('Location:admin.php');
            die();
        } else {
            if (isset($_POST['movielib_admin_pass'])) {
                $output = '<div class="panel_info">' . $lang['l_wrong_pass'] . '</div>';
            }
            $login_info = $lang['l_pass_admin'];
            $input_action = 'login.php?login=admin';
            $input_name = 'movielib_admin_pass';
        }
    }
}

// user
if ($_GET['login'] === 'user') {
    $user_check_sql = 'SELECT * FROM ' . $mysql_tables[2] . ' WHERE login = "user"';
    $user_check_result = mysql_query($user_check_sql);
    while ($user_check = mysql_fetch_array($user_check_result)) {
        if (isset($_POST['movielib_pass']) && md5($_POST['movielib_pass']) == $user_check['password']) {
            $_SESSION['logged'] = true;
            header('Location:index.php');
        } else {
            if (isset($_POST['movielib_pass'])) {
                $output = '<div class="panel_info">' . $lang['l_wrong_pass'] . '</div>';
            }
            $login_info = $lang['l_pass'];
            $input_action = 'login.php?login=user';
            $input_name = 'movielib_pass';
        }
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set['site_name'] ?> - <?PHP echo $lang['l_html_login'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <link type="image/x-icon" href="css/<?PHP echo $set['theme'] ?>/img/icon.ico" rel="icon" media="all" />
        <link type="text/css" href="css/<?PHP echo $set['theme'] ?>/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
    </head>
    <body>
        <?PHP echo $output ?>
        <div class="container_login">
            <div class="bold orange"><?PHP echo $login_info ?></div>
            <form action="<?PHP echo $input_action ?>" method="post"><br />
                <input type="password" name="<?PHP echo $input_name ?>"><br /><br />
                <input type="submit" value="OK">
            </form>
        </div>
    </body>
</html>