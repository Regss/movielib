<?PHP
/* #########
 * # CLASS #
 */#########
class Teamplate {
    function __construct($file, $set, $lang) {
        $this->file = 'templates/' . $set['theme'] . '/' . $file;
        $this->set = $set;
        $this->lang = $lang;
        $this->tpl = array();
        $this->show = array();
    }
    function tpl($title, $val) {    
        $this->tpl[$title] = $val;
    }
    function show($title, $val) {    
        $this->show[$title] = $val;
    }
    function init() {
        $cont = file_get_contents($this->file);
        
        foreach ($this->tpl as $key => $val) {
            $cont = str_replace('{' . $key . '}', $val, $cont);
        }
        foreach ($this->lang as $key => $val) {
            $cont = str_replace('{LANG.' . $key . '}', $val, $cont);
        }
        foreach ($this->set as $key => $val) {
            $cont = str_replace('{SET.' . $key . '}', $val, $cont);
        }
        foreach ($this->show as $key  => $val) {
            if ($val <> 1) {
                $cont = preg_replace('|{SHOW\.' . $key . '}.*?{/SHOW\.' . $key . '}|s', '', $cont);
            }
        }
        $cont = preg_replace('|{.?SHOW\.[^}]+}|s', '', $cont);
        return $cont;
    }
}

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
        $set_sql = 'SELECT * FROM ' . $mysql_tables[3];
        $set_result = mysql_query($set_sql);
        $get_set = mysql_fetch_assoc($set_result);
        foreach($get_set as $key => $val) {
            $_SESSION[$key] = $val;
        }
    }
    return $_SESSION;
}

/* ###########################
 * # Create and check tables #
 */###########################
function create_table($mysql_tables, $tables, $lang, $version, $drop) {
        
    // drop tables
    if ($drop == 1) {
        foreach ($mysql_tables as $table) {
            $drop_table_sql = 'DROP TABLE IF EXISTS `' . $table . '`';
            if (!@mysql_query($drop_table_sql)) {
                die(mysql_error());
            }
        }
    }
    
    $tables_array = array();
    $tables_sql = 'SHOW TABLES';
    $tables_result = mysql_query($tables_sql);
    while ($tables_db = mysql_fetch_array($tables_result)) {
        $tables_array[] = $tables_db[0];
    }
    
    // table movies
    if (!in_array($mysql_tables[0], $tables_array)) {
        $create_movies_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_tables[0] . '` (';
        foreach($tables[$mysql_tables[0]] as $key => $val) {
            $create_movies_sql.= '`' . $key . '` ' . $val . ', ';
        }
        $create_movies_sql.= 'PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8';
        if (!@mysql_query($create_movies_sql)) {
            die($lang['inst_could_create'] . ': ' . $mysql_tables[0] . ' - ' . mysql_error() . '<br>');
        }
    }
    
    // table tvshows
    if (!in_array($mysql_tables[1], $tables_array)) {
        $create_tvshows_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_tables[1] . '` (';
        foreach($tables[$mysql_tables[1]] as $key => $val) {
            $create_tvshows_sql.= '`' . $key . '` ' . $val . ', ';
        }
        $create_tvshows_sql.= 'PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8';
        if (!@mysql_query($create_tvshows_sql)) {
            die($lang['inst_could_create'] . ': ' . $mysql_tables[1] . ' - ' . mysql_error() . '<br>');
        }
    }
    
    // table seasons
    if (!in_array($mysql_tables[2], $tables_array)) {
        $create_seasons_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_tables[2] . '` (';
        foreach($tables[$mysql_tables[2]] as $key => $val) {
            $create_seasons_sql.= '`' . $key . '` ' . $val . ', ';
        }
        $create_seasons_sql.= 'PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8';
        if (!@mysql_query($create_seasons_sql)) {
            die($lang['inst_could_create'] . ': ' . $mysql_tables[2] . ' - ' . mysql_error() . '<br>');
        }
    }
    
    // table episodes
    if (!in_array($mysql_tables[2], $tables_array)) {
        $create_episodes_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_tables[2] . '` (';
        foreach($tables[$mysql_tables[2]] as $key => $val) {
            $create_episodes_sql.= '`' . $key . '` ' . $val . ', ';
        }
        $create_episodes_sql.= 'PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8';
        if (!@mysql_query($create_episodes_sql)) {
            die($lang['inst_could_create'] . ': ' . $mysql_tables[2] . ' - ' . mysql_error() . '<br>');
        }
    }
    
    // table config
    if (!in_array($mysql_tables[3], $tables_array)) {
        $create_config_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_tables[3] . '` (';
        foreach($tables[$mysql_tables[3]] as $key => $val) {
            $create_config_sql.= '`' . $key . '` ' . $val . ', ';
        }
        $create_config_sql = substr($create_config_sql, 0, -2);
        $create_config_sql.= ') DEFAULT CHARSET=utf8';
        if (!@mysql_query($create_config_sql)) {
            die($lang['inst_could_create'] . ': ' . $mysql_tables[3] . ' - ' . mysql_error() . '<br>');
        }
        if (@mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_tables[3])) == 0) {
            $insert_config_sql = 'INSERT INTO `' . $mysql_tables[3] . '` () VALUES ()';
            mysql_query ($insert_config_sql);
        }
    }
    
    // table users
    if (!in_array($mysql_tables[4], $tables_array)) {
        $create_users_sql = 'CREATE TABLE IF NOT EXISTS `' . $mysql_tables[4] . '` (';
        foreach($tables[$mysql_tables[4]] as $key => $val) {
            $create_users_sql.= '`' . $key . '` ' . $val . ', ';
        }
        $create_users_sql.= 'PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8';
        if (!@mysql_query($create_users_sql)) {
            die($lang['inst_could_create'] . ': ' . $mysql_tables[4] . ' - ' . mysql_error() . '<br>');
        }
        if (@mysql_num_rows(mysql_query('SELECT * FROM ' . $mysql_tables[4])) == 0) {
            $insert_users_sql = 'INSERT INTO `' . $mysql_tables[4] . '` (`id`, `login`, `password`) VALUES (1, "admin", "21232f297a57a5a743894a0e4a801fc3")';
            mysql_query($insert_users_sql);
            $insert_users_sql = 'INSERT INTO `' . $mysql_tables[4] . '` (`id`, `login`, `password`) VALUES (2, "user", "ee11cbb19052e40b07aac0ca060c23ee")';
            mysql_query($insert_users_sql);
        }
    }
    
    // check columns
    foreach ($mysql_tables as $table) {
        $columns_sql = 'SHOW COLUMNS FROM ' . $table;
        $columns_result = mysql_query($columns_sql);
        while($columns = mysql_fetch_assoc($columns_result)) {
            $columns_db_array[$table][] = $columns['Field'];
        }
    }
    
    foreach ($tables as $tables_key => $tables_val) {
        foreach($tables_val as $col_key => $col_type) {
            if (!in_array($col_key, $columns_db_array[$tables_key])) {
                mysql_query('ALTER TABLE `' . $tables_key . '` ADD `' . $col_key . '` ' . $col_type);
            } else {
                mysql_query('ALTER TABLE `' . $tables_key . '` CHANGE `' . $col_key . '` `' . $col_key . '` ' . $col_type);
            }
        }
    }
    
    // update version
    $update_v_sql = 'UPDATE `' . $mysql_tables[3] . '` SET 
        version = "' . $version . '"
        WHERE version LIKE "%"';
    mysql_query($update_v_sql);
    $output_create_table = $lang['a_tables_updated'] . '<br>';
    return $output_create_table;
}

/* ##########################
 * # SYNC - show id from db #
 */##########################
function show_id($table) {
    $show_id_sql = 'SELECT id FROM ' . $table;
    $show_id_result = mysql_query($show_id_sql);
    while ($id = mysql_fetch_array($show_id_result)) {
        echo $id[0] . ' ';
    }
}
 
/* ##########################
 * # SYNC - add Movie to DB #
 */##########################
function sync_add($tables, $table) {
    $insert_row = '';
    $insert_value = '';
    
    foreach($tables[$table] as $key => $val) {
        $insert_row.= '`' . $key . '`,';
        $insert_value.= '"' . (isset($_POST[$key]) ? add_slash($_POST[$key]) : '') . '",';
    }
    $insert_row = substr($insert_row, 0, -1);
    $insert_value = substr(trim($insert_value), 0, -1);
    $insert_sql = 'INSERT INTO `' . $table . '` (' . $insert_row . ') VALUES (' . $insert_value . ')';
    
    $insert = mysql_query($insert_sql);

    if (!$insert) {
        echo $insert_sql . '<br>';
        echo 'ERROR: MySQL - ' . mysql_error();
    } else {
    
        // poster
        if (isset($_POST['poster'])) {
            $poster = base64_decode($_POST['poster']);
            if (substr($poster, 0, 4) == 'http') {
                $size = @getimagesize($poster);
                if ($size[0] > $size[1]) {
                    gd_convert('cache/' . $table . '_' . $_POST['id'] . '.jpg', $poster, 140, 35);
                } else {
                    gd_convert('cache/' . $table . '_' . $_POST['id'] . '.jpg', $poster, 140, 198);
                }
            } else {
                $fp = fopen('cache/temp_' . $table . '_' . $_POST['id'], 'wb');
                fwrite($fp, $poster);
                fclose($fp);
                $size = @getimagesize('cache/temp_' . $table . '_' . $_POST['id']);
                if ($size[0] > $size[1]) {
                    gd_convert('cache/' . $table . '_' . $_POST['id'] . '.jpg', 'cache/temp_' . $table . '_' . $_POST['id'], 140, 35);
                } else {
                    gd_convert('cache/' . $table . '_' . $_POST['id'] . '.jpg', 'cache/temp_' . $table . '_' . $_POST['id'], 140, 198);
                }
                unlink('cache/temp_' . $table . '_' . $_POST['id']);
            }
        }
        
        // fanart
        if (isset($_POST['fanart'])) {
            $fanart = base64_decode($_POST['fanart']);
            if (substr($fanart, 0, 4) == 'http') {
                gd_convert('cache/' . $table . '_' . $_POST['id'] . '_f.jpg', $fanart, 1280, 720);
            } else {
                $fp = fopen('cache/temp_f_' . $table . '_' . $_POST['id'], 'wb');
                fwrite($fp, $fanart);
                fclose($fp);
                gd_convert('cache/' . $table . '_' . $_POST['id'] . '_f.jpg', 'cache/temp_f_' . $table . '_' . $_POST['id'], 1280, 720);
                unlink('cache/temp_f_' . $table . '_' . $_POST['id']);
            }
        }
    }
}

/* ###############################
 * # SYNC - remove Movie from DB #
 */###############################
function sync_remove($table) {
    
    $id = $_POST['id'];
    $delete_movie_sql = 'DELETE FROM ' . $table . ' WHERE id = "' . $id . '"';
    if (file_exists('cache/' . $table . '_' . $id . '.jpg')) {
        unlink('cache/' . $table . '_' . $id . '.jpg');
    }
    if (file_exists('cache/' . $table . '_' . $id . '_f.jpg')) {
        unlink('cache/' . $table . '_' . $id . '_f.jpg');
    }
    
    $delete = mysql_query($delete_movie_sql);
    
    if (!$delete) {
        echo 'ERROR: MySQL - ' . mysql_error();
    }
}

/* ##################
 * # SYNC - Watched #
 */##################
function sync_watched($table) {
    
    $update_sql = 'UPDATE `' . $table . '` SET 
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
function sync_unwatched($table) {
    
    $update_sql = 'UPDATE `' . $table . '` SET 
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
function sync_lastplayed($table) {
    
    $update_sql = 'UPDATE `' . $table . '` SET 
        play_count = "' . add_slash($_POST['playcount']) . '",
        last_played = "' . add_slash($_POST['lastplayed']) . '"
        WHERE id = "' . $_POST['id'] . '"';

    $update = mysql_query($update_sql);

    if (!$update) {
        echo 'ERROR: MySQL - ' . mysql_error();
    }
}

/* ####################
 * # SYNC - add actor #
 */####################
function add_actor($actor_name, $actor_thumb) {
    $actor_thumb = base64_decode($actor_thumb);
    $actor_filename = substr(md5($actor_name), 0, 10);
    if (substr($actor_thumb, 0, 4) == 'http') {
        gd_convert('cache/actors/' . $actor_filename . '.jpg', $actor_thumb, 75, 100);
    } else {
        $fp = fopen('cache/actors/temp_a_' . $actor_filename, 'wb');
        fwrite($fp, $actor_thumb);
        fclose($fp);
        gd_convert('cache/actors/' . $actor_filename . '.jpg', 'cache/actors/temp_a_' . $actor_filename, 75, 100);
        unlink('cache/actors/temp_a_' . $actor_filename);
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
    $update_sql = 'UPDATE `' . $mysql_tables[3] . '` SET token = "' . $new_token . '"';
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
        $convert = false;
        $img = @imagecreatefromjpeg($img_link);
        if (!$img) {
            $curl_opt = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true
            ); 
            $c = curl_init($img_link); 
            curl_setopt_array($c, $curl_opt); 
            curl_exec($c); 
            $redirect = curl_getinfo($c); 
            curl_close($c);
            $img = @imagecreatefromjpeg($redirect['redirect_url']);
        }
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

/* ####################
 * # ARRAYS FOR PANEL #
 */####################
function panels_array($columns, $table) {
    $panels_sql = 'SELECT ' . implode(', ', $columns) . ' FROM ' . $table . ' WHERE hide=0';
    $panels_result = mysql_query($panels_sql);
    $panels_array = array();
    while ($panels_mysql_array = mysql_fetch_assoc($panels_result)) {
        foreach ($panels_mysql_array as $column => $value) {
            if (!array_key_exists($column, $panels_array)) {
                $panels_array[$column] = array();
            }
            if (strpos($value, ' / ') !== false) {
                foreach (explode(' / ', $value) as $val) {
                    if (!in_array($val, $panels_array[$column]) && strlen($val) > 0) {
                        $panels_array[$column][] = $val;
                    }
                }
            } else {
                if (!in_array($value, $panels_array[$column]) && strlen($value) > 0) {
                    $panels_array[$column][] = $value;
                }
            }
        }
    }
    if (isset($panels_array['genre'])) { sort($panels_array['genre']); }
    if (isset($panels_array['year'])) { rsort($panels_array['year']); }
    if (isset($panels_array['country'])) { sort($panels_array['country']); }
    if (isset($panels_array['sets'])) { sort($panels_array['sets']); }
    if (isset($panels_array['v_codec'])) { sort($panels_array['v_codec']); }
    if (isset($panels_array['a_codec'])) { sort($panels_array['a_codec']); }
    if (isset($panels_array['a_chan'])) { sort($panels_array['a_chan']); }
    
    return $panels_array;
}

/* #################
 * # CREATE BANNER #
 */#################
function create_banner($lang, $set) {
    
    $banner_sql = 'SELECT id, title, originaltitle, year, rating, runtime, genre, country FROM movies ORDER BY last_played LIMIT 0, 1';
    $banner_result = mysql_query($banner_sql);
    $ban = mysql_fetch_array($banner_result);
    
    // (color R, color G, color B, font size, pos X, pos Y)
    $b = array(
        20, 20, 20, // background color
        255, 255, 255, 10, 144, 20, // last watched color
        255, 255, 255, 8, 150, 36, // title color
        128, 128, 128, 6, 150, 50, // info color
        0, 0, 0, // stroke color
        255, 255, 255); // border color
        
    if (count(explode(';', $set['banner'])) == 27 ) {
        $b = explode(';', $set['banner']);
    }
    
    $c_bg = array($b[0], $b[1], $b[2]);
    $c_lw = array($b[3], $b[4], $b[5], $b[6], $b[7], $b[8]);
    $c_t = array($b[9], $b[10], $b[11], $b[12], $b[13], $b[14]); 
    $c_i = array($b[15], $b[16], $b[17], $b[18], $b[19], $b[20]);
    $c_s = array($b[21], $b[22], $b[23]);
    $c_b = array($b[24], $b[25], $b[26]);
    $font = 'admin/css/font/archivonarrow.ttf';

    // background
    $banner = imagecreatetruecolor(400,60);
    $bg_color = imagecolorallocate($banner, $c_bg[0], $c_bg[1], $c_bg[2]);
    imagefill($banner, 0, 0, $bg_color);

    // get poster and copy
    if (file_exists('cache/movies_' . $ban['id'] . '_f.jpg')) {
        $post = imagecreatefromjpeg('cache/movies_' . $ban['id'] . '_f.jpg');
    } else {
        $post = imagecreatefromjpeg('templates/default/img/d_poster.jpg');
    }
    $width = imagesx($post);
    $height = imagesy($post);
    $new_width = 140;
    $new_height = $height / ($width / $new_width);
    imagecopyresampled($banner, $post, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // add gradient
    $width = 80;
    $gradient = imagecreatetruecolor($width, 60);
    $gradient_color = imagecolorallocatealpha($gradient, $c_bg[0], $c_bg[1], $c_bg[2], 127);
    imagefill($gradient, 0, 0, $gradient_color);
    for ($x=0; $x < $width; ++$x) {
        $alpha = 127 - $x*(127/$width);
        $gradient_color = imagecolorallocatealpha($gradient, $c_bg[0], $c_bg[1], $c_bg[2], $alpha);
        imageline($gradient, $x, 0, $x, 60, $gradient_color);
    }
    imagecopyresampled($banner, $gradient, 140-$width, 0, 0, 0, $width, 60, $width, 60);

    // add text
    $last_watched_color = imagecolorallocate($banner, $c_lw[0], $c_lw[1], $c_lw[2]);
    $title_color = imagecolorallocate($banner, $c_t[0], $c_t[1], $c_t[2]);
    $info_color = imagecolorallocate($banner, $c_i[0], $c_i[1], $c_i[2]);
    $stroke_color = imagecolorallocate($banner, $c_s[0], $c_s[1], $c_s[2]);
    imagettfstroketext($banner, $c_lw[3], 0, $c_lw[4], $c_lw[5], $last_watched_color, $stroke_color, $font, $lang['i_last_played'], 1);
    imagettfstroketext($banner, $c_t[3], 0, $c_t[4], $c_t[5], $title_color, $stroke_color, $font, $ban['title'], 1);
    imagettfstroketext($banner, $c_i[3], 0, $c_i[4], $c_i[5], $info_color, $stroke_color, $font, $ban['year'] . ' | ' . $ban['rating'] . ' | ' . $ban['runtime'] . ' ' . $lang['i_minute'] . ' | ' . $ban['genre'] . ' | ' . $ban['country'], 1);

    // icon
    $icon = imagecreatefrompng('admin/img/movie.png');
    imagecopy($banner, $icon, 374, 6, 0, 0, 20, 20);

    // border
    $border_color = imagecolorallocate($banner, $c_b[0], $c_b[1], $c_b[2]);
    imageline($banner, 0, 0, 399, 0, $border_color);
    imageline($banner, 399, 0, 399, 59, $border_color);
    imageline($banner, 0, 59, 399, 59, $border_color);
    imageline($banner, 0, 0, 0, 59, $border_color);

    // save as file
    imagejpeg($banner, 'cache/banner.jpg', 100);
    return $b;
}

/* ##########################
 * # STROKE FOR BANNER TEXT #
 */##########################
function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
    for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
        for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
            $banner = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
    return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
}
?>