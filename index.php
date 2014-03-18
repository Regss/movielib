<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

require('config.php');
require('function.php');

if (!isset($_GET['video'])) {
    $video = 'movies';
} else {
    $video = $_GET['video'];
}
if ($video == 'tvshows') {
    $mysql_table = $mysql_tables[1];
} else {
    $mysql_table = $mysql_tables[0];
}

if (file_exists('install.php') or !file_exists('db.php')) {
    header('Location:install.php');
    die();
}

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_tables);
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

/* ################
 * # SELECT MEDIA #
 */################
if ($video == 'tvshows') {
    $output_select_media = '<a id="media_movies" class="media" href="index.php?video=movies">' . mb_strtoupper($lang['i_movies']) . '</a><div id="media_tvshows" class="media">' . mb_strtoupper($lang['i_tvshows']) . '</div>';
} else {
    $output_select_media = '<div id="media_movies" class="media">' . mb_strtoupper($lang['i_movies']) . '</div><a id="media_tvshows" class="media" href="index.php?video=tvshows">' . mb_strtoupper($lang['i_tvshows']) . '</a>';
}

/* #############
 * # TOP PANEL #
 */#############
$output_panel_top = '';
$output_panel_top_title = '';

if ($set['panel_top'] == 1) {

    $top_panel_sql_array = array(
        'SELECT id, title, date_added FROM ' . $mysql_table . ' ORDER BY date_added DESC LIMIT ' . $set['panel_top_limit'],
        'SELECT id, title FROM ' . $mysql_table . ' ORDER BY play_count DESC LIMIT ' . $set['panel_top_limit'],
        'SELECT id, title, last_played FROM ' . $mysql_table . ' WHERE play_count > 0 ORDER BY last_played DESC LIMIT ' . $set['panel_top_limit'],
        'SELECT id, title, rating FROM ' . $mysql_table . ' ORDER BY rating DESC LIMIT ' . $set['panel_top_limit']
    );
    
    $output_panel_top.= '<div id="panel_top">';
    foreach ($top_panel_sql_array as $panel_top_sql) {
        $output_panel_top.= '<div class="panel_top_item">';
        $panel_top_result = mysql_query($panel_top_sql);
        while ($panel_top = mysql_fetch_array($panel_top_result)) {
            if (file_exists('cache/' . $mysql_table . '_' . $panel_top['id'] . '.jpg')) {
                $output_panel_top.= '<a href="index.php?video=' . $video . '&id=' . $panel_top['id'] . '"><img src="cache/' . $mysql_table . '_' . $panel_top['id'] . '.jpg" title="' . $panel_top['title'] . '" alt=""></a>';
            }
        }
        $output_panel_top.= '</div>';
    }
    $output_panel_top_title = '</div><div id="panel_title"><div class="panel_top_item_title">' . $lang['i_last_added'] . '</div><div class="panel_top_item_title">' . $lang['i_most_watched'] . '</div><div class="panel_top_item_title">' . $lang['i_last_played'] . '</div><div class="panel_top_item_title">' . $lang['i_top_rated'] . '</div></div>';
}

/* ####################
 * # ARRAYS FOR PANEL #
 */####################
if ($video == 'tvshows') {
    $columns = array('genre', 'premiered', 'cast');
} else {
    $columns = array('genre', 'year', 'country', 'director', 'sets', 'cast', 'v_codec', 'a_codec', 'a_chan');
}
$panels_array = panels_array($columns, $mysql_table);

if ($filter !== '') {
    $filter_mysql = $filter . ' LIKE "%' . $panels_array[$filter][$filterid] . '%" AND';
} else {
    $filter_mysql = '';
}

/* ##############
 * # LEFT PANEL #
 */##############
 
// overall panel
$output_overall_menu = '';
if ($set['panel_overall'] > 0) {
    $overall_sql = 'SELECT play_count FROM ' . $mysql_table;
    $overall_result = mysql_query($overall_sql);
    $overall_all = mysql_num_rows($overall_result);
    $overall_watched = 0;
    while($overall = mysql_fetch_array($overall_result)) {
        if ($overall['play_count'] > 0) {
            $overall_watched++;
        }
    }
    $overall_unwatched = $overall_all - $overall_watched;
    $output_overall_menu = '<div id="overall" class="panel_box_title">' . $lang['i_overall_title'] . '</div><div id="panel_overall" class="panel_box ' . $set['panel_overall'] . '"><ul><li><span class="bold orange">' . $lang['i_overall_all'] . ':</span> ' . $overall_all . '</li><li><span class="bold orange">' . $lang['i_overall_watched'] . ':</span> ' . $overall_watched . '</li><li><span class="bold orange">' . $lang['i_overall_notwatched'] . ':</span> ' . $overall_unwatched . '</li></ul></div>';
}

// menu panel
$menu_array = array('genre', 'year', 'country', 'sets', 'v_codec', 'a_codec', 'a_chan');
$output_menu = '';
foreach ($menu_array as $menu_name) {
    if ($set['panel_' . $menu_name] <> 0 && isset($panels_array[$menu_name])) {
        $output_menu.= '<div id="' . $menu_name . '" class="panel_box_title">' . $lang['i_' . $menu_name] . '</div><div id="panel_' . $menu_name . '" class="panel_box ' . $set['panel_' . $menu_name] . '"><ul>';
        foreach ($panels_array[$menu_name] as $key => $val) {
            if ($filter == $menu_name && $filterid == $key) {
                $output_menu.= '<li>' . $val . '</li>';
            } else {
                $output_menu.= '<li><a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=' . $menu_name . '&filterid=' . $key . '">' . $val . '</a></li>';
            }
        }
        $output_menu.= '</ul></div>';
    }
}

/* ########
 * # SORT #
 */########
if ($video == 'tvshows') {
    $sort_array = array(1 => $lang['i_title'], $lang['i_premiered'], $lang['i_rating'], $lang['i_added'], $lang['i_last_played'], $lang['i_most_watched']);
    $sort_mysql = array(1 => 'title ASC', 'premiered DESC', 'rating DESC', 'date_added DESC', 'last_played DESC', 'play_count DESC');
} else {
    $sort_array = array(1 => $lang['i_title'], $lang['i_year'], $lang['i_rating'], $lang['i_added'], $lang['i_runtime'], $lang['i_last_played'], $lang['i_most_watched']);
    $sort_mysql = array(1 => 'title ASC', 'year DESC', 'rating DESC', 'date_added DESC', ' CAST( runtime AS DECIMAL( 10, 2 ) ) DESC', 'last_played DESC', 'play_count DESC');
}
$play_count_mysql = ($sort == 6 ? ' > 0' : 'LIKE "%"');
$output_sort_menu = '';
foreach ($sort_array as $key => $val) {
    $output_sort_menu.= ($sort == $key ? 
    '<span class="box_inline">' . $val . '</span>' : 
    '<a class="box_inline" href="index.php?video=' . $video . '&sort=' . $key .
    '&filter=' . $filter .
    '&filterid=' . $filterid .
    '" title="' . $lang['i_sort'] . '">' . $val . '</a>');
}

/* ##########
 * # SEARCH #
 */##########
$search_mysql = '%';
if ($search !== '') {
    $search_mysql = $search;
}
$output_search = '<form method="get" action="index.php" autocomplete="off">
    <div id="panel_input_search">
        <input type="hidden" name="video" value="' . $video . '">
        <input id="search_' . $video . '" class="search" type="text" name="search" value="' . $lang['i_search'] . '..." title="' . $lang['i_search'] . '...">
        ' . ($set['live_search'] == 0 ? '' : '<div id="panel_live_search"></div>') . '
    </div></form>';

/* #############
 * # PANEL NAV #
 */#############
$id_mysql = ($id == 0 ? '%' : $id);
$nav_sql = 'SELECT id FROM ' . $mysql_table . ' WHERE
    ' . $filter_mysql . '
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
    $output_nav = ($page == 1 ? '<span class="box_inline">' . $lang['i_previous'] . '</span>' : '<a class="box_inline" href="index.php?video=' . $video . '&sort=' . $sort . '&page=' . ($page - 1) . '&filter=' . $filter . '&filterid=' . $filterid . '&search=' . $search . '">' . $lang['i_previous'] . '</a>')
             . ' <span class="box_inline">' . $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . '</span> ' .
            ($page == $i_pages ? '<span class="box_inline">' . $lang['i_next'] . '</span>' : '<a class="box_inline" href="index.php?video=' . $video . '&sort=' . $sort . '&page=' . ($page + 1) . '&filter=' . $filter . '&filterid=' . $filterid . '&search=' . $search . '">' . $lang['i_next'] . '</a>');
    if ($row == 0) {
        $output_nav = '';
    }
}

/* ################
 * # PANEL FILTER #
 */################
$output_panel_filter = '';
if ($filter !== '') {
    $output_panel_filter = '<div id="panel_filter"><div id="filter_text"><span class="orange bold">' . $lang['i_filter'] . ': </span>' . $lang['i_' . $filter] . ' &raquo; ' . $panels_array[$filter][$filterid] . '</div><a href="index.php?video=' . $video . '"><img id="filter_delete_img" class="animate" src="css/' . $set['theme'] . '/img/delete.png" title="' . $lang['i_del_result'] . '" alt=""></a></div>';
}
if ($search !== '') {
    $output_panel_filter = '<div id="panel_filter"><div id="filter_text"><span class="orange bold">' . $lang['i_search'] . ': </span>' . $lang['i_result'] . ' &raquo; ' . $search . '</div><a href="index.php?video=' . $video . '"><img id="filter_delete_img" class="animate" src="css/' . $set['theme'] . '/img/delete.png" title="' . $lang['i_del_result'] . '" alt=""></a></div>';
}
if ($id <> '') {
    $output_panel_filter = '<div id="panel_filter"><div id="filter_text"><span class="orange bold">' . $lang['i_filter'] . ': </span>' . $lang['i_title'] . '</div><a href="index.php?video=' . $video . '"><img id="filter_delete_img" class="animate" src="css/' . $set['theme'] . '/img/delete.png" title="' . $lang['i_del_result'] . '" alt=""></a></div>';
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

$list_sql = 'SELECT * FROM ' . $mysql_table . ' WHERE
    ' . $filter_mysql . '
    title LIKE "%' . $search_mysql . '%" AND
    id LIKE "' . $id_mysql . '" AND
    play_count ' . $play_count_mysql . '
    ORDER BY ' . $sort_mysql[$sort] . $limit_sql;
$list_result = mysql_query($list_sql);
$output_panel_list = '';

while ($list = mysql_fetch_array($list_result)) {
    
    $watched = '';
    $output_genre = '';
    $output_rating = '';
    $output_cast = '';
    $output_plot = '';
    $output_year = '';
    $output_country = '';
    $output_runtime = '';
    $output_director = '';
    $output_sets = '';
    $img_flag_vres = '';
    $img_flag_vtype = '';
    $img_flag_atype = '';
    $img_flag_achan = '';
    $trailer = '';
    $output_trailer = '';
    $output_premiered = '';
    $output_season = '';
    $output_episodes = '';
    $output_episodes_plot = '';
    
    // poster
    $poster = 'cache/' . $mysql_table . '_' . $list['id'] . '.jpg';
    if (!file_exists($poster)) {
        $poster = 'css/' . $set['theme'] . '/img/d_poster.jpg';
    }
    
    // wached status
    if ($set['watched_status'] == 1 && $list['play_count'] > 0) {
        $watched = '<img class="watched" src="css/' . $set['theme'] . '/img/watched.png" title="' . $lang['i_last_played'] . ': ' . $list['last_played'] . '" alt="">';
    }
    
    // genre
    $output_genre_array = array();
    foreach (explode(' / ', $list['genre']) as $val) {
        $output_genre_array[] = '<a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=genre&filterid=' . array_search($val, $panels_array['genre']) . '">' . $val . '</a>';
    }
    if ($list['genre'] !== '') {
        $output_genre = '
            <tr>
                <td class="left">' . $lang['i_genre'] . ':</td>
                <td class="right">' . implode(' / ', $output_genre_array) . '</td>
            </tr>';
    }
    
    // rating
    if ($list['rating'] !== '') {
        $output_rating = '
            <tr>
                <td class="left">' . $lang['i_rating'] . ':</td>
                <td class="right">' . round($list['rating'], 1) . '</td>
            </tr>';
    }
    
    // cast
    $output_cast_array = array();
    foreach (explode(' / ', $list['cast']) as $val) {
        if (strlen($val) > 0) {
            if (file_exists('cache/actors/' . substr(md5($val), 0, 10) . '.jpg')) {
                $actor_thumb = '<img class="actor_thumb" src="cache/actors/' . substr(md5($val), 0, 10) . '.jpg">';
            } else {
                $actor_thumb = '';
            }
            $output_cast_array[] = '<a class="actor_img" href="index.php?video=' . $video . '&sort=' . $sort . '&filter=cast&filterid=' . array_search($val, $panels_array['cast']) . '" alt="' . substr(md5($val), 0, 10) . '">' . $actor_thumb . $val . '</a>';
        }
    }
    if ($list['cast'] !== '') {
        $output_cast = '
            <tr>
                <td class="left">' . $lang['i_cast'] . ':</td>
                <td class="right">' . implode(' / ', $output_cast_array) . '</td>
            </tr>';
    }
    
    // plot
    if ($list['plot'] !== '') {
        $output_plot = '
            <tr>
                <td class="left">' . $lang['i_plot'] . ':</td>
                <td class="right">' . $list['plot'] . '</td>
            </tr>';
    }
    
    // only movies
    if ($video == 'movies') {
    
        // year
        if ($list['year'] !== '') {
            $output_year = '
                <tr>
                    <td class="left">' . $lang['i_year'] . ':</td>
                    <td class="right"><a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=year&filterid=' . array_search($list['year'], $panels_array['year']) . '">' . $list['year'] . '</a></td>
                </tr>';
        }
        
        // country
        $output_country_array = array();
        foreach (explode(' / ', $list['country']) as $val) {
            $output_country_array[] = '<a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=country&filterid=' . array_search($val, $panels_array['country']) . '">' . $val . '</a>';
        }
        if ($list['country'] !== '') {
            $output_country = '
                <tr>
                    <td class="left">' . $lang['i_country'] . ':</td>
                    <td class="right">' . implode(' / ', $output_country_array) . '</td>
                </tr>';
        }
        
        // runtime
        if ($list['runtime'] !== '0') {
            $output_runtime = '
                <tr>
                    <td class="left">' . $lang['i_runtime'] . ':</td>
                    <td class="right">' . $list['runtime'] . ' ' . $lang['i_minute'] . '</td>
                </tr>';
        }
        
        // director
        if ($list['director'] !== '') {
            $output_director = '
                <tr>
                    <td class="left">' . $lang['i_director'] . ':</td>
                    <td class="right"><a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=director&filterid=' . array_search($list['director'], $panels_array['director']) . '">' . $list['director'] . '</a></td>
                </tr>';
        }
        
        // sets
        if ($list['sets'] !== '') {
            $output_sets = '
                <tr>
                    <td class="left">' . $lang['i_sets'] . ':</td>
                    <td class="right"><a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=sets&filterid=' . array_search($list['sets'], $panels_array['sets']) . '">' . $list['sets'] . '</a></td>
                </tr>';
        }
        
        // video resolution
        foreach ($vres_assoc as $key => $val) {
            if (is_numeric($list['v_width']) && $list['v_width'] >= $key) {
                $img_flag_vres = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/vres_' . $val . '.png" alt="">';
            }
        }

        // video codec
        foreach ($vtype_assoc as $key => $val) {
            if (in_array($list['v_codec'], $vtype_assoc[$key])) {
                $img_flag_vtype = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/vc_' . $key . '.png" alt="">';
            }
        }

        // audio codec
        foreach ($atype_assoc as $key => $val) {
            if(in_array($list['a_codec'], $atype_assoc[$key])) {
                $img_flag_atype = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/ac_' . $key . '.png" alt="">';
            }
        }

        // audio channel
        foreach ($achan_assoc as $val) {
            if (is_numeric($list['a_chan']) && $list['a_chan'] >= $val) {
                $img_flag_achan = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/ach_' . $val . '.png" alt="">';
            }
        }

        // trailer
        if ($list['trailer'] !== NULL && $set['show_trailer'] == 1) {
            $trailer = '<a href="?id=' . $list['id'] . '"><img class="img_trailer" src="css/' . $set['theme'] . '/img/trailer.png" alt=""></a>';
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
        }
    }
    
    // only tvshows
    if ($video == 'tvshows') {
    
        // premiered
        if ($list['premiered'] !== '') {
            $output_premiered = '
                <tr>
                    <td class="left">' . $lang['i_premiered'] . ':</td>
                    <td class="right"><a href="index.php?video=tvshows&sort=' . $sort . '&filter=premiered&filterid=' . array_search($list['premiered'], $panels_array['premiered']) . '">' . $list['premiered'] . '</a></td>
                </tr>';
        }
    
        // seasons
        $season_array = array();
        $seasons_sql = 'SELECT season FROM ' . $mysql_tables[2] . ' WHERE tvshow = "' . $list['id'] . '" ORDER BY season';
        $seasons_result = mysql_query($seasons_sql);
        while ($seasons = mysql_fetch_array($seasons_result)) {
            if (!array_key_exists($seasons['season'], $season_array)) {
                $season_array[$seasons['season']] = '<a href="index.php?video=tvshows&id=' . $list['id'] . '#season_' . $seasons['season'] . '">' . $lang['i_season'] . ' ' . $seasons['season'] . '</a>';
            }
        }
        if (count($season_array) <> 0) {
            $output_season = '
                <tr>
                    <td class="left">' . $lang['i_seasons'] . ':</td>
                    <td class="right">' . implode(' / ', $season_array) . '</td>
                </tr>';
        }
        
        // episodes
        $episodes_array = array();
        if ($id <> 0) {
            $episodes_sql = 'SELECT title, episode, season, plot, firstaired, play_count, last_played FROM ' . $mysql_tables[2] . ' WHERE tvshow = "' . $list['id'] . '" ORDER BY season, episode ASC';
            $episodes_result = mysql_query($episodes_sql);
            $i = 0;
            $output_episodes.= '<table class="table">';
            while ($episodes = mysql_fetch_assoc($episodes_result)) {
                if ($episodes['plot'] !== '') {
                    $output_episodes_plot = '
                        <div class="episode_plot" id="plot_season_' . $episodes['season'] . '_episode_' . $episodes['episode'] . '">
                            <span class="orange bold">' . $lang['i_plot'] . ':</span> ' . $episodes['plot'] . '
                        </div>';
                }
                if ($episodes['season'] <> $i) {
                    $output_episodes.= '
                        <tr>
                            <td></td>
                            <td class="orange bold text_left" id="season_' . $episodes['season'] . '">' . $lang['i_season'] . ' ' . $episodes['season'] . '</td>
                            <td class="orange bold text_left aired">' . $lang['i_aired'] . '</td>
                            <td></td>
                        </tr>';
                }
                $output_episodes.= '
                    <tr class="episode" id="season_' . $episodes['season'] . '_episode_' . $episodes['episode'] . '">
                        <td class="left"></td>
                        <td class="right">' . $episodes['episode'] . '. ' . $episodes['title'] . $output_episodes_plot . '</td>
                        <td>' . $episodes['firstaired'] . '</td>
                        <td>' . ($episodes['play_count'] > 0 ? '<img class="watched_episode" src="css/' . $set['theme'] . '/img/watched.png" title="' . $lang['i_last_played'] . ': ' . $episodes['last_played'] . '" alt="">' : '') . '</td>
                    </tr>';
                $i = $episodes['season'];
            }
            $output_episodes.= '</table>';
        }
    }
    
    // panel movie
    $output_panel_list.= '
        <div id="' . $mysql_table . '_' . $list['id'] . '" class="movie">
            <div class="title"><a href="index.php?video=' . $video . '&id=' . $list['id'] . '">' . $list['title'] . '</a></div>
            <div class="title_org">' . $list['originaltitle'] . '</div>'
            . $watched . $trailer . '
            <img id="poster_movie_' . $list['id'] . '" class="poster" src="' . $poster . '" alt="">
            <div class="desc">
                <table class="table">
                    ' . $output_year . '
                    ' . $output_premiered . '
                    ' . $output_genre . '
                    ' . $output_rating . '
                    ' . $output_country . '
                    ' . $output_runtime . '
                    ' . $output_director . '
                    ' . $output_sets . '
                    ' . $output_season . '
                    ' . $output_cast . '
                    ' . $output_plot . '
                </table>
                ' . $output_episodes . '
                <img class="img_space" src="css/' . $set['theme'] . '/img/space.png" alt="">
                ' . $img_flag_vres . $img_flag_vtype . $img_flag_atype . $img_flag_achan . '
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
        <img src="css/<?PHP echo $set['theme'] ?>/img/bg.jpg" id="background" alt="">
        <div class="container">
            <div id="select_media">
                <?PHP echo $output_select_media ?>
            </div>
            <?PHP echo $output_panel_top . $output_panel_top_title ?>
            <div id="panel_left">
                <?PHP echo $output_overall_menu ?>
                <?PHP echo $output_menu ?>
            </div>
            <div id="panel_right" class="<?PHP echo ($set['panel_overall'] + $set['panel_genre'] + $set['panel_year'] + $set['panel_country'] + $set['panel_sets'] + $set['panel_v_codec'] + $set['panel_a_codec'] + $set['panel_a_chan'] == 0 ? '' : 'panel_right_ex') ?>">
                <div id="panel_sort"><?PHP echo $output_sort_menu ?></div>
                <div id="panel_search"><?PHP echo $output_search ?></div>
                <?PHP echo $output_nav ?>
                <?PHP echo $output_panel_filter ?>
                <?PHP echo $output_panel_list ?>
                <?PHP echo $output_nav ?>
            </div>
        </div>
        <div id="panel_bottom">
            <a href="http://github.com/Regss/movielib">MovieLib</a> <?PHP echo $version ?> - Created by <a href="mailto:regss84@gmail.com">Regss</a>
        </div>
    </body>
</html>