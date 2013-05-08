<?php
session_start();
require_once 'config.php';
require_once 'function.php';

if ($_GET['option'] == 'delete_install') {
    // unlink('install.php');
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

// Connect to database
mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
mysql_select_db($mysql_ml[4]);

// Overall
if (!isset($_GET['option'])) {
    $overall_sql = 'SELECT play_count FROM ' . $mysql_table_ml;
    $overall_result = mysql_query($overall_sql);
    $overall_watched = 0;
    while ($overall = mysql_fetch_array($overall_result)) {
        if ($overall['play_count'] !== NULL) {
            $overall_watched++;
        }
    }
    $overall_all = mysql_num_rows($overall_result);
    $overall_unwatched = $overall_all - $overall_watched;
    $output_panel_overall = 'All: ' . $overall_all . ' Watched: ' . $overall_watched . ' Unwatched: ' . $overall_unwatched;
} else {
    $output_panel_overall = '';
}

// Create cache for all posters
if (isset($_SESSION['id_to_create']) && count($_SESSION['id_to_create']) > 0) {
    foreach ($_SESSION['id_to_create'] as $key => $id) {
        $list_sql = 'SELECT id, poster FROM ' . $mysql_table_ml . ' WHERE id = ' . $id;
        $list_result = mysql_query($list_sql);
        while ($list = mysql_fetch_array($list_result)) {
            gd_convert($list['id'], $list['poster']);
            unset($_SESSION['id_to_create'][$key]);
            header('Location:admin.php');
        }
    }
}
if (isset($_GET['option']) && $_GET['option'] === 'create_cache') {
    $list_sql = 'SELECT id, poster FROM ' . $mysql_table_ml;
    $list_result = mysql_query($list_sql);
    while ($list = mysql_fetch_array($list_result)) {
        if (!file_exists('cache/' . $list['id'] . '.jpg')) {
            $id_to_create[] = $list['id'];
        }
    }
    $_SESSION['id_to_create'] = $id_to_create;
    header('Location:admin.php');
}

// Rebuild cache
if (isset($_GET['option']) && $_GET['option'] === 'rebuild_cache') {
    $dir_path = 'cache/';
    $dir = opendir($dir_path);
    while($file = readdir($dir)) {
        if($file != '.' && $file != '..') {
            unlink($dir_path.'/'.$file);
        }
    }
    header('Location:admin.php?option=create_cache');
}

// Set UTF8 connection
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

// Movie list
if (isset($_GET['option']) && $_GET['option'] === 'list') {
    $list_sql = 'SELECT id, title, poster, play_count FROM ' . $mysql_table_ml . ' ORDER BY title';
    $list_result = mysql_query($list_sql);
    $output_panel_list = '<table id="admin_table_movie">';
    while ($list = mysql_fetch_array($list_result)) {
        if (file_exists('cache/' . $list['id'] . '.jpg')) {
            $poster_exist = '<img src="img/watched.png">';
        } else {
            $poster_exist = '<img src="img/delete.png">';
        }
        $output_panel_list.= '<tr><td>' . $list['id'] . '</td><td>' . $list['title'] . '</td><td><a href="' . $list['poster'] . '" target="_blank">' . $list['poster'] . '</a></td><td>'  . $poster_exist . '</td></tr>';
    }
    $output_panel_list.= '</table>';
} else {
    $output_panel_list = '';
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set_site_name ?> - Admin Panel</title>
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
            </div>
            <div id="admin_panel_right">
                <?PHP echo $output_panel_overall ?>
                <?PHP echo $output_panel_list ?>
            </div>
        </div>
    </body>
</html>