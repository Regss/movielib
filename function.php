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
        $output = $lang['f_tab_created'] . ': movie<br/>';
    }
    return $output;
}

/* ##################################
 * # Sync XBMC database to MovieLib #
 */##################################
function sync_database($col, $mysql_ml, $mysql_xbmc, $conn_ml, $conn_xbmc, $mysql_table_ml, $lang) {
    
    $output = '';

    // Check id movie from XBMC
    $xbmc_sql = 'SELECT ' . $col['title'] . ' FROM movie';
    mysql_select_db($mysql_xbmc[4], $conn_xbmc);
    $xbmc_result = mysql_query($xbmc_sql, $conn_xbmc);
    $xbmc_assoc = array();
    while ($xbmc = mysql_fetch_assoc($xbmc_result)) {
        array_push($xbmc_assoc, $xbmc[$col['title']]);
    }

    // Check id movie from MovieLib
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
        $output = 'Zsynchronizowano';
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
            array_push($stream_assoc, $stream);
        }
        
        // Select from files table
        $select_files_sql = 'SELECT 
            playCount,
            lastPlayed,
            dateAdded
            FROM files WHERE ' . $col['id_file'] . ' = "' . $movie['idFile'] . '"';
        $select_files_result = mysql_query($select_files_sql, $conn_xbmc);
        $files = mysql_fetch_assoc($select_files_result);
        
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
            "' . addslashes($movie[$col['rating']]) . '",
            "' . addslashes($movie[$col['year']]) . '",
            "' . addslashes($movie[$col['poster']]) . '",
            "' . addslashes($movie[$col['runtime']]) . '",
            "' . addslashes($movie[$col['genre']]) . '",
            "' . addslashes($movie[$col['director']]) . '",
            "' . addslashes($movie[$col['originaltitle']]) . '",
            "' . addslashes($movie[$col['country']]) . '",
            "' . $stream_assoc[0]['strVideoCodec'] . '",
            "' . $stream_assoc[0]['fVideoAspect'] . '",
            "' . $stream_assoc[0]['iVideoWidth'] . '",
            "' . $stream_assoc[0]['iVideoHeight'] . '",
            "' . $stream_assoc[0]['iVideoDuration'] . '",
            "' . $stream_assoc[1]['strAudioCodec'] . '",
            "' . $stream_assoc[1]['iAudioChannels'] . '",
            "' . $files['playCount'] . '",
            "' . $files['lastPlayed'] . '",
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
        $output = 'Zsynchronizowano';
    } 
    return $output;
}

/* ######################
 * # Import videodb.xml #
 */######################
function import_xml($col, $mysql_ml, $conn_ml, $mysql_table_ml, $lang) {
    
    $xml = simplexml_load_file('import/videodb.xml');
    $xml_movie = $xml->movie;
    foreach ($xml_movie as $movie_val) {
        
        $genre = array();
        foreach ($movie_val->genre as $genre_val) {
            $genre[] = (string) $genre_val;
        }
        
        $country = array();
        foreach ($movie_val->country as $country_val) {
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
            "' . addslashes($movie_val->title) . '",
            "' . addslashes($movie_val->plot) . '",
            "' . addslashes($movie_val->rating) . '",
            "' . addslashes($movie_val->year) . '",
            "' . addslashes($movie_val->thumb) . '",
            "' . addslashes($movie_val->runtime) . '",
            "' . addslashes(implode(' / ', $genre)) . '",
            "' . addslashes($movie_val->director) . '",
            "' . addslashes($movie_val->originaltitle) . '",
            "' . addslashes(implode(' / ', $country)) . '",
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            "' . $movie_val->playcount . '",
            "' . $movie_val->lastplayed . '",
            "' . $movie_val->dateadded . '"
      )';
        mysql_select_db($mysql_ml[4], $conn_ml);
        mysql_query('SET CHARACTER SET utf8', $conn_ml);
        mysql_query('SET NAMES utf8', $conn_ml);
        $insert_xml = mysql_query($insert_xml_sql, $conn_ml);
        if (!$insert_xml) {
            echo '<br />' . $lang['f_synch_error'] . ': ' . mysql_error($conn_ml);
            exit;
        }
        $output = 'Zaimportowano';
    }
    return $output;
}

/* ######################################
 * # GD conversion, create poster cache #
 */######################################
function gd_convert($id, $poster) {
    $cache_poster = 'cache/' . $id . '.jpg';
    if (!file_exists($cache_poster) and !empty($poster)) {
        foreach ($poster as $val) {
            $img = @imagecreatefromjpeg($val);
            if ($img) {
                $width = imagesx($img);
                $height = imagesy($img);
                $new_width = 140;
                $new_height = 198;
                $img_temp = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($img_temp, $cache_poster, 80);
                break;
            }
        }
    }
}
?>