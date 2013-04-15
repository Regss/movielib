<?PHP
header('Content-type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'function.php';

/* ########################
 * # CHECK LOCAL DATABASE #
 */########################
$conn_local = @mysql_connect($mysql_local[0] . ':' . $mysql_local[1], $mysql_local[2], $mysql_local[3]);
if (!$conn_local) {
    die('Could not connect: ' . mysql_error());
}
$sel_local = @mysql_select_db($mysql_local[4]);
if (!$sel_local) {
    die('Could not connect: ' . mysql_error());
}

// Sets utf8 connections
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

// Check table in database
$sql_table = 'SHOW TABLES LIKE "movie"';
$result_table = mysql_query($sql_table);
if (!mysql_num_rows($result_table)) {
    $output = create_table($col, $lang);
}

/* #########################
 * # CHECK REMOTE DATABASE #
 */#########################
if (!$_COOKIE['synch']) {
    setcookie('synch', 'true', time()+3600);
    $fp = @fsockopen($mysql_remote[0], $mysql_remote[1], $errno, $errstr, 3);
    if ($fp) {
        fclose($fp);
        $conn_remote = @mysql_connect($mysql_remote[0] . ':' . $mysql_remote[1], $mysql_remote[2], $mysql_remote[3]);
        if ($conn_remote) {
            $output = synch_database($col, $mysql_local, $mysql_remote, $conn_local, $conn_remote, $lang);
        }
    }
}

mysql_connect($mysql_local[0] . ':' . $mysql_local[1], $mysql_local[2], $mysql_local[3]);
mysql_select_db($mysql_local[4]);
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

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
$sort_mysql = array(1 => $col['title'] . ' ASC', $col['year'] . ' DESC', $col['rating'] . ' DESC', $col['id_movie'] . ' DESC', ' CAST( ' . $col['runtime'] . ' AS DECIMAL( 10, 2 ) ) DESC');

/* ##########
 * # GENRES #
 */##########
$genre_sql = 'SELECT ' . $col['genre'] . ' FROM movie ORDER by ' . $col['genre'];
$genre_result = mysql_query($genre_sql);
$genre_array = array();
while ($genre_mysql_array = mysql_fetch_array($genre_result)) {
    foreach (explode(' / ', $genre_mysql_array[$col['genre']]) as $val) {
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
$nav_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['title'] . ', ' . $col['rating'] . ', ' . $col['year'] . ', ' . $col['genre'] . ', ' . $col['country'] . ' FROM movie WHERE ' . $col['genre'] . ' LIKE "%' . $genre_mysql . '%" AND ' . $col['title'] . ' LIKE "%' . $search_mysql . '%" AND ' . $col['id_movie'] . ' LIKE "' . $id_mysql . '" ORDER by ' . $sort_mysql[$sort];
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

$list_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ', ' . $col['title'] . ', ' . $col['rating'] . ', ' . $col['year'] . ', ' . $col['poster'] . ', ' . $col['plot'] . ', ' . $col['runtime'] . ', ' . $col['genre'] . ', ' . $col['country'] . ', ' . $col['director'] . ', ' . $col['originaltitle'] . ' FROM movie WHERE ' . $col['genre'] . ' LIKE "%' . $genre_mysql . '%" AND ' . $col['title'] . ' LIKE "%' . $search_mysql . '%" AND ' . $col['id_movie'] . ' LIKE "' . $id_mysql . '" ORDER by ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$panel_list = '';
while ($list = mysql_fetch_array($list_result)) {
    
    $poster = 'cache/' . $list[$col['id_movie']] . '.jpg';
    if (!file_exists($poster)) {
        preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $list[$col['poster']], $poster_path);
        $poster_path = (isset($poster_path[1]) ? $poster_path[1] : '');
        gd_convert($list[$col['id_movie']], $poster_path);
    }
    if (!file_exists($poster)) {
        $poster = 'img/d_poster.jpg';
    }


    $movie_stream_v_sql = 'SELECT strVideoCodec, fVideoAspect, iVideoWidth, iVideoHeight FROM streamdetails WHERE idFile = ' . $list[$col['id_file']] . ' AND iStreamType = 0';
    $movie_stream_v_result = mysql_query($movie_stream_v_sql);
    $movie_stream_v = mysql_fetch_array($movie_stream_v_result);

    $movie_stream_a_sql = 'SELECT strAudioCodec, iAudioChannels FROM streamdetails WHERE idFile = ' . $list[$col['id_file']] . ' AND iStreamType = 1';
    $movie_stream_a_result = mysql_query($movie_stream_a_sql);
    $movie_stream_a = mysql_fetch_array($movie_stream_a_result);

// hd flag
    if ($movie_stream_v['iVideoWidth'] > 1279) {
        $flag_hd = '<img class="img_flag_hd" src="img/hd.png" alt="">';
    } else {
        $flag_hd = '';
    }

// video resolution
    $i = 0;
    foreach ($width_height as $key => $val) {
        if ($movie_stream_v['iVideoWidth'] >= $key or $movie_stream_v['iVideoHeight'] >= $val) {
            $img_flag_vres = '<img id="vres" src="img/flags/vres_' . $vres_array[$i] . '.png" alt="">';
        }
        $i++;
    }

// video codec
    if (isset($vtype[$movie_stream_v['strVideoCodec']])) {
        $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_' . $vtype[$movie_stream_v['strVideoCodec']] . '.png" alt="">';
    } else {
        $img_flag_vtype = '<img id="vtype" src="img/flags/vcodec_defaultscreen.png" alt="">';
    }

// audio codec 
    if (isset($atype[$movie_stream_a['strAudioCodec']])) {
        $img_flag_atype = '<img id="atype" src="img/flags/acodec_' . $atype[$movie_stream_a['strAudioCodec']] . '.png" alt="">';
    } else {
        $img_flag_atype = '<img id="atype" src="img/flags/acodec_defaultsound.png" alt="">';
    }

// audio channel
    if (isset($achan[$movie_stream_a['iAudioChannels']])) {
        $img_flag_achan = '<img id="achan" src="img/flags/achan_' . $achan[$movie_stream_a['iAudioChannels']] . '.png" alt="">';
    } else {
        $img_flag_achan = '<img id="achan" src="img/flags/achan_defaultsound.png" alt="">';
    }
    $img_flag = $img_flag_vres . $img_flag_vtype . $img_flag_atype . $img_flag_achan;

    $panel_list.= '<div class="movie"><div class="title">' . $list[$col['title']] . $flag_hd . '</div><div class="title_org">' . $list[$col['originaltitle']] . '</div><img class="poster" src="' . $poster . '"><div class="flags"><table id="movie_info"><tr><td class="movie_left">Rok:</td><td class="movie_right">' . $list[$col['year']] . '</td></tr><tr><td class="movie_left">Gatunek</td><td class="movie_right">' . $list[$col['genre']] . '</td></tr><tr><td class="movie_left">Rating:</td><td class="movie_right">' . round($list[$col['rating']], 1) . '</td></tr><tr><td class="movie_left">Kraj:</td><td class="movie_right">' . $list[$col['country']] . '</td></tr><tr><td class="movie_left">Runtime:</td><td class="movie_right">' . $list[$col['runtime']] . ' min.</td></tr><tr><td class="movie_left">Reżyser:</td><td class="movie_right">' . $list[$col['director']] . '</td></tr><tr><td class="movie_left">Opis:</td><td class="movie_right">' . $list[$col['plot']] . '</td></tr></table><img id="img_space" src="img/space.png">' . $img_flag . '</div></div>';
}

// recently added panel
if ($recently_limit == 0) {
    $recently_output = '';
} else {
    $recently_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ', ' . $col['title'] . ', ' . $col['poster'] . ' FROM movie ORDER BY ' . $col['id_movie'] . ' DESC LIMIT ' . $recently_limit;
    $recently_result = mysql_query($recently_sql);
    $recently_output = '';
    while ($recently = mysql_fetch_array($recently_result)) {
        if (!file_exists('cache/' . $recently[$col['id_movie']] . '.jpg')) {
            preg_match_all('/<thumb.*?>(.*?)<\/thumb>/', $recently[$col['poster']], $poster_path);
            $poster_path = (isset($poster_path[1]) ? $poster_path[1] : '');
            gd_convert($recently[$col['id_movie']], $poster_path, '');
        }
        $recently_output.= '<a href="index.php?id=' . $recently[$col['id_movie']] . '"><img class="recently_img" src="cache/' . $recently[$col['id_movie']] . '.jpg" title="' . $recently[$col['title']] . '" alt=""></a>';
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
        <title>Movie Lib</title>
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
