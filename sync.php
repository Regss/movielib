<?PHP
require_once 'config.php';
require_once 'function.php';

// connect to database
connect($mysql_ml);

// get settings from db
$set = get_settings($mysql_ml, $mysql_tables);
require_once 'lang/lang_' . $set['language'] . '.php';

/* #################
 * # SYNC DATABASE #
 */#################

// check token
if ($option == 'checktoken') {
    if ($token == $set['token']) {
        echo 'true';
    }
}

if ($token == $set['token']) {

    switch ($option) {
        
        // show movie id from database
        case 'showid':
            show_id($mysql_ml, $mysql_tables);
            break;
        
        // sync movie
        case 'addmovie':
            sync_add($mysql_ml, $mysql_tables);
            break;
        case 'removemovie':
            sync_remove($mysql_ml, $mysql_tables);
            break;
        
        // show movie watched id from database
        case 'showwatchedid':
            $sql = 'SELECT id FROM movies WHERE play_count > 0';
            $sql_res = mysql_query($sql);
            while ($id = mysql_fetch_array($sql_res)) {
                echo $id[0] . ' ';
            }
            break;
        
        // sync watched
        case 'watchedmovie':
            sync_watched($mysql_ml, $mysql_tables);
            break;
        
        // sync unwatched
        case 'unwatchedmovie':
            sync_unwatched($mysql_ml, $mysql_tables);
            break;
        
        // show lastplayed movie id
        case 'showlastplayed':
            $sql = 'SELECT last_played FROM movies ORDER BY last_played DESC LIMIT 0 , 1';
            $sql_res = mysql_query($sql);
            while ($date = mysql_fetch_array($sql_res)) {
                echo $date[0] . ' ';
            }
            break;
        
        // sync lastplayed
        case 'lastplayed':
            sync_lastplayed($mysql_ml, $mysql_tables);
            break;
    }
}
?>