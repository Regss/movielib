<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

require('config.php');
require('function.php');

/* ############
 * # LANGUAGE #
 */############
if (isset($_POST['install_lang'])) {
    $_SESSION['install_lang'] = $_POST['install_lang'];
}

// check user language from browser
if (!isset($_SESSION['install_lang'])) {
    $install_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (!file_exists('lang/' . $install_lang . '/lang.php')) {
        $install_lang = 'en';
    }
    $_SESSION['install_lang'] = $install_lang;
} else {
    $install_lang = $_SESSION['install_lang'];
}

// check dir for language file
$output_install_lang = '';
$option_install_language = scandir('lang/');
foreach ($option_install_language as $val) {
    if (file_exists('lang/' . $val . '/lang.php')) {
        
        if (array_key_exists($val, $language)) {
            $lang_title = $language[$val];
        } else {
            $lang_title = $val;
        }
        $output_install_lang.= '<option' . ($val == $install_lang ? ' selected="selected"' : '') . ' value="' . $val . '">' . $lang_title . '</option>';
    }
}
require('lang/' . $install_lang . '/lang.php');

switch ($option) {
    
    case 'license':
        /* ###########
         * # LICENSE #
         */###########
        $license_file = 'LICENSE.txt';
        if (file_exists($license_file)) {
            $fp = fopen($license_file, 'r');
            $license = fread($fp, 88192);
        } else {
            $license = 'No license file.';
        }
        $title = $lang['ins_license'];
        $output_panel = '
        <form>
            <textarea class="textera" readonly="readonly">' . $license . '</textarea>
        </form>
        <a class="box" href="install.php?option=database">' . $lang['ins_accept'] . '</a>
        ';
        break;
        
    case 'database':
        /* ################
         * # SET DATABASE #
         */################
         if (file_exists('db.php')) {
            $output_panel_info.= $lang['ins_db_exist'] . '<br />';
        }
        $title = $lang['inst_conn_db'];
        $output_panel = '
            <form action="install.php?option=success" method="post">
                <table>
                    <tr><td>' . $lang['inst_server'] . ':</td><td><input type="text" name="host" value="localhost"></td></tr>
                    <tr><td>' . $lang['inst_port'] . ':</td><td><input type="text" name="port" value="3306"></td></tr>
                    <tr><td>' . $lang['inst_login'] . ':</td><td><input type="text" name="login" value="xbmc"></td></tr>
                    <tr><td>' . $lang['inst_pass'] . ':</td><td><input type="password" name="pass" value=""></td></tr>
                    <tr><td>' . $lang['inst_database'] . ':</td><td><input type="text" name="database" value="movielib"></td></tr>
                </table>
            <input id="ok" type="submit" value="OK" />
            </form>';
        break;
        
    case 'success':
        /* ##################
         * # CHECK DATABASE #
         */##################
        $conn_install = @mysql_connect($_POST['host'] . ':' . $_POST['port'], $_POST['login'], $_POST['pass']);
        if (!$conn_install) {
            die($lang['ins_could_connect'] . ' - ' . mysql_error());
        }
        $create_db = @mysql_query('CREATE DATABASE ' . $_POST['database']);
        $sel_install = @mysql_select_db($_POST['database']);
        if (!$sel_install) {
            die($lang['ins_could_connect'] . ' - ' . mysql_error());
        }
        create_table($mysql_tables, $tables, $lang, $version, 1);
        $fp = fopen('db.php', 'w');
        $to_write = '<?PHP $mysql_ml = array(\'' . $_POST['host'] . '\', \'' . $_POST['port'] . '\', \'' . $_POST['login'] . '\', \'' . $_POST['pass'] . '\', \'' . $_POST['database'] . '\'); ?>';
        fwrite($fp, $to_write);
        fclose($fp);
        $title = $lang['ins_finished'];
        $output_panel = '<a class="box" href="admin.php?option=delete_install">' . $lang['ins_admin'] . '</a>';
        
        // delete session var
        $_SESSION = array();
        
        break;
        
    default:
        /* ##########
         * # README #
         */##########
        
        $readme_file = 'lang/' . $_SESSION['install_lang'] . '/readme';
        if (!file_exists($readme_file)) {
            $readme_file = 'README.txt';
        }
        $fp = fopen($readme_file, 'r');
        $readme = fread($fp, 88192);
        $title = $lang['ins_lang_file'];
        $output_panel = '
            <form action="install.php" method="post">
                <select onchange="this.form.submit()" name="install_lang">' . $output_install_lang . '</select>
            </form>
            <form>
                <textarea readonly="readonly">' . $readme . '</textarea>
            </form>
            <a class="box" href="install.php?option=license">' . $lang['ins_next'] . '</a>
        ';
        break;
        
}

/* ##############
 * # PANEL INFO #
 */##############
if ($output_panel_info !== '') {
    $output_panel_info = '<div class="panel_info">' . $output_panel_info . '</div>';
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>MovieLib - <?PHP echo $lang['ins_title'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <link type="image/x-icon" href="admin/img/icon.ico" rel="icon" media="all" />
        <link type="text/css" href="admin/css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
    </head>
    <body>
        <?PHP echo $output_panel_info ?>
        <div class="container_install">
            <div class="title"><?PHP echo $title ?></div>
            <?PHP echo $output_panel ?>
        </div>
    </body>
</html>