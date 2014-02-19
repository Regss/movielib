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
function get_settings($mysql_ml, $mysql_tables) {
    
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
function create_table($mysql_tables, $lang) {
        
    // drop tables
    foreach ($mysql_tables as $table) {
        $drop_table_sql = 'DROP TABLE IF EXISTS `' . $table . '`';
        if (!@mysql_query($drop_table_sql)) {
            die(mysql_error());
        }
    }
    
    // table movie
    $create_movies_sql = 'CREATE TABLE `' . $mysql_tables[0] . '` (
                `id` int(11) NOT NULL,
                `title` varchar(100),
                `plot` text,
                `rating` text,
                `year` text,
                `trailer` text,
                `runtime` text,
                `genre` text,
                `director` text,
                `originaltitle` text,
                `country` text,
                `cast` text,
                `v_codec` text,
                `v_aspect` float DEFAULT NULL,
                `v_width` int(11) DEFAULT NULL,
                `v_height` int(11) DEFAULT NULL,
                `v_duration` int(11) DEFAULT NULL,
                `a_codec` text,
                `a_chan` int(11) DEFAULT NULL,
                `play_count` int(11) DEFAULT NULL,
                `last_played` text DEFAULT NULL,
                `date_added` text DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_movies_sql)) {
        die($lang['inst_could_create'] . ': ' . $mysql_tables[0] . ' - ' . mysql_error() . '<br/>');
    }
    
    // table config
    $create_config_sql = 'CREATE TABLE `' . $mysql_tables[1] . '` (
                `site_name` varchar(30) DEFAULT "MovieLib",
                `language` varchar(2) DEFAULT "' . $_SESSION['install_lang'] . '",
                `theme` varchar(15) DEFAULT "default",
                `per_page` int(5) DEFAULT 50,
                `panel_top_limit` int(5) DEFAULT 10,
                `panel_top_time` int(5) DEFAULT 5,
                `panel_top` int(1) DEFAULT 1,
                `watched_status` int(1) DEFAULT 1,
                `live_search` int(1) DEFAULT 1,
                `live_search_max_res int(4) DEFAULT 10,
                `panel_overall` int(1) DEFAULT 1,
                `panel_genre` int(1) DEFAULT 1,
                `panel_year` int(1) DEFAULT 1,
                `panel_country` int(1) DEFAULT 1,
                `panel_v_codec` int(1) DEFAULT 1,
                `panel_a_codec` int(1) DEFAULT 1,
                `panel_a_chan` int(1) DEFAULT 1,
                `show_fanart` int(1) DEFAULT 1,
                `show_trailer` int(1) DEFAULT 1,
                `protect_site` int(1) DEFAULT 0,
                `token` varchar(6) DEFAULT ""
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_config_sql)) {
        die($lang['inst_could_create'] . ': ' . $mysql_tables[1] . ' - ' . mysql_error() . '<br/>');
    }
    if (@mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_tables[1])) == 0) {
        $insert_config_sql = 'INSERT INTO `' . $mysql_tables[1] . '` () VALUES ()';
        mysql_query ($insert_config_sql);
    }
    
    // table users
    $create_users_sql = 'CREATE TABLE `' . $mysql_tables[2] . '` (
                `id` int(2) NOT NULL AUTO_INCREMENT,
                `login` varchar(5) DEFAULT NULL,
                `password` varchar(32) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_users_sql)) {
        die($lang['inst_could_create'] . ': ' . $mysql_tables[2] . ' - ' . mysql_error() . '<br/>');
    }
    if (@mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_tables[2])) == 0) {
        $insert_users_sql = 'INSERT INTO `' . $mysql_tables[2] . '` (`id`, `login`, `password`) VALUES ("", "admin", "21232f297a57a5a743894a0e4a801fc3")';
        mysql_query($insert_users_sql);
        $insert_users_sql = 'INSERT INTO `' . $mysql_tables[2] . '` (`id`, `login`, `password`) VALUES ("", "user", "ee11cbb19052e40b07aac0ca060c23ee")';
        mysql_query($insert_users_sql);
    }
}

/* ################################
 * # SYNC - show movie id from db #
 */################################
function show_id($mysql_ml, $mysql_tables) {
    $show_id_sql = 'SELECT id FROM ' . $mysql_tables[0];
    $show_id_result = mysql_query($show_id_sql);
    while ($id = mysql_fetch_array($show_id_result)) {
        echo $id[0] . ' ';
    }
}
 
/* ##########################
 * # SYNC - add Movie to DB #
 */##########################
function sync_add($mysql_ml, $mysql_tables) {
    
    $insert_sql = 'INSERT INTO `' . $mysql_tables[0] . '` (
        `id`,
        `title`,
        `plot`,
        `rating`,
        `year`,
        `trailer`,
        `runtime`,
        `genre`,
        `director`,
        `originaltitle`,
        `country`,
        `cast`,
        `v_codec`,
        `v_aspect`,
        `v_width`,
        `v_height`,
        `v_duration`,
        `a_codec`,
        `a_chan`,
        `play_count`,
        `last_played`,
        `date_added`
      ) VALUES ';

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
function sync_remove($mysql_ml, $mysql_tables) {
    
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
function sync_watched($mysql_ml, $mysql_tables) {
    
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
function sync_unwatched($mysql_ml, $mysql_tables) {
    
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
function sync_lastplayed($mysql_ml, $mysql_tables) {
    
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
    unset($_SESSION['site_name']);
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