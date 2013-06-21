<?php
session_start();
require_once 'config.php';
require_once 'function.php';

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_ml, $mysql_tables, $settings_name);
require_once 'lang/lang_' . $set['language'] . '.php';

if (isset($_GET['option']) && $_GET['option'] == 'delete_install') {
    unlink('install.php');
    header('Location:admin.php');
    die();
}

if (file_exists('install.php') or !file_exists('db.php')) {
    header('Location:install.php');
    die();
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

/* #############
 * # MAIN SITE #
 */#############
$output_panel = '';
if (!isset($_GET['option'])) {
    
    // Watched
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
    
    // Cached
    $cached_dir = scandir('cache/');
    $all_cached = count($cached_dir) - 2;
    $poster_cached = 0;
    $fanart_cached = 0;
    foreach ($cached_dir as $val) {
        if (preg_match_all('/[0-9]+\.jpg/', $val, $res) == 1) {
            $poster_cached++;
        }
        if (preg_match_all('/[0-9]+_f\.jpg/', $val, $res) == 1) {
            $fanart_cached++;
        }
    }
    
    $output_panel = '
        <table id="admin_table_movie">
            <tr><td class="bold des">' . $lang['a_movies'] . '</td><td></td></tr>
            <tr><td>' . $lang['a_all'] . '</td><td>' . $overall_all . '</td></tr>
            <tr><td>' . $lang['a_watched'] . '</td><td>' . $overall_watched . '</td></tr>
            <tr><td>' . $lang['a_unwatched'] . '</td><td>' . $overall_unwatched . '</td></tr>
            <tr><td class="bold des">' . $lang['a_cache'] . '</td><td></td></tr>
            <tr><td>' . $lang['a_cached_posters'] . '</td><td>' . $poster_cached . '</td></tr>
            <tr><td>' . $lang['a_cached_fanarts'] . '</td><td>' . $fanart_cached . '</td></tr>
        </table>';
}

/* ##############
 * # MOVIE LIST #
 */##############
if (isset($_GET['option']) && $_GET['option'] == 'list') {
    $list_sql = 'SELECT id, title, poster, fanart, play_count FROM ' . $mysql_tables[0] . ' ORDER BY title';
    $list_result = mysql_query($list_sql);
    $output_panel = '<table id="admin_table_movie"><tr class="bold"><td></td><td>ID</td><td>' . $lang['a_title'] . '</td><td>P</td><td></td><td>F</td><td></td></tr>';
    $i = 0;
    while ($list = mysql_fetch_array($list_result)) {
        if (file_exists('cache/' . $list['id'] . '.jpg')) {
            $poster_exist = '<img src="css/' . $set['theme'] . '/img/watched.png">';
        } else {
            $poster_exist = '<img src="css/' . $set['theme'] . '/img/delete.png">';
        }
        if (file_exists('cache/' . $list['id'] . '_f.jpg')) {
            $fanart_exist = '<img src="css/' . $set['theme'] . '/img/watched.png">';
        } else {
            $fanart_exist = '<img src="css/' . $set['theme'] . '/img/delete.png">';
        }
        $i++;
        $output_panel.= '<tr><td>' . $i . '</td><td>' . $list['id'] . '</td><td>' . $list['title'] . '</td><td><a href="' . $list['poster'] . '" target="_blank"><img src="css/' . $set['theme'] . '/img/link.png"></a></td><td>'  . $poster_exist . '</td><td><a href="' . $list['fanart'] . '" target="_blank"><img src="css/' . $set['theme'] . '/img/link.png"></a></td><td>'  . $fanart_exist . '</td></tr>';
    }
    $output_panel.= '</table>';
}

/* ##########
 * # UPLOAD #
 */##########
if (isset($_GET['option']) && $_GET['option'] == 'upload') {
    $output_panel.= $lang['a_server_size'] . ':<br /> upload_max_filesize: ' . ini_get('upload_max_filesize') . '<br />post_max_size' . ': ' . ini_get('post_max_size');
    $output_panel.= '<br /><br />' . $lang['a_select_xml_file'] . '<br /><br /><form action="admin.php?option=upload" method="post" enctype="multipart/form-data">
                        <input type="file" name="file_xml"><br /><br />
                        <input type="submit" name="submit" value="' . $lang['a_submit_upload'] . '">
                    </form>';
    if (isset($_FILES['file_xml'])) {
        if (is_uploaded_file($_FILES['file_xml']['tmp_name'])) {
            move_uploaded_file($_FILES['file_xml']['tmp_name'], 'import/' . $_FILES['file_xml']['name']);
            $output_panel_info.= $lang['a_uploaded'];
        }
        if ($_FILES['file_xml']['error'] > 0) {
            $output_panel_info.= $lang['a_upload_error'];
        }
    }
}

/* #########
 * # CACHE #
 */#########
if (isset($_GET['option']) && $_GET['option'] == 'cache') {
    $output_panel.= '
        <table id="admin_table_movie">
            <tr><td>' . $lang['a_create_cache_info'] . '</td><td><a class="admin_menu_box" href="admin.php?option=create_cache">' . $lang['a_create_cache'] . '</a></td></tr>
            <tr><td>' . $lang['a_rebuild_cache_info'] . '</td><td><a class="admin_menu_box" href="admin.php?option=rebuild_cache">' . $lang['a_rebuild_cache'] . '</a></td></tr>
        </table>';
}

// Create cache
if (isset($_GET['option']) && $_GET['option'] == 'create_cache') {
    
    if (!isset($_SESSION['id_to_create'])) {
        $list_sql = 'SELECT id, poster FROM ' . $mysql_tables[0];
        $list_result = mysql_query($list_sql);
        while ($list = mysql_fetch_array($list_result)) {
            if (!file_exists('cache/' . $list['id'] . '.jpg')) {
                $id_to_create[] = $list['id'];
            }
        }
        $_SESSION['id_to_create'] = $id_to_create;
        header('Location:admin.php?option=create_cache');
    }
    
    if (isset($_SESSION['id_to_create']) && count($_SESSION['id_to_create']) > 0) {
        foreach ($_SESSION['id_to_create'] as $key => $id) {
            $list_sql = 'SELECT id, poster FROM ' . $mysql_tables[0] . ' WHERE id = ' . $id;
            $list_result = mysql_query($list_sql);
            while ($list = mysql_fetch_array($list_result)) {
                gd_convert('cache/' . $list['id'] . '.jpg', $list['poster'], 140, 198);
                unset($_SESSION['id_to_create'][$key]);
                header('Location:admin.php?option=create_cache');
            }
        }
    } else {
        unset($_SESSION['id_to_create']);
        $output_panel_info.= $lang['a_cache_created'];
    } 
} else {
    unset($_SESSION['id_to_create']);
}

// Rebuild cache
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

/* ############
 * # SETTINGS #
 */############
if (isset($_GET['option']) && $_GET['option'] == 'settings') {
    
    $output_lang = '';
    $output_theme = '';
    $output_mode = '';
    $output_panel_top = '';
    $output_watched_status = '';
    $output_overall_panel = '';
    $output_show_fanart = '';
    $output_protect_site = '';
    $output_per_page = '';
    $output_recently_limit = '';
    $output_random_limit = '';
    $output_last_played_limit = '';
    $output_top_rated_limit = '';
    
    // set language input
    $option_language = scandir('lang/');
    foreach ($option_language as $val) {
        if ((substr($val, 0, 4) == 'lang') && (substr($val, -3) == 'php')) {
            $fp = fopen('lang/' . $val, 'r');
            for ($i=0;$i<3;$i++) {
                $line = fgets($fp);
            }
            preg_match('/([a-zA-Z]+)/', $line, $lang_title);
            preg_match('/_([a-zA-Z]+)\./', $val, $lang_id);
            $output_lang.= '<option' . ($val == 'lang_' . $set['language'] . '.php' ? ' selected="selected"' : '') . ' value="' . $lang_id[1] . '">' . ucfirst(strtolower($lang_title[1])) . '</option>';
        }
    }
    
    // set theme input
    $output_theme = '';
    $option_theme = scandir('css/');
    foreach ($option_theme as $val) {
        if ($val !== '.' && $val !== '..') {
            $output_theme.= '<option' . ($val == $set['theme'] ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
        }
    }
    
    $mode = array(0, 1);
    foreach ($mode as $val) {
            // set mode input
            $output_mode.= ($val == 0 ? $lang['a_radio_off'] : $lang['a_radio_on']) . '<input id="mode_' . $val . '" type="radio" name="mode" value="' . $val . '"' . ($set['mode'] == $val ? ' checked="checked"' : '') . ' /> ';
            // set panel_top input
            $output_panel_top.= ($val == 0 ? $lang['a_radio_off'] : $lang['a_radio_on']) . '<input type="radio" name="panel_top" value="' . $val . '" ' . ($set['panel_top'] == $val ? ' checked="checked"' : '') . ' /> ';
            // set wached status input
            $output_watched_status.= ($val == 0 ? $lang['a_radio_off'] : $lang['a_radio_on']) . '<input type="radio" name="watched_status" value="' . $val . '" ' . ($set['watched_status'] == $val ? ' checked="checked"' : '') . ' /> ';
            // set overall panel input
            $output_overall_panel.= ($val == 0 ? $lang['a_radio_off'] : $lang['a_radio_on']) . '<input type="radio" name="overall_panel" value="' . $val . '" ' . ($set['overall_panel'] == $val ? ' checked="checked"' : '') . ' /> ';
            // set show fanart input
            $output_show_fanart.= ($val == 0 ? $lang['a_radio_off'] : $lang['a_radio_on']) . '<input type="radio" name="show_fanart" value="' . $val . '" ' . ($set['show_fanart'] == $val ? ' checked="checked"' : '') . ' /> ';
            // set protect site input
            $output_protect_site.= ($val == 0 ? $lang['a_radio_off'] : $lang['a_radio_on']) . '<input type="radio" name="protect_site" value="' . $val . '" ' . ($set['protect_site'] == $val ? ' checked="checked"' : '') . ' /> ';
    }
    
    $quantity = array(5, 10, 20, 50, 100);
    foreach ($quantity as $val) {
        // set per page input
        $output_per_page.= $val . '<input type="radio" name="per_page" value="' . $val . '"' . ($set['per_page'] == $val ? ' checked="checked"' : '') . ' /> ';
        // set recently limit
        $output_recently_limit.= $val . '<input type="radio" name="recently_limit" value="' . $val . '"' . ($set['recently_limit'] == $val ? ' checked="checked"' : '') . ' /> ';
        // set random limit
        $output_random_limit.= $val . '<input type="radio" name="random_limit" value="' . $val . '"' . ($set['random_limit'] == $val ? ' checked="checked"' : '') . ' /> ';
        // set last played limit
        $output_last_played_limit.= $val . '<input type="radio" name="last_played_limit" value="' . $val . '"' . ($set['last_played_limit'] == $val ? ' checked="checked"' : '') . ' /> ';
        // set top rated limit
        $output_top_rated_limit.= $val . '<input type="radio" name="top_rated_limit" value="' . $val . '"' . ($set['top_rated_limit'] == $val ? ' checked="checked"' : '') . ' /> ';
    }

    // output form
    $output_panel.= '
        <form action="admin.php?option=settings_save" method="post">
            <table id="admin_table_movie">
                <tr><td>' . $lang['a_mode'] . ':</td><td>' . $output_mode . '</td></tr>
                <tr><td>' . $lang['a_site_name'] . ':</td><td><input type="text" name="site_name" value="' . $set['site_name'] . '" /></td></tr>
                <tr><td>' . $lang['a_language'] . ':</td><td><select name="language">' . $output_lang . '</select></td></tr>
                <tr><td>' . $lang['a_theme'] . ':</td><td><select name="theme">' . $output_theme . '</select></td></tr>
                <tr><td>' . $lang['a_per_page'] . ':</td><td>' . $output_per_page . '</td></tr>
                <tr><td>' . $lang['a_recently_limit'] . ':</td><td>' . $output_recently_limit . '</td></tr>
                <tr><td>' . $lang['a_random_limit'] . ':</td><td>' . $output_random_limit . '</td></tr>
                <tr><td>' . $lang['a_last_played_limit'] . ':</td><td>' . $output_last_played_limit . '</td></tr>
                <tr><td>' . $lang['a_top_rated_limit'] . ':</td><td>' . $output_top_rated_limit . '</td></tr>
                <tr><td>' . $lang['a_sync_time'] . ':</td><td><input type="text" name="sync_time" value="' . $set['sync_time'] . '" /></td></tr>
                <tr><td>' . $lang['a_panel_top_time'] . ':</td><td><input type="text" name="panel_top_time" value="' . $set['panel_top_time'] . '" /></td></tr>
                <tr><td>' . $lang['a_panel_top'] . ':</td><td>' . $output_panel_top . '</td></tr>
                <tr><td>' . $lang['a_watched_status'] . ':</td><td>' . $output_watched_status . '</td></tr>
                <tr><td>' . $lang['a_overall_panel'] . ':</td><td>' . $output_overall_panel . '</td></tr>
                <tr><td>' . $lang['a_show_fanart'] . ':</td><td>' . $output_show_fanart . '</td></tr>
                <tr><td>' . $lang['a_protect_site']  . ':</td><td>' . $output_protect_site . '</td></tr>
                <tr><td>' . $lang['a_mysql_host_xbmc'] . ':</td><td><input class="xbmc' . ($set['mode'] == 0 ? ' disabled' : '') . '" type="text" name="mysql_host_xbmc" value="' . $set['mysql_host_xbmc'] . '"' . ($set['mode'] == 0 ? ' disabled="disabled"' : '') . ' /></td></tr>
                <tr><td>' . $lang['a_mysql_port_xbmc'] . ':</td><td><input class="xbmc' . ($set['mode'] == 0 ? ' disabled' : '') . '" type="text" name="mysql_port_xbmc" value="' . $set['mysql_port_xbmc'] . '"' . ($set['mode'] == 0 ? ' disabled="disabled"' : '') . ' /></td></tr>
                <tr><td>' . $lang['a_mysql_login_xbmc'] . ':</td><td><input class="xbmc' . ($set['mode'] == 0 ? ' disabled' : '') . '" type="text" name="mysql_login_xbmc" value="' . $set['mysql_login_xbmc'] . '"' . ($set['mode'] == 0 ? ' disabled="disabled"' : '') . ' /></td></tr>
                <tr><td>' . $lang['a_mysql_pass_xbmc'] . ':</td><td><input class="xbmc' . ($set['mode'] == 0 ? ' disabled' : '') . '" type="text" name="mysql_pass_xbmc" value="' . $set['mysql_pass_xbmc'] . '"' . ($set['mode'] == 0 ? ' disabled="disabled"' : '') . ' /></td></tr>
                <tr><td>' . $lang['a_mysql_database_xbmc'] . ':</td><td><input class="xbmc' . ($set['mode'] == 0 ? ' disabled' : '') . '" type="text" name="mysql_database_xbmc" value="' . $set['mysql_database_xbmc'] . '"' . ($set['mode'] == 0 ? ' disabled="disabled"' : '') . ' /></td></tr>
            </table>
                <input type="submit" value="OK" />
        </form>';
}

// Saving settings
if (isset($_GET['option']) && $_GET['option'] === 'settings_save') {
    $settings_update_sql = 'UPDATE ' . $mysql_tables[1] . ' SET 
        mode = "' . $_POST['mode'] . '",
        site_name = "' . $_POST['site_name'] . '",
        language = "' . $_POST['language'] . '",
        theme = "' . $_POST['theme'] . '",
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
        show_fanart = "' . $_POST['show_fanart'] . '",
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
    $output_panel_info = $lang['a_saved'];
}

/* ###################
 * # CHANGE PASSWORD #
 */###################
if (isset($_GET['option']) && $_GET['option'] == 'password') {
    $output_panel.= '
        <form action="admin.php?option=password_save" method="post">
            <table id="admin_table_movie">
                <tr><td class="bold des">' . $lang['a_user'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_new_password'] . '</td><td><input type="password" name="password" /></td></tr>
                <tr><td>' . $lang['a_new_password_re'] . '</td><td><input type="password" name="password_re" /></td></tr>
                <tr><td class="bold des">' . $lang['a_admin'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_new_password'] . '</td><td><input type="password" name="password_admin" /></td></tr>
                <tr><td>' . $lang['a_new_password_re'] . '</td><td><input type="password" name="password_admin_re" /></td></tr>
            </table>
                <input type="submit" value="OK" />
        </form>
    ';
}

// Save password
if (isset($_GET['option']) && $_GET['option'] === 'password_save') {
    if (strlen($_POST['password']) > 0) {
        if ($_POST['password'] == $_POST['password_re']) {
            if (strlen($_POST['password']) > 3) {
                $password_update_sql = 'UPDATE ' . $mysql_tables[2] . ' SET password = "' . md5($_POST['password']) . '" WHERE login ="user"';
                mysql_query($password_update_sql);
                $output_panel_info.= $lang['a_user_pass_changed'] . '<br />';
            } else {
                $output_panel_info.= $lang['a_user_pass_min'] . '<br />';
            }
        } else {
            $output_panel_info.= $lang['a_user_pass_n_match'] . '<br />';
        }
    }
    
    if (strlen($_POST['password_admin']) > 0) {
        if ($_POST['password_admin'] == $_POST['password_admin_re']) {
            if (strlen($_POST['password_admin']) > 3) {
                $password_update_sql = 'UPDATE ' . $mysql_tables[2] . ' SET password = "' . md5($_POST['password_admin']) . '" WHERE login ="admin"';
                mysql_query($password_update_sql);
                $output_panel_info.= $lang['a_admin_pass_changed'] . '<br />';
            } else {
                $output_panel_info.= $lang['a_admin_pass_min'] . '<br />';
            }
        } else {
            $output_panel_info.= $lang['a_admin_pass_n_match'] . '<br />';
        }
    }
}

/* ##############
 * # PANEL INFO #
 */##############
if ($output_panel_info !== '') {
    $output_panel_info = '<div id="panel_info">' . $output_panel_info . '</div>';
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set['site_name'] ?> - Admin Panel</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link href="css/<?PHP echo $set['theme'] ?>/style.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
    </head>
    <body>
        <?PHP echo $output_panel_info ?>
        <div id="admin_container">
            <div id="admin_panel_left">
                <a class="admin_menu_box" href="admin.php"><?PHP echo $lang['a_html_main_site'] ?></a>
                <a class="admin_menu_box" href="admin.php?option=list"><?PHP echo $lang['a_html_movie_list'] ?></a>
                <a class="admin_menu_box" href="admin.php?option=upload"><?PHP echo $lang['a_html_upload_xml'] ?></a>
                <a class="admin_menu_box" href="admin.php?option=cache">Cache</a>
                <a class="admin_menu_box" href="admin.php?option=settings"><?PHP echo $lang['a_html_settings'] ?></a>
                <a class="admin_menu_box" href="admin.php?option=password"><?PHP echo $lang['a_html_change_password'] ?></a>
                <a class="admin_menu_box" href="login.php?login=admin_logout"><?PHP echo $lang['a_html_logout'] ?></a>
            </div>
            <div id="admin_panel_right">
                <?PHP echo $output_panel ?>
            </div>
        </div>
    </body>
</html>