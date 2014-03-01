<?PHP
/* #############
 * # FUNCTIONS #
 */#############

/* ########################
 * # Connect to databaase #
 */########################
function connect($mysql_ml) {
    $conn_ml = @mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
    if (!$conn_ml) {
        die(mysql_error());
    }
    $sel_ml = @mysql_select_db($mysql_ml[4]);
    if (!$sel_ml) {
        die(mysql_error());
    }

    // Sets utf8 connections
    mysql_query('SET CHARACTER SET utf8');
    mysql_query('SET NAMES utf8');
}

/* ##############################
 * # Get settings from database #
 */##############################
function get_settings($mysql_tables) {
    
    // if settings in session not exists get it from database
    if (!isset($_SESSION) or count($_SESSION) < 10) {
        $set_sql = 'SELECT * FROM ' . $mysql_tables[1];
        $set_result = mysql_query($set_sql);
        $get_set = mysql_fetch_assoc($set_result);
        foreach($get_set as $key => $val) {
            $_SESSION[$key] = $val;
        }
    }
    return $_SESSION;
}

/* ######################
 * # Create empty table #
 */######################
function create_table($mysql_tables, $tables, $lang) {
        
    // drop tables
    foreach ($mysql_tables as $table) {
        $drop_table_sql = 'DROP TABLE IF EXISTS `' . $table . '`';
        if (!@mysql_query($drop_table_sql)) {
            die(mysql_error());
        }
    }
    
    // table movie
    $create_movies_sql = 'CREATE TABLE `' . $mysql_tables[0] . '` (';
    foreach($tables[$mysql_tables[0]] as $key => $val) {
        $create_movies_sql.= '`' . $key . '` ' . $val . ', ';
    }
    $create_movies_sql.= 'PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_movies_sql)) {
        die($lang['inst_could_create'] . ': ' . $mysql_tables[0] . ' - ' . mysql_error() . '<br/>');
    }
    
    // table config
    $create_config_sql = 'CREATE TABLE `' . $mysql_tables[1] . '` (';
    foreach($tables[$mysql_tables[1]] as $key => $val) {
        $create_config_sql.= '`' . $key . '` ' . $val . ', ';
    }
    $create_config_sql = substr($create_config_sql, 0, -2);
    $create_config_sql.= ') DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_config_sql)) {
        die($lang['inst_could_create'] . ': ' . $mysql_tables[1] . ' - ' . mysql_error() . '<br/>');
    }
    if (@mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_tables[1])) == 0) {
        $insert_config_sql = 'INSERT INTO `' . $mysql_tables[1] . '` () VALUES ()';
        mysql_query ($insert_config_sql);
    }
    
    // table users
    $create_users_sql = 'CREATE TABLE `' . $mysql_tables[2] . '` (';
    foreach($tables[$mysql_tables[2]] as $key => $val) {
        $create_users_sql.= '`' . $key . '` ' . $val . ', ';
    }
    $create_users_sql.= 'PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_users_sql)) {
        die($lang['inst_could_create'] . ': ' . $mysql_tables[2] . ' - ' . mysql_error() . '<br/>');
    }
    if (@mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_tables[2])) == 0) {
        $insert_users_sql = 'INSERT INTO `' . $mysql_tables[2] . '` (`id`, `login`, `password`) VALUES (1, "admin", "21232f297a57a5a743894a0e4a801fc3")';
        mysql_query($insert_users_sql);
        $insert_users_sql = 'INSERT INTO `' . $mysql_tables[2] . '` (`id`, `login`, `password`) VALUES (2, "user", "ee11cbb19052e40b07aac0ca060c23ee")';
        mysql_query($insert_users_sql);
    }
}

/* ################################
 * # SYNC - show movie id from db #
 */################################
function show_id($mysql_tables) {
    $show_id_sql = 'SELECT id FROM ' . $mysql_tables[0];
    $show_id_result = mysql_query($show_id_sql);
    while ($id = mysql_fetch_array($show_id_result)) {
        echo $id[0] . ' ';
    }
}
 
/* ##########################
 * # SYNC - add Movie to DB #
 */##########################
function sync_add($tables, $mysql_tables) {
    
    $insert_sql = 'INSERT INTO `' . $mysql_tables[0] . '` (';
    foreach($tables[$mysql_tables[0]] as $key => $val) {
        $insert_sql.= '`' . $key . '`, ';
    }
    $insert_sql = substr($insert_sql, 0, -2);
    $insert_sql.= ') VALUES ';

    $insert_sql.= '(
        "' . $_POST['id'] . '",
        "' . add_slash($_POST['title']) . '",
        "' . add_slash($_POST['plot']) . '",
        "' . round($_POST['rating'], 1) . '",
        "' . $_POST['year'] . '",
        ' . ($_POST['trailer'] == '' ? 'NULL' : '"' . add_slash($_POST['trailer']) . '"') . ',
        '  . $_POST['runtime'] . ',
        "' . add_slash($_POST['genre']) . '",
        "' . add_slash($_POST['director']) . '",
        "' . add_slash($_POST['originaltitle']) . '",
        "' . add_slash($_POST['country']) . '",
        "' . add_slash($_POST['cast']) . '",
        "' . add_slash($_POST['set']) . '",
        "' . add_slash($_POST['v_codec']) . '",
        "' . add_slash($_POST['v_aspect']) . '",
        "' . add_slash($_POST['v_width']) . '",
        "' . add_slash($_POST['v_height']) . '",
        "' . add_slash($_POST['v_duration']) . '",
        "' . add_slash($_POST['a_codec']) . '",
        "' . add_slash($_POST['a_chan']) . '",
        "' . add_slash($_POST['playcount']) . '",
        ' . ($_POST['lastplayed'] == '' ? 'NULL' : '"' . add_slash($_POST['lastplayed']) . '"') . ',
        "' . add_slash($_POST['dateadded']) . '"
    )';
    $insert = mysql_query($insert_sql);

    if (!$insert) {
        echo 'ERROR: MySQL - ' . mysql_error();
    } else {
    
        // poster
        if (substr($_POST['poster'], 0, 4) == 'http') {
            gd_convert('cache/' . $_POST['id'] . '.jpg', $_POST['poster'], 140, 198);
        } else {
            $fp = fopen('cache/temp_' . $_POST['id'], 'w');
            fwrite($fp, $_POST['poster']);
            fclose($fp);
            gd_convert('cache/' . $_POST['id'] . '.jpg', 'cache/temp_' . $_POST['id'], 140, 198);
            unlink('cache/temp_' . $_POST['id']);
        }
        
        // fanart
        if (substr($_POST['fanart'], 0, 4) == 'http') {
            gd_convert('cache/' . $_POST['id'] . '_f.jpg', $_POST['fanart'], 1280, 720);
        } else {
            $fp = fopen('cache/temp_f' . $_POST['id'], 'w');
            fwrite($fp, $_POST['fanart']);
            fclose($fp);
            gd_convert('cache/' . $_POST['id'] . '_f.jpg', 'cache/temp_f' . $_POST['id'], 1280, 720);
            unlink('cache/temp_f' . $_POST['id']);
        }
    }
}

/* ####################
 * # SYNC - add actor #
 */####################
function add_actor($actor_name, $actor_thumb) {
    $actor_filename = substr(md5($actor_name), 0, 10);
    if (substr($actor_thumb, 0, 4) == 'http') {
        gd_convert('cache/actors/' . $actor_filename . '.jpg', $actor_thumb, 75, 100);
    } else {
        $fp = fopen('cache/actors/temp_a_' . $actor_filename, 'w');
        fwrite($fp, $actor_thumb);
        fclose($fp);
        gd_convert('cache/actors/' . $actor_filename . '.jpg', 'cache/actors/temp_a_' . $actor_filename, 75, 100);
        unlink('cache/actors/temp_a_' . $actor_filename);
    }
}

/* ###############################
 * # SYNC - remove Movie from DB #
 */###############################
function sync_remove($mysql_tables) {
    
    $id = $_POST['id'];
    $delete_movie_sql = 'DELETE FROM ' . $mysql_tables[0] . ' WHERE id = "' . $id . '"';
    if (file_exists('cache/' . $id . '.jpg')) {
        unlink('cache/' . $id . '.jpg');
    }
    if (file_exists('cache/' . $id . '_f.jpg')) {
        unlink('cache/' . $id . '_f.jpg');
    }
    
    $delete = mysql_query($delete_movie_sql);
    
    if (!$delete) {
        echo 'ERROR: MySQL - ' . mysql_error();
    }
}

/* ##################
 * # SYNC - Watched #
 */##################
function sync_watched($mysql_tables) {
    
    $update_sql = 'UPDATE `' . $mysql_tables[0] . '` SET 
        play_count = "' . add_slash($_POST['playcount']) . '",
        last_played = "' . add_slash($_POST['lastplayed']) . '",
        date_added = "' . add_slash($_POST['dateadded']) . '"
        WHERE id = "' . $_POST['id'] . '"';

    $update = mysql_query($update_sql);

    if (!$update) {
        echo 'ERROR: MySQL - ' . mysql_error();
    }
}

/* ####################
 * # SYNC - unWatched #
 */####################
function sync_unwatched($mysql_tables) {
    
    $update_sql = 'UPDATE `' . $mysql_tables[0] . '` SET 
        play_count = NULL,
        last_played = NULL,
        date_added = "' . add_slash($_POST['dateadded']) . '"
        WHERE id = "' . $_POST['id'] . '"';
        
    $update = mysql_query($update_sql);

    if (!$update) {
        echo 'ERROR: MySQL - ' . mysql_error();
    }
}

/* #####################
 * # SYNC - Lastplayed #
 */#####################
function sync_lastplayed($mysql_tables) {
    
    $update_sql = 'UPDATE `' . $mysql_tables[0] . '` SET 
        play_count = "' . add_slash($_POST['playcount']) . '",
        last_played = "' . add_slash($_POST['lastplayed']) . '"
        WHERE id = "' . $_POST['id'] . '"';

    $update = mysql_query($update_sql);

    if (!$update) {
        echo 'ERROR: MySQL - ' . mysql_error();
    }
}

/* ################
 * # Change Token #
 */################
function change_token($mysql_tables) {
    $array = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',0,1,2,3,4,5,6,7,8,9);
    $new_token = '';
    for ($i = 1; $i <= 6; $i++) {
        $new_token.= $array[array_rand($array)];
    }
    $update_sql = 'UPDATE `' . $mysql_tables[1] . '` SET token = "' . $new_token . '"';
    $update = mysql_query($update_sql);
    if ($update) {
        $_SESSION['token'] = $new_token;
    }
    return $new_token;
}

/* #################
 * # GD conversion #
 */#################
function gd_convert($cache_path, $img_link, $new_width, $new_height) {
    if (!file_exists($cache_path) and !empty($img_link)) {
        $img = @imagecreatefromjpeg($img_link);
        if ($img) {
            $width = imagesx($img);
            $height = imagesy($img);
            $img_temp = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($img_temp, $cache_path, 80);
        }
    }
}

/* ###############
 * # ADD SLASHES #
 */###############
function add_slash($string){
    if (get_magic_quotes_gpc()) {
        return $string;
    } else {
        return addslashes($string);
    }
}
?>