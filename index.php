<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'function.php';
$output_sync = '';

if (file_exists('install.php') or !file_exists('db.php')) {
    header('Location:install.php');
    die();
}

/* ##################
 * # CHECK PASSWORD #
 */##################
if ($set_protect_site == 1) {
    if ($_SESSION['logged'] !== true) {
        header('Location:login.php');
        die();
    }
}

/* ##################
 * # CHECK DATABASE #
 */##################
$conn_ml = @mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
if (!$conn_ml) {
    die($lang['i_could_not_connect'] . ': ' . mysql_error());
}
$sel_ml = mysql_select_db($mysql_ml[4]);
if (!$sel_ml) {
    $create_db_sql = 'CREATE DATABASE ' . $mysql_database_ml;
    $create_db_result = mysql_query($create_db_sql);
    if (!$create_db_result) {
        die($lang['i_cant_create_db']);
    } else {
        $output_sync.= $lang['i_created_db'] . '<br />';
        $sel_ml = mysql_select_db($mysql_ml[4]);
    }
}

// Sets utf8 connections
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

/* #######################
 * # CHECK XBMC DATABASE #
 */#######################
if (!isset($_COOKIE['sync']) && $set_mode == 1) {
    $fp = @fsockopen($mysql_xbmc[0], $mysql_xbmc[1], $errno, $errstr, 3);
    if ($fp) {
        fclose($fp);
        $conn_xbmc = mysql_connect($mysql_xbmc[0] . ':' . $mysql_xbmc[1], $mysql_xbmc[2], $mysql_xbmc[3]);
        if ($conn_xbmc) {
            $sel_xbmc = @mysql_select_db($mysql_xbmc[4]);
            if ($sel_xbmc) {
                $output_sync.= sync_database($col, $mysql_ml, $mysql_xbmc, $conn_ml, $conn_xbmc, $mysql_table_ml, $lang);
                setcookie('sync', true, time()+$set_sync_time*60);
            }
        }
    }
}

/* ##########################
 * # CHECK FILE videodb.xml #
 */##########################
if (file_exists('import/videodb.xml') && $set_mode == 2) {
    $output_sync.= import_xml($col, $mysql_ml, $conn_ml, $mysql_table_ml, $lang);
}

/* ################################
 * # CONNECT TO MOVIELIB DATABASE #
 */################################
mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
mysql_select_db($mysql_ml[4]);
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

/* ##############
 * # INFO PANEL #
 */##############
if (!isset($output_sync) or $output_sync == '') {
    $output_panel_info ='';
} else {
    $output_panel_info = '<div id="panel_info">' . $output_sync . '</div>';
}

/* ##############
 * # TOP PANELS #
 */##############
if ($set_panel_top == 1) {
    
    // recently added
    $output_recently = '<div id="panel_recently">';
    $recently_sql = 'SELECT id, title, poster, date_added FROM ' . $mysql_table_ml . ' ORDER BY date_added DESC LIMIT ' . $set_recently_limit;
    $recently_result = mysql_query($recently_sql);
    while ($recently = mysql_fetch_array($recently_result)) {
        if (!file_exists('cache/' . $recently['id'] . '.jpg')) {
            gd_convert($recently['id'], $recently['poster'], '');
        }
        $output_recently.= '<a href="index.php?id=' . $recently['id'] . '"><img src="cache/' . $recently['id'] . '.jpg" title="' . $recently['title'] . '" alt=""></a>';
    }
    $output_recently.= '</div>';

    // random
    $output_random = '<div id="panel_random">';
    $random_sql = 'SELECT id, title, poster FROM ' . $mysql_table_ml . ' ORDER BY RAND() LIMIT ' . $set_random_limit;
    $random_result = mysql_query($random_sql);
    while ($random = mysql_fetch_array($random_result)) {
        if (!file_exists('cache/' . $random['id'] . '.jpg')) {
            gd_convert($random['id'], $random['poster'], '');
        }
        $output_random.= '<a href="index.php?id=' . $random['id'] . '"><img src="cache/' . $random['id'] . '.jpg" title="' . $random['title'] . '" alt=""></a>';
    }
    $output_random.= '</div>';

    // last_played
    $output_last_played = '<div id="panel_last_played">';
    $last_played_sql = 'SELECT id, title, poster, last_played FROM ' . $mysql_table_ml . ' ORDER BY last_played DESC LIMIT ' . $set_last_played_limit;
    $last_played_result = mysql_query($last_played_sql);
    while ($last_played = mysql_fetch_array($last_played_result)) {
        if (!file_exists('cache/' . $last_played['id'] . '.jpg')) {
            gd_convert($last_played['id'], $last_played['poster'], '');
        }
        $output_last_played.= '<a href="index.php?id=' . $last_played['id'] . '"><img src="cache/' . $last_played['id'] . '.jpg" title="' . $last_played['title'] . '" alt=""></a>';
    }
    $output_last_played.= '</div>';

    // top_rated
    $output_top_rated = '<div id="panel_top_rated">';
    $top_rated_sql = 'SELECT id, title, poster, rating FROM ' . $mysql_table_ml . ' ORDER BY rating DESC LIMIT ' . $set_top_rated_limit;
    $top_rated_result = mysql_query($top_rated_sql);
    while ($top_rated = mysql_fetch_array($top_rated_result)) {
        if (!file_exists('cache/' . $last_played['id'] . '.jpg')) {
            gd_convert($top_rated['id'], $top_rated['poster'], '');
        }
        $output_top_rated.= '<a href="index.php?id=' . $top_rated['id'] . '"><img src="cache/' . $top_rated['id'] . '.jpg" title="' . $top_rated['title'] . '" alt=""></a>';
    }
    $output_top_rated.= '</div>';
    
    $output_panel_top = '<div id="panel_top" class="' . $set_panel_top_time * 100 . '">' . $output_recently . $output_random . $output_last_played . $output_top_rated . '</div>';
    $output_panel_top_title = '<div id="panel_title"><div id="panel_recently_title">' . $lang['i_last_added'] . '</div><div id="panel_random_title">' . $lang['i_randomly'] . '</div><div id="panel_last_played_title">' . $lang['i_last_played'] . '</div><div id="panel_top_rated_title">' . $lang['i_top_rated'] . '</div></div>';
} else {
    $output_panel_top = '';
    $output_panel_top_title = '';
}

/* #################
 * # OVERALL PANEL #
 */#################
if ($set_overall_panel == 1) {
    $overall_sql = 'SELECT play_count FROM ' . $mysql_table_ml;
    $overall_result = mysql_query($overall_sql);
    $overall_all = mysql_num_rows($overall_result);
    $overall_nw = 0;
    while($a = mysql_fetch_array($overall_result)) {
        if ($a[0] == NULL) {
            $overall_nw++;
        }
    }
    $overall_w = $overall_all - $overall_nw;
    $output_panel_overall = '<div id="overall" class="panel_box_title">' . $lang['i_overall_title'] . ':</div><div id="panel_overall" class="panel_box"><ul><li><span class="orange">' . $lang['i_overall_all'] . ':</span> ' . $overall_all . '</li><li><span class="orange">' . $lang['i_overall_watched'] . ':</span> ' . $overall_w . '</li><li><span class="orange">' . $lang['i_overall_notwatched'] . ':</span> ' . $overall_nw . '</li></ul></div>';
} else {
    $output_panel_overall = '';
}

/* ##########
 * # GENRES #
 */##########
$genre_sql = 'SELECT genre FROM ' . $mysql_table_ml . ' ORDER BY genre';
$genre_result = mysql_query($genre_sql);
$genre_array = array();
while ($genre_mysql_array = mysql_fetch_array($genre_result)) {
    foreach (explode(' / ', $genre_mysql_array['genre']) as $val) {
        if (!in_array($val, $genre_array) && strlen($val) > 2) {
            $genre_array[] = $val;
        }
    }
}
$output_genre_menu = '<ul><li>' . (($genre == 'all' and $id == 0) ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;genre=all">' . $lang['i_all'] . '</a>') . '</li>';
sort($genre_array);
$genre_mysql = '%';
foreach ($genre_array as $key => $val) {
    if ((string) $key === (string) $genre) {
        $output_genre_menu.= '<li>' . $val . '</li>';
        $genre_mysql = $val;
    } else {
        $output_genre_menu.= '<li><a href="index.php?sort=' . $sort . '&amp;genre=' . $key . '">' . $val . '</a></li>';
    }
}
$output_genre_menu.= '</ul>';

/* ########
 * # SORT #
 */########
$sort_array = array(1 => $lang['i_title'], $lang['i_year'], $lang['i_rating'], $lang['i_added'], $lang['i_runtime']);
$output_sort_menu = '<span class="bold">' . $lang['i_sort'] . ':</span>';
foreach ($sort_array as $key => $val) {
    $output_sort_menu.= ($sort == $key ? ' <span>' . $val . '</span> ' : ' <a href="index.php?sort=' . $key . '&amp;genre=' . $genre . '">' . $val . '</a> ');
}
$sort_mysql = array(1 => 'title ASC', 'year DESC', 'rating DESC', 'date_added DESC', ' CAST( runtime AS DECIMAL( 10, 2 ) ) DESC');

/* ##########
 * # SEARCH #
 */##########
if ($search == '') {
    $output_search_text = '<input type="text" name="search" value="' . $lang['i_search'] . '..." title="' . $lang['i_search'] . '...">';
    $search_mysql = '%';
} else {
    $output_search_text = $lang['i_result'] . ': ' . $search . ' <a href="index.php"><img src="img/delete.png" title="' . $lang['i_search_del'] . '" alt=""></a> <input type="text" name="search" value="' . $lang['i_search'] . '..." title="' . $lang['i_search'] . '...">';
    $search_mysql = $search;
}

/* #############
 * # PANEL NAV #
 */#############
$nav_sql = 'SELECT id, title, rating, year, genre, country FROM ' . $mysql_table_ml . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" AND id LIKE "' . $id_mysql . '" ORDER BY ' . $sort_mysql[$sort];
$nav_result = mysql_query($nav_sql);
$row = mysql_num_rows($nav_result);
if ($set_per_page == 0) {
    $i_pages = 1;
    $output_nav = '';
} else {
    $i_pages = (ceil($row / $set_per_page));
    $output_nav = ($page == 1 ? $lang['i_previous'] : '<a href="index.php?sort=' . $sort . '&amp;genre=' . $genre . '&amp;page=' . ($page - 1) . '&amp;search=' . $search . '">' . $lang['i_previous'] . '</a>') . ' ' .
            $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . ' ' .
            ($page == $i_pages ? $lang['i_next'] : '<a href="index.php?sort=' . $sort . '&amp;genre=' . $genre . '&amp;page=' . ($page + 1) . '&amp;search=' . $search . '">' . $lang['i_next'] . '</a>');
    if ($row == 0) {
        $output_nav = '';
    }
}

/* ##############
 * # MOVIE LIST #
 */##############
if ($set_per_page == 0) {
    $limit_sql = '';
} else {
    $start = ($page - 1) * $set_per_page;
    $limit_sql = ' LIMIT ' . $start . ', ' . $set_per_page;
}

$list_sql = 'SELECT * FROM ' . $mysql_table_ml . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" AND id LIKE "' . $id_mysql . '" ORDER by ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$output_panel_list = '';
while ($list = mysql_fetch_array($list_result)) {
    
    // poster
    $poster = 'cache/' . $list['id'] . '.jpg';
    if (!file_exists($poster)) {
        gd_convert($list['id'], $list['poster']);
    }
    if (!file_exists($poster)) {
        $poster = 'img/d_poster.jpg';
    }

    // hd flag
    if ($list['v_width'] > 1279) {
        $flag_hd = '<img class="img_flag_hd" src="img/hd.png" alt="">';
    } else {
        $flag_hd = '';
    }

    // video resolution
    $i = 0;
    foreach ($width_height as $key => $val) {
        if ($list['v_width'] >= $key or $list['v_height'] >= $val) {
            $img_flag_vres = '<img id="vres" src="img/flags/vres_' . $vres_array[$i] . '.png" alt="">';
        }
        $i++;
    }

    // video codec
    if (isset($vtype[$list['v_codec']])) {
        $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_' . $vtype[$list['v_codec']] . '.png" alt="">';
    } else {
        $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_defaultscreen.png" alt="">';
    }

    // audio codec 
    if (isset($atype[$list['a_codec']])) {
        $img_flag_atype = '<img id="atype" src="img/flags/acodec_' . $atype[$list['a_codec']] . '.png" alt="">';
    } else {
        $img_flag_atype = '<img id="atype" src="img/flags/acodec_defaultsound.png" alt="">';
    }

    // audio channel
    if (isset($achan[$list['a_channels']])) {
        $img_flag_achan = '<img id="achan" src="img/flags/achan_' . $achan[$list['a_channels']] . '.png" alt="">';
    } else {
        $img_flag_achan = '<img id="achan" src="img/flags/achan_defaultsound.png" alt="">';
    }
    $img_flag = $img_flag_vres . $img_flag_vtype . $img_flag_atype . $img_flag_achan;

    // wached status
    if ($set_watched_status == 1 && $list['play_count'] > 0) {
        $watched = '<img class="watched" src="img/watched.png" alt="" title="' . $lang['i_last_played'] . ': ' . $list['last_played'] . '">';
    } else {
        $watched = '';
    }
    
    $output_panel_list.= '
<div id="movie_' . $list['id'] . '" class="movie">
    <div class="title">' . $list['title'] . '</div>
    <div class="title_org">' . $list['originaltitle'] . '</div>'
    . $watched 
    . $flag_hd . '
    <img id="poster_movie_' . $list['id'] . '" class="poster" src="' . $poster . '">
    <div class="flags">
        <table id="movie_info">
            <tr>
                <td class="movie_left">' . $lang['i_year'] . ':</td>
                <td class="movie_right">' . $list['year'] . '</td>
            </tr>
            <tr>
                <td class="movie_left">' . $lang['i_genre'] . ':</td>
                <td class="movie_right">' . $list['genre'] . '</td>
            </tr>
            <tr>
                <td class="movie_left">' . $lang['i_rating'] . ':</td>
                <td class="movie_right">' . round($list['rating'], 1) . '</td>
            </tr>
            <tr>
                <td class="movie_left">' . $lang['i_country'] . ':</td>
                <td class="movie_right">' . $list['country'] . '</td>
            </tr>
            <tr>
                <td class="movie_left">' . $lang['i_runtime'] . ':</td>
                <td class="movie_right">' . $list['runtime'] . ' ' . $lang['i_minute'] . '</td>
            </tr>
            <tr>
                <td class="movie_left">' . $lang['i_director'] . ':</td>
                <td class="movie_right">' . $list['director'] . '</td>
            </tr>
            <tr>
                <td class="movie_left">' . $lang['i_plot'] . ':</td>
                <td class="movie_right">' . $list['plot'] . '</td>
            </tr>
        </table>
        <img id="img_space" src="img/space.png">
        ' . $img_flag . '
    </div>
</div>';
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set_site_name ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.cycle.lite.js"></script>
        <script type="text/javascript" src="js/jquery.index.js"></script>
    </head>
    <body>
        <?PHP echo $output_panel_info ?>
        <div id="container">
            <?PHP echo $output_panel_top . $output_panel_top_title ?>
            <div id="panel_left">
                <?PHP echo $output_panel_overall ?>
                <div id="genre" class="panel_box_title"><?PHP echo $lang['i_genre'] ?>:</div>
                <div id="panel_genre" class="panel_box"><?PHP echo $output_genre_menu ?></div>
            </div>
            <div id="panel_right">
                <div id="panel_sort"><?PHP echo $output_sort_menu ?></div>
                <div id="panel_search"><form method="get" action="index.php"><?PHP echo $output_search_text ?></form></div>
                <div class="panel_nav"><?PHP echo $output_nav ?></div>
                <?PHP echo $output_panel_list ?>
                <div class="panel_nav"><?PHP echo $output_nav ?></div>
            </div>
        </div>
    </body>
</html>