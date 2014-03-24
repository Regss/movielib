<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

require('config.php');
require('function.php');

/* #################
 * # DEFINE ARRAYS #
 */#################
$output = array(
    'panel_top_last_added'          = '',
    'panel_top_most_watched'        = '',
    'panel_top_last_played'         = '',
    'panel_top_top_rated'           = '',
    'overall_all'                   = '',
    'overall_watched'               = '',
    'overall_unwatched'             = '',
    'panel_genre'                   = '',
    'panel_year'                    = '',
    'panel_country'                 = '',
    'panel_sets'                    = '',
    'panel_v_codec'                 = '',
    'panel_a_codec'                 = '',
    'panel_a_chan'                  = '',
    'panel_sort'                    = '',
    'panel_nav'                     = ''
    
    
);
$show = array(
    'panel_top'                     = 0,
    'panel_overall'                 = 0,
    'panel_genre'                   = 0,
    'panel_year'                    = 0,
    'panel_country'                 = 0,
    'panel_sets'                    = 0,
    'panel_v_codec'                 = 0,
    'panel_a_codec'                 = 0,
    'panel_a_chan'                  = 0,
    'panel_live_search'             = $set['panel_live_search'],
    'panel_filter'                  = 0,
    
);

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
    $movies = '';
    $tvshows = 'media_active';
} else {
    $movies = 'media_active';
    $tvshows = '';
}

/* #############
 * # TOP PANEL #
 */#############

if ($set['panel_top'] == 1) {
    $show['panel_top'] = 1;
    $top_panel_sql = array(
        'panel_top_last_added' => 'SELECT id, title, date_added FROM ' . $mysql_table . ' ORDER BY date_added DESC LIMIT ' . $set['panel_top_limit'],
        'panel_top_most_watched' => 'SELECT id, title FROM ' . $mysql_table . ' ORDER BY play_count DESC LIMIT ' . $set['panel_top_limit'],
        'panel_top_last_played' => 'SELECT id, title, last_played FROM ' . $mysql_table . ' WHERE play_count > 0 ORDER BY last_played DESC LIMIT ' . $set['panel_top_limit'],
        'panel_top_top_rated' => 'SELECT id, title, rating FROM ' . $mysql_table . ' ORDER BY rating DESC LIMIT ' . $set['panel_top_limit']
    );
    foreach ($top_panel_sql as $name => $item_top_sql) {
        $output_item_top = '';
        $item_top_result = mysql_query($item_top_sql);
        while ($item_top = mysql_fetch_array($item_top_result)) {
            if (file_exists('cache/' . $mysql_table . '_' . $item_top['id'] . '.jpg')) {
                $output_item_top.= '<a href="index.php?video=' . $video . '&id=' . $item_top['id'] . '"><img src="cache/' . $mysql_table . '_' . $item_top['id'] . '.jpg" title="' . $item_top['title'] . '" alt=""></a>';
            }
        }
        $output[$name] = $output_item_top;
    }
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
if ($set['panel_overall'] > 0) {
    $show['panel_overall'] = 1;
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
}

// menu panel
$menu_array = array('genre', 'year', 'country', 'sets', 'v_codec', 'a_codec', 'a_chan');
foreach ($menu_array as $menu_name) {
    $output_li = '';
    if ($set['panel_' . $menu_name] <> 0 && isset($panels_array[$menu_name])) {
        $show['panel_' . $menu_name] = 1;
        foreach ($panels_array[$menu_name] as $key => $val) {
            if ($filter == $menu_name && $filterid == $key) {
                $output_li.= '<li>' . $val . '</li>';
            } else {
                $output_li.= '<li><a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=' . $menu_name . '&filterid=' . $key . '">' . $val . '</a></li>';
            }
        }
    }
    $output['panel_' . $menu_name] = $output_li;
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
    '<span>' . $val . '</span>' : 
    '<a href="index.php?video=' . $video . '&sort=' . $key .
    '&filter=' . $filter .
    '&filterid=' . $filterid .
    '" title="' . $lang['i_sort'] . '">' . $val . '</a>');
}
$output['panel_sort'] = $output_sort_menu;

/* ##########
 * # SEARCH #
 */##########
$search_mysql = '%';
if ($search !== '') {
    $search_mysql = $search;
}
    
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
    $output_nav = ($page == 1 ? '<span>' . $lang['i_previous'] . '</span>' : '<a href="index.php?video=' . $video . '&sort=' . $sort . '&page=' . ($page - 1) . '&filter=' . $filter . '&filterid=' . $filterid . '&search=' . $search . '">' . $lang['i_previous'] . '</a>')
             . ' <span>' . $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . '</span> ' .
            ($page == $i_pages ? '<span>' . $lang['i_next'] . '</span>' : '<a href="index.php?video=' . $video . '&sort=' . $sort . '&page=' . ($page + 1) . '&filter=' . $filter . '&filterid=' . $filterid . '&search=' . $search . '">' . $lang['i_next'] . '</a>');
    if ($row == 0) {
        $output_nav = '';
    }
}
$output['panel_nav'] = $output_nav;

/* ################
 * # PANEL FILTER #
 */################
if ($filter !== '') {
    $output['panel_filter'] = '<span>' . $lang['i_filter'] . ': </span>' . $lang['i_' . $filter] . ' &raquo; ' . $panels_array[$filter][$filterid];
    $show['panel_filter'] = 1;
}
if ($search !== '') {
    $output['panel_filter'] = '<span>' . $lang['i_search'] . ': </span>' . $lang['i_result'] . ' &raquo; ' . $search;
    $show['panel_filter'] = 1;
}
if ($id <> '') {
    $output['panel_filter'] = '<span>' . $lang['i_filter'] . ': </span>' . $lang['i_title'];
    $show['panel_filter'] = 1;
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
    
    $output_desc = array();
    
    $output_desc['mysql_table']     = $mysql_table;
    $output_desc['id']              = $list['id'];
    $output_desc['video']           = $video;
    $output_desc['title']           = $list['title'];
    $output_desc['originaltitle']   = $list['originaltitle'];
    $output_desc['poster']          = '';
    $output_desc['watched_img']     = '';
    $output_desc['genre']           = '';
    $output_desc['rating']          = '';
    $output_desc['cast']            = '';
    $output_desc['plot']            = '';
    $output_desc['year']            = '';
    $output_desc['country']         = '';
    $output_desc['runtime']         = '';
    $output_desc['director']        = '';
    $output_desc['sets']            = '';
    $output_desc['img_flag_vres']   = '';
    $output_desc['img_flag_vtype']  = '';
    $output_desc['img_flag_atype']  = '';
    $output_desc['img_flag_achan']  = '';
    $output_desc['trailer_img']     = '';
    $output_desc['trailer']         = '';
    $output_desc['premiered']       = '';
    $output_desc['season']          = '';
    $output_desc['episodes']        = '';
    $output_desc['episodes_plot']   = '';
        
    // poster
    $poster = 'cache/' . $mysql_table . '_' . $list['id'] . '.jpg';
    if (!file_exists($poster)) {
        $output_desc['poster'] = 'css/' . $set['theme'] . '/img/d_poster.jpg';
    }
    
    // wached status
    if ($set['watched_status'] == 1 && $list['play_count'] > 0) {
        $output_desc['watched_img'] = '<img class="watched" src="css/' . $set['theme'] . '/img/watched.png" title="' . $lang['i_last_played'] . ': ' . $list['last_played'] . '" alt="">';
    }
    
    // genre
    $output_genre_array = array();
    foreach (explode(' / ', $list['genre']) as $val) {
        $output_genre_array[] = '<a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=genre&filterid=' . array_search($val, $panels_array['genre']) . '">' . $val . '</a>';
    }
    if ($list['genre'] !== '') {
        $output_desc['genre'] = implode(' / ', $output_genre_array);
    }
    
    // rating
    if ($list['rating'] !== '') {
        $output_desc['rating'] = round($list['rating'], 1);
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
        $output_desc['cast'] = implode(' / ', $output_cast_array);
    }
    
    // plot
    if ($list['plot'] !== '') {
        $output_desc['plot'] = $list['plot'];
    }
    
    // only movies
    if ($video == 'movies') {
    
        // year
        if ($list['year'] !== '') {
            $output_desc['year'] = '<a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=year&filterid=' . array_search($list['year'], $panels_array['year']) . '">' . $list['year'] . '</a>';
        }
        
        // country
        $output_country_array = array();
        foreach (explode(' / ', $list['country']) as $val) {
            $output_country_array[] = '<a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=country&filterid=' . array_search($val, $panels_array['country']) . '">' . $val . '</a>';
        }
        if ($list['country'] !== '') {
            $output_desc['country'] = implode(' / ', $output_country_array);
        }
        
        // runtime
        if ($list['runtime'] !== '0') {
            $output_desc['runtime'] = $list['runtime'];
        }
        
        // director
        if ($list['director'] !== '') {
            $output_desc['director'] = '<a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=director&filterid=' . array_search($list['director'], $panels_array['director']) . '">' . $list['director'] . '</a>';
        }
        
        // sets
        if ($list['sets'] !== '') {
            $output_desc['sets'] = '<a href="index.php?video=' . $video . '&sort=' . $sort . '&filter=sets&filterid=' . array_search($list['sets'], $panels_array['sets']) . '">' . $list['sets'] . '</a>';
        }
        
        // video resolution
        foreach ($vres_assoc as $key => $val) {
            if (is_numeric($list['v_width']) && $list['v_width'] >= $key) {
                $output_desc['img_flag_vres'] = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/vres_' . $val . '.png" alt="">';
            }
        }

        // video codec
        foreach ($vtype_assoc as $key => $val) {
            if (in_array($list['v_codec'], $vtype_assoc[$key])) {
                $output_desc['img_flag_vtype'] = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/vc_' . $key . '.png" alt="">';
            }
        }

        // audio codec
        foreach ($atype_assoc as $key => $val) {
            if(in_array($list['a_codec'], $atype_assoc[$key])) {
                $output_desc['img_flag_atype'] = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/ac_' . $key . '.png" alt="">';
            }
        }

        // audio channel
        foreach ($achan_assoc as $val) {
            if (is_numeric($list['a_chan']) && $list['a_chan'] >= $val) {
                $output_desc['img_flag_achan'] = '<img class="flag" src="css/' . $set['theme'] . '/img/flags/ach_' . $val . '.png" alt="">';
            }
        }

        // trailer
        if ($list['trailer'] !== NULL && $set['show_trailer'] == 1) {
            $output_desc['trailer_img'] = '<a href="?id=' . $list['id'] . '"><img class="img_trailer" src="css/' . $set['theme'] . '/img/trailer.png" alt=""></a>';
        }
        if ($list['trailer'] !== NULL && $set['show_trailer'] == 1 && $id <> 0) {
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
        }
        $output_desc['trailer'] = $output_trailer;
    }
    
    // only tvshows
    if ($video == 'tvshows') {
    
        // premiered
        if ($list['premiered'] !== '') {
            $output_desc['premiered'] = '<a href="index.php?video=tvshows&sort=' . $sort . '&filter=premiered&filterid=' . array_search($list['premiered'], $panels_array['premiered']) . '">' . $list['premiered'] . '</a>';
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
            $output_desc['season'] = implode(' / ', $season_array);
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
            $output_desc['episodes'] = $output_episodes;
        }
    }
    
    // panel movie
    $panel_list = new Teamplate('panel_list.tpl', $set, $lang);
    
    foreach ($output_desc as $key => $val) {
        $panel_list->tpl($key, $val);
    }
    $output_panel_list.= $panel_list->init();
}

$index = new Teamplate('index.tpl', $set, $lang);

$index->tpl('video', $video);
$index->tpl('version', $version);
$index->tpl('movies', $movies);
$index->tpl('tvshows', $tvshows);
$index->tpl('top_item_last_added', $output_top_item['last_added']);
$index->tpl('top_item_most_watched', $output_top_item['most_watched']);
$index->tpl('top_item_last_played', $output_top_item['last_played']);
$index->tpl('top_item_top_rated', $output_top_item['top_rated']);
$index->tpl('top_item_title', $output_panel_top_title);
$index->tpl('overall_all', $overall_all);
$index->tpl('overall_watched', $overall_watched);
$index->tpl('overall_unwatched', $overall_unwatched);
$index->tpl('panel_genre', $output_menu['panel_genre']);
$index->tpl('panel_year', $output_menu['panel_year']);
$index->tpl('panel_country', $output_menu['panel_country']);
$index->tpl('panel_sets', $output_menu['panel_sets']);
$index->tpl('panel_v_codec', $output_menu['panel_v_codec']);
$index->tpl('panel_a_codec', $output_menu['panel_a_codec']);
$index->tpl('panel_a_chan', $output_menu['panel_a_chan']);
$index->tpl('sort_menu', $output_sort_menu);
$index->tpl('output_nav', $output_nav);
$index->tpl('output_panel_filter', $output_panel_filter);
$index->tpl('panel_list', $output_panel_list);

$index->show('panel_overall', $set['panel_overall']);
$index->show('panel_genre', $show['panel_genre']);
$index->show('panel_year', $show['panel_year']);
$index->show('panel_country', $show['panel_country']);
$index->show('panel_sets', $show['panel_sets']);
$index->show('panel_v_codec', $show['panel_v_codec']);
$index->show('panel_a_codec', $show['panel_a_codec']);
$index->show('panel_a_chan', $show['panel_a_chan']);
$index->show('panel_filter', $show['panel_filter']);
$index->show('panel_live_search', $set['live_search']);
print $index->init();

?>