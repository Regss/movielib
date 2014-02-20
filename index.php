<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');
require('config.php');
require('function.php');

if (file_exists('install.php') or !file_exists('db.php')) {
    header('Location:install.php');
    die();
}

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_ml, $mysql_tables);
require('lang/' . $set['language'] . '/lang.php');

/* ##################
 * # CHECK PASSWORD #
 */##################
if ($set['protect_site'] == 1) {
    if ($_SESSION['logged'] !== true) {
        header('Location:login.php');
        die();
    }
}

/* #############
 * # TOP PANEL #
 */#############
if ($set['panel_top'] == 1) {
    
    // recently added
    $output_recently = '<div class="panel_top_item">';
    $recently_sql = 'SELECT id, title, date_added FROM ' . $mysql_tables[0] . ' ORDER BY date_added DESC LIMIT ' . $set['panel_top_limit'];
    $recently_result = mysql_query($recently_sql);
    while ($recently = mysql_fetch_array($recently_result)) {
        if (file_exists('cache/' . $recently['id'] . '.jpg')) {
            $output_recently.= '<a href="index.php?id=' . $recently['id'] . '"><img src="cache/' . $recently['id'] . '.jpg" title="' . $recently['title'] . '" alt=""></a>';
        }
    }
    $output_recently.= '</div>';

    // most watched
    $output_most_watched = '<div class="panel_top_item">';
    $most_watched_sql = 'SELECT id, title FROM ' . $mysql_tables[0] . ' ORDER BY play_count DESC LIMIT ' . $set['panel_top_limit'];
    $most_watched_result = mysql_query($most_watched_sql);
    while ($most_watched = mysql_fetch_array($most_watched_result)) {
        if (file_exists('cache/' . $most_watched['id'] . '.jpg')) {
            $output_most_watched.= '<a href="index.php?id=' . $most_watched['id'] . '"><img src="cache/' . $most_watched['id'] . '.jpg" title="' . $most_watched['title'] . '" alt=""></a>';
        }
    }
    $output_most_watched.= '</div>';

    // last_played
    $output_last_played = '<div class="panel_top_item">';
    $last_played_sql = 'SELECT id, title, last_played FROM ' . $mysql_tables[0] . ' WHERE play_count > 0 ORDER BY last_played DESC LIMIT ' . $set['panel_top_limit'];
    $last_played_result = mysql_query($last_played_sql);
    while ($last_played = mysql_fetch_array($last_played_result)) {
        if (file_exists('cache/' . $last_played['id'] . '.jpg')) {
            $output_last_played.= '<a href="index.php?id=' . $last_played['id'] . '"><img src="cache/' . $last_played['id'] . '.jpg" title="' . $last_played['title'] . '" alt=""></a>';
        }
    }
    $output_last_played.= '</div>';

    // top_rated
    $output_top_rated = '<div class="panel_top_item">';
    $top_rated_sql = 'SELECT id, title, rating FROM ' . $mysql_tables[0] . ' ORDER BY rating DESC LIMIT ' . $set['panel_top_limit'];
    $top_rated_result = mysql_query($top_rated_sql);
    while ($top_rated = mysql_fetch_array($top_rated_result)) {
        if (file_exists('cache/' . $top_rated['id'] . '.jpg')) {
            $output_top_rated.= '<a href="index.php?id=' . $top_rated['id'] . '"><img src="cache/' . $top_rated['id'] . '.jpg" title="' . $top_rated['title'] . '" alt=""></a>';
        }
    }
    $output_top_rated.= '</div>';
    
    $output_panel_top = '<div id="panel_top" class="' . $set['panel_top_time'] * 1000 . '">' . $output_recently . $output_most_watched . $output_last_played . $output_top_rated . '</div>';
    $output_panel_top_title = '<div id="panel_title"><div class="panel_top_item_title">' . $lang['i_last_added'] . '</div><div class="panel_top_item_title">' . $lang['i_most_watched'] . '</div><div class="panel_top_item_title">' . $lang['i_last_played'] . '</div><div class="panel_top_item_title">' . $lang['i_top_rated'] . '</div></div>';
} else {
    $output_panel_top = '';
    $output_panel_top_title = '';
}

/* #################
 * # OVERALL PANEL #
 */#################
if ($set['panel_overall'] > 0) {
    $overall_sql = 'SELECT play_count FROM ' . $mysql_tables[0];
    $overall_result = mysql_query($overall_sql);
    $overall_all = mysql_num_rows($overall_result);
    $overall_watched = 0;
    while($overall = mysql_fetch_array($overall_result)) {
        if ($overall['play_count'] > 0) {
            $overall_watched++;
        }
    }
    $overall_unwatched = $overall_all - $overall_watched;
    $output_panel_overall = '<div id="overall" class="panel_box_title">' . $lang['i_overall_title'] . '</div><div id="panel_overall" class="panel_box ' . $set['panel_overall'] . '"><ul><li><span class="bold orange">' . $lang['i_overall_all'] . ':</span> ' . $overall_all . '</li><li><span class="bold orange">' . $lang['i_overall_watched'] . ':</span> ' . $overall_watched . '</li><li><span class="bold orange">' . $lang['i_overall_notwatched'] . ':</span> ' . $overall_unwatched . '</li></ul></div>';
} else {
    $output_panel_overall = '';
}

/* ##########
 * # GENRES #
 */##########
$genre_mysql = '%';
$genre_sql = 'SELECT genre FROM ' . $mysql_tables[0];
$genre_result = mysql_query($genre_sql);
$genre_array = array();
while ($genre_mysql_array = mysql_fetch_array($genre_result)) {
    foreach (explode(' / ', $genre_mysql_array['genre']) as $val) {
        if (!in_array($val, $genre_array) && strlen($val) > 0) {
            $genre_array[] = $val;
        }
    }
}
$output_panel_genre = '<div id="genre" class="panel_box_title">' . $lang['i_genre'] . '</div><div id="panel_genre" class="panel_box ' . $set['panel_genre'] . '"><ul><li>' . (($genre == 'all' and $id == 0) ? $lang['i_all'] :
    '<a href="index.php?sort=' . $sort . 
    '&amp;genre=all' .
    '&amp;year=' . $year .
    '&amp;country=' . $country .
    '&amp;v_codec=' . $v_codec .
    '&amp;a_codec=' . $a_codec .
    '&amp;a_chan=' . $a_chan .
    '">' . $lang['i_all'] . '</a>') . '</li>';
sort($genre_array);
foreach ($genre_array as $key => $val) {
    if ((string) $key === (string) $genre) {
        $output_panel_genre.= '<li>' . $val . '</li>';
        $genre_mysql = $val;
    } else {
        $output_panel_genre.= 
        '<li><a href="index.php?sort=' . $sort . 
        '&amp;genre=' . $key . 
        '&amp;year=' . $year .
        '&amp;country=' . $country .
        '&amp;v_codec=' . $v_codec .
        '&amp;a_codec=' . $a_codec .
        '&amp;a_chan=' . $a_chan .
        '">' . $val . '</a></li>';
    }
}
$output_panel_genre.= '</ul></div>';
if ($set['panel_genre'] == 0) {
    $output_panel_genre = '';
}

/* #########
 * # YEARS #
 */#########
$year_mysql = '%';
$year_sql = 'SELECT year FROM ' . $mysql_tables[0] . ' ORDER BY year DESC';
$year_result = mysql_query($year_sql);
$year_array = array();
while ($year_mysql_array = mysql_fetch_array($year_result)) {
    if (!in_array($year_mysql_array['year'], $year_array) && strlen($year_mysql_array['year']) > 0) {
        $year_array[] = $year_mysql_array['year'];
    }
}
$output_year_menu = '<div id="year" class="panel_box_title">' . $lang['i_year'] . '</div><div id="panel_year" class="panel_box ' . $set['panel_year'] . '"><ul><li>' . (($year == 'all' and $id == 0) ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;year=all">' . $lang['i_all'] . '</a>') . '</li>';
foreach ($year_array as $key => $val) {
    if ((string) $key === (string) $year) {
        $output_year_menu.= '<li>' . $val . '</li>';
        $year_mysql = $val;
    } else {
        $output_year_menu.= '<li><a href="index.php?sort=' . $sort . '&amp;year=' . $key . '">' . $val . '</a></li>';
    }
}
$output_year_menu.= '</ul></div>';
if ($set['panel_year'] == 0) {
    $output_year_menu = '';
}

/* ###########
 * # COUNTRY #
 */###########
$country_mysql = '%';
$country_sql = 'SELECT country FROM ' . $mysql_tables[0] . ' ORDER BY country';
$country_result = mysql_query($country_sql);
$country_array = array();
while ($country_mysql_array = mysql_fetch_array($country_result)) {
    foreach (explode(' / ', $country_mysql_array['country']) as $val) {
        if (!in_array($val, $country_array) && strlen($val) > 0) {
            $country_array[] = $val;
        }
    }
}
$output_country_menu = '<div id="country" class="panel_box_title">' . $lang['i_country'] . '</div><div id="panel_country" class="panel_box ' . $set['panel_country'] . '"><ul><li>' . (($country == 'all' and $id == 0) ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;country=all">' . $lang['i_all'] . '</a>') . '</li>';
foreach ($country_array as $key => $val) {
    if ((string) $key === (string) $country) {
        $output_country_menu.= '<li>' . $val . '</li>';
        $country_mysql = $val;
    } else {
        $output_country_menu.= '<li><a href="index.php?sort=' . $sort . '&amp;country=' . $key . '">' . $val . '</a></li>';
    }
}
$output_country_menu.= '</ul></div>';
if ($set['panel_country'] == 0) {
    $output_country_menu = '';
}

/* ########
 * # CAST #
 */########
$cast_mysql = '%';
$cast_sql = 'SELECT cast FROM ' . $mysql_tables[0] . ' ORDER BY cast';
$cast_result = mysql_query($cast_sql);
$cast_array = array();
while ($cast_mysql_array = mysql_fetch_array($cast_result)) {
    foreach (explode(' / ', $cast_mysql_array['cast']) as $key => $val) {
        if (!in_array($val, $cast_array) && strlen($val) > 0) {
            $cast_array[] = $val;
        }
    }
}
foreach ($cast_array as $key => $val) {
    if ((string) $key === (string) $cast) {
        $cast_mysql = $val;
    }
}

/* ###############
 * # VIDEO CODEC #
 */###############
 $v_codec_mysql = '%';
if ($set['panel_v_codec'] > 0) {
    $v_codec_sql = 'SELECT v_codec FROM ' . $mysql_tables[0];
    $v_codec_result = mysql_query($v_codec_sql);
    $v_codec_array = array();
    while ($v_codec_mysql_array = mysql_fetch_array($v_codec_result)) {
        if (!in_array($v_codec_mysql_array['v_codec'], $v_codec_array) && strlen($v_codec_mysql_array['v_codec']) > 0) {
            $v_codec_array[] = $v_codec_mysql_array['v_codec'];
        }
    }
    $output_v_codec_menu = '<div id="v_codec" class="panel_box_title">' . $lang['i_v_codec'] . '</div><div id="panel_v_codec" class="panel_box ' . $set['panel_v_codec'] . '"><ul><li>' . (($v_codec == 'all' and $id == 0) ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;v_codec=all">' . $lang['i_all'] . '</a>') . '</li>';
    sort($v_codec_array);
    foreach ($v_codec_array as $key => $val) {
        if ((string) $key === (string) $v_codec) {
            $output_v_codec_menu.= '<li>' . $val . '</li>';
            $v_codec_mysql = $val;
        } else {
            $output_v_codec_menu.= '<li><a href="index.php?sort=' . $sort . '&amp;v_codec=' . $key . '">' . $val . '</a></li>';
        }
    }
    $output_v_codec_menu.= '</ul></div>';
} else {
    $output_v_codec_menu = '';
}

/* ###############
 * # AUDIO CODEC #
 */###############
 $a_codec_mysql = '%';
if ($set['panel_a_codec'] > 0) {
    $a_codec_sql = 'SELECT a_codec FROM ' . $mysql_tables[0];
    $a_codec_result = mysql_query($a_codec_sql);
    $a_codec_array = array();
    while ($a_codec_mysql_array = mysql_fetch_array($a_codec_result)) {
        if (!in_array($a_codec_mysql_array['a_codec'], $a_codec_array) && strlen($a_codec_mysql_array['a_codec']) > 0) {
            $a_codec_array[] = $a_codec_mysql_array['a_codec'];
        }
    }
    $output_a_codec_menu = '<div id="a_codec" class="panel_box_title">' . $lang['i_a_codec'] . '</div><div id="panel_a_codec" class="panel_box ' . $set['panel_a_codec'] . '"><ul><li>' . (($a_codec == 'all' and $id == 0) ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;a_codec=all">' . $lang['i_all'] . '</a>') . '</li>';
    sort($a_codec_array);
    foreach ($a_codec_array as $key => $val) {
        if ((string) $key === (string) $a_codec) {
            $output_a_codec_menu.= '<li>' . $val . '</li>';
            $a_codec_mysql = $val;
        } else {
            $output_a_codec_menu.= '<li><a href="index.php?sort=' . $sort . '&amp;a_codec=' . $key . '">' . $val . '</a></li>';
        }
    }
    $output_a_codec_menu.= '</ul></div>';
} else {
    $output_a_codec_menu = '';
}

/* ########
 * # CHAN #
 */########
 $a_chan_mysql = '%';
if ($set['panel_a_chan'] > 0) {
    $a_chan_sql = 'SELECT a_chan FROM ' . $mysql_tables[0];
    $a_chan_result = mysql_query($a_chan_sql);
    $a_chan_array = array();
    while ($a_chan_mysql_array = mysql_fetch_array($a_chan_result)) {
        if (!in_array($a_chan_mysql_array['a_chan'], $a_chan_array) && strlen($a_chan_mysql_array['a_chan']) > 0) {
            $a_chan_array[] = $a_chan_mysql_array['a_chan'];
        }
    }
    $output_a_chan_menu = '<div id="a_chan" class="panel_box_title">' . $lang['i_a_chan'] . '</div><div id="panel_a_chan" class="panel_box ' . $set['panel_a_chan'] . '"><ul><li>' . (($a_chan == 'all' and $id == 0) ? $lang['i_all'] : '<a href="index.php?sort=' . $sort . '&amp;a_chan=all">' . $lang['i_all'] . '</a>') . '</li>';
    sort($a_chan_array);
    foreach ($a_chan_array as $key => $val) {
        if ((string) $key === (string) $a_chan) {
            $output_a_chan_menu.= '<li>' . $val . '</li>';
            $a_chan_mysql = $val;
        } else {
            $output_a_chan_menu.= '<li><a href="index.php?sort=' . $sort . '&amp;a_chan=' . $key . '">' . $val . '</a></li>';
        }
    }
    $output_a_chan_menu.= '</ul></div>';
} else {
    $output_a_chan_menu = '';
}

/* ########
 * # SORT #
 */########
$sort_array = array(1 => $lang['i_title'], $lang['i_year'], $lang['i_rating'], $lang['i_added'], $lang['i_runtime'], $lang['i_last_played'], $lang['i_most_watched']);
$output_sort_menu = '';
foreach ($sort_array as $key => $val) {
    $output_sort_menu.= ($sort == $key ? 
    '<span class="box_inline">' . $val . '</span>' : 
    '<a class="box_inline" href="index.php?sort=' . $key .
    '&amp;genre=' . $genre .
    '&amp;year=' . $year .
    '&amp;country=' . $country .
    '&amp;cast=' . $cast .
    '&amp;v_codec=' . $v_codec .
    '&amp;a_codec=' . $a_codec .
    '&amp;a_chan=' . $a_chan .
    '" title="' . $lang['i_sort'] . '">' . $val . '</a>');
}
$sort_mysql = array(1 => 'title ASC', 'year DESC', 'rating DESC', 'date_added DESC', ' CAST( runtime AS DECIMAL( 10, 2 ) ) DESC', 'last_played DESC', 'play_count DESC');
$play_count_mysql = ($sort == 6 ? ' > 0' : 'LIKE "%"');


/* ###############
 * # LIVE SEARCH #
 */###############
if ($set['live_search'] == 1) {
    $output_live_search = '<div id="panel_live_search"></div>';
} else {
    $output_live_search = '';
}

/* ##########
 * # SEARCH #
 */########## 
$output_search = '<form method="get" action="index.php" autocomplete="off">
    <div id="panel_input_search">
        <input id="search" type="text" name="search" value="' . $lang['i_search'] . '..." title="' . $lang['i_search'] . '...">
        ' . $output_live_search . '
    </div>';
if ($search == '') {
    $search_mysql = '%';
} else {
    $output_search.= '<div id="search_res">'.$lang['i_result'] . ': ' . $search . '<a id="search_res_img" href="index.php"><img src="css/' . $set['theme'] . '/img/delete.png" title="' . $lang['i_search_del'] . '" alt=""></a></div>';
    $search_mysql = $search;
}
$output_search.= '</form>';

/* #############
 * # PANEL NAV #
 */#############
$id_mysql = ($id == 0 ? '%' : $id);
$nav_sql = 'SELECT id FROM ' . $mysql_tables[0] . ' WHERE
    genre LIKE "%' . $genre_mysql . '%" AND
    year LIKE "%' . $year_mysql . '%" AND
    country LIKE "%' . $country_mysql . '%" AND
    cast LIKE "%' . $cast_mysql . '%" AND
    v_codec LIKE "%' . $v_codec_mysql . '%" AND
    a_codec LIKE "%' . $a_codec_mysql . '%" AND
    a_chan LIKE "%' . $a_chan_mysql . '%" AND
    title LIKE "%' . $search_mysql . '%" AND
    id LIKE "' . $id_mysql . '" AND
    play_count ' . $play_count_mysql . '
    ORDER BY ' . $sort_mysql[$sort];
$nav_result = mysql_query($nav_sql);
$row = mysql_num_rows($nav_result);
if ($set['per_page'] == 0) {
    $i_pages = 1;
    $output_nav = '';
} else {
    $i_pages = (ceil($row / $set['per_page']));
    $output_nav = ($page == 1 ? '<span class="box_inline">' . $lang['i_previous'] . '</span>' : '<a class="box_inline" href="index.php?sort=' . $sort . '&amp;genre=' . $genre . '&amp;page=' . ($page - 1) . '&amp;search=' . $search . '">' . $lang['i_previous'] . '</a>')
             . ' <span class="box_inline">' . $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . '</span> ' .
            ($page == $i_pages ? '<span class="box_inline">' . $lang['i_next'] . '</span>' : '<a class="box_inline" href="index.php?sort=' . $sort . '&amp;genre=' . $genre . '&amp;page=' . ($page + 1) . '&amp;search=' . $search . '">' . $lang['i_next'] . '</a>');
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

$list_sql = 'SELECT * FROM ' . $mysql_tables[0] . ' WHERE
    genre LIKE "%' . $genre_mysql . '%" AND
    year LIKE "%' . $year_mysql . '%" AND
    country LIKE "%' . $country_mysql . '%" AND
    cast LIKE "%' . $cast_mysql . '%" AND
    v_codec LIKE "%' . $v_codec_mysql . '%" AND
    a_codec LIKE "%' . $a_codec_mysql . '%" AND
    a_chan LIKE "%' . $a_chan_mysql . '%" AND
    title LIKE "%' . $search_mysql . '%" AND
    id LIKE "' . $id_mysql . '" AND
    play_count ' . $play_count_mysql . '
    ORDER BY ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$output_panel_list = '';

while ($list = mysql_fetch_array($list_result)) {
    
    // poster
    $poster = 'cache/' . $list['id'] . '.jpg';
    if (!file_exists($poster)) {
        $poster = 'css/' . $set['theme'] . '/img/d_poster.jpg';
    }
    
    // video resolution
    $img_flag_vres = '';
    foreach ($vres_assoc as $key => $val) {
        if (is_numeric($list['v_width']) && $list['v_width'] >= $key) {
            $img_flag_vres = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/vres_' . $val . '.png" alt="">';
        }
    }

    // video codec
    $img_flag_vtype = '';
    foreach ($vtype_assoc as $key => $val) {
        if (in_array($list['v_codec'], $vtype_assoc[$key])) {
            $img_flag_vtype = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/vc_' . $key . '.png" alt="">';
        }
    }

    // audio codec
    $img_flag_atype = '';
    foreach ($atype_assoc as $key => $val) {
        if(in_array($list['a_codec'], $atype_assoc[$key])) {
            $img_flag_atype = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/ac_' . $key . '.png" alt="">';
        }
    }

    // audio channel
    $img_flag_achan = '';
    foreach ($achan_assoc as $val) {
        if (is_numeric($list['a_chan']) && $list['a_chan'] >= $val) {
            $img_flag_achan = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/ach_' . $val . '.png" alt="">';
        }
    }

    // panel flags
    $img_flag = $img_flag_vres . $img_flag_vtype . $img_flag_atype . $img_flag_achan;

    // wached status
    $watched = '';
    if ($set['watched_status'] == 1 && $list['play_count'] > 0) {
        $watched = '<img class="watched" src="css/' . $set['theme'] . '/img/watched.png" alt="" title="' . $lang['i_last_played'] . ': ' . $list['last_played'] . '" alt="">';
    }

    // trailer
    if ($list['trailer'] !== NULL && $set['show_trailer'] == 1) {
        $trailer = '<a href="?id=' . $list['id'] . '"><img class="img_trailer" src="css/' . $set['theme'] . '/img/trailer.png" alt=""></a>';
    } else {
        $trailer = '';
    }
    if ($list['trailer'] !== NULL && $set['show_trailer'] == 1 && $id <> 0) {
        $output_trailer = '<div class="trailer">';
        if (substr($list['trailer'], 0, 18) == 'http://www.youtube') {
            $output_trailer.= '
                <iframe id="player" type="text/html" width="560" height="260" src="' . $list['trailer'] . '" frameborder="0"></iframe>
            ';
        } else {
            $ext = substr($list['trailer'], strrpos($list['trailer'], '.')+1, strlen($list['trailer']));
            foreach ($mimetype_assoc as $key => $val) {
                if(in_array($ext, $val)) {
                    $mimetype = $key;
                    break;
                } else {
                    $mimetype = '';
                }
            }
            
            if ($ext == 'mov') {
                $output_trailer.= '
                    <embed src="' . $list['trailer'] . '" width="560" height="260" cache="false" autoplay="false" scale="tofit" />';
            } else {
                $output_trailer.= '
                <video id="player" class="video-js vjs-default-skin player" controls preload="none" width="560" height="260" data-setup="{}">
                    <source src="' . $list['trailer'] . '" type="' . $mimetype . '" />
                </video>';
            }
        }
        $output_trailer.= '</div>';
    } else {
        $output_trailer = '';
    }
    
    // genre
    $output_genre = array();
    foreach (explode(' / ', $list['genre']) as $val) {
        $output_genre[] = '<a href="index.php?sort=' . $sort . '&genre=' . array_search($val, $genre_array) . '">' . $val . '</a>';
    }
    
    // country
    $output_country = array();
    foreach (explode(' / ', $list['country']) as $val) {
        $output_country[] = '<a href="index.php?sort=' . $sort . '&country=' . array_search($val, $country_array) . '">' . $val . '</a>';
    }
    
    // cast
    $output_cast = array();
    foreach (explode(' / ', $list['cast']) as $val) {
        if (file_exists('cache/actors/' . substr(md5($val), 0, 10) . '.jpg')) {
            $actor_thumb = '<img class="actor_thumb" src="cache/actors/' . substr(md5($val), 0, 10) . '.jpg">';
        } else {
            $actor_thumb = '';
        }
        $output_cast[] = '<a class="actor_img" href="index.php?sort=' . $sort . '&cast=' . array_search($val, $cast_array) . '" alt="' . substr(md5($val), 0, 10) . '">' . $actor_thumb . $val . '</a>';
    }
    
    $output_panel_list.= '
<div id="' . $list['id'] . '" class="movie">
    <div class="title"><a href="index.php?id=' . $list['id'] . '">' . $list['title'] . '</a></div>
    <div class="title_org">' . $list['originaltitle'] . '</div>'
    . $watched . $trailer . '
    <img id="poster_movie_' . $list['id'] . '" class="poster" src="' . $poster . '" alt="">
    <div class="desc">
        <table class="table">
            <tr>
                <td class="left">' . $lang['i_year'] . ':</td>
                <td class="right">' . $list['year'] . '</td>
            </tr>
            <tr>
                <td class="left">' . $lang['i_genre'] . ':</td>
                <td class="right">' . implode(' / ', $output_genre) . '</td>
            </tr>
            <tr>
                <td class="left">' . $lang['i_rating'] . ':</td>
                <td class="right">' . round($list['rating'], 1) . '</td>
            </tr>
            <tr>
                <td class="left">' . $lang['i_country'] . ':</td>
                <td class="right">' . implode(' / ', $output_country) . '</td>
            </tr>
            <tr>
                <td class="left">' . $lang['i_runtime'] . ':</td>
                <td class="right">' . $list['runtime'] . ' ' . $lang['i_minute'] . '</td>
            </tr>
            <tr>
                <td class="left">' . $lang['i_director'] . ':</td>
                <td class="right">' . $list['director'] . '</td>
            </tr>
            <tr>
                <td class="left">' . $lang['i_cast'] . ':</td>
                <td class="right">' . implode(' / ', $output_cast) . '</td>
            </tr>
            <tr>
                <td class="left">' . $lang['i_plot'] . ':</td>
                <td class="right">' . $list['plot'] . '</td>
            </tr>
        </table>
        <img class="img_space" src="css/' . $set['theme'] . '/img/space.png" alt="">
        ' . $img_flag . '
    </div>
    ' . $output_trailer . '
</div>';
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title><?PHP echo $set['site_name'] ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <link type="image/x-icon" href="css/<?PHP echo $set['theme'] ?>/img/icon.ico" rel="icon" media="all" />
        <link type="text/css" href="css/<?PHP echo $set['theme'] ?>/style.css" rel="stylesheet" media="all" />
        <link type="text/css" href="css/<?PHP echo $set['theme'] ?>/video.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.cycle.lite.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
        <script type="text/javascript" src="js/video.js"></script>
    </head>
    <body>
        <img src="css/<?PHP echo $set['theme'] ?>/img/bg.jpg" id="background" alt="<?PHP echo ($set['show_fanart'] == 1 ? '1' : '') ?>">
        <div class="container">
            <?PHP echo $output_panel_top . $output_panel_top_title ?>
            <div id="panel_left">
                <?PHP echo $output_panel_overall ?>
                <?PHP echo $output_panel_genre ?>
                <?PHP echo $output_year_menu ?>
                <?PHP echo $output_country_menu ?>
                <?PHP echo $output_v_codec_menu ?>
                <?PHP echo $output_a_codec_menu ?>
                <?PHP echo $output_a_chan_menu ?>
            </div>
            <div id="panel_right" class="<?PHP echo ($set['panel_overall'] + $set['panel_genre'] + $set['panel_year'] + $set['panel_country'] + $set['panel_v_codec'] + $set['panel_a_codec'] + $set['panel_a_chan'] == 0 ? '' : 'panel_right_ex') ?>">
                <div id="panel_sort"><?PHP echo $output_sort_menu ?></div>
                <div id="panel_search"><?PHP echo $output_search ?></div>
                <?PHP echo $output_nav ?>
                <?PHP echo $output_panel_list ?>
                <?PHP echo $output_nav ?>
            </div>
        </div>
        <div id="panel_bottom">
            <a href="http://github.com/Regss/movielib">MovieLib</a> <?PHP echo $version ?> - Created by <a href="mailto:regss84@gmail.com">Regss</a>
        </div>
    </body>
</html>