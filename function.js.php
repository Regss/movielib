<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

include('config.php');

// get setting for js
if ($option == 'settings') {
    include('function.php');
    connect($mysql_ml);
    $setting = get_settings();
    $set_js = array(
        'show_fanart'       => (isset($setting['show_fanart']) ? $setting['show_fanart'] : ''),
        'fadeout_fanart'    => (isset($setting['fadeout_fanart']) ? $setting['fadeout_fanart'] : ''),
        'panel_top_time'    => (isset($setting['panel_top_time']) ? $setting['panel_top_time'] : ''),
        'theme'             => (isset($setting['theme']) ? $setting['theme'] : '')
    );
    echo json_encode($set_js);
}

// save panel status
if ($option == 'panel') {
    echo $_GET['id'] . ' - ' . $_GET['opt'];
    $_SESSION[$_GET['id']] = $_GET['opt'];
    $setting[$_GET['id']] = $_GET['opt'];
}

// live search
if ($option == 'search') {
    include('function.php');
    connect($mysql_ml);
    $setting = get_settings();
    $output = '';
        
    function search($table, $search_sql, $search_array, $a_href, $setting) {
        $output = '';
        $search_res = mysql_query($search_sql);
        if (!$search_res) {
            echo mysql_error();
        }
        if (mysql_num_rows($search_res) > 0) {
            while($searched = mysql_fetch_assoc($search_res)) {
                // thumb
                if ($table == 'movies' or $table == 'tvshows') {
                    if (file_exists('cache/' . $table . '_' . $searched['id'] . '.jpg')) {
                        $searched['thumb'] = 'cache/' . $table . '_' . $searched['id'] . '.jpg';
                    } else {
                        $searched['thumb'] = 'templates/' . $setting['theme'] . '/img/d_poster.jpg';
                    }
                }
                if ($table == 'actor') {
                    if (file_exists('cache/actors/' . substr(md5($searched['actor']), 0, 10) . '.jpg')) {
                        $searched['thumb'] = 'cache/actors/' . substr(md5($searched['actor']), 0, 10) . '.jpg';
                    } else {
                        $searched['thumb'] = 'templates/' . $setting['theme'] . '/img/d_actor.jpg';
                    }
                    $searched['title'] = $searched['actor'];
                }
                // panels
                foreach ($search_array as $val) {
                    $sel_sql = 'SELECT ' . $val . '.' . $val . ' FROM ' . $val . ', ' . $table . '_' . $val . ' WHERE ' . $val . '.id = ' . $table . '_' . $val . '.' . $val . 'id AND ' . $table . '_' . $val . '.id = "' . $searched['id'] . '"';
                    $sel_res = mysql_query($sel_sql);
                    $out = array();
                    while ($s = mysql_fetch_row($sel_res)) {
                        $out[] = $s[0];
                    }
                    $searched[$val] = implode(' / ', $out);
                }
                $output.= '
                    <a href="' . $a_href . $searched['id'] . '">
                        <div class="live_search_box" title="' . $searched['title'] . '">
                            <img class="img_live_search" src="'  .  $searched['thumb']  .  '">
                            <div class="live_search_title">' . $searched['title'] . '</div>' . 
                            (isset($searched['originaltitle']) ? '<div class="live_search_orig_title">' . $searched['originaltitle'] . '</div>' : '') .
                            (isset($searched['year']) ? $searched['year']  . ' | ' : '') .
                            (isset($searched['rating']) ? $searched['rating']  . ' | ' : '') .
                            (isset($searched['runtime']) ? $searched['runtime'] . ' min. | ' : '') .
                            (isset($searched['genre']) ? $searched['genre'] . ' | ' : '') .
                            (isset($searched['country']) ? $searched['country'] . ' | ' : '') .
                            (isset($searched['director']) ? $searched['director'] : '') . '
                        </div>
                    </a>';
            }
        }
        return $output;
    }
    
    if ($_GET['video'] == 'search_movies') {
        $table = 'movies';
        $search_sql = 'SELECT id, title, rating, year, runtime, originaltitle, hide FROM ' . $table . ' WHERE (title LIKE "%' . $_GET['search'] . '%" OR originaltitle LIKE "%' . $_GET['search'] . '%") AND hide=0 LIMIT 0, ' . $setting['live_search_max_res'];
        $search_array = array('director', 'genre', 'country');
        $a_href = 'index.php?video=' . $table . '&id=';
        $output.= search($table, $search_sql, $search_array, $a_href, $setting);
    }
    if ($_GET['video'] == 'search_tvshows') {
        $table = 'tvshows';
        $search_sql = 'SELECT id, title, rating, originaltitle, hide FROM ' . $table . ' WHERE (title LIKE "%' . $_GET['search'] . '%" OR originaltitle LIKE "%' . $_GET['search'] . '%") AND hide=0 LIMIT 0, ' . $setting['live_search_max_res'];
        $search_array = array('genre');
        $a_href = 'index.php?video=' . $table . '&id=';
        $output.= search($table, $search_sql, $search_array, $a_href, $setting);
    }
    
    $table = 'actor';
    $search_sql = 'SELECT id, actor FROM ' . $table . ' WHERE actor LIKE "%' . $_GET['search'] . '%" LIMIT 0, ' . $setting['live_search_max_res'];
    $search_array = array();
    $a_href = 'index.php?video=' . ($_GET['video'] == 'search_movies' ? 'movies' : 'tvshows') . '&filter=actor&filterid=';
    $output.= search($table, $search_sql, $search_array, $a_href, $setting);
    
    echo $output;
}

// remote control
if ($option  == 'remote') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    include('function.php');
    $setting = get_settings();
    
    $time_assoc['http'] = array('timeout' => 3);
    $timeout = stream_context_create($time_assoc);
            
    switch ($_GET['f']) {
        case 'list':
            file_put_contents('cache/list.m3u', $_GET['file']);
            break;
        case 'play':
            if ($_GET['video'] == 'tvshows') {
                $video = 'episodeid';
            } else {
                $video = 'movieid';
            }
            $json = urlencode('{"jsonrpc": "2.0", "params": {"item": {"' . $video . '": ' . $_GET['id'] . '}}, "method": "Player.Open", "id": 1}');
            break;
        case 'stepforward': // stepforward
        case '190':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "stepforward"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'stepback': // stepback
        case '188':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "stepback"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'bigstepforward': // bigstepforward
        case '221':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "bigstepforward"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'bigstepback': // bigstepback
        case '219':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "bigstepback"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'v_up': // volume up
        case '61':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "volumeup"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'v_down': // volume down
        case '173':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "volumedown"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'mute': // volume mute
        case '48':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "mute"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'stop': // stop
        case '88':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"playerid": 1}, "method": "Player.Stop", "id": 1}');
            break;
        case 'pause': // pause
        case '32':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"playerid": 1}, "method": "Player.PlayPause", "id": 1}');
            break;
        case 'right': // right
        case '39':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.Right", "id": 1}');
            break;
        case 'left': // left
        case '37':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.Left", "id": 1}');
            break;
        case 'up': // up
        case '38':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.Up", "id": 1}');
            break;
        case 'down': // down
        case '40':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.Down", "id": 1}');
            break;
        case 'watch': // watched
        case '87':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"action": "togglewatched"}, "method": "Input.ExecuteAction", "id": 1}');
            break;
        case 'info': // info
        case '73':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.Info", "id": 1}');
            break;
        case 'select': // select
        case '13':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.Select", "id": 1}');
            break;
        case 'back': // back
        case '8':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.Back", "id": 1}');
            break;
        case 'context': // context menu
        case '67':
            $json = urlencode('{"jsonrpc": "2.0", "method": "Input.ContextMenu", "id": 1}');
            break;
        case 'power': // shutdown menu
        case '83':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"window":"shutdownmenu"}, "method": "GUI.ActivateWindow", "id": 1}');
            break;
        case 'sync': // sync
        case '82':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"addonid":"script.movielib"}, "method": "Addons.ExecuteAddon", "id": 1}');
            break;
            
        case 'playing':
            // get player status
            $json_player = urlencode('{"jsonrpc": "2.0", "params": {"playerid": 1}, "method": "Player.GetItem", "id": 1}');
            $get_player = @file_get_contents('http://' . $setting['xbmc_login'] . ':' . $setting['xbmc_pass'] . '@' . $setting['xbmc_host'] . ':' . $setting['xbmc_port'] . '/jsonrpc?request=' . $json_player, false, $timeout);
            $player = json_decode($get_player, true);
            if (isset($player['result'])) {
                $json_player_status = urlencode('{"jsonrpc": "2.0", "params": {"playerid": 1, "properties": ["percentage", "time", "totaltime"]}, "method": "Player.GetProperties", "id": 1}');
                $get_player_status = @file_get_contents('http://' . $setting['xbmc_login'] . ':' . $setting['xbmc_pass'] . '@' . $setting['xbmc_host'] . ':' . $setting['xbmc_port'] . '/jsonrpc?request=' . $json_player_status, false, $timeout);
                $player_status = json_decode($get_player_status, true);
                $item = array_merge($player['result']['item'], $player_status['result']);
                connect($mysql_ml);
                include('lang/' . $setting['language'] . '/lang.php');
                if ($item['type'] == 'episode') {
                    // get episode
                    $episode_sql = 'SELECT tvshow, season, episode, title, plot FROM episodes WHERE id = "' . $item['id'] . '"';
                    $episode_result = mysql_query($episode_sql);
                    $episode = mysql_fetch_assoc($episode_result);
                    // get tvshow
                    $tvshow_sql = 'SELECT title, rating FROM tvshows WHERE id = "' . $episode['tvshow'] . '"';
                    $tvshow_result = mysql_query($tvshow_sql);
                    $tvshow = mysql_fetch_assoc($tvshow_result);
                    // get panels
                    $search_array = array('genre');
                    foreach ($search_array as $val) {
                        $sel_sql = 'SELECT ' . $val . '.' . $val . ' FROM ' . $val . ', tvshows_' . $val . ' WHERE ' . $val . '.id = tvshows_' . $val . '.' . $val . 'id AND tvshows_' . $val . '.id = "' . $item['id'] . '"';
                        $sel_res = mysql_query($sel_sql);
                        $out = array();
                        while ($s = mysql_fetch_row($sel_res)) {
                            $out[] = $s[0];
                        }
                        $tvshow[$val] = implode(' / ', $out);
                    }
                    $item['type'] = 'tvshows';
                    $item['id'] = $episode['tvshow'];
                    $item['details'] = '
                        <div id="np_d_title">' . $tvshow['title'] . '</div>
                        <div id="np_d_otitle">' . zero($episode['season']) . 'x' . zero($episode['episode']) . ' ' . $episode['title'] . '</div>
                        <div id="bar"><div id="prog"></div></div>
                        <div id="np_d_time">' . zero($item['time']['hours']) . ':' . zero($item['time']['minutes']) . ':' . zero($item['time']['seconds']) . ' / ' . zero($item['totaltime']['hours']) . ':' . zero($item['totaltime']['minutes']) . ':' . zero($item['totaltime']['seconds']) . '</div>
                        ' . (file_exists('cache/tvshows_' . $item['id'] . '.jpg') ? '<img src="cache/tvshows_' . $item['id'] . '.jpg">' : '') . '
                        <div id="np_d_det">
                            <div><span>' . $lang['i_season'] . ':</span> ' . $episode['season'] . '</div>
                            <div><span>' . $lang['i_episode'] . ':</span> ' . $episode['episode'] . '</div>
                            <div><span>' . $lang['i_rating'] . ':</span> ' . $tvshow['rating'] . '</div>
                            <div><span>' . $lang['i_genre'] . ':</span> ' . $tvshow['genre'] . '</div>
                            <div><span>' . $lang['i_plot'] . ':</span> ' . $episode['plot'] . '</div>
                        </div>';
                } else if($item['type'] == 'movie') {
                    // get movie
                    $movie_sql = 'SELECT `title`, `originaltitle`, `rating`, `runtime`, `plot`, `set`, `year` FROM movies WHERE id = "' . $item['id'] . '"';
                    $movie_result = mysql_query($movie_sql);
                    $movie = mysql_fetch_assoc($movie_result);
                    // get panels
                    $search_array = array('genre', 'country', 'director', 'studio');
                    foreach ($search_array as $val) {
                        $sel_sql = 'SELECT ' . $val . '.' . $val . ' FROM ' . $val . ', movies_' . $val . ' WHERE ' . $val . '.id = movies_' . $val . '.' . $val . 'id AND movies_' . $val . '.id = "' . $item['id'] . '"';
                        $sel_res = mysql_query($sel_sql);
                        $out = array();
                        while ($s = mysql_fetch_row($sel_res)) {
                            $out[] = $s[0];
                        }
                        $movie[$val] = implode(' / ', $out);
                    }
                    $item['type'] = 'movies';
                    $item['details'] = '
                        <div id="np_d_title">' . $movie['title'] . '</div>
                        <div id="np_d_otitle">' . $movie['originaltitle'] . '</div>
                        <div id="bar"><div id="prog"></div></div>
                        <div id="np_d_time">' . zero($item['time']['hours']) . ':' . zero($item['time']['minutes']) . ':' . zero($item['time']['seconds']) . ' / ' . zero($item['totaltime']['hours']) . ':' . zero($item['totaltime']['minutes']) . ':' . zero($item['totaltime']['seconds']) . '</div>
                        ' . (file_exists('cache/movies_' . $item['id'] . '.jpg') ? '<img src="cache/movies_' . $item['id'] . '.jpg">' : '') . '
                        <div id="np_d_det">'
                             . ($movie['year'] == '' ? '' : '<div><span>' . $lang['i_year'] . ':</span> ' . $movie['year'] . '</div>')
                             . ($movie['rating'] == '' ? '' : '<div><span>' . $lang['i_rating'] . ':</span> ' . $movie['rating'] . '</div>')
                             . ($movie['runtime'] == '' ? '' : '<div><span>' . $lang['i_runtime'] . ':</span> ' . $movie['runtime'] . ' ' . $lang['i_minute'] . '</div>')
                             . ($movie['genre'] == '' ? '' : '<div><span>' . $lang['i_genre'] . ':</span> ' . $movie['genre'] . '</div>')
                             . ($movie['country'] == '' ? '' : '<div><span>' . $lang['i_country'] . ':</span> ' . $movie['country'] . '</div>')
                             . ($movie['director'] == '' ? '' : '<div><span>' . $lang['i_director'] . ':</span> ' . $movie['director'] . '</div>')
                             . ($movie['set'] == '' ? '' : '<div><span>' . $lang['i_set'] . ':</span> ' . $movie['set'] . '</div>')
                             . ($movie['studio'] == '' ? '' : '<div><span>' . $lang['i_studio'] . ':</span> ' . $movie['studio'] . '</div>')
                             . ($movie['plot'] == '' ? '' : '<div><span>' . $lang['i_plot'] . ':</span> ' . $movie['plot'] . '</div>') . '
                        </div>';
                } else {
                    $item['details'] = '
                        <div id="np_d_title">' . $item['label'] . '</div>
                        <div id="bar"><div id="prog"></div></div>
                        <div id="np_d_time">' . zero($item['time']['hours']) . ':' . zero($item['time']['minutes']) . ':' . zero($item['time']['seconds']) . ' / ' . zero($item['totaltime']['hours']) . ':' . zero($item['totaltime']['minutes']) . ':' . zero($item['totaltime']['seconds']) . '</div>
                        ';
                
                }
                echo json_encode($item);
            } else {
                echo '{"stop": "stop"}';
            }
            break;
        case 'check':
            $json = urlencode('{"jsonrpc": "2.0", "params": {"labels": ["System.BuildVersion"]}, "method": "XBMC.GetInfoLabels", "id": 1}');
            break;
        case 'xbmc_test':
            $json_test = urlencode('{"jsonrpc": "2.0", "params": {"labels": ["System.BuildVersion"]}, "method": "XBMC.GetInfoLabels", "id": 1}');
            $get_test = @file_get_contents('http://' . $_GET['xbmc_login'] . ':' . $_GET['xbmc_pass'] . '@' . $_GET['xbmc_host'] . ':' . $_GET['xbmc_port'] . '/jsonrpc?request=' . $json_test, false, $timeout);
            if (!$get_test) {
                echo '{"error": "error"}';
            } else {
                echo $get_test;
            }
        break;
    }
    if (isset($json)) {
        $get = @file_get_contents('http://' . $setting['xbmc_login'] . ':' . $setting['xbmc_pass'] . '@' . $setting['xbmc_host'] . ':' . $setting['xbmc_port'] . '/jsonrpc?request=' . $json, false, $timeout);
        if (!$get) {
            echo '{"error": "error"}';
        } else {
            echo $get;
        }
    }
}

// delete movie or tvshow
if ($option  == 'deletemovie' or $option  == 'deletetvshow') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    include('function.php');
    connect($mysql_ml);
    if ($option  == 'deletemovie') {
        $table = 'movies';
    } else {
        $table = 'tvshows';
        $delete_sql = 'DELETE FROM episodes WHERE tvshow in (' . $_GET['id'] . ')';
        $delete = mysql_q($delete_sql);
    }
    sync_delete(array($_GET['id']), $table);
}

// hide movie or tvshow
if ($option  == 'hidemovie' or $option  == 'hidetvshow' or $option  == 'visiblemovie' or $option  == 'visibletvshow') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    include('function.php');
    $id = $_GET['id'];
    if($option  == 'hidemovie' or $option  == 'hidetvshow') {
        $hide = 1;
    } else {
        $hide = 0;
    }
    if($option  == 'hidemovie' or $option  == 'visiblemovie') {
        $table = 'movies';
    } else {
        $table = 'tvshows';
    }
    connect($mysql_ml);
    $hide_sql = 'UPDATE `' . $table . '` SET hide = ' . $hide . ' WHERE id = "' . $id . '"';
    mysql_query($hide_sql);
}

// banner
if ($option  == 'banner') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    include('function.php');
    connect($mysql_ml);
    $setting = get_settings();
    include('lang/' . $setting['language'] . '/lang.php');
    $b = create_banner($lang, 'banner_v.jpg', $_GET['banner']);
}

// fanart exist
if ($option == 'fexist') {
    if (file_exists('cache/' . $_GET['id'] . '_f.jpg')) {
        echo '{"fexist": "exist"}';
    } else {
        echo '{"fexist": "notexist"}';
    }
}

?>