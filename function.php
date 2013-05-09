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
    $sel_ml = mysql_select_db($mysql_ml[4]);
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
    if (!isset($_SESSION['mode'])) {
        $set_sql = 'SELECT * FROM ' . $mysql_tables[1];
        $set_result = mysql_query($set_sql);
        while ($set = mysql_fetch_array($set_result)) {
            $_SESSION['mode']               = $set['mode']; // 1 - Synchronize witch XBMC database, 2 - Synchronize witch videodb.xml file
            $_SESSION['site_name']          = $set['site_name']; // Site title
            $_SESSION['language']           = $set['language']; // The file that contains the language, file must be in the lang/ folder
            $_SESSION['per_page']           = $set['per_page']; // Movies per page
            $_SESSION['recently_limit']     = $set['recently_limit']; // Movies in recently added panel
            $_SESSION['random_limit']       = $set['random_limit']; // Movies in random panel
            $_SESSION['last_played_limit']  = $set['last_played_limit']; // Movies in last played panel
            $_SESSION['top_rated_limit']    = $set['top_rated_limit']; // Movies in top rated panel
            $_SESSION['sync_time']          = $set['sync_time']; // Time in minutes after which the script will attempt to synchronize databases
            $_SESSION['panel_top_time']     = $set['panel_top_time']; // Time in second to change displayed item
            $_SESSION['panel_top']          = $set['panel_top']; // Show top panel
            $_SESSION['watched_status']     = $set['watched_status']; // Show watched status
            $_SESSION['overall_panel']      = $set['overall_panel']; // Show overall panel
            $_SESSION['protect_site']       = $set['protect_site']; // Protect access to site
            $_SESSION['mysql_host_xbmc']    = $set['mysql_host_xbmc']; // Database host
            $_SESSION['mysql_port_xbmc']    = $set['mysql_port_xbmc']; // Database port, default is 3306
            $_SESSION['mysql_login_xbmc']   = $set['mysql_login_xbmc']; // Database login
            $_SESSION['mysql_pass_xbmc']    = $set['mysql_pass_xbmc']; // Database password
            $_SESSION['mysql_database_xbmc']= $set['mysql_database_xbmc']; // Database name
        }
    }
    $output_set = array(
    'mode'                  => $_SESSION['mode'],
    'site_name'             => $_SESSION['site_name'],
    'language'              => $_SESSION['language'],
    'per_page'              => $_SESSION['per_page'],
    'recently_limit'        => $_SESSION['recently_limit'],
    'random_limit'          => $_SESSION['random_limit'],
    'last_played_limit'     => $_SESSION['last_played_limit'],
    'top_rated_limit'       => $_SESSION['top_rated_limit'],
    'sync_time'             => $_SESSION['sync_time'],
    'panel_top_time'        => $_SESSION['panel_top_time'],
    'panel_top'             => $_SESSION['panel_top'],
    'watched_status'        => $_SESSION['watched_status'],
    'overall_panel'         => $_SESSION['overall_panel'],
    'protect_site'          => $_SESSION['protect_site'],
    'mysql_host_xbmc'       => $_SESSION['mysql_host_xbmc'],
    'mysql_port_xbmc'       => $_SESSION['mysql_port_xbmc'],
    'mysql_login_xbmc'      => $_SESSION['mysql_login_xbmc'],
    'mysql_pass_xbmc'       => $_SESSION['mysql_pass_xbmc'],
    'mysql_database_xbmc'   => $_SESSION['mysql_database_xbmc']
    );
return $output_set;
}

/* ######################
 * # Create empty table #
 */######################
function create_table($mysql_table) {
    
    $output_create_table = '';
    
    // table movie
    $create_movies_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_table[0] . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `file` int(11) NOT NULL,
                `title` varchar(100),
                `plot` text,
                `rating` text,
                `year` text,
                `poster` text,
                `runtime` text,
                `genre` text,
                `director` text,
                `originaltitle` text,
                `country` text,
                `v_codec` text,
                `v_aspect` float DEFAULT NULL,
                `v_width` int(11) DEFAULT NULL,
                `v_height` int(11) DEFAULT NULL,
                `v_duration` int(11) DEFAULT NULL,
                `a_codec` text,
                `a_channels` int(11) DEFAULT NULL,
                `play_count` int(11) DEFAULT NULL,
                `last_played` text DEFAULT NULL,
                `date_added` text DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_movies_sql)) {
        $output_create_table.= 'Could not create table: ' . $mysql_table[0] . ' - ' . mysql_error() . '<br/>';
    }
    
    // table config
    $create_config_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_table[1] . '` (
                `mode` int(1) DEFAULT 0,
                `site_name` varchar(30) DEFAULT "MovieLib",
                `language` varchar(15) DEFAULT "lang_pl.php",
                `per_page` int(5) DEFAULT 50,
                `recently_limit` int(5) DEFAULT 10,
                `random_limit` int(5) DEFAULT 10,
                `last_played_limit` int(5) DEFAULT 10,
                `top_rated_limit` int(5) DEFAULT 10,
                `sync_time` int(5) DEFAULT 10,
                `panel_top_time` int(5) DEFAULT 50,
                `panel_top` int(1) DEFAULT 1,
                `watched_status` int(1) DEFAULT 1,
                `overall_panel` int(1) DEFAULT 1,
                `protect_site` int(1) DEFAULT 0,
                `mysql_host_xbmc` text DEFAULT NULL,
                `mysql_port_xbmc` text DEFAULT NULL,
                `mysql_login_xbmc` text DEFAULT NULL,
                `mysql_pass_xbmc` text DEFAULT NULL,
                `mysql_database_xbmc` text DEFAULT NULL
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_config_sql)) {
        $output_create_table.= 'Could not create table: ' . $mysql_table[1] . ' - ' . mysql_error() . '<br/>';
    }
    if (mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_table[1])) == 0) {
        $insert_config_sql = 'INSERT INTO `' . $mysql_table[1] . '` () VALUES ()';
        mysql_query ($insert_config_sql);
    }
    
    // table users
    $create_users_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_table[2] . '` (
                `id` int(2) NOT NULL AUTO_INCREMENT,
                `login` varchar(5) DEFAULT NULL,
                `password` varchar(32) DEFAULT NULL,
                `s_id` varchar(30) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    if (!@mysql_query($create_users_sql)) {
        $output_create_table.= 'Could not create table: ' . $mysql_table[2] . ' - ' . mysql_error() . '<br/>';
    }
    if (mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_table[2])) == 0) {
        $insert_users_sql = 'INSERT INTO `' . $mysql_table[2] . '` (`id`, `login`, `password`, `s_id`) VALUES ("", "admin", "21232f297a57a5a743894a0e4a801fc3", "")';
        mysql_query($insert_users_sql);
        $insert_users_sql = 'INSERT INTO `' . $mysql_table[2] . '` (`id`, `login`, `password`, `s_id`) VALUES ("", "user", "ee11cbb19052e40b07aac0ca060c23ee", "")';
        mysql_query($insert_users_sql);
    }
    return $output_create_table;
}

/* ###########################
 * # Sync with XBMC database #
 */###########################
function sync_database($col, $mysql_ml, $mysql_xbmc, $conn_ml, $conn_xbmc, $mysql_table_ml, $lang) {
    
    // Check movie from XBMC
    $xbmc_sql = 'SELECT ' . $col['title'] . ' FROM movie';
    mysql_select_db($mysql_xbmc[4], $conn_xbmc);
    $xbmc_result = mysql_query($xbmc_sql, $conn_xbmc);
    $xbmc_assoc = array();
    while ($xbmc = mysql_fetch_assoc($xbmc_result)) {
        $xbmc_assoc[] = $xbmc[$col['title']];
    }

    // Check movie from MovieLib
    $ml_sql = 'SELECT id, title FROM ' . $mysql_table_ml;
    mysql_select_db($mysql_ml[4], $conn_ml);
    $ml_result = mysql_query($ml_sql, $conn_ml);
    $ml_assoc = array();
    while ($ml = mysql_fetch_assoc($ml_result)) {
        $ml_assoc[$ml['id']] = $ml['title'];
    }

    // Set movie to remove
    $to_remove = array();
    foreach ($ml_assoc as $key => $val) {
        if (!in_array($val, $xbmc_assoc)) {
            $to_remove[$key] = $val;
        }
    }
    
    // Delete a no exist movies
    foreach ($to_remove as $key => $val) {
        $delete_movie_sql = 'DELETE FROM ' . $mysql_table_ml . ' WHERE title = "' . addslashes($val) . '"';
        mysql_select_db($mysql_ml[4], $conn_ml);
        mysql_query($delete_movie_sql, $conn_ml);
        if (file_exists('cache/' . $key . '.jpg')) {
            unlink('cache/' . $key . '.jpg');
        }
    }

    // Set movie to add
    $to_add = array();
    foreach ($xbmc_assoc as $val) {
        if (!in_array($val, $ml_assoc)) {
            $to_add[] = $val;
        }
    }

    // Add new movies
    // Select from movie table
    foreach ($to_add as $key => $val) {
        $select_sql = 'SELECT 
            ' . $col['id_file'] . ',
            ' . $col['title'] . ',
            ' . $col['plot'] . ',
            ' . $col['rating'] . ',
            ' . $col['year'] . ',
            ' . $col['poster'] . ',
            ' . $col['runtime'] . ',
            ' . $col['genre'] . ',
            ' . $col['director'] . ',
            ' . $col['originaltitle'] . ',
            ' . $col['country'] . ' 
            FROM movie WHERE ' . $col['title'] . ' = "' . addslashes($val) . '"';
        mysql_select_db($mysql_xbmc[4], $conn_xbmc);
        mysql_query('SET CHARACTER SET utf8', $conn_xbmc);
        mysql_query('SET NAMES utf8', $conn_xbmc);
        $select_result = mysql_query($select_sql, $conn_xbmc);
        $movie = mysql_fetch_assoc($select_result);
        
        // Select from streamdetails table
        $select_stream_sql = 'SELECT 
            iStreamType,
            strVideoCodec,
            fVideoAspect,
            iVideoWidth,
            iVideoHeight,
            strAudioCodec,
            iAudioChannels,
            iVideoDuration
            FROM streamdetails WHERE ' . $col['id_file'] . ' = "' . $movie['idFile'] . '" ORDER BY iStreamType';
        $select_stream_result = mysql_query($select_stream_sql, $conn_xbmc);
        $stream_assoc = array();
        while ($stream = mysql_fetch_assoc($select_stream_result)) {
            $stream_assoc[] = $stream;
        }
        
        // Select from files table
        $select_files_sql = 'SELECT 
            playCount,
            lastPlayed,
            dateAdded
            FROM files WHERE ' . $col['id_file'] . ' = "' . $movie['idFile'] . '"';
        $select_files_result = mysql_query($select_files_sql, $conn_xbmc);
        $files = mysql_fetch_assoc($select_files_result);
        
        // Get poster URL
        preg_match_all('/>(http:[^<]+)</', $movie[$col['poster']], $poster_path);
        $poster_url =  (isset($poster_path[1][0]) ? $poster_path[1][0] : '');
        
        // Insert to MovieLib table
        $insert_sql = 'INSERT INTO `' . $mysql_table_ml . '` (
            `title`,
            `plot`,
            `rating`,
            `year`,
            `poster`,
            `runtime`,
            `genre`,
            `director`,
            `originaltitle`,
            `country`,
            `v_codec`,
            `v_aspect`,
            `v_width`,
            `v_height`,
            `v_duration`,
            `a_codec`,
            `a_channels`,
            `play_count`,
            `last_played`,
            `date_added`
      ) VALUES (
            "' . addslashes($movie[$col['title']]) . '",
            "' . addslashes($movie[$col['plot']]) . '",
            "' . $movie[$col['rating']] . '",
            "' . $movie[$col['year']] . '",
            "' . addslashes($poster_url) . '", '
            . ($movie[$col['runtime']] == 0 ? '"' . round($stream_assoc[0]['iVideoDuration'] / 60, 0) . '", ' : '"' . $movie[$col['runtime']] . '", ') . '
            "' . addslashes($movie[$col['genre']]) . '",
            "' . addslashes($movie[$col['director']]) . '",
            "' . addslashes($movie[$col['originaltitle']]) . '",
            "' . addslashes($movie[$col['country']]) . '", '
            . (isset($stream_assoc[0]['strVideoCodec']) ? '"' . $stream_assoc[0]['strVideoCodec'] . '", ' : 'NULL, ')
            . (isset($stream_assoc[0]['fVideoAspect']) ? '"' . $stream_assoc[0]['fVideoAspect'] . '", ' : 'NULL, ')
            . (isset($stream_assoc[0]['iVideoWidth']) ? '"' . $stream_assoc[0]['iVideoWidth'] . '", ' : 'NULL, ')
            . (isset($stream_assoc[0]['iVideoHeight']) ? '"' . $stream_assoc[0]['iVideoHeight'] . '", ' : 'NULL, ')
            . (isset($stream_assoc[0]['iVideoDuration']) ? '"' . $stream_assoc[0]['iVideoDuration'] . '", ' : 'NULL, ')
            . (isset($stream_assoc[1]['strAudioCodec']) ? '"' . $stream_assoc[1]['strAudioCodec'] . '", ' : 'NULL, ')
            . (isset($stream_assoc[1]['iAudioChannels']) ? '"' . $stream_assoc[1]['iAudioChannels'] . '", ' : 'NULL, ')
            . ($files['playCount'] == null ? 'NULL, ' : '"' . $files['playCount'] . '", ')
            . ($files['lastPlayed'] == null ? 'NULL, ' : '"' . $files['lastPlayed'] . '", ') . '
            "' . $files['dateAdded'] . '"
      )';

        mysql_select_db($mysql_ml[4], $conn_ml);
        mysql_query('SET CHARACTER SET utf8', $conn_ml);
        mysql_query('SET NAMES utf8', $conn_ml);
        $insert = mysql_query($insert_sql, $conn_ml);
        if (!$insert) {
            echo '<br />' . $lang['f_synch_error'] . ': ' . mysql_error($conn_ml);
            exit;
        }
    }
    $added = count($to_add);
    $removed = count($to_remove);
    if ($added == 0 && $removed == 0) {
        $output = '';
    } else {
        $output = $lang['f_synchronized'] . '<br />' . $lang['f_added'] . ': ' . $added . ' ' . $lang['f_movies'] . '<br />' . $lang['f_removed'] . ': ' . $removed . ' ' . $lang['f_movies'] . '<br />';
    }
    return $output;
}

/* #########################
 * # Sync with videodb.xml #
 */#########################
function import_xml($col, $mysql_ml, $conn_ml, $mysql_table_ml, $lang) {
        
    // Load XML file
    $xml = simplexml_load_file('import/videodb.xml');
    $xml_movie = $xml->movie;
    
    // Check movie from XML
    $xml_assoc = array();
    $xml_title = array();
    foreach ($xml_movie as $movie_val) {
        $xml_assoc[] = $movie_val;
        $xml_title_assoc[] = (string) $movie_val->title;
    }

    // Check movie from MovieLib
    $ml_sql = 'SELECT id, title FROM ' . $mysql_table_ml;
    mysql_select_db($mysql_ml[4], $conn_ml);
    $ml_result = mysql_query($ml_sql, $conn_ml);
    $ml_assoc = array();
    while ($ml = mysql_fetch_assoc($ml_result)) {
        $ml_assoc[$ml['id']] = $ml['title'];
    }
    
    // Set movie to remove
    $to_remove = array();
    foreach ($ml_assoc as $key => $val) {
        if (!in_array($val, $xml_title_assoc)) {
            $to_remove[$key] = $val;
        }
    }

    // Delete a no exist movies
    foreach ($to_remove as $key => $val) {
        $delete_movie_sql = 'DELETE FROM ' . $mysql_table_ml . ' WHERE title = "' . addslashes($val) . '"';
        mysql_select_db($mysql_ml[4], $conn_ml);
        mysql_query($delete_movie_sql, $conn_ml);
        if (file_exists('cache/' . $key . '.jpg')) {
            unlink('cache/' . $key . '.jpg');
        }
    }

    // Set movie to add
    $to_add = array();
    foreach ($xml_title_assoc as $val) {
        if (!in_array($val, $ml_assoc)) {
            $to_add[] = $val;
        }
    }
    
    // Add new movies
    // Select from movie table
    foreach ($to_add as $key => $val) {
        
        $movie = $xml_movie[$key];

        // Insert to MovieLib table
        $genre = array();
        foreach ($movie->genre as $genre_val) {
            $genre[] = (string) $genre_val;
        }
        
        $country = array();
        foreach ($movie->country as $country_val) {
            $country[] = (string) $country_val;
        }
        
        $insert_xml_sql = 'INSERT INTO `' . $mysql_table_ml . '` (
            `title`,
            `plot`,
            `rating`,
            `year`,
            `poster`,
            `runtime`,
            `genre`,
            `director`,
            `originaltitle`,
            `country`,
            `v_codec`,
            `v_aspect`,
            `v_width`,
            `v_height`,
            `v_duration`,
            `a_codec`,
            `a_channels`,
            `play_count`,
            `last_played`,
            `date_added`
      ) VALUES (
            "' . addslashes($movie->title) . '",
            "' . addslashes($movie->plot) . '",
            "' . addslashes($movie->rating) . '",
            "' . addslashes($movie->year) . '",
            "' . addslashes($movie->thumb) . '",
            "' . addslashes($movie->runtime) . '",
            "' . addslashes(implode(' / ', $genre)) . '",
            "' . addslashes($movie->director) . '",
            "' . addslashes($movie->originaltitle) . '",
            "' . addslashes(implode(' / ', $country)) . '",
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL, '
            . ($movie->playcount == 0 ? 'NULL,' : '"' . $movie->playcount . '",')
            . ($movie->lastplayed == '1601-01-01' ? 'NULL,' : '"' . $movie->lastplayed . '", ') . '
            "' . $movie->dateadded . '"
      )';
        mysql_select_db($mysql_ml[4], $conn_ml);
        mysql_query('SET CHARACTER SET utf8', $conn_ml);
        mysql_query('SET NAMES utf8', $conn_ml);
        $insert_xml = mysql_query($insert_xml_sql, $conn_ml);
        if (!$insert_xml) {
            echo '<br />' . $lang['f_synch_error'] . ': ' . mysql_error($conn_ml);
            exit;
        }
    }
    $added = count($to_add);
    $removed = count($to_remove);
    if ($added == 0 && $removed == 0) {
        $output = '';
    } else {
        $output = $lang['f_synchronized'] . '<br />' . $lang['f_added'] . ': ' . $added . ' ' . $lang['f_movies'] . '<br />' . $lang['f_removed'] . ': ' . $removed . ' ' . $lang['f_movies'] . '<br />';
        
    }
    rename('import/videodb.xml', 'import/videodb.bak');
    return $output;
}

/* ######################################
 * # GD conversion, create poster cache #
 */######################################
function gd_convert($id, $poster) {
    $cache_poster = 'cache/' . $id . '.jpg';
    if (!file_exists($cache_poster) and !empty($poster)) {
        $img = @imagecreatefromjpeg($poster);
        if ($img) {
            $width = imagesx($img);
            $height = imagesy($img);
            $new_width = 140;
            $new_height = 198;
            $img_temp = imagecreatetruecolor($new_width, $new_height);
            imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($img_temp, $cache_poster, 80);
        }
    }
}
?>