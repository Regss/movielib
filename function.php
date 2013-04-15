<?PHP

/* #############
 * # FUNCTIONS #
 */#############

/* ######################
 * # Create empty table #
 */######################

function create_table($col, $lang) {
    $create_movie_sql = 'CREATE TABLE IF NOT EXISTS `movie` (
                `' . $col['id_movie'] . '` int(11) NOT NULL AUTO_INCREMENT,
                `' . $col['id_file'] . '` int(11) NOT NULL,
                `' . $col['title'] . '` varchar(100),
                `' . $col['plot'] . '` text,
                `' . $col['rating'] . '` text,
                `' . $col['year'] . '` text,
                `' . $col['poster'] . '` text,
                `' . $col['runtime'] . '` text,
                `' . $col['genre'] . '` text,
                `' . $col['director'] . '` text,
                `' . $col['originaltitle'] . '` text,
                `' . $col['country'] . '` text,
                `check` INT(1) DEFAULT NULL,
                PRIMARY KEY (`' . $col['id_movie'] . '`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8';
    $create_streamdetails_sql = 'CREATE TABLE IF NOT EXISTS `streamdetails` (
                  `idFile` int(11) DEFAULT NULL,
                  `iStreamType` int(11) DEFAULT NULL,
                  `strVideoCodec` text,
                  `fVideoAspect` float DEFAULT NULL,
                  `iVideoWidth` int(11) DEFAULT NULL,
                  `iVideoHeight` int(11) DEFAULT NULL,
                  `strAudioCodec` text,
                  `iAudioChannels` int(11) DEFAULT NULL,
                  `strAudioLanguage` text,
                  `strSubtitleLanguage` text,
                  `iVideoDuration` int(11) DEFAULT NULL,
                  KEY (`idFile`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8';
    if (!mysql_query($create_movie_sql)) {
        $output = $lang['f_tab_cant_create'] . ': ' . mysql_error() . '<br/>';
    } else {
        $output = $lang['f_tab_created'] . ': movie<br/>';
    }
    if (!mysql_query($create_streamdetails_sql)) {
        $output.= $lang['f_tab_cant_create'] . ': ' . mysql_error();
    } else {
        $output.= $lang['f_tab_created'] . ': streamdetails';
    }
    return $output;
}

/* ##################################
 * # Synch remote database to local #
 */##################################

function synch_database($col, $mysql_local, $mysql_remote, $conn_local, $conn_remote, $lang) {
    
    $output = '';

    // Check id movie from remote
    $remote_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ' FROM movie ORDER BY ' . $col['id_movie'];
    mysql_select_db($mysql_remote[4], $conn_remote);
    $remote_result = mysql_query($remote_sql, $conn_remote);
    $id_remote_assoc = array();
    while ($remote = mysql_fetch_assoc($remote_result)) {
        array_push($id_remote_assoc, $remote[$col['id_movie']]);
    }

    // Check id movie from local
    $local_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ' FROM movie ORDER BY ' . $col['id_movie'];
    mysql_select_db($mysql_local[4], $conn_local);
    $local_result = mysql_query($local_sql, $conn_local);
    $id_local_assoc = array();
    $file_local_assoc = array();
    while ($local = mysql_fetch_assoc($local_result)) {
        array_push($id_local_assoc, $local[$col['id_movie']]);
        array_push($file_local_assoc, $local[$col['id_file']]);
    }

    // Set movie to remove
    $id_to_remove = array();
    $file_to_remove = array();
    foreach ($id_local_assoc as $key => $val) {
        if (!in_array($val, $id_remote_assoc)) {
            array_push($id_to_remove, $val);
            array_push($file_to_remove, $file_local_assoc[$key]);
        }
    }

    // Set movie to add
    $id_to_add = array();
    foreach ($id_remote_assoc as $val) {
        if (!in_array($val, $id_local_assoc)) {
            array_push($id_to_add, $val);
        }
    }

    // Delete a no exist movies
    foreach ($id_to_remove as $key => $val) {
        $delete_movie_sql = 'DELETE FROM movie WHERE ' . $col['id_movie'] . ' = "' . $val . '"';
        $delete_stream_sql = 'DELETE FROM streamdetails WHERE ' . $col['id_file'] . ' = "' . $file_to_remove[$key] . '"';
        mysql_select_db($mysql_local[4], $conn_local);
        mysql_query($delete_movie_sql, $conn_local);
        mysql_query($delete_stream_sql, $conn_local);
        if (file_exists('cache/' . $val . '.jpg')) {
            unlink('cache/' . $val . '.jpg');
        }
        $output = 'Zsynchronizowano';
    }

    // Add new movies
    foreach ($id_to_add as $key => $val) {
        $select_sql = 'SELECT 
      ' . $col['id_movie'] . ',
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
      ' . $col['country'] . ' FROM movie WHERE ' . $col['id_movie'] . ' = "' . $val . '"';
        mysql_select_db($mysql_remote[4], $conn_remote);
        mysql_query('SET CHARACTER SET utf8', $conn_remote);
        mysql_query('SET NAMES utf8', $conn_remote);
        $select_result = mysql_query($select_sql, $conn_remote);
        $movie = mysql_fetch_assoc($select_result);
        $select_stream_sql = 'SELECT * FROM streamdetails WHERE ' . $col['id_file'] . ' = "' . $movie['idFile'] . '" ORDER BY iStreamType';
        $select_stream_result = mysql_query($select_stream_sql, $conn_remote);
        $stream_assoc = array();
        while ($stream = mysql_fetch_assoc($select_stream_result)) {
            array_push($stream_assoc, $stream);
        }
        $insert_sql = 'INSERT INTO `movie` (
      `' . $col['id_movie'] . '`,
      `' . $col['id_file'] . '`,
      `' . $col['title'] . '`,
      `' . $col['plot'] . '`,
      `' . $col['rating'] . '`,
      `' . $col['year'] . '`,
      `' . $col['poster'] . '`,
      `' . $col['runtime'] . '`,
      `' . $col['genre'] . '`,
      `' . $col['director'] . '`,
      `' . $col['originaltitle'] . '`,
      `' . $col['country'] . '`
      ) VALUES (
      "' . $movie[$col['id_movie']] . '",
      "' . $movie[$col['id_file']] . '",
      "' . addslashes($movie[$col['title']]) . '",
      "' . addslashes($movie[$col['plot']]) . '",
      "' . addslashes($movie[$col['rating']]) . '",
      "' . addslashes($movie[$col['year']]) . '",
      "' . addslashes($movie[$col['poster']]) . '",
      "' . addslashes($movie[$col['runtime']]) . '",
      "' . addslashes($movie[$col['genre']]) . '",
      "' . addslashes($movie[$col['director']]) . '",
      "' . addslashes($movie[$col['originaltitle']]) . '",
      "' . addslashes($movie[$col['country']]) . '"
      )';

        $insert_streamdetails_0_sql = 'INSERT INTO `streamdetails` (
        `idFile`,
        `iStreamType`,
        `strVideoCodec`,
        `fVideoAspect`,
        `iVideoWidth`,
        `iVideoHeight`,
        `iVideoDuration`
        ) VALUES (
        "' . $stream_assoc[0]['idFile'] . '",
        "' . $stream_assoc[0]['iStreamType'] . '",
        "' . $stream_assoc[0]['strVideoCodec'] . '",
        "' . $stream_assoc[0]['fVideoAspect'] . '",
        "' . $stream_assoc[0]['iVideoWidth'] . '",
        "' . $stream_assoc[0]['iVideoHeight'] . '",
        "' . $stream_assoc[0]['iVideoDuration'] . '"
        )';

        $insert_streamdetails_1_sql = 'INSERT INTO `streamdetails` (
        `idFile`,
        `iStreamType`,
        `strAudioCodec`,
        `iAudioChannels`
        ) VALUES (
        "' . $stream_assoc[1]['idFile'] . '",
        "' . $stream_assoc[1]['iStreamType'] . '",
        "' . $stream_assoc[1]['strAudioCodec'] . '",
        "' . $stream_assoc[1]['iAudioChannels'] . '"
        )';
        mysql_select_db($mysql_local[4], $conn_local);
        mysql_query('SET CHARACTER SET utf8', $conn_local);
        mysql_query('SET NAMES utf8', $conn_local);
        $insert = mysql_query($insert_sql, $conn_local);
        if (!$insert) {
            echo '<br />' . $lang['f_synch_error'] . ': ' . mysql_error($conn_local);
            exit;
        } else {
            mysql_query($insert_streamdetails_0_sql, $conn_local);
            mysql_query($insert_streamdetails_1_sql, $conn_local);
        }
        $output = 'Zsynchronizowano';
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