<?PHP
/* #########
 * # CLASS #
 */#########
class Teamplate {
    function __construct($file, $setting, $lang) {
        $this->file = 'templates/' . $setting['theme'] . '/' . $file;
        $this->set = $setting;
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
            if ($val == 0) {
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

/* ###############
 * # MYSQL query #
 */###############

function mysql_q($query) {
    $time = microtime(true);
    $result = mysql_query($query);
    if (!$result) {
        echo $query . '<br>';
        die ('ERROR: MySQL - ' . mysql_error());
    } else {
        if (isset($_GET['debug'])) {
             echo $query . ' - ' . (microtime(true) - $time) . '<br>';
        }
        return $result;
    }
}

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
    mysql_q('SET CHARACTER SET utf8');
    mysql_q('SET NAMES utf8');
}

/* ##############################
 * # Get settings from database #
 */##############################
function get_settings() {
    
    // if settings in session not exists get it from database
    if (!isset($_SESSION) or count($_SESSION) < 10) {
        $set_sql = 'SELECT * FROM `config`';
        $set_res = mysql_q($set_sql);
        $get_set = mysql_fetch_assoc($set_res);
        foreach($get_set as $key => $val) {
            $_SESSION[$key] = $val;
        }
    }
    return $_SESSION;
}

/* ###########################
 * # Create and check tables #
 */###########################
function create_table($mysql_tables, $mysql_indexes, $lang, $version, $drop) {
        
    // drop tables
    if ($drop == 1) {
        foreach ($mysql_tables as $table => $table_val) {
            $drop_table_sql = 'DROP TABLE IF EXISTS `' . $table . '`';
            $drop_table_res = mysql_q($drop_table_sql);
        }
    }
    
    $tables_array = array();
    $tables_sql = 'SHOW TABLES';
    $tables_result = mysql_q($tables_sql);
    while ($tables_db = mysql_fetch_array($tables_result)) {
        $tables_array[] = $tables_db[0];
    }
    
    // all tables
    foreach ($mysql_tables as $table => $table_val) {
        if (!in_array($table, $tables_array)) {
            $create_sql_array = array();
            foreach($table_val as $key => $val) {
                $create_sql_array[] = '`' . $key . '` ' . $val;
            }
            $create_sql = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (' . implode(', ', $create_sql_array) . ') DEFAULT CHARSET=utf8';
            $create_res = mysql_q($create_sql);
        }
    }
    // insert config
    $sel = 'SELECT * FROM config';
    $res = mysql_q($sel);
    if (mysql_num_rows($res) == 0) {
        $insert_config_sql = 'INSERT INTO `config` () VALUES ()';
        $insert_config_res = mysql_q($insert_config_sql);
    }
    // insert users
    $sel = 'SELECT * FROM users';
    $res = mysql_q($sel);
    if (mysql_num_rows($res) == 0) {
            $insert_users_sql = 'INSERT INTO `users` (`id`, `login`, `password`) VALUES (1, "admin", "21232f297a57a5a743894a0e4a801fc3"), (2, "user", "ee11cbb19052e40b07aac0ca060c23ee")';
            $insert_users_res = mysql_q($insert_users_sql);
        }
    // insert hash
    $sel = 'SELECT * FROM hash';
    $res = mysql_q($sel);
    if (mysql_num_rows($res) == 0) {
        $insert_hash_sql = 'INSERT INTO `hash` () VALUES ()';
        $insert_hash_res = mysql_q ($insert_hash_sql);
    }
    // check columns
    $columns_db_array = array();
    foreach ($mysql_tables as $table => $table_val) {
        $columns_sql = 'SHOW COLUMNS FROM `' . $table . '`';
        $columns_result = mysql_q($columns_sql);
        while($columns = mysql_fetch_assoc($columns_result)) {
            $columns_db_array[$table][] = $columns['Field'];
        }
    }
    foreach ($mysql_tables as $table => $tables_val) {
        $alter = array();
        foreach($tables_val as $col_key => $col_type) {
            if (!in_array($col_key, $columns_db_array[$table])) {
                $alter[] = 'ADD `' . $col_key . '` ' . $col_type;
            } else {
                if ($col_key !== 'id') {
                    $alter[] = 'CHANGE `' . $col_key . '` `' . $col_key . '` ' . $col_type;
                }
            }
        }
        foreach ($columns_db_array[$table] as $col) {
            if (!array_key_exists($col, $mysql_tables[$table])) {
                $alter[] = 'DROP COLUMN `' . $col . '`';
            }
        }
        $alter_sql = 'ALTER TABLE `' . $table . '` ' . implode(', ', $alter);
        mysql_q($alter_sql);
    }
    // check indexes
    $index_db_array = array();
    foreach ($mysql_indexes as $table => $index_val) {
        $index_sql = 'SHOW INDEX FROM `' . $table . '`';
        $index_res = mysql_q($index_sql);
        while($index = mysql_fetch_assoc($index_res)) {
            if ($index['Key_name'] !== 'PRIMARY') {
                $index_db_array[$table][] = $index['Key_name'];
            }
        }
    }
    foreach ($mysql_indexes as $table => $index_val) {
        if (in_array($table, array_keys($index_db_array))) {
            foreach (array_unique($index_db_array[$table]) as $index) {
                $alter_sql = 'DROP INDEX `' . $index . '` ON `' . $table . '`';
                mysql_q($alter_sql);
            }
        }
        foreach($index_val as $index_name) {
            $alter_sql = 'CREATE INDEX `' . $index_name . '` ON `' . $table . '` (`' . substr($index_name, 3) . '`)';
            mysql_q($alter_sql);
        }
    }
    // update version
    $update_v_sql = 'UPDATE `config` SET `version` = "' . $version . '" WHERE `version` LIKE "%"';
    mysql_q($update_v_sql);
    $output_create_table = $lang['a_tables_updated'] . '<br>';
    return $output_create_table;
}

/* #############################
 * # SYNC - show video from db #
 */#############################
function show($cols, $table) {
    $show_sql = 'SELECT `' . implode('`, `', $cols) . '` FROM `' . $table . '`';
    $show_result = mysql_q($show_sql);
    
    $output = array();
    while ($d = mysql_fetch_row($show_result)) {
        $output[$d[0]] = $d[1];
    }
    if (count($output) > 0) {
        echo json_encode($output);
    } else {
        echo '{}';
    }
}

/* ######################
 * # SYNC - show images #
 */######################
function show_images() {
    $types = array(
        'movies' => array(
            'poster' => '^movies_([0-9]+)\.jpg$',
            'fanart' => '^movies_([0-9]+)_f\.jpg$',
            'exthumb' => '^movies_([0-9]+)_t([0-9])\.jpg$'
        ),
        'tvshows' => array(
            'poster' => '^tvshows_([0-9]+)\.jpg$',
            'fanart' => '^tvshows_([0-9]+)_f\.jpg$',
            'exthumb' => '^tvshows_([0-9]+)_t([0-9])\.jpg$'
        ),
        'episodes' => array(
            'poster' => '^episodes_([0-9]+)\.jpg$'
        ),
        'actors' => array(
            'thumb' => '^([^_]{10})\.jpg$'
        )
    );
    $files = scandir('cache');
    $files_a = scandir('cache/actors');
    $output = array(
        'movies' => array('poster' => array(), 'fanart' => array(), 'exthumb' => array()),
        'tvshows' => array('poster' => array(), 'fanart' => array(), 'exthumb' => array()),
        'episodes' => array('poster' => array()),
        'actors' => array('thumb' => array())
    );
    foreach ($types as $type => $img_type) {
        $thumb_temp = array();
        foreach ($img_type as $img => $regexp) {
            if ($type == 'actors') {
                foreach ($files_a as $file) {
                    preg_match('|' . $regexp . '|', $file, $match);
                    if (count($match) > 1) {
                        $output[$type][$img][] = $match[1];
                    }
                }
            } else {
                foreach ($files as $file) {
                    preg_match('|' . $regexp . '|', $file, $match);
                    if (count($match) > 1) {
                        if ($img == 'exthumb') {
                            $output[$type]['exthumb'][] = $match[1] . '_t' . $match[2];
                        } else {
                            $output[$type][$img][] = (int)$match[1];
                        }
                    }
                }
            }
        }
    }
    if (count($output) > 0) {
        echo json_encode($output);
    } else {
        echo '{}';
    }
}

/* #####################
 * # SYNC - add images #
 */#####################
function add_images($data) {
    // create img
    $name = $data['name'];
    $img_thumb = base64_decode($data['img']);
    if (!file_exists('cache/' . $name) && $img_thumb !== '') {
        $fp = fopen('cache/' . $name, 'wb');
        fwrite($fp, $img_thumb);
        fclose($fp);
        if (preg_match('|^([^_]+_[0-9]+_t[0-9]).jpg|', $name, $t)) {
            gd_convert('cache/' . $t[1] . 'm.jpg', 'cache/' . $name, 100, 54);
        }
    }
}

/* #######################
* # SYNC - remove images #
*/########################
function remove_images($data) {
    foreach ($data as $name) {
        if (file_exists('cache/' . $name)) {
            unlink('cache/' . $name);
        }
    }
    reset_hash();
}

/* ##########################
 * # SYNC - add Video to DB #
 */##########################
function sync_add($mysql_tables) {
    $insert_array = array();
    // add actors, genres, countries
    $panels = array('actor', 'genre', 'country', 'studio', 'director', 'stream');
    foreach ($panels as $panel) {
        if (isset($_POST[$panel])) {
            $values = array();
            foreach ($_POST[$panel] as $key => $val) {
                // add stream
                if ($panel == 'stream') {
                    $cols = array_keys($mysql_tables['movies_stream']);
                    $str = explode(';', $val);
                    foreach ($str as $k => $s) {
                        if (substr($mysql_tables['movies_stream'][$cols[$k+1]], 0, 3) == 'int') {
                            $str[$k] = ($s == '' ? 'NULL' : $s);
                        } else {
                            $str[$k] = '"' . $s . '"';
                        }
                    }
                    $values[] = '("' . $_POST['id'] . '", ' . implode(', ', $str) . ')';
                    
                } else {
                    // check if panel exist
                    $sql_id = 'SELECT `id` FROM `' . $panel . '` WHERE `' . $panel . '` = "' . add_slash($val) . '"';
                    $res_id = mysql_q($sql_id);
                    if (!mysql_num_rows($res_id)) {
                        $sql_ins = 'INSERT INTO `' . $panel . '` (`' . $panel . '`) VALUES ("' . add_slash($val) . '")';
                        mysql_q($sql_ins);
                        $id = mysql_insert_id();
                    }
                    else {
                        $row = mysql_fetch_assoc($res_id);
                        $id = $row['id'];
                    }
                    // add panels info
                    if($panel == 'actor') {
                        $cols = array('id', $panel . 'id', 'order');
                        $values[] = '("' . $_POST['id'] . '", "' . $id . '", "' . $key . '")';
                    } else {
                        $cols = array('id', $panel . 'id');
                        $values[] = '("' . $_POST['id'] . '", "' . $id . '")';
                    }
                }
            }
            $insert_sql = 'INSERT INTO `' . $_POST['table'] . '_' . $panel . '` (`' . implode('`, `', $cols) . '`) VALUES ' . implode(', ', $values);
            $result = mysql_q($insert_sql);
            unset($_POST[$panel]);
        }
    }
    
    # insert values
    foreach($mysql_tables[$_POST['table']] as $key => $val) {
        if (isset($_POST[$key]) && strlen($_POST[$key]) > 0) {
            if (substr($val, 0, 3) == 'int' or substr($val, 0, 5) == 'float') {
                $insert_array['`' . $key . '`'] = add_slash($_POST[$key]);
            } else {
                $insert_array['`' . $key . '`'] = '"' . add_slash($_POST[$key]) . '"';
            }
        }
    }
    $insert_sql = 'INSERT INTO `' . $_POST['table'] . '` (' . implode(', ', array_keys($insert_array)) . ') VALUES (' . implode(', ', $insert_array) . ')';
    $insert = mysql_q($insert_sql);
}

/* ###############################
 * # SYNC - delete Video from DB #
 */###############################
function sync_delete($id, $table) {
    $del_array = array($table);
    if ($table == 'movies') {
        array_push($del_array, $table . '_actor', $table . '_genre', $table . '_country', $table . '_studio', $table . '_director', $table . '_stream');
    }
    if ($table == 'tvshows') {
        array_push($del_array, $table . '_actor', $table . '_genre');
    }
    if ($table == 'episodes') {
        array_push($del_array, $table . '_stream');
    }
    foreach ($del_array as $t) {
        $delete_sql = 'DELETE FROM `' . $t . '` WHERE `id` IN ("' . implode('", "', $id) . '")';
        $delete = mysql_q($delete_sql);
    }
    
    # delete images
    $files_to_remove = array();
    $files = scandir('cache/');
    foreach ($id as $i) {
        foreach($files as $file) {
            $match = preg_match('/^' . $table . '_' . $i . '[_\.]/', $file);
            if ($match == 1) {
                $files_to_remove[] = $file;
            }
        }
    }
    remove_images($files_to_remove);
}

/* ##################
 * # Clean database #
 */##################
function clean_db() {
    $clean_array = array('movies_actor', 'movies_country', 'movies_director', 'movies_genre', 'movies_studio', 'movies_stream', 'tvshows_actor', 'tvshows_genre', 'episodes_stream');
    foreach ($clean_array as $table) {
        $split = explode('_', $table);
        $video = $split[0];
        $panel = $split[1];
        # delete from video_panel not existing id in movies or tvshow table
        $clean_sql = 'DELETE FROM `' . $table . '` WHERE `id` NOT IN (SELECT `' . $video . '`.`id` FROM `' . $video . '`)';
        mysql_q($clean_sql);
        # delete from video_panel not existing id in panel table
        if ($panel != 'stream') {
            $clean_sql = 'DELETE FROM `' . $table . '` WHERE `' . $panel . 'id` NOT IN (SELECT `' . $panel . '`.`id` FROM `' . $panel . '`)';
            mysql_q($clean_sql);
        }
        # delete from panel not existing id in video_panel table
        if ($video == 'movies' && $panel != 'stream') {
            $clean_sql = 'DELETE FROM `' . $panel . '` WHERE `id` NOT IN (SELECT `movies_' . $panel . '`.`' . $panel . 'id` FROM `movies_' . $panel . '`)';
            if ($panel == 'actor' or $panel == 'genre') {
                $clean_sql.= ' AND `id` NOT IN (SELECT `tvshows_' . $panel . '`.`' . $panel . 'id` FROM `tvshows_' . $panel . '`)';
            }
            mysql_q($clean_sql);
        }
    }
}

/* ##############
 * # Reset hash #
 */##############
function reset_hash() {
    $reset_sql = 'UPDATE `hash` SET `movies` = "", `tvshows` = "", `episodes` = "", `images` = ""';
    mysql_q($reset_sql);
}

/* ################
 * # Change Token #
 */################
function change_token() {
    $array = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',0,1,2,3,4,5,6,7,8,9);
    $new_token = '';
    for ($i = 1; $i <= 6; $i++) {
        $new_token.= $array[array_rand($array)];
    }
    $update_sql = 'UPDATE `config` SET `token` = "' . $new_token . '"';
    $update = mysql_q($update_sql);
    $_SESSION['token'] = $new_token;
    return $new_token;
}

/* #################
 * # GD conversion #
 */#################
function gd_convert($cache_path, $img_link, $new_width, $new_height) {
    if (!file_exists($cache_path) and !empty($img_link)) {
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
    
    $sep_tab = array('actor', 'genre', 'country', 'studio', 'director');
    $panels_array = array();
    foreach ($columns as $val) {
        if (in_array($val, $sep_tab)) {
            $sel = 'SELECT DISTINCT ' . $val . '.id, ' . $val . '.' . $val . ' FROM `' . $val . '`, `' . $table . '_' . $val . '` WHERE ' . $val . '.id=' . $table . '_' . $val . '.' . $val . 'id ORDER BY ' . $val . '.' . $val;
            $res = mysql_q($sel);
            while ($r = mysql_fetch_assoc($res)) {
                $panels_array[$val][$r['id']] = $r[$val];
            }
        } else {
            $sel = 'SELECT DISTINCT `' . $val . '` FROM `' . $table . '` WHERE `hide` = 0 ORDER BY `' . $val . '`';
            $res = mysql_q($sel);
            if (mysql_num_rows($res) > 0) {
                while ($r = mysql_fetch_assoc($res)) {
                    if ($r[$val] != '') {
                        $panels_array[$val][] = $r[$val];
                    }
                }
            }
        }
    }
    if (isset($panels_array['year'])) { rsort($panels_array['year']); }
    
    return $panels_array;
}

/* ##############
 * # CREATE URL #
 */##############
function create_url($setting, $urls) {
    if ($setting['mod_rewrite'] == 1 && array_key_exists('HTTP_MOD_REWRITE', $_SERVER)) {
        $index = 'index,';
        $p = '-';
        $c = ',';
        $end = '.html';
    } else {
        $index = 'index.php?';
        $p = '=';
        $c = '&';
        $end = '';
    }
    $pair = array();
    foreach ($urls as $k => $v) {
        if (strlen($v) > 0) {
            $pair[] = $k . $p . $v;
        }
    }
    return $index . implode($c, $pair) . $end;
}

/* ######################
 * # AUTO CONFIG REMOTE #
 */######################
function auto_conf_remote($s) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $xbmc_update_sql = 'UPDATE `config` SET 
        `xbmc_host` = "' . $ip . '", 
        `xbmc_port` = "' . $s['webserverport'] . '",
        `xbmc_login` = "' . $s['webserverusername'] . '", 
        `xbmc_pass` = "' . $s['webserverpassword'] . '"';
    mysql_q($xbmc_update_sql);
    $_SESSION = array();
}

/* #################
 * # CREATE BANNER #
 */#################
function create_banner($lang, $file, $data) {
    
    $movie_sql = 'SELECT `id`, `title`, `originaltitle`, `rating`, `runtime`, `year`, `last_played` FROM `movies` ORDER BY `last_played` DESC LIMIT 0, 1';
    $movie_result = mysql_q($movie_sql);
    $movie = mysql_fetch_assoc($movie_result);
    
    $episode_sql = 'SELECT `episode`, `season`, `tvshow`, `title`, `last_played` FROM `episodes` ORDER BY `last_played` DESC LIMIT 0, 1';
    $episode_result = mysql_q($episode_sql);
    $episode = mysql_fetch_assoc($episode_result);
    $episode['e_title'] = $episode['title'];
    unset($episode['title']);
    
    if (isset($episode['last_played']) && $episode['last_played'] > $movie['last_played']) {
        $tvshow_sql = 'SELECT `id`, `title`, `originaltitle`, `rating`, `last_played` FROM `tvshows` WHERE `id` = ' . $episode['tvshow'];
        $tvshow_result = mysql_q($tvshow_sql);
        $tvshow = mysql_fetch_assoc($tvshow_result);
        $ban = array_merge($tvshow, $episode);
        $table = 'tvshows';
        $panels_array = array('genre');
    } else {
        $ban = $movie;
        $table = 'movies';
        $panels_array = array('genre', 'country');
    }
    
    if(isset($ban['id'])) {
        foreach ($panels_array as $val) {
            $sel_sql = 'SELECT ' . $val . '.' . $val . ' FROM ' . $val . ', ' . $table . '_' . $val . ' WHERE ' . $val . '.id = ' . $table . '_' . $val . '.' . $val . 'id AND ' . $table . '_' . $val . '.id = "' . $ban['id'] . '"';
            $sel_res = mysql_q($sel_sql);
            $out = array();
            while ($s = mysql_fetch_row($sel_res)) {
                $out[] = $s[0];
            }
            $ban[$val] = implode(' / ', $out);
        }
    }
    
    $b = array();
    $b['w']     = 400; // banner width
    $b['h']     = 70; // banner height
    $b['bg_c']  = '141414'; // background color
    $b['lw_c']  = 'FFFFFF'; // last watched color
    $b['lw_s']  = 10; // last watched font size
    $b['lw_x']  = 130; // last watched pos. x
    $b['lw_y']  = 20; // last watched pos. y
    $b['t_c']   = 'FFFFFF'; // title color
    $b['t_s']   = 8; // title font size
    $b['t_x']   = 136; // title pos. x
    $b['t_y']   = 36; // title pos. y
    $b['o_c']   = 'AAAAAA'; // title color
    $b['o_s']   = 8; // title font size
    $b['o_x']   = 136; // title pos. x
    $b['o_y']   = 51; // title pos. y
    $b['i_c']   = '808080'; // info color
    $b['i_s']   = 6; // info font size
    $b['i_x']   = 130; // info pos. x
    $b['i_y']   = 63; // info pos. y
    $b['st_c']  = '000000'; // stroke color
    $b['b_c']   = 'FFFFFF'; // border color

    if ($data !== '0') {
        $banner_array = explode(';', $data);
        $banner = array();
        foreach ($banner_array as $val) {
            $i = explode(':', $val);
            $banner[$i[0]] = $i[1];
        }
        $b = $banner;
    }
    
    $bg_c = hex2rgb($b['bg_c']);
    $lw_c = hex2rgb($b['lw_c']);
    $t_c  = hex2rgb($b['t_c']);
    $o_c  = hex2rgb($b['o_c']);
    $i_c  = hex2rgb($b['i_c']);
    $st_c = hex2rgb($b['st_c']);
    $b_c  = hex2rgb($b['b_c']);
        
    $font = 'admin/css/font/archivonarrow.ttf';

    // background
    $banner = imagecreatetruecolor($b['w'], $b['h']);
    $bg_color = imagecolorallocate($banner, $bg_c['r'], $bg_c['g'], $bg_c['b']);
    imagefill($banner, 0, 0, $bg_color);

    // get poster and copy
    if (file_exists('cache/' . $table . '_' . $ban['id'] . '_f.jpg')) {
        $post = imagecreatefromjpeg('cache/' . $table . '_' . $ban['id'] . '_f.jpg');
    } elseif (file_exists('cache/' . $table . '_' . $ban['id'] . '.jpg')) {
        $post = imagecreatefromjpeg('cache/' . $table . '_' . $ban['id'] . '.jpg');
    } else {
        $post = imagecreatefromjpeg('templates/default/img/d_poster.jpg');
    }
    $width = imagesx($post);
    $height = imagesy($post);
    $new_height = $b['h'];
    $new_width = $width / ($height / $new_height);
    imagecopyresampled($banner, $post, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // add gradient
    $width = $b['h'];
    $gradient = imagecreatetruecolor($width, $b['h']);
    $gradient_color = imagecolorallocatealpha($gradient, $bg_c['r'], $bg_c['g'], $bg_c['b'], 127);
    imagefill($gradient, 0, 0, $gradient_color);
    for ($x=0; $x < $width; ++$x) {
        $alpha = 127 - $x*(127/$width);
        $gradient_color = imagecolorallocatealpha($gradient, $bg_c['r'], $bg_c['g'], $bg_c['b'], $alpha);
        imageline($gradient, $x, 0, $x, $b['h'], $gradient_color);
    }
    imagecopyresampled($banner, $gradient, $new_width-$width, 0, 0, 0, $width, $b['h'], $width, $b['h']);

    // add text
    $last_watched_color = imagecolorallocate($banner, $lw_c['r'], $lw_c['g'], $lw_c['b']);
    $title_color = imagecolorallocate($banner, $t_c['r'], $t_c['g'], $t_c['b']);
    $o_title_color = imagecolorallocate($banner, $o_c['r'], $o_c['g'], $o_c['b']);
    $info_color = imagecolorallocate($banner, $i_c['r'], $i_c['g'], $i_c['b']);
    $stroke_color = imagecolorallocate($banner, $st_c['r'], $st_c['g'], $st_c['b']);
    imagettfstroketext($banner, $b['lw_s'], 0, $b['lw_x'], $b['lw_y'], $last_watched_color, $stroke_color, $font, $lang['i_last_played'], 1);
    imagettfstroketext($banner, $b['t_s'], 0, $b['t_x'], $b['t_y'], $title_color, $stroke_color, $font, 
        (isset($ban['title']) ? $ban['title'] : '') . 
        (isset($ban['season']) ? ' - ' . $ban['season'] . 'x' : '') . 
        (isset($ban['episode']) ? $ban['episode'] . ' ' : '') . 
        (isset($ban['e_title']) ? $ban['e_title'] : '')
        , 1);
    imagettfstroketext($banner, $b['o_s'], 0, $b['o_x'], $b['o_y'], $o_title_color, $stroke_color, $font, (isset($ban['originaltitle']) ? $ban['originaltitle'] : ''), 1);
    imagettfstroketext($banner, $b['i_s'], 0, $b['i_x'], $b['i_y'], $info_color, $stroke_color, $font, 
        (isset($ban['year']) ? $ban['year'] : '') . ' | ' . 
        (isset($ban['rating']) ? $ban['rating'] : '') . ' | ' . 
        (isset($ban['runtime']) ? $ban['runtime'] . ' ' . $lang['i_minute'] : '') . ' | ' . 
        (isset($ban['genre']) ? $ban['genre'] : '') . ' | ' . 
        (isset($ban['country']) ? $ban['country'] : '')
        , 1);

    // icon
    $icon = imagecreatefrompng('admin/img/' . $table . '.png');
    imagecopy($banner, $icon, $b['w']-26, 6, 0, 0, 18, 18);

    // border
    $border_color = imagecolorallocate($banner, $b_c['r'], $b_c['g'], $b_c['b']);
    imageline($banner, 0, 0, $b['w']-1, 0, $border_color);
    imageline($banner, $b['w']-1, 0, $b['w']-1, $b['h']-1, $border_color);
    imageline($banner, 0, $b['h']-1, $b['w']-1, $b['h']-1, $border_color);
    imageline($banner, 0, 0, 0, $b['h']-1, $border_color);

    // save as file
    imagejpeg($banner, 'cache/' . $file, 100);
    return $b;
}

/* ################
 * # BANNER 2 STR #
 */################
function banner2str($array) {
    $banner = '';
    foreach ($array as $key => $val) {
        $banner.= $key . ':' . strtoupper($val) . ';';
    }
    return substr($banner, 0, -1);
}

/* #############
 * # HEX 2 RGB #
 */#############
function hex2rgb($hex) {
    $match = preg_match('/^[0-9abcdefABCDEF]{6}$/', $hex);
    if ($match == true) {
        $rgb = str_split($hex, 2);
        $rgb = array('r' => hexdec($rgb[0]), 'g' => hexdec($rgb[1]), 'b' => hexdec($rgb[2]));
        return $rgb;
    } else {
        return False;
    }
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

/* #################
 * # ADD 0 TO LEFT #
 */#################
function zero($dig) {
    return str_pad($dig, 2, 0, STR_PAD_LEFT);
}

/* #################
 * # LANGUAGE FLAG #
 */#################
function check_flag($f, $iso_lang) {
    foreach ($iso_lang as $k => $v) {
        if (in_array($f, $v)) {
            return $k;
        }
    }
}

?>