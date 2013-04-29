<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'function.php';

/* ##################
 * # CHECK PASSWORD #
 */##################
if (!isset($protect_site) or $protect_site !== false) {
    if ($_SESSION['logged'] !== true) {
        header('Location:login.php');
        die();
    }
}

/* #############
 * # CHECK DIR #
 */#############
foreach ($folders_assoc as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder);
    }
}

/* ##################
 * # CHECK DATABASE #
 */##################
$conn_ml = @mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
if (!$conn_ml) {
    die($lang['i_could_not_connect'] . ': ' . mysql_error());
}
$sel_ml = @mysql_select_db($mysql_ml[4]);
if (!$sel_ml) {
    die($lang['i_could_not_connect'] . ': ' . mysql_error());
}

// Sets utf8 connections
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

// Check table in database
$sql_table = 'SHOW TABLES';
$result_table = mysql_query($sql_table);
$table_count = mysql_num_rows($result_table);
if ($table_count > 1) {
    die($lang['i_not_movielib_db']);
} elseif ($table_count == 0) {
    $output = create_table($col, $mysql_table_ml, $lang);
}

/* #######################
 * # CHECK XBMC DATABASE #
 */#######################
if (!isset($_COOKIE['sync']) && $mode == 1) {
    $fp = @fsockopen($mysql_xbmc[0], $mysql_xbmc[1], $errno, $errstr, 3);
    if ($fp) {
        fclose($fp);
        $conn_xbmc = mysql_connect($mysql_xbmc[0] . ':' . $mysql_xbmc[1], $mysql_xbmc[2], $mysql_xbmc[3]);
        if ($conn_xbmc) {
            $sel_xbmc = @mysql_select_db($mysql_xbmc[4]);
            if ($sel_xbmc) {
                $output = sync_database($col, $mysql_ml, $mysql_xbmc, $conn_ml, $conn_xbmc, $mysql_table_ml, $lang);
                setcookie('sync', true, time()+$sync_time*60);
            }
        }
    }
}

/* ##########################
 * # CHECK FILE videodb.xml #
 */##########################
if (file_exists('import/videodb.xml') && $mode == 2) {
    $output = import_xml($col, $mysql_ml, $conn_ml, $mysql_table_ml, $lang);
}

/* ################################
 * # CONNECT TO MOVIELIB DATABASE #
 */################################
mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
mysql_select_db($mysql_ml[4]);
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

/* #################
 * # OVERALL PANEL #
 */#################
if ($set_overall_panel == true) {
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
    $overall_panel = '<div class="panel_box_title">' . $lang['i_overall_title'] . ':</div><div class="panel_box"><span class="orange">' . $lang['i_overall_all'] . ':</span> ' . $overall_all . '<br /><span class="orange">' . $lang['i_overall_watched'] . ':</span> ' . $overall_w . '<br /><span class="orange">' . $lang['i_overall_notwatched'] . ':</span> ' . $overall_nw . '</div>';
} else {
    $overall_panel = '';
}

/* ##########
 * # SEARCH #
 */##########
if ($search == '') {
    $search_text = '<input type="text" name="search" class="search"><input type="image" class="search_img" src="img/search.png" title="' . $lang['i_search'] . '" alt="Search" />';
    $search_mysql = '%';
} else {
    $search_text = $lang['i_result'] . ': ' . $search . ' <a href="index.php"><img src="img/delete.png" title="' . $lang['i_search_del'] . '" alt=""></a> <input type="text" name="search" class="search"><input type="image" class="search_img" src="img/search.png" title="' . $lang['i_search'] . '" alt="Search" />';
    $search_mysql = $search;
}

/* ########
 * # SORT #
 */########
$sort_array = array(1 => $lang['i_title'], $lang['i_year'], $lang['i_rating'], $lang['i_added'], $lang['i_runtime']);
$sort_menu = '<span class="bold">' . $lang['i_sort'] . ':</span>';
foreach ($sort_array as $key => $val) {
    $sort_menu.= ($sort == $key ? ' <span class="block">' . $val . '</span> ' : ' <a class="block" href="index.php?sort=' . $key . '&amp;genre=' . $genre . '">' . $val . '</a> ');
}
$sort_mysql = array(1 => 'title ASC', 'year DESC', 'rating DESC', 'date_added DESC', ' CAST( runtime AS DECIMAL( 10, 2 ) ) DESC');

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
$genre_menu = '<div class="genre">' . (($genre == 'all' and $id == 0) ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;genre=all">' . $lang['i_all'] . '</a>') . '</div>';
sort($genre_array);
$genre_mysql = '%';
foreach ($genre_array as $key => $val) {
    if ((string) $key === (string) $genre) {
        $genre_menu.= '<div class="genre">' . $val . '</div>';
        $genre_mysql = $val;
    } else {
        $genre_menu.= '<div class="genre"><a href="index.php?sort=' . $sort . '&amp;genre=' . $key . '">' . $val . '</a></div>';
    }
}

/* #############
 * # PANEL NAV #
 */#############
$nav_sql = 'SELECT id, title, rating, year, genre, country FROM ' . $mysql_table_ml . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" AND id LIKE "' . $id_mysql . '" ORDER by ' . $sort_mysql[$sort];
$nav_result = mysql_query($nav_sql);
$row = mysql_num_rows($nav_result);
if ($per_page == 0) {
    $i_pages = 1;
    $nav = '';
} else {
    $i_pages = (ceil($row / $per_page));
    $nav = ($page == 1 ? $lang['i_previous'] : '<a href="index.php?sort=' . $sort . '&amp;genre=' . $genre . '&amp;page=' . ($page - 1) . '&amp;search=' . $search . '">' . $lang['i_previous'] . '</a>') . ' ' .
            $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . ' ' .
            ($page == $i_pages ? $lang['i_next'] : '<a href="index.php?sort=' . $sort . '&amp;genre=' . $genre . '&amp;page=' . ($page + 1) . '&amp;search=' . $search . '">' . $lang['i_next'] . '</a>');
    if ($row == 0) {
        $nav ='';
    }
}

/* ##############
 * # MOVIE LIST #
 */##############
if ($per_page == 0) {
    $limit_sql = '';
} else {
    $start = ($page - 1) * $per_page;
    $limit_sql = ' LIMIT ' . $start . ', ' . $per_page;
}

$list_sql = 'SELECT * FROM ' . $mysql_table_ml . ' WHERE genre LIKE "%' . $genre_mysql . '%" AND title LIKE "%' . $search_mysql . '%" AND id LIKE "' . $id_mysql . '" ORDER by ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$panel_list = '';
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
    if ($watched_status == true && $list['play_count'] > 0) {
        $watched = '<img class="watched" src="img/watched.png" alt="" title="' . $lang['i_last_played'] . ': ' . $list['last_played'] . '">';
    } else {
        $watched = '';
    }
    
    $panel_list.= '
<div class="movie">
    <div class="title">' . $list['title'] . '</div>
    <div class="title_org">' . $list['originaltitle'] . '</div>'
    . $watched 
    . $flag_hd . '
    <img class="poster" src="' . $poster . '">
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

// recently added panel
if ($recently_limit == 0) {
    $recently_output = '';
} else {
    $recently_sql = 'SELECT id, title, poster, date_added FROM ' . $mysql_table_ml . ' ORDER BY date_added DESC LIMIT ' . $recently_limit;
    $recently_result = mysql_query($recently_sql);
    $recently_output = '';
    while ($recently = mysql_fetch_array($recently_result)) {
        if (!file_exists('cache/' . $recently['id'] . '.jpg')) {
            gd_convert($recently['id'], $recently['poster'], '');
        }
        $recently_output.= '<a href="index.php?id=' . $recently['id'] . '"><img src="cache/' . $recently['id'] . '.jpg" title="' . $recently['title'] . '" alt=""></a>';
    }
}

// random panel
if ($random_limit == 0) {
    $random_output = '';
} else {
    $random_sql = 'SELECT id, title, poster FROM ' . $mysql_table_ml . ' ORDER BY RAND() LIMIT ' . $random_limit;
    $random_result = mysql_query($random_sql);
    $random_output = '';
    while ($random = mysql_fetch_array($random_result)) {
        if (!file_exists('cache/' . $random['id'] . '.jpg')) {
            gd_convert($random['id'], $random['poster'], '');
        }
        $random_output.= '<a href="index.php?id=' . $random['id'] . '"><img src="cache/' . $random['id'] . '.jpg" title="' . $random['title'] . '" alt=""></a>';
    }
}

// premiere panel
$year = date('Y');
$premiere_sql = 'SELECT id, title, poster, year FROM ' . $mysql_table_ml . ' WHERE year=' . $year;
$premiere_result = mysql_query($premiere_sql);
$premiere_output = '';
while ($premiere = mysql_fetch_array($premiere_result)) {
    if (!file_exists('cache/' . $premiere['id'] . '.jpg')) {
        gd_convert($premiere['id'], $premiere['poster'], '');
    }
    $premiere_output.= '<a href="index.php?id=' . $premiere['id'] . '"><img src="cache/' . $premiere['id'] . '.jpg" title="' . $premiere['title'] . '" alt=""></a>';
}

/* ##############
 * # INFO PANEL #
 */##############
if (!isset($output) or $output == '') {
    $panel_info ='';
} else {
    $panel_info = '<div id="panel_info">' . $output . '</div>';
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $site_name ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.cycle.lite.js"></script>
        <script type="text/javascript" src="js/jquery.index.js"></script>
    </head>
    <body>
        <?PHP echo $panel_info ?>
        
        <div id="container">
            <div id="panel_header">
                <div id="panel_recently"><?PHP echo $recently_output ?></div>
                <div id="panel_recently_title">Ostatnio dodane</div>
                <div id="panel_random"><?PHP echo $random_output ?></div>
                <div id="panel_random_title">Wybrane</div>
                <div id="panel_premiere"><?PHP echo $premiere_output ?></div>
                <div id="panel_premiere_title">Premiery</div>
            </div>
            <div id="panel_left">
                <?PHP echo $overall_panel ?>
                <div class="panel_box_title"><?PHP echo $lang['i_genre'] ?>:</div>
                <div class="panel_box"><?PHP echo $genre_menu ?></div>
            </div>
            <div id="panel_right">
                <div id="panel_options"><?PHP echo $sort_menu ?></div>
                <div id="panel_search"><form method="get" action="index.php"><?PHP echo $search_text ?></form></div>
                <div id="panel_movie">
                    <div class="panel_nav"><?PHP echo $nav ?></div>
                    <?PHP echo $panel_list ?>
                    <div class="panel_nav"><?PHP echo $nav ?></div>
                </div>
            </div>
        </div>
    </body>
</html>
