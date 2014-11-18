<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

include('config.php');
include('function.php');

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

/* ##################
 * # CHECK PASSWORD #
 */##################
if ($setting['protect_site'] == 1) {
    if ($_SESSION['logged'] !== true) {
        header('Location:login.php');
        die('Can\'t redirect to login.php');
    }
}

/* #################
 * # DEFINE ARRAYS #
 */#################
 if (!isset($_GET['video'])) {
    $video = 'movies';
} else {
    $video = $_GET['video'];
}

// views
$view = $setting['view'];
if (isset($_COOKIE['view'])) {
    $view = $_COOKIE['view'];
}
if (isset($_GET['view'])) {
    $view = $_GET['view'];
    setcookie('view', $view, time()+(60 * 60 * 24 * 7));
}
if ($id == 0) {
    $include_view = $view;
} else {
    $include_view = 0;
}

// output and show arrays
$output = array();
$show = array();    
foreach ($item as $val) {
    $output[$val] = '';
    $show[$val] = 0;
}
 
$output['version'] = $version;
$output['view'] = $view;
$output['include_view'] = $views[$include_view];
$output['video'] = $video;
$output['watch'] = $watch;

/* ################
 * # SELECT MEDIA #
 */################
if ($video == 'tvshows') {
    $mysql_table = 'tvshows';
    $output['select_media'] = '<a href="index.php?video=movies&view=' . $view . '">' . mb_strtoupper($lang['i_movies']) . '</a><span>' . mb_strtoupper($lang['i_tvshows']) . '</span>';
} else {
    $mysql_table = 'movies';
    $output['select_media'] = '<span>' . mb_strtoupper($lang['i_movies']) . '</span><a href="index.php?video=tvshows&view=' . $view . '">' . mb_strtoupper($lang['i_tvshows']) . '</a>';
}

/* #############
 * # TOP PANEL #
 */#############
$show['panel_top'] = $setting['panel_top'];
if ($setting['panel_top'] == 1) {
    $top_panel_sql = array(
        'top_item_last_added' => 'SELECT id, title, date_added, hide FROM ' . $mysql_table . ' WHERE hide=0 ORDER BY date_added DESC LIMIT ' . $setting['panel_top_limit'],
        'top_item_most_watched' => 'SELECT id, title, hide FROM ' . $mysql_table . ' WHERE hide=0 ORDER BY play_count DESC LIMIT ' . $setting['panel_top_limit'],
        'top_item_last_played' => 'SELECT id, title, last_played, hide FROM ' . $mysql_table . ' WHERE hide=0 ORDER BY last_played DESC LIMIT ' . $setting['panel_top_limit'],
        'top_item_top_rated' => 'SELECT id, title, rating, hide FROM ' . $mysql_table . ' WHERE hide=0 ORDER BY rating DESC LIMIT ' . $setting['panel_top_limit']
    );
    foreach ($top_panel_sql as $name => $item_top_sql) {
        $output[$name] = '';
        $item_top_result = mysql_q($item_top_sql);
        while ($item_top = mysql_fetch_array($item_top_result)) {
            if (file_exists('cache/' . $mysql_table . '_' . $item_top['id'] . '.jpg')) {
                $output[$name].= '<a href="index.php?video=' . $video . '&view=' . $view . '&id=' . $item_top['id'] . '"><img src="cache/' . $mysql_table . '_' . $item_top['id'] . '.jpg" title="' . $item_top['title'] . '" alt=""></a>';
            }
        }
    }
}

/* ####################
 * # ARRAYS FOR PANEL #
 */####################
if ($video == 'tvshows') {
    $columns = array('actor', 'genre', 'premiered');
} else {
    $columns = array('actor', 'genre', 'country', 'year', 'director', 'set', 'studio');
}
$panels_array = panels_array($columns, $mysql_table);

$filter_array = array('actor', 'genre', 'country', 'studio', 'director');
if ($filter == '') {
    $mysql_table2 = '';
    $filter_mysql = '';
} else if (in_array($filter, $filter_array)) {
    $mysql_table2 = ', ' . $mysql_table . '_' . $filter;
    $filter_mysql = $mysql_table . '_' . $filter . '.' . $filter . 'id = '. $_GET['filterid'] . ' AND ' . $mysql_table . '.id = ' . $mysql_table . '_' . $filter . '.id AND';
} else {
    $mysql_table2 = '';
    $filter_mysql = $mysql_table . '.' . $filter . ' LIKE "%' . $panels_array[$filter][$filterid] . '%" AND';
}

/* ##############
 * # LEFT PANEL #
 */##############
 
// overall panel
$show['panel_overall'] = $setting['panel_overall'];
if ($setting['panel_overall'] > 0) {
    $overall_sql = 'SELECT play_count, hide FROM ' . $mysql_table . ' WHERE hide=0';
    $overall_result = mysql_q($overall_sql);
    $overall_all = mysql_num_rows($overall_result);
    $overall_watched = 0;
    while($overall = mysql_fetch_array($overall_result)) {
        if ($overall['play_count'] > 0) {
            $overall_watched++;
        }
    }
    $output['overall_all'] = $overall_all;
    $output['overall_watched'] = $overall_watched;
    $output['overall_unwatched'] = $overall_all - $overall_watched;
}

// menu panel
$menu_array = array('genre', 'year', 'country', 'set', 'studio');
foreach ($menu_array as $menu_name) {
    $output['panel_' . $menu_name] = '';
    if ($setting['panel_' . $menu_name] <> 0 && isset($panels_array[$menu_name]) && count($panels_array[$menu_name]) > 0) {
        $show['panel_' . $menu_name] = 1;
        foreach ($panels_array[$menu_name] as $key => $val) {
            if ($filter == $menu_name && $filterid == $key) {
                $output['panel_' . $menu_name].= '<li>' . $val . '</li>';
            } else {
                $output['panel_' . $menu_name].= '<li><a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=' . $menu_name . '&filterid=' . $key . '">' . $val . '</a></li>';
            }
        }
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
$output['panel_sort'] = '';
foreach ($sort_array as $key => $val) {
    $output['panel_sort'].= ($sort == $key ? 
    '<span>' . $val . '</span>' : 
    '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $key .
    '&filter=' . $filter .
    '&filterid=' . $filterid .
    '" title="' . $lang['i_sort'] . '">' . $val . '</a>');
}

/* #########
 * # WATCH #
 */#########
$show['panel_watch'] = 1;
$watch_arr = array(0 => 'all', 1 => 'watched', 2 => 'unwatched');
$output['panel_watch'].= '<div id="watch_menu"><div id="watch_title">' . $lang['i_watched_status'] . ': ' . $lang['i_' . $watch_arr[$watch]] . '</div><div id="watch">';
foreach ($watch_arr as $key => $val) {
        if ($watch == $key) {
            $output['panel_watch'].= '<span>' . $lang['i_' . $val] . '</span>';
        } else {
            $output['panel_watch'].= '<a href="index.php?id=' . $id . '&video=' . $video . '&view=' . $view . '&watch=' . $key . '&sort=' . $sort . '&filter=' . $filter . '&filterid=' . $filterid . '">' . $lang['i_' . $val] . '</a>';
        }
}
if ($watch == 1) {
    $watch_mysql = ' > 0';
} elseif ($watch == 2) {
    $watch_mysql = ' = 0';
} else {
    $watch_mysql = ' >= 0';
}
$output['panel_watch'].= '</div></div>';

/* ########
 * # VIEW #
 */########
if ($setting['panel_view'] > 0) {
    $show['panel_view'] = 1;
    $output['panel_view'].= '<div id="view_menu"><div id="view_title">' . $lang['i_view'] . ': ' . $lang['i_' . $views[$view]] . '</div><div id="views">';
    foreach ($views as $key => $val) {
            if ($view == $key) {
                $output['panel_view'].= '<span>' . $lang['i_' . $val] . '</span>';
            } else {
                $output['panel_view'].= '<a href="index.php?id=' . $id . '&video=' . $video . '&view=' . $key . '&watch=' . $watch . '&sort=' . $sort . '&filter=' . $filter . '&filterid=' . $filterid . '">' . $lang['i_' . $val] . '</a>';
            }
    }
    $output['panel_view'].= '</div></div>';
}

/* ##########
 * # SEARCH #
 */##########
 $show['panel_live_search'] = $setting['live_search'];
$search_mysql = '%';
if ($search !== '') {
    $search_mysql = $search;
}

/* #############
 * # PANEL NAV #
 */#############
$id_mysql = ($id == 0 ? '%' : $id);
$nav_sql = 'SELECT ' . $mysql_table . '.id FROM ' . $mysql_table . $mysql_table2 . ' WHERE
    ' . $filter_mysql . '
    ' . $mysql_table . '.title LIKE "%' . $search_mysql . '%" AND
    ' . $mysql_table . '.id LIKE "' . $id_mysql . '" AND
    ' . $mysql_table . '.play_count ' . $watch_mysql . ' AND
    ' . $mysql_table . '.hide=0
    ORDER BY ' . $sort_mysql[$sort];
    
$nav_result = mysql_q($nav_sql);
$row = mysql_num_rows($nav_result);
if ($setting['per_page'] == 0) {
    $i_pages = 1;
    $output['panel_nav'] = '';
} else {
    $i_pages = (ceil($row / $setting['per_page']));
    $output['panel_nav'] = ($page == 1 ? '<span>' . $lang['i_previous'] . '</span>' : '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&page=' . ($page - 1) . '&filter=' . $filter . '&filterid=' . $filterid . '&search=' . $search . '">' . $lang['i_previous'] . '</a>')
             . ' <span>' . $lang['i_page'] . ' ' . $page . ' / ' . $i_pages . '</span> ' .
            ($page == $i_pages ? '<span>' . $lang['i_next'] . '</span>' : '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&page=' . ($page + 1) . '&filter=' . $filter . '&filterid=' . $filterid . '&search=' . $search . '">' . $lang['i_next'] . '</a>');
    if ($row == 0) {
        $output['panel_nav'] = '';
    }
}

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
if ($id > 0) {
    $output['panel_filter'] = '<span>' . $lang['i_filter'] . ': </span>' . $lang['i_title'];
    $show['panel_filter'] = 1;
}

/* ##################
 * # CONTROL REMOTE #
 */##################
if (isset($_SESSION['logged_admin']) && $_SESSION['logged_admin'] == true) {
    $show['panel_remote'] = 1;
}

/* ##############
 * # MOVIE LIST #
 */##############
if ($setting['per_page'] == 0) {
    $limit_sql = '';
} else {
    $start = ($page - 1) * $setting['per_page'];
    $limit_sql = ' LIMIT ' . $start . ', ' . $setting['per_page'];
}
$list_sql = 'SELECT ' . $mysql_table . '.* FROM ' . $mysql_table . $mysql_table2 . ' WHERE
    ' . $filter_mysql . '
    ' . $mysql_table . '.title LIKE "%' . $search_mysql . '%" AND
    ' . $mysql_table . '.id LIKE "' . $id_mysql . '" AND
    ' . $mysql_table . '.play_count ' . $watch_mysql . ' AND
    ' . $mysql_table . '.hide=0
    ORDER BY ' . $sort_mysql[$sort] . $limit_sql;

$list_result = mysql_q($list_sql);
        
$output_panel_list = '';
while ($list = mysql_fetch_assoc($list_result)) {

    // output and show desc arrays
    $output_desc = array();
    $show_desc = array();    
    foreach ($item_desc as $val) {
        $output_desc[$val] = '';
        $show_desc[$val] = 0;
    }
    
    $output_desc['mysql_table']     = $mysql_table;
    $output_desc['id']              = $list['id'];
    $output_desc['video']           = $video;
    $output_desc['view']            = $view;
    $output_desc['sort']            = $sort;
    $output_desc['filter']          = $filter;
    $output_desc['filterid']        = $filterid;
    $output_desc['title']           = $list['title'];
    
    $show_desc['mysql_table']     = 1;
    $show_desc['id']              = 1;
    $show_desc['video']           = 1;
    $show_desc['title']           = 1;
    
    if (isset($_SESSION['logged_admin']) && $_SESSION['logged_admin'] == true && $video == 'movies') {
        $show_desc['xbmc'] = 1;
    }
    if (isset($_SESSION['logged_admin']) && $_SESSION['logged_admin'] == true && $video == 'tvshows') {
        $show_desc['xbmc_episode'] = 1;
    }
    
    // originaltitle
    if ($list['originaltitle'] !== '') {
        $show_desc['originaltitle'] = 1;
        $output_desc['originaltitle'] = $list['originaltitle'];
    }
    
    // file
    if ($video == 'movies') {
        $output_desc['file'] = 'http://' . $setting['xbmc_login'] . ':' . $setting['xbmc_pass'] . '@' . $setting['xbmc_host'] . ':' . $setting['xbmc_port'] . '/vfs/' . urlencode($list['file']);
    }
    
    // poster
    $poster = 'cache/' . $mysql_table . '_' . $list['id'] . '.jpg';
    if (!file_exists($poster)) {
        $output_desc['poster'] = 'templates/' . $setting['theme'] . '/img/d_poster.jpg';
    } else {
        $output_desc['poster'] = $poster;
    }
    
    // wached status
    if ($setting['watched_status'] == 1 && $list['play_count'] > 0) {
        $output_desc['watched_img'] = '<img class="watched_img" src="templates/' . $setting['theme'] . '/img/watched.png" title="' . $lang['i_last_played'] . ': ' . $list['last_played'] . '" alt="">';
    }
    
    // genre
    $output_genre_array = array();
    $genre_sql = 'SELECT genre.id, genre.genre FROM genre, ' . $video . '_genre WHERE ' . $video . '_genre.id = "' . $list['id'] . '" AND genre.id = ' . $video . '_genre.genreid';
    $genre_res = mysql_q($genre_sql);
        
    while ($val =  mysql_fetch_assoc($genre_res)) {
        $output_genre_array[] = '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=genre&filterid=' . $val['id'] . '">' . $val['genre'] . '</a>';
    }
    if (count($output_genre_array) > 0) {
        $show_desc['genre'] = 1;
        $output_desc['genre'] = implode(' / ', $output_genre_array);
    }
    
    // rating
    if ($list['rating'] !== '') {
        $show_desc['rating'] = 1;
        $output_desc['rating'] = round($list['rating'], 1);
    }
    
    // actors
    $output_actor_array = array();
    $actor_sql = 'SELECT actor.id, actor.actor FROM actor, ' . $video . '_actor WHERE ' . $video . '_actor.id = "' . $list['id'] . '" AND actor.id = ' . $video . '_actor.actorid ORDER BY ' . $video . '_actor.order';
    $actor_res = mysql_q($actor_sql);
    
    while ($val = mysql_fetch_assoc($actor_res)) {
        if ($val['actor'] !== '') {
            if (file_exists('cache/actors/' . substr(md5($val['actor']), 0, 10) . '.jpg')) {
                $actor_thumb = '<img class="actor_thumb" src="cache/actors/' . substr(md5($val['actor']), 0, 10) . '.jpg">';
            } else {
                $actor_thumb = '';
            }
            $output_actor_array[] = '<a class="actor_img" href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=actor&filterid=' . $val['id'] . '" alt="' . substr(md5($val['actor']), 0, 10) . '">' . $actor_thumb . $val['actor'] . '</a>';
        }
    }
    if (count($output_actor_array) > 0) {
        $show_desc['actor'] = 1;
        $output_desc['actor'] = implode(' / ', $output_actor_array);
    }
    
    // plot
    if ($list['plot'] !== '') {
        $show_desc['plot'] = 1;
        $output_desc['plot'] = $list['plot'];
    }
    
    // only movies
    if ($video == 'movies') {
    
        // year
        if ($list['year'] !== '') {
            $show_desc['year'] = 1;
            $output_desc['year'] = '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=year&filterid=' . array_search($list['year'], $panels_array['year']) . '">' . $list['year'] . '</a>';
        }
        
        // country
        $output_country_array = array();
        $country_sql = 'SELECT country.id, country.country FROM country, ' . $video . '_country WHERE ' . $video . '_country.id = "' . $list['id'] . '" AND country.id = ' . $video . '_country.countryid';
        $country_res = mysql_q($country_sql);
        
        while ($val =  mysql_fetch_assoc($country_res)) {
            $output_country_array[] = '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=country&filterid=' . $val['id'] . '">' . $val['country'] . '</a>';
        }
        if (count($output_country_array) > 0) {
            $show_desc['country'] = 1;
            $output_desc['country'] = implode(' / ', $output_country_array);
        }
        
        // runtime
        if ($list['runtime'] !== '0') {
            $show_desc['runtime'] = 1;
            $output_desc['runtime'] = $list['runtime'];
        }
        
        // director
        $director_sql = 'SELECT director.id, director.director FROM director, ' . $video . '_director WHERE ' . $video . '_director.id = "' . $list['id'] . '" AND director.id = ' . $video . '_director.directorid';
        $director_res = mysql_q($director_sql);
        $val =  mysql_fetch_assoc($director_res);
        if (isset($val['director'])) {
            $show_desc['director'] = 1;
            $output_desc['director'] = '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=director&filterid=' . $val['id'] . '">' . $val['director'] . '</a>';
        }
        
        // set
        if ($list['set'] !== '') {
            $show_desc['set'] = 1;
            $output_desc['set'] = '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=set&filterid=' . array_search($list['set'], $panels_array['set']) . '">' . $list['set'] . '</a>';
        }
        
        // studio
        $studio_sql = 'SELECT studio.id, studio.studio FROM studio, ' . $video . '_studio WHERE ' . $video . '_studio.id = "' . $list['id'] . '" AND studio.id = ' . $video . '_studio.studioid';
        $studio_res = mysql_q($studio_sql);
        $val =  mysql_fetch_assoc($studio_res);
        if (isset($val['studio'])) {
            $show_desc['studio'] = 1;
            $output_desc['studio'] = '<a href="index.php?video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=studio&filterid=' . $val['id'] . '">' . $val['studio'] . '</a>';
            if (file_exists('templates/' . $setting['theme'] . '/img/studios/' . $val['studio'] . '.png')) {
                $show_desc['studio_art'] = 1;
                $output_desc['studio_art'] = '<img class="studio" src="templates/' . $setting['theme'] . '/img/studios/' . $val['studio'] . '.png">';
            }
        }
        
        // streams
        $stream_sql = 'SELECT * FROM `movies_stream` WHERE id = "' . $list['id'] . '"';
        $stream_res = mysql_q($stream_sql);
        
        if (mysql_num_rows($stream_res) > 0) {
            $str = array('v' => array(), 'a' => array(), 's' => array());
            while ($stream = mysql_fetch_assoc($stream_res)) {
                $str[$stream['type']][] = $stream;
            }
        }
        
        $img_flag_vres = '';
        $img_flag_vtype = '';
        $img_flag_vq = '';
        if (isset($str['v'])) {
            foreach ($str['v'] as $s) {
                // video resolution
                foreach ($vres_assoc as $key => $val) {
                    if (is_numeric($s['v_width']) && $s['v_width'] >= $key) {
                        $img_flag_vres = '<img class="flag" src="templates/' . $setting['theme'] . '/img/flags/vres_' . $val . '.png" alt="">';
                    }
                }
                
                // video codec
                foreach ($vtype_assoc as $key => $val) {
                    if (in_array($s['v_codec'], $vtype_assoc[$key])) {
                        $img_flag_vtype = '<img class="flag" src="templates/' . $setting['theme'] . '/img/flags/vc_' . $key . '.png" alt="">';
                    }
                }
                
                // video hd or sd
                if (is_numeric($s['v_width']) && $s['v_width'] >= 1280) {
                    $img_flag_vq = '<img class="flag" src="templates/' . $setting['theme'] . '/img/flags/v_hd.png" alt="">';
                } else {
                    $img_flag_vq = '<img class="flag" src="templates/' . $setting['theme'] . '/img/flags/v_sd.png" alt="">';
                }
                
                $output_desc['img_flag_v'].= '<div>' . $img_flag_vres . $img_flag_vtype . $img_flag_vq . '</div>';
            }
        }
        if (isset($str['a'])) {
            foreach ($str['a'] as $s) {
                // audio codec
                foreach ($atype_assoc as $key => $val) {
                    if(in_array($s['a_codec'], $atype_assoc[$key])) {
                        $img_flag_atype = '<img class="flag" src="templates/' . $setting['theme'] . '/img/flags/ac_' . $key . '.png" alt="">';
                    }
                }
                
                // audio channel
                foreach ($achan_assoc as $val) {
                    if (is_numeric($s['a_chan']) && $s['a_chan'] >= $val) {
                        $img_flag_achan = '<img class="flag" src="templates/' . $setting['theme'] . '/img/flags/ach_' . $val . '.png" alt="">';
                    }
                }
                
                // audio language
                if (file_exists('templates/' . $setting['theme'] . '/img/flags/l_' . $s['a_lang'] . '.png')) {
                    $img_flag_alang = '<img class="flag" src="templates/' . $setting['theme'] . '/img/flags/l_' . $s['a_lang'] . '.png" alt="">';
                } else {
                    $img_flag_alang = $s['a_lang'];
                }
                $output_desc['img_flag_a'].= '<div>' . $img_flag_atype . $img_flag_achan . $img_flag_alang . '</div>';
            }
        }
        
        // subtitles
        if (isset($str['s'])) {
            foreach ($str['s'] as $s) {
                if (file_exists('templates/' . $setting['theme'] . '/img/flags/l_' . $s['s_lang'] . '.png')) {
                    $img_flag_slang = '<img src="templates/' . $setting['theme'] . '/img/flags/sub.png" alt=""><img class="flag" src="templates/' . $setting['theme'] . '/img/flags/l_' . $s['s_lang'] . '.png" alt="">';
                } else {
                    $img_flag_slang = $s['s_lang'];
                }
                $output_desc['img_flag_s'].= '<div>' . $img_flag_slang . '</div>';
            }
        }
        
        // extra thumbs
        $c = 1;
        $ex_thumb_array = array();
        for (;;) {
            $ex_t = 'cache/movies_' . $list['id'] . '_t' . $c . 'm.jpg';
            if (file_exists($ex_t)) {
                $ex_thumb_array[] = '<img src="' . $ex_t . '">';
            } else {
                break;
            }
            $c++;
        }
        if (count($ex_thumb_array) > 0) {
            $show_desc['extra_thumbs'] = 1;
            $output_desc['extra_thumbs'] = '<div class="ex_thumbs">' . implode('', $ex_thumb_array) . '</div>';
        }
        
        // trailer
        if ($list['trailer'] !== '' && $setting['show_trailer'] == 1) {
            $output_desc['trailer_img'] = '<a href="index.php?id=' . $list['id'] . '&video=' . $video . '&view=' . $view . '&watch=' . $watch . '&sort=' . $sort . '&filter=' . $filter . '&filterid=' . $filterid . '#trailer"><img class="trailer_img animate" src="templates/' . $setting['theme'] . '/img/trailer.png" title="' . $lang['i_show_trailer'] . '" alt=""></a>';
        }
        if ($list['trailer'] !== '' && $setting['show_trailer'] == 1 && $id <> 0) {
            $show_desc['trailer'] = 1;
            if (substr($list['trailer'], 0, 18) == 'http://www.youtube') {
                $output_desc['trailer'].= '
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
                    $output_desc['trailer'].= '
                        <embed src="' . $list['trailer'] . '" width="560" height="260" cache="false" autoplay="false" scale="tofit" />';
                } else {
                    $output_desc['trailer'].= '
                    <video id="player" class="video-js vjs-default-skin player" controls preload="none" width="560" height="260" data-setup="{}">
                        <source src="' . $list['trailer'] . '" type="' . $mimetype . '" />
                    </video>';
                }
            }
        }
    }
    
    // only tvshows
    if ($video == 'tvshows') {
    
        // premiered
        if ($list['premiered'] !== '') {
            $show_desc['premiered'] = 1;
            $output_desc['premiered'] = '<a href="index.php?video=tvshows&view=' . $view . '&sort=' . $sort . '&filter=premiered&filterid=' . array_search($list['premiered'], $panels_array['premiered']) . '">' . $list['premiered'] . '</a>';
        }
    
        // seasons
        $season_array = array();
        $seasons_sql = 'SELECT season FROM episodes WHERE tvshow = "' . $list['id'] . '" ORDER BY season';
        $seasons_result = mysql_q($seasons_sql);
        while ($seasons = mysql_fetch_array($seasons_result)) {
            if (!array_key_exists($seasons['season'], $season_array)) {
                $season_array[$seasons['season']] = '<a href="index.php?video=tvshows&view=' . $view . '&id=' . $list['id'] . '#season_' . $seasons['season'] . '">' . $lang['i_season'] . ' ' . $seasons['season'] . '</a>';
            }
        }
        if (count($season_array) <> 0) {
            $show_desc['seasons'] = 1;
            $output_desc['seasons'] = implode(' / ', $season_array);
        }
        
        // episodes
        $episodes_array = array();
        if ($id <> 0) {
            $show_desc['episodes'] = 1;
            $episodes_sql = 'SELECT id, title, episode, season, plot, firstaired, file, play_count, last_played FROM episodes WHERE tvshow = "' . $list['id'] . '" ORDER BY season, episode ASC';
            $episodes_result = mysql_q($episodes_sql);
            $i = -1;
            $output_desc['episodes'].= '<table class="table">';
            while ($episodes = mysql_fetch_assoc($episodes_result)) {
                if ($show_desc['xbmc_episode'] == 1) {
                    $e_xbmc = '
                    <div id="' . $episodes['id'] . '" class="xbmc_e">
                        <img class="play animate" src="templates/{SET.theme}/img/play.png" title="' . $lang['i_xbmc_play'] . '">
                        <a href="http://' . $setting['xbmc_login'] . ':' . $setting['xbmc_pass'] . '@' . $setting['xbmc_host'] . ':' . $setting['xbmc_port'] . '/vfs/' . urlencode($episodes['file']) . '"><img class="download animate" src="templates/{SET.theme}/img/download.png" title="' . $lang['i_xbmc_download'] . '"></a>
                        <a id="http://' . $setting['xbmc_login'] . ':' . $setting['xbmc_pass'] . '@' . $setting['xbmc_host'] . ':' . $setting['xbmc_port'] . '/vfs/' . urlencode($episodes['file']) . '" href="cache/list.m3u"><img class="list animate" src="templates/{SET.theme}/img/list.png" title="' . $lang['i_xbmc_m3u'] . '"></a>
                    </div>';
                } else {
                    $e_xbmc = '';
                }
                if ($episodes['plot'] !== '') {
                    $output_episodes_plot = '
                        <div class="episode_plot" id="plot_season_' . $episodes['season'] . '_episode_' . $episodes['episode'] . '">
                            <span class="orange bold">' . $lang['i_plot'] . ':</span> ' . $episodes['plot'] . '
                        </div>';
                } else {
                    $output_episodes_plot = '';
                }
                if ($episodes['season'] <> $i) {
                    $output_desc['episodes'].= '
                        <tr>
                            <td></td>
                            <td class="orange bold text_left" id="season_' . $episodes['season'] . '">' . $lang['i_season'] . ' ' . $episodes['season'] . '</td>
                            <td class="orange bold text_left aired">' . $lang['i_aired'] . '</td>
                            <td></td>
                            <td></td>
                        </tr>';
                }
                $output_desc['episodes'].= '
                    <tr class="episode" id="season_' . $episodes['season'] . '_episode_' . $episodes['episode'] . '">
                        <td class="left"></td>
                        <td class="right plot">' . $episodes['episode'] . '. ' . $episodes['title'] . $output_episodes_plot . '</td>
                        <td>' . $episodes['firstaired'] . '</td>
                        <td>' . ($episodes['play_count'] > 0 ? '<img class="watched_episode" src="templates/' . $setting['theme'] . '/img/watched.png" title="' . $lang['i_last_played'] . ': ' . $episodes['last_played'] . '" alt="">' : '') . '</td>
                        <td>' . $e_xbmc . '</td>
                    </tr>';
                $i = $episodes['season'];
            }
            $output_desc['episodes'].= '</table>';
        }
    }
    
    // panel movie
    $panel_list = new Teamplate($views[$include_view] . '.tpl', $setting, $lang);
    foreach ($output_desc as $key => $val) {
        $panel_list->tpl($key, $val);
    }
    foreach ($show_desc as $key => $val) {
        $panel_list->show($key, $val);
    }
    $output_panel_list.= $panel_list->init();
}
$output['panel_list'] = $output_panel_list;
$output['sort'] = $sort;

// meta data
$url = 'http://' . $_SERVER['SERVER_NAME'] . implode('/', array_slice(explode('/', $_SERVER['REQUEST_URI']), 0, -1)) . '/';
if ($id <> 0) {
    $meta_sql = 'SELECT title, originaltitle, plot FROM ' . $mysql_table . ' WHERE id = ' . $id;
    $meta_result = mysql_q($meta_sql);
    $meta = mysql_fetch_array($meta_result);
    $output['meta_img'] = (file_exists('cache/' . $mysql_table . '_' . $id . '.jpg') ? $url . 'cache/' . $mysql_table . '_' . $id . '.jpg' : 'templates/' . $setting['theme'] . '/img/d_poster.jpg');
    $output['meta_title'] = htmlspecialchars($meta['title']);
    $output['meta_originaltitle'] = htmlspecialchars($meta['originaltitle']);
    $output['meta_plot'] = htmlspecialchars($meta['plot']);
    $output['meta_url'] = $url . 'index.php?video=' . $video . '&id=' . $id;
} else {
    $output['meta_title'] = 'Movielib';
    $output['meta_originaltitle'] = $setting['site_name'];
    $output['meta_url'] = $url . 'index.php';
}

// create page
$index = new Teamplate('index.tpl', $setting, $lang);
foreach ($output as $key => $val) {
    $index->tpl($key, $val);
}
foreach ($show as $key => $val) {
    $index->show($key, $val);
}

print $index->init();

?>