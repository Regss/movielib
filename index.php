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

/* ##################
 * # CHECK DATABASE #
 */##################
$conn_ml = @mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
if (!$conn_ml) {
    die('Could not connect: ' . mysql_error());
}
$sel_ml = @mysql_select_db($mysql_ml[4]);
if (!$sel_ml) {
    die('Could not connect: ' . mysql_error());
}

// Sets utf8 connections
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

// Check table in database
$sql_table = 'SHOW TABLES LIKE "' . $mysql_table_ml . '"';
$result_table = mysql_query($sql_table);
if (!mysql_num_rows($result_table)) {
    $output = create_table($col, $mysql_table_ml, $lang);
} elseif (mysql_num_rows($result_table) > 1) {
    $output = 'To nie jest baza danych MovieLib. Posiada za dużo tabel.';
}

/* #######################
 * # CHECK XBMC DATABASE #
 */#######################
if (!isset($_COOKIE['sync'])) {
    $fp = @fsockopen($mysql_xbmc[0], $mysql_xbmc[1], $errno, $errstr, 3);
    if ($fp) {
        fclose($fp);
        $conn_xbmc = mysql_connect($mysql_xbmc[0] . ':' . $mysql_xbmc[1], $mysql_xbmc[2], $mysql_xbmc[3]);
        if ($conn_xbmc) {
            $output = sync_database($col, $mysql_ml, $mysql_xbmc, $conn_ml, $conn_xbmc, $mysql_table_ml, $lang);
            // setcookie('sync', true, time()+3600);
        }
    }
}
mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
mysql_select_db($mysql_ml[4]);
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

/* ##########################
 * # CHECK FILE videodb.xml #
 */##########################
if (file_exists('import/videodb.xml')) {
    // $output = import_xml($col, $mysql_ml, $conn_ml, $mysql_table_ml, $lang);
}

// Set id
if (!isset($_GET['id'])) {
    $id = 0;
    $id_mysql = '%';
} else {
    $id = $_GET['id'];
    $id_mysql = $_GET['id'];
}

/* ##########
 * # SEARCH #
 */##########
if ($search == '') {
    $search_text = '<input type="text" name="search" class="search"><input type="image" class="search_img" src="img/search.png" title="' . $lang['i_search'] . '" alt="Search" />';
    $search_mysql = '%';
} else {
    $search_text = $lang['i_result'] . ': ' . $search . ' <a href="index.php"><img src="img/delete.png" title="' . $lang['i_search_del'] . '" alt=""></a>';
    $search_mysql = $search;
}

/* ########
 * # SORT #
 */########
$sort_array = array(1 => $lang['i_title'], $lang['i_year'], $lang['i_rating'], $lang['i_added'], $lang['i_runtime']);
$sort_menu = '<span class="bold">Sortuj:</span>';
foreach ($sort_array as $key => $val) {
    $sort_menu.= ($sort == $key ? ' ' . $val . ' ' : ' <a href="index.php?sort=' . $key . '&amp;genre=' . $genre . '">' . $val . '</a> ');
}
$sort_mysql = array(1 => 'title ASC', 'year DESC', 'rating DESC', 'id DESC', ' CAST( runtime AS DECIMAL( 10, 2 ) ) DESC');

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
    
    $poster = 'cache/' . $list['id'] . '.jpg';
    if (!file_exists($poster)) {
        preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $list['poster'], $poster_path);
        $poster_path = (isset($poster_path[1]) ? $poster_path[1] : '');
        gd_convert($list['id'], $poster_path);
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

    $panel_list.= '<div class="movie"><div class="title">' . $list['title'] . $flag_hd . '</div><div class="title_org">' . $list['originaltitle'] . '</div><img class="poster" src="' . $poster . '"><div class="flags"><table id="movie_info"><tr><td class="movie_left">Rok:</td><td class="movie_right">' . $list['year'] . '</td></tr><tr><td class="movie_left">Gatunek:</td><td class="movie_right">' . $list['genre'] . '</td></tr><tr><td class="movie_left">Rating:</td><td class="movie_right">' . round($list['rating'], 1) . '</td></tr><tr><td class="movie_left">Kraj:</td><td class="movie_right">' . $list['country'] . '</td></tr><tr><td class="movie_left">Runtime:</td><td class="movie_right">' . $list['runtime'] . ' min.</td></tr><tr><td class="movie_left">Reżyser:</td><td class="movie_right">' . $list['director'] . '</td></tr><tr><td class="movie_left">Opis:</td><td class="movie_right">' . $list['plot'] . '</td></tr></table><img id="img_space" src="img/space.png">' . $img_flag . '</div></div>';
}

// recently added panel
if ($recently_limit == 0) {
    $recently_output = '';
} else {
    $recently_sql = 'SELECT id, file, title, poster FROM ' . $mysql_table_ml . ' ORDER BY id DESC LIMIT ' . $recently_limit;
    $recently_result = mysql_query($recently_sql);
    $recently_output = '';
    while ($recently = mysql_fetch_array($recently_result)) {
        if (!file_exists('cache/' . $recently['id'] . '.jpg')) {
            preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $recently['poster'], $poster_path);
            $poster_path = (isset($poster_path[1]) ? $poster_path[1] : '');
            gd_convert($recently['id'], $poster_path, '');
        }
        $recently_output.= '<a href="index.php?id=' . $recently['id'] . '"><img class="recently_img" src="cache/' . $recently['id'] . '.jpg" title="' . $recently['title'] . '" alt=""></a>';
    }
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
        <link type="text/css" href="css/style.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
        <script type="text/javascript" src="js/jquery.index.js"></script>
    </head>
    <body>
        <?PHP echo $panel_info ?>
        <div id="panel_recently"><?PHP echo $recently_output ?></div>
        <div id="container">
            <div id="panel_menu">
                <div id="panel_menu_title">Gatunek:</div>
                    <?PHP echo $genre_menu ?>
            </div>
            <div id="panel_options"><?PHP echo $sort_menu ?></div>
            <div id="panel_search"><form method="get" action="index.php"><?PHP echo $search_text ?></form></div>
            <div id="panel_movie">
                <div class="panel_nav"><?PHP echo $nav ?></div>
                    <?PHP echo $panel_list ?>
                <div class="panel_nav"><?PHP echo $nav ?></div>
            </div>
        </div>
    </body>
</html>
