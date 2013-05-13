<?php
session_start();
require_once ('config.php');
require_once ('function.php');

$output_install = '';
$install_delete = '';

/* ############
 * # LANGUAGE #
 */############
if (isset($_POST['install_lang'])) {
    $_SESSION['install_lang'] = $_POST['install_lang'];
}
$install_lang = (isset($_SESSION['install_lang']) ? $_SESSION['install_lang'] : 'lang_en.php');
include_once 'lang/' . $install_lang;
$output_install_lang = '';
$option_install_language = scandir('lang/');
foreach ($option_install_language as $val) {
    if ($val !== '.' && $val !== '..') {
        $output_install_lang.= '<option' . ($install_lang == $val ? ' selected="selected"' : '') . '>' . $val . '</option>';
    }
}

/* ##################
 * # CHECK DATABASE #
 */##################
$host       = (isset($_POST['host']) ? $_POST['host'] : 'localhost');
$port       = (isset($_POST['port']) ? $_POST['port'] : '3306');
$login      = (isset($_POST['login']) ? $_POST['login'] : 'xbmc');
$pass       = (isset($_POST['pass']) ? $_POST['pass'] : '');
$database   = (isset($_POST['database']) ? $_POST['database'] : 'movielib');

if (!isset($_POST['host']) && file_exists('db.php')) {
        $output_install.= $lang['ins_db_exist'];
        $install_delete = '<div id="install_delete"><a href="admin.php?option=delete_install">' . $lang['ins_delete_file'] . '</a></div>';
} 
if (isset($_POST['host'])) {
    $conn_install = @mysql_connect($_POST['host'] . ':' . $_POST['port'], $_POST['login'], $_POST['pass']);
    if (!$conn_install) {
        $output_install.= $lang['ins_could_connect'] . ' - ' . mysql_error() . '<br />';
    }
    $sel_install = @mysql_select_db($_POST['database']);
    if (!$sel_install) {
        $output_install.= $lang['ins_could_connect'] . ' - ' . mysql_error() . '<br />';
    } else {
        $output_install.= create_table($mysql_tables);
        $fp = fopen('db.php', 'w');
        $to_write = '<?PHP $mysql_ml = array(\'' . $_POST['host'] . '\', \'' . $_POST['port'] . '\', \'' . $_POST['login'] . '\', \'' . $_POST['pass'] . '\', \'' . $_POST['database'] . '\'); ?>';
        fwrite($fp, $to_write);
    }
    if ($output_install == '') {
        $output_install = $lang['ins_success'];
        $install_delete = '<div id="install_delete"><a href="admin.php?option=delete_install">' . $lang['ins_delete_file'] . '</a></div>';
    }
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>MovieLib - Install</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?PHP echo $output_install ?>
        
        <div id="install_database">
            <form action="install.php" method="post">
                Language file:<BR />
                <select onchange="this.form.submit()" name="install_lang"><?PHP echo $output_install_lang ?></select>
            </form>
            <?PHP echo $install_delete ?>
            <?PHP echo $lang['inst_conn_db'] ?>:
            <form action="install.php" method="post">
                <table id="install_table">
                <tr><td><?PHP echo $lang['inst_server'] ?>:</td><td><input type="text" name="host" value="<?PHP echo $host ?>"></td></tr>
                <tr><td><?PHP echo $lang['inst_port'] ?>:</td><td><input type="text" name="port" value="<?PHP echo $port ?>"></td></tr>
                <tr><td><?PHP echo $lang['inst_login'] ?>:</td><td><input type="text" name="login" value="<?PHP echo $login ?>"></td></tr>
                <tr><td><?PHP echo $lang['inst_pass'] ?>:</td><td><input type="password" name="pass" value="<?PHP echo $pass ?>"></td></tr>
                <tr><td><?PHP echo $lang['ints_database'] ?>:</td><td><input type="text" name="database" value="<?PHP echo $database ?>"></td></tr>
                </table>
                <input type="submit" value="OK">
            </form>
        </div>
    </body>
</html>