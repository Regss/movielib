<?PHP

/* #############
 * # FUNCTIONS #
 */#############

/* ######################
 * # Create empty table #
 */######################
function create_table($col, $lang) {
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
function sync_database($col, $mysql_ml, $mysql_xbmc, $conn_ml, $conn_xbmc, $lang) {
    
    $output = '';

    // Check id movie from XBMC
    $xbmc_sql = 'SELECT ' . $col['id_movie'] . ', ' . $col['id_file'] . ' FROM movie ORDER BY ' . $col['id_movie'];
    mysql_select_db($mysql_xbmc[4], $conn_xbmc);
    $xbmc_result = mysql_query($xbmc_sql, $conn_xbmc);
    $id_xbmc_assoc = array();
    while ($xbmc = mysql_fetch_assoc($xbmc_result)) {
        array_push($id_xbmc_assoc, $xbmc[$col['id_movie']]);
    }

    // Check id movie from MovieLib
    $ml_sql = 'SELECT id, file FROM ' . $mysql_table_ml . ' ORDER BY id';
    mysql_select_db($mysql_ml[4], $conn_ml);
    $ml_result = mysql_query($ml_sql, $conn_ml);
    $id_ml_assoc = array();
    $file_ml_assoc = array();
    while ($ml = mysql_fetch_assoc($ml_result)) {
        array_push($id_ml_assoc, $ml['id']);
        array_push($file_ml_assoc, $ml['file']);
    }

    // Set movie to remove
    $id_to_remove = array();
    $file_to_remove = array();
    foreach ($id_ml_assoc as $key => $val) {
        if (!in_array($val, $id_xbmc_assoc)) {
            array_push($id_to_remove, $val);
            array_push($file_to_remove, $file_ml_assoc[$key]);
        }
    }

    // Set movie to add
    $id_to_add = array();
    foreach ($id_xbmc_assoc as $val) {
        if (!in_array($val, $id_ml_assoc)) {
            array_push($id_to_add, $val);
        }
    }

    // Delete a no exist movies
    foreach ($id_to_remove as $key => $val) {
        $delete_movie_sql = 'DELETE FROM ' . $mysql_table_ml . ' WHERE id_movie = "' . $val . '"';
        mysql_select_db($mysql_ml[4], $conn_ml);
        mysql_query($delete_movie_sql, $conn_ml);
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
            ' . $col['country'] . ' 
            FROM movie WHERE ' . $col['id_movie'] . ' = "' . $val . '"';
        mysql_select_db($mysql_xbmc[4], $conn_xbmc);
        mysql_query('SET CHARACTER SET utf8', $conn_xbmc);
        mysql_query('SET NAMES utf8', $conn_xbmc);
        $select_result = mysql_query($select_sql, $conn_xbmc);
        $movie = mysql_fetch_assoc($select_result);
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
        $insert_sql = 'INSERT INTO `' . $mysql_table_ml . '` (
            `id`,
            `file`,
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
            `a_channels`
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
            "' . addslashes($movie[$col['country']]) . '",
            "' . $stream_assoc[0]['strVideoCodec'] . '",
            "' . $stream_assoc[0]['fVideoAspect'] . '",
            "' . $stream_assoc[0]['iVideoWidth'] . '",
            "' . $stream_assoc[0]['iVideoHeight'] . '",
            "' . $stream_assoc[0]['iVideoDuration'] . '",
            "' . $stream_assoc[1]['strAudioCodec'] . '",
            "' . $stream_assoc[1]['iAudioChannels'] . '"
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