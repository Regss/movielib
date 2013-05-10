<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_ml, $mysql_tables, $settings_name);
require_once 'lang/' . $set['language'];

if (isset($_GET['option']) && $_GET['option'] == 'delete_install') {
    // unlink('install.php');
    header('Location:admin.php');
    die();
}
if (file_exists('install.php') or !file_exists('db.php')) {
    // header('Location:install.php');
    // die();
}

// Check admin password
if ($_SESSION['logged_admin'] !== true) {
    header('Location:login.php?login=admin');
    die();
}

/* #############
 * # CHECK DIR #
 */#############
foreach ($dir_assoc as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir);
    }
}

$output_panel = '';

/* ###########
 * # OVERALL #
 */###########
if (!isset($_GET['option'])) {
    $overall_sql = 'SELECT play_count FROM ' . $mysql_tables[0];
    $overall_result = mysql_query($overall_sql);
    $overall_watched = 0;
    while ($overall = mysql_fetch_array($overall_result)) {
        if ($overall['play_count'] !== NULL) {
            $overall_watched++;
        }
    }
    $overall_all = mysql_num_rows($overall_result);
    $overall_unwatched = $overall_all - $overall_watched;
    $output_panel = $lang['a_all'] . ': ' . $overall_all . ' ' . $lang['a_watched'] . ': ' . $overall_watched . ' ' . $lang['a_unwatched'] . ': ' . $overall_unwatched;
}

/* ################
 * # CREATE CACHE #
 */################
if (isset($_SESSION['id_to_create']) && count($_SESSION['id_to_create']) > 0) {
    foreach ($_SESSION['id_to_create'] as $key => $id) {
        $list_sql = 'SELECT id, poster FROM ' . $mysql_tables[0] . ' WHERE id = ' . $id;
        $list_result = mysql_query($list_sql);
        while ($list = mysql_fetch_array($list_result)) {
            gd_convert($list['id'], $list['poster']);
            unset($_SESSION['id_to_create'][$key]);
            header('Location:admin.php');
        }
    }
}
if (isset($_GET['option']) && $_GET['option'] == 'create_cache') {
    $list_sql = 'SELECT id, poster FROM ' . $mysql_tables[0];
    $list_result = mysql_query($list_sql);
    while ($list = mysql_fetch_array($list_result)) {
        if (!file_exists('cache/' . $list['id'] . '.jpg')) {
            $id_to_create[] = $list['id'];
        }
    }
    $_SESSION['id_to_create'] = $id_to_create;
    header('Location:admin.php');
}

/* #################
 * # REBUILD CACHE #
 */#################
if (isset($_GET['option']) && $_GET['option'] == 'rebuild_cache') {
    $dir_path = 'cache/';
    $dir = opendir($dir_path);
    while($file = readdir($dir)) {
        if($file != '.' && $file != '..') {
            unlink($dir_path.'/'.$file);
        }
    }
    header('Location:admin.php?option=create_cache');
}

/* ##############
 * # MOVIE LIST #
 */##############
if (isset($_GET['option']) && $_GET['option'] == 'list') {
    $list_sql = 'SELECT id, title, poster, play_count FROM ' . $mysql_tables[0] . ' ORDER BY title';
    $list_result = mysql_query($list_sql);
    $output_panel = '<table id="admin_table_movie">';
    while ($list = mysql_fetch_array($list_result)) {
        if (file_exists('cache/' . $list['id'] . '.jpg')) {
            $poster_exist = '<img src="img/watched.png">';
        } else {
            $poster_exist = '<img src="img/delete.png">';
        }
        $output_panel.= '<tr><td>' . $list['id'] . '</td><td>' . $list['title'] . '</td><td><a href="' . $list['poster'] . '" target="_blank">' . $list['poster'] . '</a></td><td>'  . $poster_exist . '</td></tr>';
    }
    $output_panel.= '</table>';
}

/* ############
 * # SETTINGS #
 */############
if (isset($_GET['option']) && $_GET['option'] == 'settings') {
    
    // set lenguage input
    $output_lang = '';
    $option_language = scandir('lang/');
    foreach ($option_language as $val) {
        if ($val !== '.' && $val !== '..') {
            $output_lang.= '<option' . ($set['language'] == $val ? ' selected="selected"' : '') . '>' . $val . '</option>';
        }
    }
    
    $output_mode = '';
    $output_panel_top = '';
    $output_watched_status = '';
    $output_overall_panel = '';
    $output_protect_site = '';
    $output_per_page = '';
    $output_recently_limit = '';
    $output_random_limit = '';
    $output_last_played_limit = '';
    $output_top_rated_limit = '';
    
    $mode = array(0 => 'OFF', 1 => 'ON');
    foreach ($mode as $key => $val) {
            // set mode input
            $output_mode.= '<option value="' . $key . '" ' . ($set['mode'] == $key ? ' selected="selected"' : '') . '>' . $val . '</option>';
            // set panel_top input
            $output_panel_top.= '<option value="' . $key . '" ' . ($set['panel_top'] == $key ? ' selected="selected"' : '') . '>' . $val . '</option>';
            // set wached status input
            $output_watched_status.= '<option value="' . $key . '" ' . ($set['watched_status'] == $key ? ' selected="selected"' : '') . '>' . $val . '</option>';
            // set overall panel input
            $output_overall_panel.= '<option value="' . $key . '" ' . ($set['overall_panel'] == $key ? ' selected="selected"' : '') . '>' . $val . '</option>';
            // set protect site input
            $output_protect_site.= '<option value="' . $key . '" ' . ($set['protect_site'] == $key ? ' selected="selected"' : '') . '>' . $val . '</option>';
    }
    
    $quantity = array(5, 10, 20, 50, 100);
    foreach ($quantity as $val) {
        // set per page input
        $output_per_page.= '<option' . ($set['per_page'] == $val ? ' selected="selected"' : '') . '>' . $val . '</option>';
        // set recently limit
        $output_recently_limit.= '<option' . ($set['recently_limit'] == $val ? ' selected="selected"' : '') . '>' . $val . '</option>';
        // set random limit
        $output_random_limit.= '<option' . ($set['random_limit'] == $val ? ' selected="selected"' : '') . '>' . $val . '</option>';
        // set last played limit
        $output_last_played_limit.= '<option' . ($set['last_played_limit'] == $val ? ' selected="selected"' : '') . '>' . $val . '</option>';
        // set top rated limit
        $output_top_rated_limit.= '<option' . ($set['top_rated_limit'] == $val ? ' selected="selected"' : '') . '>' . $val . '</option>';
        
    }

    // output form
    $output_panel.= '<form action="admin.php?option=settings_save" method="post"><table id="admin_table_movie">
                <tr><td>' . $lang['a_mode'] . '</td><td><select name="mode">' . $output_mode . '</select></td></tr>
                <tr><td>' . $lang['a_site_name'] . '</td><td><input type="text" name="site_name" value="' . $set['site_name'] . '"></td></tr>
                <tr><td>' . $lang['a_language'] . '</td><td><select name="language">' . $output_lang . '</select></td></tr>
                <tr><td>' . $lang['a_per_page'] . '</td><td><select name="per_page">' . $output_per_page . '</select></td></tr>
                <tr><td>' . $lang['a_recently_limit'] . '</td><td><select name="recently_limit">' . $output_recently_limit . '</select></td></tr>
                <tr><td>' . $lang['a_random_limit'] . '</td><td><select name="random_limit">' . $output_random_limit . '</select></td></tr>
                <tr><td>' . $lang['a_last_played_limit'] . '</td><td><select name="last_played_limit">' . $output_last_played_limit . '</select></td></tr>
                <tr><td>' . $lang['a_top_rated_limit'] . '</td><td><select name="top_rated_limit">' . $output_top_rated_limit . '</select></td></tr>
                <tr><td>' . $lang['a_sync_time'] . '</td><td><input type="text" name="sync_time" value="' . $set['sync_time'] . '"></td></tr>
                <tr><td>' . $lang['a_panel_top_time'] . '</td><td><input type="text" name="panel_top_time" value="' . $set['panel_top_time'] . '"></td></tr>
                <tr><td>' . $lang['a_panel_top'] . '</td><td><select name="panel_top">' . $output_panel_top . '</select></td></tr>
                <tr><td>' . $lang['a_watched_status'] . '</td><td><select name="watched_status">' . $output_watched_status . '</select></td></tr>
                <tr><td>' . $lang['a_overall_panel'] . '</td><td><select name="overall_panel">' . $output_overall_panel . '</select></td></tr>
                <tr><td>' . $lang['a_protect_site']  . '</td><td><select name="protect_site">' . $output_protect_site . '</select></td></tr>
                <tr><td>' . $lang['a_mysql_host_xbmc'] . '</td><td><input type="text" name="mysql_host_xbmc" value="' . $set['mysql_host_xbmc'] . '"></td></tr>
                <tr><td>' . $lang['a_mysql_port_xbmc'] . '</td><td><input type="text" name="mysql_port_xbmc" value="' . $set['mysql_port_xbmc'] . '"></td></tr>
                <tr><td>' . $lang['a_mysql_login_xbmc'] . '</td><td><input type="text" name="mysql_login_xbmc" value="' . $set['mysql_login_xbmc'] . '"></td></tr>
                <tr><td>' . $lang['a_mysql_pass_xbmc'] . '</td><td><input type="text" name="mysql_pass_xbmc" value="' . $set['mysql_pass_xbmc'] . '"></td></tr>
                <tr><td>' . $lang['a_mysql_database_xbmc'] . '</td><td><input type="text" name="mysql_database_xbmc" value="' . $set['mysql_database_xbmc'] . '"></td></tr>
                </table>
                <input type="submit" value="SAVE">
                </form>';
}

/* ###################
 * # SAVING SETTINGS #
 */###################
if (isset($_GET['option']) && $_GET['option'] === 'settings_save') {
    print_r($_POST);
    $settings_update_sql = 'UPDATE ' . $mysql_tables[1] . ' SET 
        mode = "' . $_POST['mode'] . '",
        site_name = "' . $_POST['site_name'] . '",
        language = "' . $_POST['language'] . '",
        per_page = "' . $_POST['per_page'] . '",
        recently_limit = "' . $_POST['recently_limit'] . '",
        random_limit = "' . $_POST['random_limit'] . '",
        last_played_limit = "' . $_POST['last_played_limit'] . '",
        top_rated_limit = "' . $_POST['top_rated_limit'] . '",
        sync_time = "' . $_POST['sync_time'] . '",
        panel_top_time = "' . $_POST['panel_top_time'] . '",
        panel_top = "' . $_POST['panel_top'] . '",
        watched_status = "' . $_POST['watched_status'] . '",
        overall_panel = "' . $_POST['overall_panel'] . '",
        protect_site = "' . $_POST['protect_site'] . '",
        mysql_host_xbmc = ' . (isset($_POST['mysql_host_xbmc']) ? '"' . $_POST['mysql_host_xbmc'] . '"' : 'NULL') . ',
        mysql_port_xbmc = ' . (isset($_POST['mysql_port_xbmc']) ? '"' . $_POST['mysql_port_xbmc'] . '"' : 'NULL') . ',
        mysql_login_xbmc = ' . (isset($_POST['mysql_login_xbmc']) ? '"' . $_POST['mysql_login_xbmc'] . '"' : 'NULL') . ',
        mysql_pass_xbmc = ' . (isset($_POST['mysql_pass_xbmc']) ? '"' . $_POST['mysql_pass_xbmc'] . '"' : 'NULL') . ',
        mysql_database_xbmc = ' . (isset($_POST['mysql_database_xbmc']) ? '"' . $_POST['mysql_database_xbmc'] . '"' : 'NULL');
    mysql_query($settings_update_sql);
    
    // delete session var
    foreach ($settings_name as $val) {
        unset($_SESSION[$val]);
    }
    
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set['site_name'] ?> - Admin Panel</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link href="css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="admin_container">
            <div id="admin_panel_left">
                <a class="admin" href="admin.php">Main site</a>
                <a class="admin" href="admin.php?option=list">Movie list</a>
                <a class="admin" href="admin.php?option=create_cache">Create cache</a>
                <a class="admin" href="admin.php?option=rebuild_cache">Rebuild cache</a>
                <a class="admin" href="admin.php?option=settings">Settings</a>
                <a class="admin" href="login.php?login=admin_logout">Logout</a>
            </div>
            <div id="admin_panel_right">
                <?PHP echo $output_panel ?>
            </div>
        </div>
    </body>
</html>