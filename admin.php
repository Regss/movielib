<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

include('config.php');
include('function.php');

if ($option == 'delete_install') {
    unlink('install.php');
    header('Location:admin.php');
    die('Can\'t redirect to admin.php');
}

if (!file_exists('db.php')) {
    if (file_exists('install.php')) {
        header('Location:install.php');
        die('Can\'t redirect to install.php');
    }
    die('Copy install.php file to script directory');
}

// connect to database
connect($mysql_ml);

// get settings from db
$setting = get_settings();
include('lang/' . $setting['language'] . '/lang.php');

// check install.php file exist
if (file_exists('install.php')) {
    $output_panel_info.= $lang['a_install_exist'] . '<br />';
}

/* ######################
 * CHECK ADMIN PASSWORD #
 */######################
if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
    header('Location:login.php?login=admin');
    die('Cant\'t redirect to login.php');
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
    
    // get version from db
    $db_vers_sql = 'SELECT version FROM config';
    $db_vers_result = mysql_q($db_vers_sql);
    $db_version_assoc = mysql_fetch_assoc($db_vers_result);
    $db_version = $db_version_assoc['version'];
    
    // check tables if versions not match
    if ($db_version !== $version or isset($_GET['tables'])) {
        $output_panel_info.= create_table($mysql_tables, $mysql_indexes, $lang, $version, 0);

        // delete session var
        $_SESSION = array();
        $_SESSION['logged_admin'] = true;
    }

    // Watched
    $overall_movies_sql = 'SELECT play_count, hide FROM movies';
    $overall_movies_result = mysql_q($overall_movies_sql);
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
    
    $overall_tvshows_sql = 'SELECT play_count, hide FROM tvshows';
    $overall_tvshows_result = mysql_q($overall_tvshows_sql);
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
    
    // Cached poster and fanarts
    $cached_dir = scandir('cache/');
    $poster_cached = 0;
    $fanart_cached = 0;
    $exthumb_cached = 0;
    foreach ($cached_dir as $val) {
        if (preg_match_all('/_[0-9]+\.jpg/', $val, $res) == 1) {
            $poster_cached++;
        }
        if (preg_match_all('/_[0-9]+_f\.jpg/', $val, $res) == 1) {
            $fanart_cached++;
        }
        if (preg_match_all('/_[0-9]+_t[0-9]\.jpg/', $val, $res) == 1) {
            $exthumb_cached++;
        }
    }
    
    // Cached actors
    $cached_dir = scandir('cache/actors/');
    $actors_cached = 0;
    foreach ($cached_dir as $val) {
        if (preg_match_all('/[0-9a-z]{10}\.jpg/', $val, $res) == 1) {
            $actors_cached++;
        }
    }
    
    // Directories
    $output_dirs = '';
    foreach ($dir_assoc as $dir) {
        if (file_exists($dir)) {
            $output_dirs.= '<tr><td>' . $dir . '</td><td>' . (file_exists($dir) ? '<span class="green">' . $lang['a_exists'] . '</span>' : '<span class="red">' . $lang['a_not_exists'] . '</span>') . '</td></tr>';
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
            <tr><td>' . $lang['a_cached_actors'] . '</td><td>' . $actors_cached . '</td></tr>
            <tr><td>' . $lang['a_cached_exthumb'] . '</td><td>' . $exthumb_cached . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_server_settings'] . '</td><td></td></tr>
            <tr><td>GD</td><td>' . (extension_loaded('gd') && function_exists('gd_info') ? '<span class="green">' . $lang['a_setting_on'] . '</span>' : '<span class="red">' . $lang['a_setting_off'] . '</span>') . '</td></tr>
            <tr><td>CURL</td><td>' . (function_exists('curl_version') ? '<span class="green">' . $lang['a_setting_on'] . '</span>' : '<span class="red">' . $lang['a_setting_off'] . '</span>') . '</td></tr>
            <tr><td>MOD REWRITE</td><td>' . (array_key_exists('HTTP_MOD_REWRITE', $_SERVER) ? '<span class="green">' . $lang['a_setting_on'] . '</span>' : '<span class="red">' . $lang['a_setting_off'] . '</span>') . '</td></tr>
            <tr><td>ALLOW URL FOPEN</td><td>' . (ini_get('allow_url_fopen') == 1 ? '<span class="green">' . $lang['a_setting_on'] . '</span>' : '<span class="red">' . $lang['a_setting_off'] . '</span>') . '</td></tr>
            <tr><td>MAX EXECUTION TIME</td><td>' . ini_get('max_execution_time') . '</td></tr>
            <tr><td>UPLOAD MAX FILESIZE</td><td>' . ini_get('upload_max_filesize') . '</td></tr>
            <tr><td>POST MAX SIZE</td><td>' . ini_get('post_max_size') . '</td></tr>
            <tr><td class="bold orange">' . $lang['a_server_directories'] . '</td><td></td></tr>
            ' . $output_dirs . '
            <tr><td class="bold orange">' . $lang['a_files_md5'] . '</td><td></td></tr>
            ' . $output_md5 . '
        </table>';
}

/* #########################
 * # MOVIE AND TVSHOW LIST #
 */#########################
if ($option == 'movieslist' or $option == 'tvshowslist') {
    if ($option == 'movieslist') {
        $t = 'movies';
        $list_sql = 'SELECT id, title, trailer, play_count, hide FROM movies ORDER BY title';
    } else {
        $t = 'tvshows';
        $list_sql = 'SELECT id, title, play_count, hide FROM tvshows ORDER BY title';
    }
    
    $list_result = mysql_q($list_sql);
    $output_panel = '
        <table id="' . substr($t, 0, -1) . '" class="table">
            <tr class="bold">
                <td></td>
                <td>ID</td>
                <td>' . $lang['a_title'] . '</td>
                <td><img src="admin/img/i_poster.png" title="' . $lang['a_poster'] . '" alt=""></td>
                <td><img src="admin/img/i_fanart.png" title="' . $lang['a_fanart'] . '" alt=""></td>
                <td><img src="admin/img/i_trailer.png" title="' . $lang['a_trailer'] . '" alt=""></td>
                <td><img src="admin/img/i_hidden.png" title="' . $lang['a_visible'] . ' / ' . $lang['a_hidden'] . '" alt=""></td>
                <td><img src="admin/img/i_delete.png" title="' . $lang['a_delete'] . '" alt=""></td>
            </tr>';
    $i = 0;
    while ($list = mysql_fetch_array($list_result)) {
        if (file_exists('cache/' . $t . '_' . $list['id'] . '.jpg')) {
            $poster_exist = '<img class="p_exist animate" src="admin/img/exist.png" alt="" title="' . $lang['a_delete_poster'] . '">';
        } else {
            $poster_exist = '';
        }
        if (file_exists('cache/' . $t . '_' . $list['id'] . '_f.jpg')) {
            $fanart_exist = '<img class="f_exist animate" src="admin/img/exist.png" alt="" title="' . $lang['a_delete_fanart'] . '">';
        } else {
            $fanart_exist = '';
        }
        if ($t == 'movies' && stristr($list['trailer'], 'http://')) {
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
                <td class="poster">'  . $poster_exist . '</td>
                <td class="fanart">'  . $fanart_exist . '</td>
                <td>'  . $trailer_link . '</td>
                <td>' . $hide . '</td>
                <td><img class="delete_row animate" src="admin/img/delete.png" title="' . $lang['a_delete'] . '" alt=""></td>
            </tr>';
    }
    $output_panel.= '</table><a id="delete_all" class="box" href="admin.php?option=delete_all_' . $t . '">' . $lang['a_delete_all'] . '</a>';
}

// DELETE ALL
if ($option == 'delete_all_movies' or $option == 'delete_all_tvshows') {
    if ($option == 'delete_all_movies') {
        $truncate = array('movies', 'movies_country', 'movies_actor', 'movies_director', 'movies_genre', 'movies_stream', 'movies_studio');
        $reg_exp = '#^(movies)#';
    } else {
        $truncate = array('tvshows', 'tvshows_actor', 'tvshows_genre', 'episodes', 'episodes_stream');
        $reg_exp = '#^(tvshows|episodes)#';
    }
    foreach ($truncate as $t) {
        $sql = 'TRUNCATE `' . $t . '`';
        mysql_q($sql);
    }
    $files = scandir('cache/');
    $files_to_remove = array();
    foreach($files as $file) {
        $match = preg_match_all($reg_exp, $file);
        if ($match > 0) {
            $files_to_remove[] = $file;
        }
    }
    remove_images($files_to_remove);
}

/* ############
 * # SETTINGS #
 */############
if ($option == 'settings') {
    
    $output_lang = '';
    $output_theme = '';
    $output_select_media_header = '';
    $output_view = '';
    $output_panel_top = '';
    $output_panel_view = '';
    $output_watched_status = '';
    $output_show_playcount = '';
    $output_live_search = '';
    $output_live_search_max_res = '';
    $output_panel_overall = '';
    $output_panel_genre = '';
    $output_panel_year = '';
    $output_panel_country = '';
    $output_panel_set = '';
    $output_panel_studio = '';
    $output_show_fanart = '';
    $output_fadeout_fanart = '';
    $output_show_trailer = '';
    $output_show_facebook = '';
    $output_protect_site = '';
    $output_mod_rewrite = '';
    $output_per_page = '';
    $output_default_sort = '';
    $output_default_watch = '';
    $output_panel_top_limit = '';
    $output_xbmc_thumbs = '';
    $output_xbmc_posters = '';
    $output_xbmc_fanarts = '';
    $output_xbmc_exthumbs = '';
    $output_xbmc_exthumbs_q = '';
    $output_xbmc_auto_conf_remote = '';
    $output_xbmc_master = '';
    
    // set language input
    $option_language = scandir('lang/');
    foreach ($option_language as $val) {
        if (file_exists('lang/' . $val . '/lang.php')) {
            if (array_key_exists($val, $langs)) {
                $lang_title = $langs[$val];
            } else {
                $lang_title = $val;
            }
            $output_lang.= '<option' . ($val == $setting['language'] ? ' selected="selected"' : '') . ' value="' . $val . '">' . $lang_title . '</option>';
        }
    }
    
    // set default sort
    $sort_array = array(
        1 => $lang['i_title'],
        4 => $lang['i_rating'],
        5 => $lang['i_added'],
        7 => $lang['i_last_played'],
        8 => $lang['i_most_watched']
    );
    foreach ($sort_array as $key => $val) {
        $output_default_sort.= '<option' . ($key == $setting['default_sort'] ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
    }
    
     // set default watch
    $watch_array = array(
        0 => $lang['i_all'],
        1 => $lang['i_watched'],
        2 => $lang['i_unwatched']
    );
    foreach ($watch_array as $key => $val) {
        $output_default_watch.= '<option' . ($key == $setting['default_watch'] ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
    }
    
    // set theme input
    $option_theme = scandir('templates/');
    foreach ($option_theme as $val) {
        if ($val !== '.' && $val !== '..') {
            $output_theme.= '<option' . ($val == $setting['theme'] ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
        }
    }
    
    // set view input
    foreach ($views as $key => $val) {
        $output_view.= '<option' . ($key == $setting['view'] ? ' selected="selected"' : '') . ' value="' . $key . '">' . $lang['a_' . $val] . '</option>';
    }
    
    // extra thumbs size
    $dimens = array('1920x1080', '1280x720', '853x480');
    foreach ($dimens as $val) {
        $output_xbmc_exthumbs_q.= '<option' . ($val == $setting['xbmc_exthumbs_q'] ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
    }
    
    $mode = array(0, 1);
    foreach ($mode as $val) {
        $output_panel_top.= '<option' . ($setting['panel_top'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_select_media_header.= '<option' . ($setting['select_media_header'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_panel_view.= '<option' . ($setting['panel_view'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_watched_status.= '<option' . ($setting['watched_status'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_show_playcount.= '<option' . ($setting['show_playcount'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_live_search.= '<option' . ($setting['live_search'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_show_fanart.= '<option' . ($setting['show_fanart'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_fadeout_fanart.= '<option' . ($setting['fadeout_fanart'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_show_trailer.= '<option' . ($setting['show_trailer'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_show_facebook.= '<option' . ($setting['show_facebook'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_protect_site.= '<option' . ($setting['protect_site'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_mod_rewrite.= '<option' . ($setting['mod_rewrite'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_xbmc_thumbs.= '<option' . ($setting['xbmc_thumbs'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_xbmc_posters.= '<option' . ($setting['xbmc_posters'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_xbmc_fanarts.= '<option' . ($setting['xbmc_fanarts'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_xbmc_exthumbs.= '<option' . ($setting['xbmc_exthumbs'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_xbmc_auto_conf_remote.= '<option' . ($setting['xbmc_auto_conf_remote'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
        $output_xbmc_master.= '<option' . ($setting['xbmc_master'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . ($val == 0 ? $lang['a_setting_off'] : $lang['a_setting_on']) . '</option>';
    }
    
    $mode2 = array(0 => $lang['a_setting_off'], 1 => $lang['a_setting_on_expanded'], 2 => $lang['a_setting_on_collapsed']);
    foreach ($mode2 as $key => $val) {
        $output_panel_overall.= '<option' . ($setting['panel_overall'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val  . '</option>';
        $output_panel_genre.= '<option' . ($setting['panel_genre'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        $output_panel_year.= '<option' . ($setting['panel_year'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        $output_panel_country.= '<option' . ($setting['panel_country'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        $output_panel_set.= '<option' . ($setting['panel_set'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
        $output_panel_studio.= '<option' . ($setting['panel_studio'] == $key ? ' selected="selected"' : '') . ' value="' . $key . '">' . $val . '</option>';
    }
    
    $quantity = array(5, 10, 20, 50, 100);
    foreach ($quantity as $val) {
        // set per page input
        $output_per_page.= '<option' . ($setting['per_page'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
        // set panel top limit
        $output_panel_top_limit.= '<option' . ($setting['panel_top_limit'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
        // set live search max res
        $output_live_search_max_res.= '<option' . ($setting['live_search_max_res'] == $val ? ' selected="selected"' : '') . ' value="' . $val . '">' . $val . '</option>';
    }

    // output form
    $output_panel.= '
        <form action="admin.php?option=settings_save" method="post">
            <table class="table">
                <tr><td class="bold orange">' . $lang['a_set_sync'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_xbmc_thumbs'] . ':</td><td><select name="xbmc_thumbs">' . $output_xbmc_thumbs . '</select></td></tr>
                <tr><td>' . $lang['a_xbmc_posters'] . ':</td><td><select name="xbmc_posters">' . $output_xbmc_posters . '</select></td></tr>
                <tr><td>' . $lang['a_xbmc_fanarts'] . ':</td><td><select name="xbmc_fanarts">' . $output_xbmc_fanarts . '</select></td></tr>
                <tr><td>' . $lang['a_xbmc_exthumbs'] . ':</td><td><select name="xbmc_exthumbs">' . $output_xbmc_exthumbs . '</select></td></tr>
                <tr><td>' . $lang['a_xbmc_exthumbs_q'] . ':</td><td><select name="xbmc_exthumbs_q">' . $output_xbmc_exthumbs_q . '</select></td></tr>
                <tr><td>' . $lang['a_xbmc_auto_conf_remote'] . ':</td><td><select name="xbmc_auto_conf_remote">' . $output_xbmc_auto_conf_remote . '</select></td></tr>
                <tr><td>' . $lang['a_xbmc_master'] . ':</td><td><select name="xbmc_master">' . $output_xbmc_master . '</select></td></tr>
                <tr><td class="bold orange">' . $lang['a_set_main'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_site_name'] . ':</td><td><input type="text" name="site_name" value="' . $setting['site_name'] . '" /></td></tr>
                <tr><td>' . $lang['a_language'] . ':</td><td><select name="language">' . $output_lang . '</select></td></tr>
                <tr><td>' . $lang['a_theme'] . ':</td><td><select name="theme">' . $output_theme . '</select></td></tr>
                <tr><td>' . $lang['a_select_media_header'] . ':</td><td><select name="select_media_header">' . $output_select_media_header . '</select></td></tr>
                <tr><td>' . $lang['a_view'] . ':</td><td><select name="view">' . $output_view . '</select></td></tr>
                <tr><td>' . $lang['a_per_page'] . ':</td><td><select name="per_page">' . $output_per_page . '</select></td></tr>
                <tr><td>' . $lang['a_default_sort'] . ':</td><td><select name="default_sort">' . $output_default_sort . '</select></td></tr>
                <tr><td>' . $lang['a_default_watch'] . ':</td><td><select name="default_watch">' . $output_default_watch . '</select></td></tr>
                <tr><td>' . $lang['a_panel_top'] . ':</td><td><select name="panel_top">' . $output_panel_top . '</select></td></tr>
                <tr><td>' . $lang['a_panel_view'] . ':</td><td><select name="panel_view">' . $output_panel_view . '</select></td></tr>
                <tr><td>' . $lang['a_watched_status'] . ':</td><td><select name="watched_status">' . $output_watched_status . '</select></td></tr>
                <tr><td>' . $lang['a_show_playcount'] . ':</td><td><select name="show_playcount">' . $output_show_playcount . '</select></td></tr>
                <tr><td>' . $lang['a_live_search'] . ':</td><td><select name="live_search">' . $output_live_search . '</select></td></tr>
                <tr><td>' . $lang['a_live_search_max_res'] . ':</td><td><select name="live_search_max_res">' . $output_live_search_max_res . '</select></td></tr>
                <tr><td>' . $lang['a_show_fanart'] . ':</td><td><select name="show_fanart">' . $output_show_fanart . '</select></td></tr>
                <tr><td>' . $lang['a_fadeout_fanart'] . ':</td><td><select name="fadeout_fanart">' . $output_fadeout_fanart . '</select></td></tr>
                <tr><td>' . $lang['a_show_trailer'] . ':</td><td><select name="show_trailer">' . $output_show_trailer . '</select></td></tr>
                <tr><td>' . $lang['a_show_facebook'] . ':</td><td><select name="show_facebook">' . $output_show_facebook . '</select></td></tr>
                <tr><td>' . $lang['a_protect_site']  . ':</td><td><select name="protect_site">' . $output_protect_site . '</select></td></tr>
                <tr><td>' . $lang['a_mod_rewrite']  . ':</td><td><select name="mod_rewrite">' . $output_mod_rewrite . '</select></td></tr>
                <tr><td class="bold orange">' . $lang['a_set_panel_left'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_panel_overall'] . ':</td><td><select name="panel_overall">' . $output_panel_overall . '</select></td></tr>
                <tr><td>' . $lang['a_panel_genre'] . ':</td><td><select name="panel_genre">' . $output_panel_genre . '</select></td></tr>
                <tr><td>' . $lang['a_panel_year'] . ':</td><td><select name="panel_year">' . $output_panel_year . '</select></td></tr>
                <tr><td>' . $lang['a_panel_country'] . ':</td><td><select name="panel_country">' . $output_panel_country . '</select></td></tr>
                <tr><td>' . $lang['a_panel_set'] . ':</td><td><select name="panel_set">' . $output_panel_set . '</select></td></tr>
                <tr><td>' . $lang['a_panel_studio'] . ':</td><td><select name="panel_studio">' . $output_panel_studio . '</select></td></tr>
                <tr><td class="bold orange">' . $lang['a_set_panel_top'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_panel_top_time'] . ':</td><td><input type="text" name="panel_top_time" value="' . $setting['panel_top_time'] . '" /></td></tr>
                <tr><td>' . $lang['a_panel_top_limit'] . ':</td><td><select name="panel_top_limit">' . $output_panel_top_limit . '</select></td></tr>
            </table><br />
                <input type="submit" value="' . $lang['a_save'] . '" />
        </form>';
}

// Saving settings
if ($option == 'settings_save' && isset($_POST) && count($_POST) > 10) {
    $settings_array = array();
    $test = true;
    foreach ($_POST as $key => $val) {
        $settings_array[] = $key . ' = "' . $val . '"';
        if (strlen($val) == 0) {
            $test = false;
            break;
        }
    }
    $settings_update_sql = 'UPDATE config SET ' . implode(', ', $settings_array);
    
    // delete session var
    $_SESSION = array();
    $_SESSION['logged_admin'] = true;
    if ($test) {
        $settings_update_res = mysql_q($settings_update_sql);
        $output_panel_info.= $lang['a_saved'] . '<br />';
    } else {
        $output_panel_info.= $lang['a_not_saved'] . '<br />';
    }
    reset_hash();
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
                $password_update_sql = 'UPDATE users SET password = "' . md5($_POST['password']) . '" WHERE login ="user"';
                mysql_q($password_update_sql);
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
                $password_update_sql = 'UPDATE users SET password = "' . md5($_POST['password_admin']) . '" WHERE login ="admin"';
                mysql_q($password_update_sql);
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
$pass_check_sql = 'SELECT * FROM users WHERE login = "admin"';
$pass_check_result = mysql_q($pass_check_sql);
$pass_check = mysql_fetch_array($pass_check_result);
if ($pass_check['password'] == '21232f297a57a5a743894a0e4a801fc3') {
    $output_panel_info.= $lang['a_pass_default'] . '<br />';
}

/* #########
 * # TOKEN #
 */#########
if ($option == 'token') {
    if (isset($_POST['new_token'])) {
        $new_token = change_token();
        $output_panel_info.= $lang['a_token_changed'] . '<br />';
    } else {
        $new_token = $setting['token'];
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
            $update_sql = 'UPDATE config SET `banner` = "' . banner2str($_POST['banner']) . '"';
            mysql_q($update_sql);
            $_SESSION['banner'] = $setting['banner'] = banner2str($_POST['banner']);
            $b = create_banner($lang, 'banner.jpg', banner2str($_POST['banner']));
        } else {
            $output_panel_info.= $lang['a_error_form'];
        }
    }
    
    if (isset($_POST['reset'])) {
        $b = create_banner($lang, 'banner.jpg', '0');
        $_SESSION['banner'] = $setting['banner'] = banner2str($b);
    }

    $b = create_banner($lang, 'banner_v.jpg', $setting['banner']);
    
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

/* ########
 * # XBMC #
 */########
if ($option == 'xbmc') {
    $_SESSION = array();
    $_SESSION['logged_admin'] = true;
    if ($setting['xbmc_auto_conf_remote'] == 1) {
        $output_panel.= '<p class="green">' . $lang['a_xbmc_auto_conf_enabled'] . '</p>';
        $d = 'disabled';
    } else {
        $d = '';
    }
    $output_panel.= '
        <form action="admin.php?option=xbmc_save" method="post">
            <table class="table">
                <tr><td class="bold orange">' . $lang['a_xbmc_settings'] . '</td><td></td></tr>
                <tr><td>' . $lang['a_xbmc_host'] . '</td><td><input id="xbmc_host" type="input" name="xbmc_host" value="' . $setting['xbmc_host'] . '" ' . $d . '/></td></tr>
                <tr><td>' . $lang['a_xbmc_port'] . '</td><td><input id="xbmc_port" type="input" name="xbmc_port" value="' . $setting['xbmc_port'] . '" ' . $d . '/></td></tr>
                <tr><td>' . $lang['a_xbmc_login'] . '</td><td><input id="xbmc_login" type="input" name="xbmc_login" value="' . $setting['xbmc_login'] . '" ' . $d . '/></td></tr>
                <tr><td>' . $lang['a_xbmc_pass'] . '</td><td><input id="xbmc_pass" type="input" name="xbmc_pass" value="' . $setting['xbmc_pass'] . '" ' . $d . '/></td></tr>
            </table>
                <div id="xbmc_test" class="box"><div></div>' . $lang['a_xmbc_test'] . '</div>
                <input type="submit" value="' . $lang['a_save'] . '" ' . $d . '/>
        </form>
    ';
}

// Save connection
if ($option == 'xbmc_save') {
    $xbmc_update_sql = 'UPDATE config SET 
        xbmc_host = "' . $_POST['xbmc_host'] . '", 
        xbmc_port = "' . $_POST['xbmc_port'] . '",
        xbmc_login = "' . $_POST['xbmc_login'] . '", 
        xbmc_pass = "' . $_POST['xbmc_pass'] . '"';
    mysql_q($xbmc_update_sql);
    $output_panel_info.= $lang['a_xbmc_saved'] . '<br />';
    $_SESSION = array();
    $_SESSION['logged_admin'] = true;
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
        <title><?PHP echo $setting['site_name'] ?> - <?PHP echo $lang['a_html_admin_panel'] ?></title>
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
                <a class="box" href="admin.php"><?PHP echo $lang['a_html_overall'] ?></a>
                <a class="box" href="index.php"><?PHP echo $lang['a_html_library'] ?></a>
                <a class="box" href="admin.php?option=movieslist"><?PHP echo $lang['a_html_movie_list'] ?></a>
                <a class="box" href="admin.php?option=tvshowslist"><?PHP echo $lang['a_html_tvshow_list'] ?></a>
                <a class="box" href="admin.php?option=settings"><?PHP echo $lang['a_html_settings'] ?></a>
                <a class="box" href="admin.php?option=password"><?PHP echo $lang['a_html_password'] ?></a>
                <a class="box" href="admin.php?option=token"><?PHP echo $lang['a_html_change_token'] ?></a>
                <a class="box" href="admin.php?option=banner"><?PHP echo $lang['a_html_banner'] ?></a>
                <a class="box" href="admin.php?option=xbmc">XBMC</a>
                <a class="box" href="login.php?login=admin_logout"><?PHP echo $lang['a_html_logout'] ?></a>
            </div>
            <div id="panel_right">
                <?PHP echo $output_panel ?>
            </div>
        </div>
    </body>
</html>