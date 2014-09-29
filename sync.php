<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

require('config.php');
require('function.php');

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_tables);
require('lang/' . $set['language'] . '/lang.php');

/* #################
 * # SYNC DATABASE #
 */#################

 // check version
if ($option == 'checkversion') {
    echo  $version;
}

// check settings
if ($option == 'checksettings') {
    $settings = array();
    $settings['GD'] = (extension_loaded('gd') && function_exists('gd_info') ? 'On' : 'Off');
    $settings['CURL'] = (function_exists('curl_version') ? 'On' : 'Off');
    $settings['ALLOW_URL_FOPEN'] = (ini_get('allow_url_fopen') == 1 ? 'On' : 'Off');
    $settings['MAX_EXECUTION_TIME'] = ini_get('max_execution_time');
    $settings['UPLOAD_MAX_FILESIZE'] = ini_get('upload_max_filesize');
    $settings['POST_MAX_SIZE'] = ini_get('post_max_size');
    echo json_encode($settings);
}

// check token
if ($option == 'checktoken') {
    if ($token == $set['token']) {
        echo 'true';
    } else {
        echo 'false';
    }
}

if ($token == $set['token']) {

    switch ($option) {
        
        // check allow_url_fopen
        case 'checkallowurlfopen':
            echo (ini_get('allow_url_fopen') == 1 ? 'true' : 'false');
            break;
        
        // generate banner
        case 'generatebanner':
            create_banner($lang, 'banner.jpg', $set['banner'], $mysql_tables);
            break;
        
        /* #########
         * # MOVIE #
         */#########
        // show movie id from database
        case 'showmovieid':
            show_id($mysql_tables[0]);
            break;
        
        // show hash
        case 'showhash':
            $sql = 'SELECT id, hash FROM ' . $mysql_tables[0];
            $sql_res = mysql_query($sql);
            $hash_a = array();
            while ($hash = mysql_fetch_array($sql_res)) {
                $hash_a[] = '"' . $hash['id'] . '": "' . $hash['hash'] . '"';
            }
            echo '{' . implode(', ', $hash_a) . '}';
            break;
        
        // sync movie
        case 'addmovie':
            sync_add($tables, $mysql_tables[0]);
            break;
        case 'removemovie':
            sync_remove($mysql_tables[0]);
            break;
        
        // show movie watched id from database
        case 'showwatchedmovieid':
            $sql = 'SELECT id FROM ' . $mysql_tables[0] . ' WHERE play_count > 0';
            $sql_res = mysql_query($sql);
            while ($id = mysql_fetch_array($sql_res)) {
                echo $id[0] . ' ';
            }
            break;
        
        // sync watched
        case 'watchedmovie':
            sync_watched($mysql_tables[0]);
            break;
        
        // sync unwatched
        case 'unwatchedmovie':
            sync_unwatched($mysql_tables[0]);
            break;
        
        // show lastplayed movie id
        case 'showlastplayedmovie':
            $sql = 'SELECT last_played FROM ' . $mysql_tables[0] . ' ORDER BY last_played DESC LIMIT 0 , 1';
            $sql_res = mysql_query($sql);
            while ($date = mysql_fetch_array($sql_res)) {
                echo $date[0] . ' ';
            }
            break;
        
        // sync lastplayed
        case 'lastplayedmovie':
            sync_lastplayed($mysql_tables[0]);
            break;
            
        /* ##########
         * # TVSHOW #
         */##########
        // show tvshow id from database
        case 'showtvshowid':
            show_id($mysql_tables[1]);
            break;
        
        // sync tvshow
        case 'addtvshow':
            sync_add($tables, $mysql_tables[1]);
            break;
        case 'removetvshow':
            sync_remove($mysql_tables[1]);
            break;
        
        // show tvshow watched id from database
        case 'showwatchedtvshowid':
            $sql = 'SELECT id FROM ' . $mysql_tables[1] . ' WHERE play_count > 0';
            $sql_res = mysql_query($sql);
            while ($id = mysql_fetch_array($sql_res)) {
                echo $id[0] . ' ';
            }
            break;
        
        // sync watched
        case 'watchedtvshow':
            sync_watched($mysql_tables[1]);
            break;
        
        // sync unwatched
        case 'unwatchedtvshow':
            sync_unwatched($mysql_tables[1]);
            break;
        
        // show lastplayed tvshow id
        case 'showlastplayedtvshow':
            $sql = 'SELECT last_played FROM ' . $mysql_tables[1] . ' ORDER BY last_played DESC LIMIT 0 , 1';
            $sql_res = mysql_query($sql);
            while ($date = mysql_fetch_array($sql_res)) {
                echo $date[0] . ' ';
            }
            break;
        
        // sync lastplayed
        case 'lastplayedtvshow':
            sync_lastplayed($mysql_tables[1]);
            break;
            
        /* ###########
         * # EPISODE #
         */###########
        // show episode id from database
        case 'showepisodeid':
            show_id($mysql_tables[2]);
            break;
            
        // sync episode
        case 'addepisode':
            sync_add($tables, $mysql_tables[2]);
            break;
        case 'removeepisode':
            sync_remove($mysql_tables[2]);
            break;
        
        // show episode watched id from database
        case 'showwatchedepisodeid':
            $sql = 'SELECT id FROM ' . $mysql_tables[2] . ' WHERE play_count > 0';
            $sql_res = mysql_query($sql);
            while ($id = mysql_fetch_array($sql_res)) {
                echo $id[0] . ' ';
            }
            break;
        
        // sync watched
        case 'watchedepisode':
            sync_watched($mysql_tables[2]);
            break;
        
        // sync unwatched
        case 'unwatchedepisode':
            sync_unwatched($mysql_tables[2]);
            break;
        
        // show lastplayed episode id
        case 'showlastplayedepisode':
            $sql = 'SELECT last_played FROM ' . $mysql_tables[2] . ' ORDER BY last_played DESC LIMIT 0 , 1';
            $sql_res = mysql_query($sql);
            while ($date = mysql_fetch_array($sql_res)) {
                echo $date[0] . ' ';
            }
            break;
        
        // sync lastplayed
        case 'lastplayedepisode':
            sync_lastplayed($mysql_tables[2]);
            break;
        
        /* #########
         * # ACTOR #
         */#########
        // add actor
        case 'addactor':
            add_actor($_POST['name'], $_POST['actor']);
            break;
        
    }
}
?>