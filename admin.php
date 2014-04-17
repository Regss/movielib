<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

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

/* #############
 * # MAIN SITE #
 */#############
$output_panel = '';
if ($option == '') {
    
    // check DB
    $db_vers_sql = 'SELECT version FROM ' . $mysql_tables[3];
    $db_vers_result = mysql_query($db_vers_sql);
    if ($db_vers_result == true) {
        $db_version_assoc = mysql_fetch_assoc($db_vers_result);
        $db_version = $db_version_assoc['version'];
    } else {
        $db_version = '0';
    }
    if ($db_version !== $version) {
        $output_panel_info.= create_table($mysql_tables, $tables, $lang, $version, 0);

        // rename cache
        $dir = scandir('cache/');
        foreach ($dir as $val) {
            preg_match('|^([0-9]+).jpg$|', $val, $matches);
            if (isset($matches[1])) {
                rename('cache/' . $val, 'cache/movies_' . $val);
            }
            preg_match('|^([0-9]+)_f.jpg$|', $val, $matches2);
            if (isset($matches2[1])) {
                rename('cache/' . $val, 'cache/movies_' . $val);
            }
        }
        
        // delete session var
        $_SESSION = array();
        $_SESSION['logged_admin'] = true;
    }

    // Watched
    $overall_movies_sql = 'SELECT play_count, hide FROM ' . $mysql_tables[0];
    $overall_movies_result = mysql_query($overall_movies_sql);
    $overall_movies_all = mysql_num_rows($overall_movies_result);
    $overall_movies_watched = 0;
    $overall_movies_hidden = 0;
    while ($overall_movies = mysql_fetch_array($overall_movies_result)) {
        if ($overall_movies['hide'] == 1) {
            $overall_movies_hidden++;
        } else {
            if ($overall_movies['play_count'] > 0) {
                $overall_movies_watched++;
            }
        }
    }
    $overall_movies_unwatched = $overall_movies_all - $overall_movies_watched;
    
    $overall_tvshows_sql = 'SELECT play_count, hide FROM ' . $mysql_tables[1];
    $overall_tvshows_result = mysql_query($overall_tvshows_sql);
    $overall_tvshows_all = mysql_num_rows($overall_tvshows_result);
    $overall_tvshows_watched = 0;
    $overall_tvshows_hidden = 0;
    while ($overall_tvshows = mysql_fetch_array($overall_tvshows_result)) {
        if ($overall_tvshows['hide'] == 1) {
            $overall_tvshows_hidden++;
        } else {
            if ($overall_tvshows['play_count'] > 0) {
                $overall_tvshows_watched++;
            }
        }
    }
    $overall_tvshows_unwatched = $overall_tvshows_all - $overall_tvshows_watched;
    
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
            <tr><td>' . $lang['a_all'] . '</td><td>' . $overall_movies_all . '</td></tr>
            <tr><td>' . $lang['a_watched'] . '</td><td>' . $overall_movies_watched . '</td></tr>
            <tr><td>' . $lang['a_unwatched'] . '</td><td>' . $overall_movies_unwatched . '</td></tr>
            <tr><td>' . $lang['a_hidden'] . '</td><td>' . $overall_movies_hidden . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_tvshows'] . '</td><td></td></tr>
            <tr><td>' . $lang['a_all'] . '</td><td>' . $overall_tvshows_all . '</td></tr>
            <tr><td>' . $lang['a_watched'] . '</td><td>' . $overall_tvshows_watched . '</td></tr>
            <tr><td>' . $lang['a_unwatched'] . '</td><td>' . $overall_tvshows_unwatched . '</td></tr>
            <tr><td>' . $lang['a_hidden'] . '</td><td>' . $overall_tvshows_hidden . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_cache'] . '</td><td></td></tr>
            <tr><td>' . $lang['a_cached_posters'] . '</td><td>' . $poster_cached . '</td></tr>
            <tr><td>' . $lang['a_cached_fanarts'] . '</td><td>' . $fanart_cached . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_server_settings'] . '</td><td></td></tr>
            <tr><td>GD</td><td>' . (extension_loaded('gd') && function_exists('gd_info') ? $lang['a_setting_on'] : $lang['a_setting_off']) . '</td></tr>
            <tr><td>CURL</td><td>' . (function_exists('curl_version') ? $lang['a_setting_on'] : $lang['a_setting_off']) . '</td></tr>
            <tr><td>ALLOW URL FOPEN</td><td>' . (ini_get('allow_url_fopen') == 1 ? $lang['a_setting_on'] : $lang['a_setting_off']) . '</td></tr>
            <tr><td>MAX EXECUTION TIME</td><td>' . ini_get('max_execution_time') . '</td></tr>
            <tr><td>UPLOAD MAX FILESIZE</td><td>' . ini_get('upload_max_filesize') . '</td></tr>
            <tr><td>POST MAX SIZE</td><td>' . ini_get('post_max_size') . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_files_md5'] . '</td><td></td></tr>
            ' . $output_md5 . '
        </table>';
}

/* ##############
 * # MOVIE LIST #
 */##############
if ($option == 'movieslist') {
    $list_sql = 'SELECT id, title, trailer, play_count, hide FROM ' . $mysql_tables[0] . ' ORDER BY title';
    $list_result = mysql_query($list_sql);
    $output_panel = '
        <table id="movie" class="table">
            <tr class="bold"><td>
                </td><td>ID</td>
                <td>' . $lang['a_title'] . '</td>
                <td><img src="admin/img/i_poster.png" title="' . $lang['a_poster'] . '" alt=""></td>
                <td><img src="admin/img/i_fanart.png" title="' . $lang['a_fanart'] . '" alt=""></td>
                <td><img src="admin/img/i_trailer.png" title="' . $lang['a_trailer'] . '" alt=""></td>
                <td><img src="admin/img/i_hidden.png" title="' . $lang['a_visible'] . ' / ' . $lang['a_hidden'] . '" alt=""></td>
                <td><img src="admin/img/i_delete.png" title="' . $lang['a_delete'] . '" alt=""></td>
            </tr>';
    $i = 0;
    while ($list = mysql_fetch_array($list_result)) {
        if (file_exists('cache/' . $mysql_tables[0] . '_' . $list['id'] . '.jpg')) {
            $poster_exist = '<img src="admin/img/exist.png" alt="">';
        } else {
            $poster_exist = '';
        }
        if (file_exists('cache/' . $mysql_tables[0] . '_' . $list['id'] . '_f.jpg')) {
            $fanart_exist = '<img src="admin/img/exist.png" alt="">';
        } else {
            $fanart_exist = '';
        }
        if (stristr($list['trailer'], 'http://')) {
            $trailer_link = '<a href="' . $list['trailer'] . '" target="_blank"><img class="animate" src="admin/img/link.png" title="Link" alt=""></a>';
        } else {
            $trailer_link = '';
        }
        if ($list['hide'] == 1) {
            $hide = '<img class="hidden animate" src="admin/img/hidden.png" title="' . $lang['a_visible'] . ' / ' . $lang['a_hidden'] . '" alt="">';
        } else {
            $hide = '<img class="visible animate" src="admin/img/visible.png" title="' . $lang['a_visible'] . ' / ' . $lang['a_hidden'] . '" alt="">';
        }
        $i++;
        $output_panel.= '
            <tr id="' . $list['id'] . '">
                <td>' . $i . '</td><td>' . $list['id'] . '</td>
                <td>' . $list['title'] . '</td>
                <td>'  . $poster_exist . '</td>
                <td>'  . $fanart_exist . '</td>
                <td>'  . $trailer_link . '</td>
                <td>' . $hide . '</td>
                <td><img class="delete_row animate" src="admin/img/delete.png" title="' . $lang['a_delete'] . '" alt=""></td>
            </tr>';
    }
    $output_panel.= '</table><a id="delete_all" class="box" href="admin.php?option=delete_all_movies">' . $lang['a_delete_all'] . '</a>';
}

// DELETE ALL
if ($option == 'delete_all_movies') {
    $truncate_sql = 'TRUNCATE ' . $mysql_tables[0];
    $truncate_result = mysql_query($truncate_sql);
    $files = scandir('cache/');
    foreach($files as $file) {
        $match = preg_match('|^movies|', $file);
        if ($match == 1) {
            unlink('cache/' . $file);
        }
    }
}

/* ###############
 * # TVSHOW LIST #
 */###############
if ($option == 'tvshowslist') {
    $list_sql = 'SELECT id, title, play_count, hide FROM ' . $mysql_tables[1] . ' ORDER BY title';
    $list_result = mysql_query($list_sql);
    $output_panel = '
        <table id="tvshow" class="table">
            <tr class="bold"><td>
                </td><td>ID</td>
                <td>' . $lang['a_title'] . '</td>
                <td><img src="admin/img/i_poster.png" title="' . $lang['a_poster'] . '" alt=""></td>
                <td><img src="admin/img/i_fanart.png" title="' . $lang['a_fanart'] . '" alt=""></td>
                <td><img src="admin/img/i_hidden.png" title="' . $lang['a_visible'] . ' / ' . $lang['a_hidden'] . '" alt=""></td>
                <td><img src="admin/img/i_delete.png" title="' . $lang['a_delete'] . '" alt=""></td>
            </tr>';
    $i = 0;
    while ($list = mysql_fetch_array($list_result)) {
        if (file_exists('cache/' . $mysql_tables[1] . '_' . $list['id'] . '.jpg')) {
            $poster_exist = '<img src="admin/img/exist.png" alt="">';
        } else {
            $poster_exist = '';
        }
        if (file_exists('cache/' . $mysql_tables[1] . '_' . $list['id'] . '_f.jpg')) {
            $fanart_exist = '<img src="admin/img/exist.png" alt="">';
        } else {
            $fanart_exist = '';
        }
        if ($list['hide'] == 1) {
            $hide = '<img class="hidden animate" src="admin/img/hidden.png" title="visible / hide" alt="">';
        } else {
            $hide = '<img class="visible animate" src="admin/img/visible.png" title="visible / hide" alt="">';
        }
        $i++;
        $output_panel.= '
            <tr id="' . $list['id'] . '">
                <td>' . $i . '</td><td>' . $list['id'] . '</td>
                <td>' . $list['title'] . '</td>
                <td>'  . $poster_exist . '</td>
                <td>'  . $fanart_exist . '</td>
                <td>' . $hide . '</td>
                <td><img class="delete_row animate" src="admin/img/delete.png" title="' . $lang['a_delete'] . '" alt=""></td>
            </tr>';
    }
    $output_panel.= '</table><a id="delete_all" class="box" href="admin.php?option=delete_all_tvshows">' . $lang['a_delete_all'] . '</a>';
}

// DELETE ALL
if ($option == 'delete_all_tvshows') {
    $truncate_sql = 'TRUNCATE ' . $mysql_tables[1];
    $truncate_result = mysql_query($truncate_sql);
    $truncate_sql = 'TRUNCATE ' . $mysql_tables[2];
    $truncate_result = mysql_query($truncate_sql);
    $files = scandir('cache/');
    foreach($files as $file) {
        $match = preg_match('|^tvshows|', $file);
        if ($match == 1) {
            unlink('cache/' . $file);
        }
    }
}

/* ############
 * # SETTINGS #
 */############
if ($option == 'settings') {
    
    $output_lang = '';
    $output_theme = '';
    $output_view = '';
    $output_panel_top = '';
    $output_panel_view = '';
    $output_watched_status = '';
    $output_live_search = '';
    $output_live_search_max_res = '';
    $output_panel_overall = '';
    $output_panel_genre = '';
    $output_panel_year = '';
    $output_panel_country = '';
    $output_panel_sets = '';
    $output_panel_v_codec = '';
    $output_panel_a_codec = '';
    $output_panel_a_chan = '';
    $output_show_fanart = '';
    $output_fadeout_fanart = '';
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
    $option_theme = scandir('templates/');
    foreach ($option_theme as $val) {
        if ($val !== '.' && $val !== '..') {
            $output_theme.= '<option' . ($val == $set['theme'] ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
        }
    }
    
    // set view input
    foreach ($views as $key => $val) {
        $output_view.= '<option' . ($key == $set['view'] ? ' selected="selected"' : '') . ' value="' . $key . '">' . $lang['a_' . $val] . '</option>';
    }
    
    $mode = array(0, 1);
    foreach ($mode as $val) {
        // set panel_top input
        $output_panel_top.= '<option' . ($set['panel_top'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set panel_view input
        $output_panel_view.= '<option' . ($set['panel_view'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set wached status input
        $output_watched_status.= '<option' . ($set['watched_status'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set live search input
        $output_live_search.= '<option' . ($set['live_search'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set show fanart input
        $output_show_fanart.= '<option' . ($set['show_fanart'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        // set fadeout fanart input
        $output_fadeout_fanart.= '<option' . ($set['fadeout_fanart'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
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
        // set panel sets input
        $output_panel_sets.= '<option' . ($set['panel_sets'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
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
                <tr><td>' . $lang['a_view'] . ':</td><td><select name="view">' . $output_view . '</select></td></tr>
                <tr><td>' . $lang['a_per_page'] . ':</td><td><select name="per_page">' . $output_per_page . '</select></td></tr>
                <tr><td>' . $lang['a_panel_top'] . ':</td><td><select name="panel_top">' . $output_panel_top . '</select></td></tr>
                <tr><td>' . $lang['a_panel_view'] . ':</td><td><select name="panel_view">' . $output_panel_view . '</select></td></tr>
                <tr><td>' . $lang['a_watched_status'] . ':</td><td><select name="watched_status">' . $output_watched_status . '</select></td></tr>
                <tr><td>' . $lang['a_live_search'] . ':</td><td><select name="live_search">' . $output_live_search . '</select></td></tr>
                <tr><td>' . $lang['a_live_search_max_res'] . ':</td><td><select name="live_search_max_res">' . $output_live_search_max_res . '</select></td></tr>
                <tr><td>' . $lang['a_show_fanart'] . ':</td><td><select name="show_fanart">' . $output_show_fanart . '</select></td></tr>
                <tr><td>' . $lang['a_fadeout_fanart'] . ':</td><td><select name="fadeout_fanart">' . $output_fadeout_fanart . '</select></td></tr>
                <tr><td>' . $lang['a_show_trailer'] . ':</td><td><select name="show_trailer">' . $output_show_trailer . '</select></td></tr>
                <tr><td>' . $lang['a_protect_site']  . ':</td><td><select name="protect_site">' . $output_protect_site . '</select></td></tr>
                <tr><td class="bold orange">' . $lang['a_set_panel_left'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_panel_overall'] . ':</td><td><select name="panel_overall">' . $output_panel_overall . '</select></td></tr>
                <tr><td>' . $lang['a_panel_genre'] . ':</td><td><select name="panel_genre">' . $output_panel_genre . '</select></td></tr>
                <tr><td>' . $lang['a_panel_year'] . ':</td><td><select name="panel_year">' . $output_panel_year . '</select></td></tr>
                <tr><td>' . $lang['a_panel_country'] . ':</td><td><select name="panel_country">' . $output_panel_country . '</select></td></tr>
                <tr><td>' . $lang['a_panel_sets'] . ':</td><td><select name="panel_sets">' . $output_panel_sets . '</select></td></tr>
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
    $settings_update_sql = 'UPDATE ' . $mysql_tables[3] . ' SET 
        site_name = "' . $_POST['site_name'] . '",
        language = "' . $_POST['language'] . '",
        theme = "' . $_POST['theme'] . '",
        view = "' . $_POST['view'] . '",
        per_page = "' . $_POST['per_page'] . '",
        panel_top_limit = "' . $_POST['panel_top_limit'] . '",
        panel_top_time = "' . $_POST['panel_top_time'] . '",
        panel_top = "' . $_POST['panel_top'] . '",
        panel_view = "' . $_POST['panel_view'] . '",
        watched_status = "' . $_POST['watched_status'] . '",
        live_search = "' . $_POST['live_search'] . '",
        live_search_max_res = "' . $_POST['live_search_max_res'] . '",
        panel_overall = "' . $_POST['panel_overall'] . '",
        panel_genre = "' . $_POST['panel_genre'] . '",
        panel_year = "' . $_POST['panel_year'] . '",
        panel_country = "' . $_POST['panel_country'] . '",
        panel_sets = "' . $_POST['panel_sets'] . '",
        panel_v_codec = "' . $_POST['panel_v_codec'] . '",
        panel_a_codec = "' . $_POST['panel_a_codec'] . '",
        panel_a_chan = "' . $_POST['panel_a_chan'] . '",
        show_fanart = "' . $_POST['show_fanart'] . '",
        fadeout_fanart = "' . $_POST['fadeout_fanart'] . '",
        show_trailer = "' . $_POST['show_trailer'] . '",
        protect_site = "' . $_POST['protect_site'] . '"';
    mysql_query($settings_update_sql);
    
    // delete session var
    $_SESSION = array();
    $_SESSION['logged_admin'] = true;
    
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
                $password_update_sql = 'UPDATE ' . $mysql_tables[4] . ' SET password = "' . md5($_POST['password']) . '" WHERE login ="user"';
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
                $password_update_sql = 'UPDATE ' . $mysql_tables[4] . ' SET password = "' . md5($_POST['password_admin']) . '" WHERE login ="admin"';
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
$pass_check_sql = 'SELECT * FROM ' . $mysql_tables[4] . ' WHERE login = "admin"';
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

/* ##########
 * # BANNER #
 */##########
if ($option == 'banner') {
    if (isset($_POST['banner'])) {
        foreach ($_POST['banner'] as $val) {
            if (!is_numeric($val)) {
                if (!preg_match('/^[0-9abcdefABCDEF]{6}$/', $val)) {
                    $false = true;
                    break;
                }
            }
        }
        if (!isset($false)) {
            $update = 'UPDATE ' . $mysql_tables[3] . ' SET `banner` = "' . banner2str($_POST['banner']) . '"';
            mysql_query($update);
            $_SESSION['banner'] = $set['banner'] = banner2str($_POST['banner']);
            $b = create_banner($lang, 'banner.jpg', banner2str($_POST['banner']), $mysql_tables);
        } else {
            $output_panel_info.= $lang['a_error_form'];
        }
    }
    
    if (isset($_POST['reset'])) {
        $b = create_banner($lang, 'banner.jpg', '0', $mysql_tables);
        $_SESSION['banner'] = $set['banner'] = banner2str($b);
    }

    $b = create_banner($lang, 'banner_v.jpg', $set['banner'], $mysql_tables);
    
    $output_panel.= '<img id="banner" src="cache/banner_v.jpg">';
    $output_panel.= '<form class="banner" action="admin.php?option=banner" method="post"><table id="t_banner">';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_size'] . ':</td><td class="text_left orange">W <input id="w" class="ban" type="text" name="banner[w]" value="' . $b['w'] . '"> H <input id="h" class="ban" type="text" name="banner[h]" value="' . $b['h'] . '"></td></tr>';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_bg'] . ':</td><td class="text_left orange"> color #<input id="bg_c" class="ban" type="text" name="banner[bg_c]" value="' . $b['bg_c'] . '"></td></tr>';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_last_played'] . ':</td><td class="text_left orange"> color #<input id="lw_c" class="ban" type="text" name="banner[lw_c]" value="' . $b['lw_c'] . '"> size <input id="lw_s" class="ban" type="text" name="banner[lw_s]" value="' . $b['lw_s'] . '"> poz. X <input id="lw_x" class="ban" type="text" name="banner[lw_x]" value="' . $b['lw_x'] . '"> poz. Y <input id="lw_y" class="ban" type="text" name="banner[lw_y]" value="' . $b['lw_y'] . '"></td></tr>';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_title'] . ':</td><td class="text_left orange"> color #<input id="t_c" class="ban" type="text" name="banner[t_c]" value="' . $b['t_c'] . '"> size <input id="t_s" class="ban" type="text" name="banner[t_s]" value="' . $b['t_s'] . '"> poz. X <input id="t_x" class="ban" type="text" name="banner[t_x]" value="' . $b['t_x'] . '"> poz. Y <input id="t_y" class="ban" type="text" name="banner[t_y]" value="' . $b['t_y'] . '"></td></tr>';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_o_title'] . ':</td><td class="text_left orange"> color #<input id="o_c" class="ban" type="text" name="banner[o_c]" value="' . $b['o_c'] . '"> size <input id="o_s" class="ban" type="text" name="banner[o_s]" value="' . $b['o_s'] . '"> poz. X <input id="o_x" class="ban" type="text" name="banner[o_x]" value="' . $b['o_x'] . '"> poz. Y <input id="o_y" class="ban" type="text" name="banner[o_y]" value="' . $b['o_y'] . '"></td></tr>';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_info'] . ':</td><td class="text_left orange"> color #<input id="i_c" class="ban" type="text" name="banner[i_c]" value="' . $b['i_c'] . '"> size <input id="i_s" class="ban" type="text" name="banner[i_s]" value="' . $b['i_s'] . '"> poz. X <input id="i_x" class="ban" type="text" name="banner[i_x]" value="' . $b['i_x'] . '"> poz. Y <input id="i_y" class="ban" type="text" name="banner[i_y]" value="' . $b['i_y'] . '"></td></tr>';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_stroke'] . ':</td><td class="text_left orange"> color #<input id="st_c" class="ban" type="text" name="banner[st_c]" value="' . $b['st_c'] . '"></td></tr>';
    $output_panel.= '<tr><td class="text_right">' . $lang['a_banner_border'] . ':</td><td class="text_left orange"> color #<input id="b_c" class="ban" type="text" name="banner[b_c]" value="' . $b['b_c'] . '"></td></tr>';
    $output_panel.= '</table><input type="submit" value="' . $lang['a_save'] . '"></form>';
    $output_panel.= '<p><form action="admin.php?option=banner" method="post">';
    $output_panel.= '<input type="submit" name="reset" value="' . $lang['a_reset'] . '"></form></p>';
    $url = 'http://' . $_SERVER['SERVER_NAME'] . implode('/', array_slice(explode('/', $_SERVER['REQUEST_URI']), 0, -1)) . '/';
    $output_panel.= '<textarea readonly="readonly">' . $url . 'cache/banner.jpg</textarea>';
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
        <link type="image/x-icon" href="admin/img/icon.ico" rel="icon" media="all" />
        <link type="text/css" href="admin/css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
    </head>
    <body>
        <?PHP echo $output_panel_info ?>
        <div class="container">
            <div id="panel_left">
                <a class="box" href="admin.php"><?PHP echo $lang['a_html_main_site'] ?></a>
                <a class="box" href="admin.php?option=movieslist"><?PHP echo $lang['a_html_movie_list'] ?></a>
                <a class="box" href="admin.php?option=tvshowslist"><?PHP echo $lang['a_html_tvshow_list'] ?></a>
                <a class="box" href="admin.php?option=settings"><?PHP echo $lang['a_html_settings'] ?></a>
                <a class="box" href="admin.php?option=password"><?PHP echo $lang['a_html_change_password'] ?></a>
                <a class="box" href="admin.php?option=token"><?PHP echo $lang['a_html_change_token'] ?></a>
                <a class="box" href="admin.php?option=banner"><?PHP echo $lang['a_html_banner'] ?></a>
                <a class="box" href="login.php?login=admin_logout"><?PHP echo $lang['a_html_logout'] ?></a>
            </div>
            <div id="panel_right">
                <?PHP echo $output_panel ?>
            </div>
        </div>
    </body>
</html>