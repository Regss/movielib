<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

require('config.php');

// get setting for js
if ($option == 'settings') {
    require('function.php');
    $set = get_settings($mysql_tables);
    $set_js = array(
        'show_fanart'       => $set['show_fanart'],
        'fadeout_fanart'    => $set['fadeout_fanart'],
        'panel_top_time'    => $set['panel_top_time']
    );
    echo json_encode($set_js);
}

// save panel status
if ($option == 'panel') {
    echo $_GET['id'] . ' - ' . $_GET['opt'];
    $_SESSION[$_GET['id']] = $_GET['opt'];
    $set[$_GET['id']] = $_GET['opt'];
}

// live search
if ($option == 'searchmovie' or $option == 'searchtvshow') {
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $set = get_settings($mysql_tables);
    $json_assoc = array();
    if ($option == 'searchmovie') {
        $table = $mysql_tables[0];
        $search_sql = 'SELECT id, title, rating, year, runtime, genre, director, originaltitle, country, hide FROM ' . $table . ' WHERE (title LIKE "%' . $_GET['search'] . '%" OR originaltitle LIKE "%' . $_GET['search'] . '%") AND hide=0 LIMIT 0, ' . $_SESSION['live_search_max_res'];
    } else {
        $table = $mysql_tables[1];
        $search_sql = 'SELECT id, title, rating, genre, originaltitle, hide FROM ' . $table . ' WHERE (title LIKE "%' . $_GET['search'] . '%" OR originaltitle LIKE "%' . $_GET['search'] . '%") AND hide=0 LIMIT 0, ' . $_SESSION['live_search_max_res'];
    }
    $search_res = mysql_query($search_sql);
    while($searched = mysql_fetch_assoc($search_res)) {
        if (file_exists('cache/' . $table . '_' . $searched['id'] . '.jpg')) {
            $searched['poster'] = 'cache/' . $table . '_' . $searched['id'] . '.jpg';
        } else {
            $searched['poster'] = 'templates/' . $set['theme'] . '/img/d_poster.jpg';
        }
        $json_assoc[] = $searched;
    }
    echo json_encode($json_assoc);
}

// remote control
if ($option  == 'remote') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    require('config.php');
    $f = $_GET['f'];
    $json = urlencode('{"jsonrpc": "2.0", "params": {' . $json_f[$f]['p'] . '}, "method": "' . $json_f[$f]['m'] . '", "id": 1}');
    $get = file_get_contents('http://xbmc:xbmc@192.168.1.201:8080/jsonrpc?request=' . $json);
    file_put_contents('ble.txt', $json . "\n\r" . $get);
}

// delete movie or tvshow
if ($option  == 'deletemovie' or $option  == 'deletetvshow') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    require('config.php');
    require('function.php');
    $id = $_GET['id'];
    if ($option  == 'deletemovie') {
        $table = $mysql_tables[0];
    } else {
        $table = $mysql_tables[1];
        $delete_sql = 'DELETE FROM ' . $table . ' WHERE tvshow = "' . $id . '"';
        mysql_query($delete_sql);
    }
    connect($mysql_ml);
    $delete_sql = 'DELETE FROM ' . $table . ' WHERE id = "' . $id . '"';
    if (file_exists('cache/' . $table . '_' . $id . '.jpg')) {
        unlink('cache/' . $table . '_' . $id . '.jpg');
    }
    if (file_exists('cache/' . $table . '_' . $id . '_f.jpg')) {
        unlink('cache/' . $table . '_' . $id . '_f.jpg');
    }
    mysql_query($delete_sql);
}

// hide movie or tvshow
if ($option  == 'hidemovie' or $option  == 'hidetvshow' or $option  == 'visiblemovie' or $option  == 'visibletvshow') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    require('config.php');
    require('function.php');
    $id = $_GET['id'];
    if($option  == 'hidemovie' or $option  == 'hidetvshow') {
        $hide = 1;
    } else {
        $hide = 0;
    }
    if($option  == 'hidemovie' or $option  == 'visiblemovie') {
        $table = $mysql_tables[0];
    } else {
        $table = $mysql_tables[1];
    }
    connect($mysql_ml);
    $hide_sql = 'UPDATE `' . $table . '` SET hide = ' . $hide . ' WHERE id = "' . $id . '"';
    mysql_query($hide_sql);
}

// banner
if ($option  == 'banner') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $set = get_settings($mysql_tables);
    require('lang/' . $set['language'] . '/lang.php');
    $b = create_banner($lang, 'banner_v.jpg', $_GET['banner'], $mysql_tables);
}

?>