<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'function.php';

if (file_exists('install.php') or !file_exists('db.php')) {
    header('Location:install.php');
    die();
}

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_ml, $mysql_tables, $settings_name);
require_once 'lang/lang_' . $set['language'] . '.php';

/* ##################
 * # CHECK PASSWORD #
 */##################
if ($set['protect_site'] == 1) {
    if ($_SESSION['logged'] !== true) {
        header('Location:login.php');
        die();
    }
}

/* #######################
 * # CHECK XBMC DATABASE #
 */#######################
if (!isset($_COOKIE['sync']) && $set['mode'] == 1) {
    $fp = fsockopen($set['mysql_host_xbmc'], $set['mysql_port_xbmc'], $errno, $errstr, 3);
    if ($fp) {
        fclose($fp);
        connect_xbmc($set);
        $output_panel_info.= sync_database($col, $mysql_ml, $set, $mysql_tables[0], $lang);
    }
}

/* ##########################
 * # CHECK FILE videodb.xml #
 */##########################
if (file_exists('import/videodb.xml') && $set['mode'] == 0) {
    $output_panel_info.= import_xml($col, $mysql_ml, $mysql_tables[0], $lang);
}

/* ################################
 * # CONNECT TO MOVIELIB DATABASE #
 */################################
connect($mysql_ml);

/* #############
 * # TOP PANEL #
 */#############
if ($set['panel_top'] == 1) {
    
    // recently added
    $output_recently = '<div id="panel_recently">';
    $recently_sql = 'SELECT id, title, poster, date_added FROM ' . $mysql_tables[0] . ' ORDER BY date_added DESC LIMIT ' . $set['recently_limit'];
    $recently_result = mysql_query($recently_sql);
    while ($recently = mysql_fetch_array($recently_result)) {
        if (!file_exists('cache/' . $recently['id'] . '.jpg')) {
            gd_convert('cache/' . $recently['id'] . '.jpg', $recently['poster'], 140, 198);
        }
        if (file_exists('cache/' . $recently['id'] . '.jpg')) {
            $output_recently.= '<a href="index.php?id=' . $recently['id'] . '"><img src="cache/' . $recently['id'] . '.jpg" title="' . $recently['title'] . '" alt=""></a>';
        }
    }
    $output_recently.= '</div>';

    // random
    $output_random = '<div id="panel_random">';
    $random_sql = 'SELECT id, title, poster FROM ' . $mysql_tables[0] . ' ORDER BY RAND() LIMIT ' . $set['random_limit'];
    $random_result = mysql_query($random_sql);
    while ($random = mysql_fetch_array($random_result)) {
        if (!file_exists('cache/' . $random['id'] . '.jpg')) {
            gd_convert('cache/' . $random['id'] . '.jpg', $random['poster'], 140, 198);
        }
        if (file_exists('cache/' . $random['id'] . '.jpg')) {
            $output_random.= '<a href="index.php?id=' . $random['id'] . '"><img src="cache/' . $random['id'] . '.jpg" title="' . $random['title'] . '" alt=""></a>';
        }
    }
    $output_random.= '</div>';

    // last_played
    $output_last_played = '<div id="panel_last_played">';
    $last_played_sql = 'SELECT id, title, poster, last_played FROM ' . $mysql_tables[0] . ' ORDER BY last_played DESC LIMIT ' . $set['last_played_limit'];
    $last_played_result = mysql_query($last_played_sql);
    while ($last_played = mysql_fetch_array($last_played_result)) {
        if (!file_exists('cache/' . $last_played['id'] . '.jpg')) {
            gd_convert('cache/' . $last_played['id'] . '.jpg', $last_played['poster'], 140, 198);
        }
        if (file_exists('cache/' . $last_played['id'] . '.jpg')) {
            $output_last_played.= '<a href="index.php?id=' . $last_played['id'] . '"><img src="cache/' . $last_played['id'] . '.jpg" title="' . $last_played['title'] . '" alt=""></a>';
        }
    }
    $output_last_played.= '</div>';

    // top_rated
    $output_top_rated = '<div id="panel_top_rated">';
    $top_rated_sql = 'SELECT id, title, poster, rating FROM ' . $mysql_tables[0] . ' ORDER BY rating DESC LIMIT ' . $set['top_rated_limit'];
    $top_rated_result = mysql_query($top_rated_sql);
    while ($top_rated = mysql_fetch_array($top_rated_result)) {
        if (!file_exists('cache/' . $top_rated['id'] . '.jpg')) {
            gd_convert('cache/' . $top_rated['id'] . '.jpg', $top_rated['poster'], 140, 198);
        }
        if (file_exists('cache/' . $top_rated['id'] . '.jpg')) {
            $output_top_rated.= '<a href="index.php?id=' . $top_rated['id'] . '"><img src="cache/' . $top_rated['id'] . '.jpg" title="' . $top_rated['title'] . '" alt=""></a>';
        }
    }
    $output_top_rated.= '</div>';
    
    $output_panel_top = '<div id="panel_top" class="' . $set['panel_top_time'] * 1000 . '">' . $output_recently . $output_random . $output_last_played . $output_top_rated . '</div>';
    $output_panel_top_title = '<div id="panel_title"><div id="panel_recently_title">' . $lang['i_last_added'] . '</div><div id="panel_random_title">' . $lang['i_randomly'] . '</div><div id="panel_last_played_title">' . $lang['i_last_played'] . '</div><div id="panel_top_rated_title">' . $lang['i_top_rated'] . '</div></div>';
} else {
    $output_panel_top = '';
    $output_panel_top_title = '';
}

/* #################
 * # OVERALL PANEL #
 */#################
if ($set['overall_panel'] == 1) {
    $overall_sql = 'SELECT play_count FROM ' . $mysql_tables[0];
    $overall_result = mysql_query($overall_sql);
    $overall_all = mysql_num_rows($overall_result);
    $overall_nw = 0;
    while($a = mysql_fetch_array($overall_result)) {
        if ($a[0] == NULL) {
            $overall_nw++;
        }
    }
    $overall_w = $overall_all - $overall_nw;
    $output_panel_overall = '<div id="overall" class="panel_box_title">' . $lang['i_overall_title'] . ':</div><div id="panel_overall" class="panel_box"><ul><li><span class="des">' . $lang['i_overall_all'] . ':</span> ' . $overall_all . '</li><li><span class="des">' . $lang['i_overall_watched'] . ':</span> ' . $overall_w . '</li><li><span class="des">' . $lang['i_overall_notwatched'] . ':</span> ' . $overall_nw . '</li></ul></div>';
} else {
    $output_panel_overall = '';
}

/* ##########
 * # GENRES #
 */##########
$genre_sql = 'SELECT genre FROM ' . $mysql_tables[0] . ' ORDER BY genre';
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
    $output_search_text = $lang['i_result'] . ': ' . $search . ' <a href="index.php"><img src="css/' . $set['theme'] . '/img/delete.png" title="' . $lang['i_search_del'] . '" alt=""></a> <input type="text" name="search" value="' . $lang['i_search'] . '..." title="' . $lang['i_search'] . '...">';
    $search_mysql = $search;
}

/* #############
 * # PANEL NAV #
 */#############
$nav_sql = 'SELECT id, title, rating, year, genre, country FROM ' . $mysql_tables[0] . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" AND id LIKE "' . $id_mysql . '" ORDER BY ' . $sort_mysql[$sort];
$nav_result = mysql_query($nav_sql);
$row = mysql_num_rows($nav_result);
if ($set['per_page'] == 0) {
    $i_pages = 1;
    $output_nav = '';
} else {
    $i_pages = (ceil($row / $set['per_page']));
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
if ($set['per_page'] == 0) {
    $limit_sql = '';
} else {
    $start = ($page - 1) * $set['per_page'];
    $limit_sql = ' LIMIT ' . $start . ', ' . $set['per_page'];
}

$list_sql = 'SELECT * FROM ' . $mysql_tables[0] . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" AND id LIKE "' . $id_mysql . '" ORDER by ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$output_panel_list = '';
while ($list = mysql_fetch_array($list_result)) {
    
    // poster
    $poster = 'cache/' . $list['id'] . '.jpg';
    if (!file_exists($poster)) {
        gd_convert($poster, $list['poster'], 140, 198);
    }
    if (!file_exists($poster)) {
        $poster = 'css/' . $set['theme'] . '/img/d_poster.jpg';
    }

    // hd flag
    $flag_hd = '';
    if ($list['v_width'] >= 1280) {
        $flag_hd = '<img class="img_flag_hd" src="css/' . $set['theme'] . '/img/hd.png" alt="">';
    }
    
    // video resolution
    $img_flag_vres = '';
    foreach ($vres_assoc as $val) {
        if (is_numeric($list['v_width']) && $list['v_width'] >= $val) {
            $img_flag_vres = '<img id="vres" src="css/' . $set['theme'] . '/img/flags/vres_' . $val . '.png" alt="">';
        }
    }

    // video codec
    $img_flag_vtype = '';
    foreach ($vtype_assoc as $key => $val) {
        if (in_array($list['v_codec'], $vtype_assoc[$key])) {
            $img_flag_vtype = '<img id="vtype" src="css/' . $set['theme'] . '/img/flags/vc_' . $key . '.png" alt="">';
        }
    }

    // audio codec
    $img_flag_atype = '';
    foreach ($atype_assoc as $key => $val) {
        if(in_array($list['a_codec'], $atype_assoc[$key])) {
            $img_flag_atype = '<img id="atype" src="css/' . $set['theme'] . '/img/flags/ac_' . $key . '.png" alt="">';
        }
    }

    // audio channel
    $img_flag_achan = '';
    foreach ($achan_assoc as $val) {
        if (is_numeric($list['a_channels']) && $list['a_channels'] >= $val) {
            $img_flag_achan = '<img id="vres" src="css/' . $set['theme'] . '/img/flags/ach_' . $val . '.png" alt="">';
        }
    }

    // panel flags
    $img_flag = $img_flag_vres . $img_flag_vtype . $img_flag_atype . $img_flag_achan;

    // wached status
    $watched = '';
    if ($set['watched_status'] == 1 && $list['play_count'] > 0) {
        $watched = '<img class="watched" src="css/' . $set['theme'] . '/img/watched.png" alt="" title="' . $lang['i_last_played'] . ': ' . $list['last_played'] . '">';
    }
    
    $output_panel_list.= '
<div id="' . $list['id'] . '" class="movie">
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
        <img id="img_space" src="css/' . $set['theme'] . '/img/space.png">
        ' . $img_flag . '
    </div>
</div>';
}

/* ##############
 * # INFO PANEL #
 */##############
if ($output_panel_info !== '') {
    $output_panel_info = '<div id="panel_info">' . $output_panel_info . '</div>';
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set['site_name'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link type="text/css" href="css/<?PHP echo $set['theme'] ?>/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.cycle.lite.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
    </head>
    <body>
        <img src="css/<?PHP echo $set['theme'] ?>/img/bg.jpg" id="background" alt="<?PHP echo ($set['show_fanart'] == 1 ? '1' : '') ?>">
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
        <div id="panel_bottom">
            <a href="http://github.com/Regss/movielib">MovieLib</a> v. 0.9.1 - Created by <a href="mailto:regss84@gmail.com">Regss</a>
        </div>
    </body>
</html>