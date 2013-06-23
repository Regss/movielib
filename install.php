<?PHP
session_start();
require_once ('config.php');
require_once ('function.php');

/* ############
 * # LANGUAGE #
 */############
if (isset($_POST['install_lang'])) {
    $_SESSION['install_lang'] = $_POST['install_lang'];
}

// check user language from browser
if (!isset($_SESSION['install_lang'])) {
    $install_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (!file_exists('lang/lang_' . $install_lang . '.php')) {
        $install_lang = 'en';
    }
    $_SESSION['install_lang'] = $install_lang;
} else {
    $install_lang = $_SESSION['install_lang'];
}
include_once 'lang/lang_' . $install_lang . '.php';

$output_install_lang = '';

// check dir for language file
$option_install_language = scandir('lang/');
foreach ($option_install_language as $val) {
    if ((substr($val, 0, 4) == 'lang') && (substr($val, -3) == 'php')) {
        $fp = fopen('lang/' . $val, 'r');
        for ($i=0;$i<3;$i++) {
            $line = fgets($fp);
        }
        preg_match('/([a-zA-Z]+)/', $line, $lang_title);
        preg_match('/_([a-zA-Z]+)\./', $val, $lang_id);
        $output_install_lang.= '<option' . ($val == 'lang_' . $install_lang . '.php' ? ' selected="selected"' : '') . ' value="' . $lang_id[1] . '">' . ucfirst(strtolower($lang_title[1])) . '</option>';
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

$install_delete = '';

if (!isset($_POST['host']) && file_exists('db.php')) {
        $output_panel_info.= $lang['ins_db_exist'];
        $install_delete = '<a id="install_delete" href="admin.php?option=delete_install">' . $lang['ins_delete_file'] . '</a><br /><br />';
}
if (isset($_POST['host'])) {
    $conn_install = @mysql_connect($_POST['host'] . ':' . $_POST['port'], $_POST['login'], $_POST['pass']);
    if (!$conn_install) {
        $output_panel_info.= $lang['ins_could_connect'] . ' - ' . mysql_error() . '<br />';
    }
    $sel_install = @mysql_select_db($_POST['database']);
    if (!$sel_install) {
        $output_panel_info.= $lang['ins_could_connect'] . ' - ' . mysql_error() . '<br />';
    } else {
        $output_panel_info.= create_table($mysql_tables, $lang) . $lang['ins_success'] . '. Redirect to admin on <span id="sec">5</span> sec.<script>$(redirectAdmin);</script>';
        $fp = fopen('db.php', 'w');
        $to_write = '<?PHP $mysql_ml = array(\'' . $_POST['host'] . '\', \'' . $_POST['port'] . '\', \'' . $_POST['login'] . '\', \'' . $_POST['pass'] . '\', \'' . $_POST['database'] . '\'); ?>';
        fwrite($fp, $to_write);
        $install_delete = '<a id="install_delete" href="admin.php?option=delete_install">' . $lang['ins_delete_file'] . '</a><br /><br />';
    }
}
if ($output_panel_info !== '') {
    $output_panel_info = '<div id="panel_info">' . $output_panel_info . '</div>';
}

/* ##########
 * # README #
 */##########
$readme_file = 'lang/lang_' . $_SESSION['install_lang'] . '.readme';
if (!file_exists($readme_file)) {
    $readme_file = 'README.txt';
}
$fp = fopen($readme_file, 'r');
$readme = fread($fp, 88192);

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

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>MovieLib - <?PHP echo $lang['ins_title'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link href="css/default/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
        <script>
            // auto redirect to admin if installation successfull
            var i = 5;
            function redirectAdmin() {
                setInterval(function() {
                    if(i > 0) {
                        i--;
                        $('#sec').html(i);
                    } else {
                        window.location.href = 'admin.php?option=delete_install';
                    }
                }, 1000);
            }
        </script>
        <style> 
        <!--
            #install_container {
                width: 300px;
                margin: auto;
                background-color: #111;
                border: 1px solid #222;
                margin-top: 10px;
                padding-bottom: 10px;
                padding-top: 10px;
                text-align: center;
            }
            #install_container input, #install_container select {
                font-family: sans-serif; /* IE fix input pass width */
                background-color: #222;
                color: #DDD;
                border: 1px solid #444;
            }
            #install_delete {
                color: #33ff00;
                background-color: #000;
                font-weight: bold;
                display: inline-block;
                border: 1px solid #444;
                padding: 3px 5px 3px 5px;
                margin: 5px 0px 5px 0px;
            }
            #install_table {
                display: block;
                border-collapse: collapse;
                background-color: #000;
                border: 1px solid #444;
                margin: 10px;
            }
            #install_table td {
                padding: 5px 5px 5px 5px;
                text-align: right;
            }
            #install_license_container {
                width: 600px;
                height: 200px;
                margin: auto;
                margin-top: 10px;
                background-color: #111;
                border: 1px solid #222;
                padding: 10px;
            }
            #install_license {
                width: 600px;
                height: 200px;
                background-color: #000;
                color: #fff;
                border: 1px solid #333;
                margin: auto;
                resize: none;
            }
            #install_readme_container {
                width: 600px;
                height: 140px;
                margin: auto;
                margin-top: 10px;
                background-color: #111;
                border: 1px solid #222;
                padding: 10px;
            }
            #install_readme {
                width: 600px;
                height: 140px;
                background-color: #000;
                color: #fff;
                border: 1px solid #333;
                margin: auto;
                resize: none;
            }
        -->
        </style>
    </head>
    <body>
        <?PHP echo $output_panel_info ?>
        
        <div id="install_container">
            <form action="install.php" method="post">
                <?PHP echo $lang['ins_lang_file'] ?>:<BR /><BR />
                <select onchange="this.form.submit()" name="install_lang"><?PHP echo $output_install_lang ?></select><BR /><BR />
            </form>
            <?PHP echo $install_delete ?>
            <?PHP echo $lang['inst_conn_db'] ?>:
            <form action="install.php" method="post">
                <table id="install_table">
                <tr><td><?PHP echo $lang['inst_server'] ?>:</td><td><input type="text" name="host" value="<?PHP echo $host ?>"></td></tr>
                <tr><td><?PHP echo $lang['inst_port'] ?>:</td><td><input type="text" name="port" value="<?PHP echo $port ?>"></td></tr>
                <tr><td><?PHP echo $lang['inst_login'] ?>:</td><td><input type="text" name="login" value="<?PHP echo $login ?>"></td></tr>
                <tr><td><?PHP echo $lang['inst_pass'] ?>:</td><td><input type="password" name="pass" value="<?PHP echo $pass ?>"></td></tr>
                <tr><td><?PHP echo $lang['inst_database'] ?>:</td><td><input type="text" name="database" value="<?PHP echo $database ?>"></td></tr>
                </table>
                <input id="ok" type="submit" value="OK" />
            </form>
        </div>
        <div id="install_readme_container">
            <form>
                <textarea id="install_readme" readonly="readonly"><?PHP echo $readme ?></textarea>
            </form>
        </div>
        <div id="install_license_container">
            <form>
                <textarea id="install_license" readonly="readonly"><?PHP echo $license ?></textarea>
            </form>
        </div>
    </body>
</html>