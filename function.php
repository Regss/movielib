<?PHP

/* #############
 * # FUNCTIONS #
 */#############

/* ######################
 * # Create empty table #
 */######################
function create_table($col, $mysql_table_ml, $lang) {
    $create_movie_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_table_ml . '` (
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
    if (!mysql_query($create_movie_sql)) {
        $output = $lang['f_tab_cant_create'] . ': ' . mysql_error() . '<br/>';
    } else {
        $output = $lang['f_tab_created'] . ': ' . $mysql_table_ml . '<br/>';
    }
    return $output;
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