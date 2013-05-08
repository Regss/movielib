<?php
require_once ('config.php');
require_once ('function.php');

$output_install = '';
$install_delete = '';

/* ##################
 * # CHECK DATABASE #
 */##################
$host       = (isset($_POST['host']) ? $_POST['host'] : 'localhost');
$port       = (isset($_POST['port']) ? $_POST['port'] : '3306');
$login      = (isset($_POST['login']) ? $_POST['login'] : 'xbmc');
$pass       = (isset($_POST['pass']) ? $_POST['pass'] : '');
$database   = (isset($_POST['database']) ? $_POST['database'] : 'movielib');

if (!isset($_POST['host']) && file_exists('db.php')) {
        $output_install.= 'Database configuration file alerdy exists. Continue will overwrite the existing settings. If you successfull configured database please delete install.php file.';
        $install_delete = '<div id="install_delete"><a href="admin.php?option=delete_install">DELETE INSTALL FILE</a></div>';
} 
if (isset($_POST['host'])) {
    $conn_install = @mysql_connect($_POST['host'] . ':' . $_POST['port'], $_POST['login'], $_POST['pass']);
    if (!$conn_install) {
        $output_install.= 'Could not connect to database - ' . mysql_error() . '<br />';
    }
    $sel_install = @mysql_select_db($_POST['database']);
    if (!$sel_install) {
        $output_install.= 'Could not select database - ' . mysql_error() . '<br />';
    } else {
        $output_install.= create_table($mysql_tables);
        $fp = fopen('db.php', 'w');
        $to_write = '<?PHP $mysql_ml = array(\'' . $_POST['host'] . '\', \'' . $_POST['port'] . '\', \'' . $_POST['login'] . '\', \'' . $_POST['pass'] . '\', \'' . $_POST['database'] . '\'); ?>';
        fwrite($fp, $to_write);
        
        // Check tables in database
        $table_sql = 'SHOW TABLES';
        $table_result = mysql_query($table_sql);
        while ($table = mysql_fetch_array($table_result)) {
            $table_check[] = $table[0];
        }
        foreach ($mysql_tables as $table_val) {
            if (!in_array($table_val, $table_check)) {
                $output_install.= 'Table: ' . $table_val . ' not exist<br />';
            }
        }    
    }
    if ($output_install == '') {
        $output_install = 'Instalation successfull, delete install.php file, and go to admin panel change default password.';
        $install_delete = '<div id="install_delete"><a href="admin.php?option=delete_install">DELETE INSTALL FILE</a></div>';
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
            <?PHP echo $install_delete ?>
            Connection to database:
            <form action="install.php" method="post">
                <table id="install_table">
                <tr><td>Server:</td><td><input type="host" name="host" value="<?PHP echo $host ?>"></td></tr>
                <tr><td>Port:</td><td><input type="port" name="port" value="<?PHP echo $port ?>"></td></tr>
                <tr><td>Login:</td><td><input type="login" name="login" value="<?PHP echo $login ?>"></td></tr>
                <tr><td>Password:</td><td><input type="pass" name="pass" value="<?PHP echo $pass ?>"></td></tr>
                <tr><td>Database:</td><td><input type="database" name="database" value="<?PHP echo $database ?>"></td></tr>
                </table>
                <input type="submit" value="Check">
            </form>
        </div>
    </body>
</html>