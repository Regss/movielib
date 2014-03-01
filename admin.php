<?PHP
session_start();
require('config.php');
require('function.php');

if ($option == 'delete_install') {
    unlink('install.php');
    header('Location:admin.php');
    die();
}

if (!file_exists('db.php')) {
    header('Location:install.php');
    die();
}

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_tables);
require('lang/' . $set['language'] . '/lang.php');

// check install.php file exist
if (file_exists('install.php')) {
    $output_panel_info.= $lang['a_install_exist'] . '<br />';
}

/* ######################
 * CHECK ADMIN PASSWORD #
 */######################
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

/* ################
 * # CHECK TABLES #
 */################
$output_tables = '';
foreach ($mysql_tables as $table) {
    $columns_sql = 'SHOW COLUMNS FROM ' . $table;
    $columns_result = mysql_query($columns_sql);
    while($columns = mysql_fetch_assoc($columns_result)) {
        $columns_db_array[$table][] = $columns['Field'];
    }
}
foreach ($tables as $tables_key => $tables_val) {
    foreach($tables_val as $col_key => $col_type) {
        if (!in_array($col_key, $columns_db_array[$tables_key])) {
            mysql_query('ALTER TABLE `' . $tables_key . '` ADD `' . $col_key . '` ' . $col_type);
            $output_panel_info.= $lang['a_tables_updated'] . ' - ' . $tables_key . '.' . $col_key . '<br />';
        }
    }
}

/* #############
 * # MAIN SITE #
 */#############
$output_panel = '';
if ($option == '') {
    
    // Watched
    $overall_sql = 'SELECT play_count FROM ' . $mysql_tables[0];
    $overall_result = mysql_query($overall_sql);
    $overall_all = mysql_num_rows($overall_result);
    $overall_watched = 0;
    while ($overall = mysql_fetch_array($overall_result)) {
        if ($overall['play_count'] > 0) {
            $overall_watched++;
        }
    }
    
    $overall_unwatched = $overall_all - $overall_watched;
    
    // Cached
    $cached_dir = scandir('cache/');
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
    
    // MD5 files
    $md5_file = 'files.md5';
    $output_md5 = '';
    $fp = fopen($md5_file, 'r');
    $data = fread($fp, filesize($md5_file));
    fclose($fp);
    foreach (explode(';', $data) as $f) {
        $file = explode(':', $f);
        $output_md5.= '<tr><td>' . $file[0] . '</td><td>' . (md5_file($file[0]) == $file[1] ? '<span class="green">' . $lang['a_match'] . '</span>' : '<span class="red">' . $lang['a_mismatch'] . '</span>') . '</td></tr>';
    }
    
    $output_panel = '
        <table class="table">
            <tr><td class="bold orange">' . $lang['a_movies'] . '</td><td></td></tr>
            <tr><td>' . $lang['a_all'] . '</td><td>' . $overall_all . '</td></tr>
            <tr><td>' . $lang['a_watched'] . '</td><td>' . $overall_watched . '</td></tr>
            <tr><td>' . $lang['a_unwatched'] . '</td><td>' . $overall_unwatched . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_cache'] . '</td><td></td></tr>
            <tr><td>' . $lang['a_cached_posters'] . '</td><td>' . $poster_cached . '</td></tr>
            <tr><td>' . $lang['a_cached_fanarts'] . '</td><td>' . $fanart_cached . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_server_settings'] . '</td><td></td></tr>
            <tr><td>ALLOW URL FOPEN</td><td>' . (ini_get('allow_url_fopen') == 1 ? $lang['a_setting_on'] : $lang['a_setting_off']) . '</td></tr>
            <tr><td>UPLOAD MAX FILESIZE</td><td>' . ini_get('upload_max_filesize') . '</td></tr>
            <tr><td>POST MAX SIZE</td><td>' . ini_get('post_max_size') . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_files_md5'] . '</td><td></td></tr>
            ' . $output_md5 . '
        </table>';
}

/* ##############
 * # MOVIE LIST #
 */##############
if ($option == 'list') {
    $list_sql = 'SELECT id, title, trailer, play_count FROM ' . $mysql_tables[0] . ' ORDER BY title';
    $list_result = mysql_query($list_sql);
    $output_panel = '
        <table class="table">
            <tr class="bold"><td>
                </td><td>ID</td>
                <td>' . $lang['a_title'] . '</td>
                <td>P</td>
                <td>F</td>
                <td>T</td>
                <td></td>
            </tr>';
    $i = 0;
    while ($list = mysql_fetch_array($list_result)) {
        if (file_exists('cache/' . $list['id'] . '.jpg')) {
            $poster_exist = '<img src="css/' . $set['theme'] . '/admin/img/exist.png" alt="">';
        } else {
            $poster_exist = '';
        }
        if (file_exists('cache/' . $list['id'] . '_f.jpg')) {
            $fanart_exist = '<img src="css/' . $set['theme'] . '/admin/img/exist.png" alt="">';
        } else {
            $fanart_exist = '';
        }
        if (stristr($list['trailer'], 'http://')) {
            $trailer_link = '<a href="' . $list['trailer'] . '" target="_blank"><img src="css/' . $set['theme'] . '/admin/img/link.png" title="Link" alt=""></a>';
        } else {
            $trailer_link = '';
        }
        $i++;
        $output_panel.= '
            <tr id="row_' . $list['id'] . '">
                <td>' . $i . '</td><td>' . $list['id'] . '</td>
                <td>' . $list['title'] . '</td>
                <td>'  . $poster_exist . '</td>
                <td>'  . $fanart_exist . '</td>
                <td>'  . $trailer_link . '</td>
                <td><img id="' . $list['id'] . '" class="delete_row" src="css/' . $set['theme'] . '/admin/img/delete.png" title="' . $lang['a_delete'] . '" alt=""></td>
            </tr>';
    }
    $output_panel.= '</table><a id="delete_all" class="box" href="admin.php?option=delete_all">' . $lang['a_delete_all'] . '</a>';
}

// DELETE ALL
if ($option == 'delete_all') {
    $truncate_sql = 'TRUNCATE ' . $mysql_tables[0];
    $truncate_result = mysql_query($truncate_sql);
    $files = scandir('cache/');
    foreach($files as $d) {
        if(substr($d, -4) == '.jpg') {
            unlink('cache/' . $d);
        }
    }
}

/* ############
 * # SETTINGS #
 */############
if ($option == 'settings') {
    
    $output_lang = '';
    $output_theme = '';
    $output_panel_top = '';
    $output_watched_status = '';
    $output_live_search = '';
    $output_live_search_max_res = '';
    $output_panel_overall = '';
    $output_panel_genre = '';
    $output_panel_year = '';
    $output_panel_country = '';
    $output_panel_v_codec = '';
    $output_panel_a_codec = '';
    $output_panel_a_chan = '';
    $output_show_fanart = '';
    $output_show_trailer = '';
    $output_protect_site = '';
    $output_per_page = '';
    $output_panel_top_limit = '';
    
    // set language input
    $option_language = scandir('lang/');
    foreach ($option_language as $val) {
        if (file_exists('lang/' . $val . '/lang.php')) {
            if (array_key_exists($val, $language)) {
                $lang_title = $language[$val];
            } else {
                $lang_title = $val;
            }
            $output_lang.= '<option' . ($val == $set['language'] ? ' selected="selected"' : '') . ' value="' . $val . '">' . $lang_title . '</option>';
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
        // set panel_top input
        $output_panel_top.= '<option' . ($set['panel_top'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set wached status input
        $output_watched_status.= '<option' . ($set['watched_status'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set live search input
        $output_live_search.= '<option' . ($set['live_search'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
       // set show fanart input
        $output_show_fanart.= '<option' . ($set['show_fanart'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set show trailer input
        $output_show_trailer.= '<option' . ($set['show_trailer'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set protect site input
        $output_protect_site.= '<option' . ($set['protect_site'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
    }
    
    $mode2 = array(0 => $lang['a_setting_off'], 1 => $lang['a_setting_on_expanded'], 2 => $lang['a_setting_on_collapsed']);
    foreach ($mode2 as $key => $val) {
        // set panel overall input
        $output_panel_overall.= '<option' . ($set['panel_overall'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val  . '</option>';
        // set panel genre input
        $output_panel_genre.= '<option' . ($set['panel_genre'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        // set panel year input
        $output_panel_year.= '<option' . ($set['panel_year'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        // set panel country input
        $output_panel_country.= '<option' . ($set['panel_country'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        // set panel v_codec input
        $output_panel_v_codec.= '<option' . ($set['panel_v_codec'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        // set panel a_codec input
        $output_panel_a_codec.= '<option' . ($set['panel_a_codec'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        // set panel a_chan input
        $output_panel_a_chan.= '<option' . ($set['panel_a_chan'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
    }
    
    $quantity = array(5, 10, 20, 50, 100);
    foreach ($quantity as $val) {
        // set per page input
        $output_per_page.= '<option' . ($set['per_page'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
        // set panel top limit
        $output_panel_top_limit.= '<option' . ($set['panel_top_limit'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
        // set live search max res
        $output_live_search_max_res.= '<option' . ($set['live_search_max_res'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
    }

    // output form
    $output_panel.= '
        <form action="admin.php?option=settings_save" method="post">
            <table class="table">
                <tr><td class="bold orange">' . $lang['a_set_main'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_site_name'] . ':</td><td><input type="text" name="site_name" value="' . $set['site_name'] . '" /></td></tr>
                <tr><td>' . $lang['a_language'] . ':</td><td><select name="language">' . $output_lang . '</select></td></tr>
                <tr><td>' . $lang['a_theme'] . ':</td><td><select name="theme">' . $output_theme . '</select></td></tr>
                <tr><td>' . $lang['a_per_page'] . ':</td><td><select name="per_page">' . $output_per_page . '</select></td></tr>
                <tr><td>' . $lang['a_panel_top'] . ':</td><td><select name="panel_top">' . $output_panel_top . '</select></td></tr>
                <tr><td>' . $lang['a_watched_status'] . ':</td><td><select name="watched_status">' . $output_watched_status . '</select></td></tr>
                <tr><td>' . $lang['a_live_search'] . ':</td><td><select name="live_search">' . $output_live_search . '</select></td></tr>
                <tr><td>' . $lang['a_live_search_max_res'] . ':</td><td><select name="live_search_max_res">' . $output_live_search_max_res . '</select></td></tr>
                <tr><td>' . $lang['a_show_fanart'] . ':</td><td><select name="show_fanart">' . $output_show_fanart . '</select></td></tr>
                <tr><td>' . $lang['a_show_trailer'] . ':</td><td><select name="show_trailer">' . $output_show_trailer . '</select></td></tr>
                <tr><td>' . $lang['a_protect_site']  . ':</td><td><select name="protect_site">' . $output_protect_site . '</select></td></tr>
                <tr><td class="bold orange">' . $lang['a_set_panel_left'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_panel_overall'] . ':</td><td><select name="panel_overall">' . $output_panel_overall . '</select></td></tr>
                <tr><td>' . $lang['a_panel_genre'] . ':</td><td><select name="panel_genre">' . $output_panel_genre . '</select></td></tr>
                <tr><td>' . $lang['a_panel_year'] . ':</td><td><select name="panel_year">' . $output_panel_year . '</select></td></tr>
                <tr><td>' . $lang['a_panel_country'] . ':</td><td><select name="panel_country">' . $output_panel_country . '</select></td></tr>
                <tr><td>' . $lang['a_panel_v_codec'] . ':</td><td><select name="panel_v_codec">' . $output_panel_v_codec . '</select></td></tr>
                <tr><td>' . $lang['a_panel_a_codec'] . ':</td><td><select name="panel_a_codec">' . $output_panel_a_codec . '</select></td></tr>
                <tr><td>' . $lang['a_panel_a_chan'] . ':</td><td><select name="panel_a_chan">' . $output_panel_a_chan . '</select></td></tr>
                <tr><td class="bold orange">' . $lang['a_set_panel_top'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_panel_top_time'] . ':</td><td><input type="text" name="panel_top_time" value="' . $set['panel_top_time'] . '" /></td></tr>
                <tr><td>' . $lang['a_panel_top_limit'] . ':</td><td><select name="panel_top_limit">' . $output_panel_top_limit . '</select></td></tr>
            </table><br />
                <input type="submit" value="' . $lang['a_save'] . '" />
        </form>';
}

// Saving settings
if ($option == 'settings_save') {
    $settings_update_sql = 'UPDATE ' . $mysql_tables[1] . ' SET 
        site_name = "' . $_POST['site_name'] . '",
        language = "' . $_POST['language'] . '",
        theme = "' . $_POST['theme'] . '",
        per_page = "' . $_POST['per_page'] . '",
        panel_top_limit = "' . $_POST['panel_top_limit'] . '",
        panel_top_time = "' . $_POST['panel_top_time'] . '",
        panel_top = "' . $_POST['panel_top'] . '",
        watched_status = "' . $_POST['watched_status'] . '",
        live_search = "' . $_POST['live_search'] . '",
        live_search_max_res = "' . $_POST['live_search_max_res'] . '",
        panel_overall = "' . $_POST['panel_overall'] . '",
        panel_genre = "' . $_POST['panel_genre'] . '",
        panel_year = "' . $_POST['panel_year'] . '",
        panel_country = "' . $_POST['panel_country'] . '",
        panel_v_codec = "' . $_POST['panel_v_codec'] . '",
        panel_a_codec = "' . $_POST['panel_a_codec'] . '",
        panel_a_chan = "' . $_POST['panel_a_chan'] . '",
        show_fanart = "' . $_POST['show_fanart'] . '",
        show_trailer = "' . $_POST['show_trailer'] . '",
        protect_site = "' . $_POST['protect_site'] . '"';
    mysql_query($settings_update_sql);
    
    // delete session var
    foreach ($set as $key => $val) {
        if ($key != 'logged_admin') {
            unset($_SESSION[$key]);
        }
    }
    $output_panel_info.= $lang['a_saved'] . '<br />';
}

/* ###################
 * # CHANGE PASSWORD #
 */###################
if ($option == 'password') {
    $output_panel.= '
        <form action="admin.php?option=password_save" method="post">
            <table class="table">
                <tr><td class="bold orange">' . $lang['a_user'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_new_password'] . '</td><td><input type="password" name="password" /></td></tr>
                <tr><td>' . $lang['a_new_password_re'] . '</td><td><input type="password" name="password_re" /></td></tr>
                <tr><td class="bold orange">' . $lang['a_admin'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_new_password'] . '</td><td><input type="password" name="password_admin" /></td></tr>
                <tr><td>' . $lang['a_new_password_re'] . '</td><td><input type="password" name="password_admin_re" /></td></tr>
            </table><br />
                <input type="submit" value="' . $lang['a_save'] . '" />
        </form>
    ';
}

// Save password
if ($option == 'password_save') {
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
// check admin pass is not default
$pass_check_sql = 'SELECT * FROM ' . $mysql_tables[2] . ' WHERE login = "admin"';
$pass_check_result = mysql_query($pass_check_sql);
$pass_check = mysql_fetch_array($pass_check_result);
if ($pass_check['password'] == '21232f297a57a5a743894a0e4a801fc3') {
    $output_panel_info.= $lang['a_pass_default'] . '<br />';
}

/* #########
 * # TOKEN #
 */#########
if ($option == 'token') {
    if (isset($_POST['new_token'])) {
        $new_token = change_token($mysql_tables);
        $output_panel_info.= $lang['a_token_changed'] . '<br />';
    } else {
        $new_token = $set['token'];
    }
    $output_panel.= '
        <table class="table">
            <tr><td>Token:</td><td class="bold orange">' . $new_token . '</td></tr>
        </table><br />
        <form action="admin.php?option=token" method="post">
        <input type="hidden" name="new_token" />
        <input type="submit" value="' . $lang['a_token_change'] . '" />
        </form>
    ';
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
        <title><?PHP echo $set['site_name'] ?> - <?PHP echo $lang['a_html_admin_panel'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <link type="image/x-icon" href="css/<?PHP echo $set['theme'] ?>/img/icon.ico" rel="icon" media="all" />
        <link type="text/css" href="css/<?PHP echo $set['theme'] ?>/admin/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
    </head>
    <body>
        <?PHP echo $output_panel_info ?>
        <div class="container">
            <div id="panel_left">
                <a class="box" href="admin.php"><?PHP echo $lang['a_html_main_site'] ?></a>
                <a class="box" href="admin.php?option=list"><?PHP echo $lang['a_html_movie_list'] ?></a>
                <a class="box" href="admin.php?option=settings"><?PHP echo $lang['a_html_settings'] ?></a>
                <a class="box" href="admin.php?option=password"><?PHP echo $lang['a_html_change_password'] ?></a>
                <a class="box" href="admin.php?option=token"><?PHP echo $lang['a_html_change_token'] ?></a>
                <a class="box" href="login.php?login=admin_logout"><?PHP echo $lang['a_html_logout'] ?></a>
            </div>
            <div id="panel_right">
                <?PHP echo $output_panel ?>
            </div>
        </div>
    </body>
</html>