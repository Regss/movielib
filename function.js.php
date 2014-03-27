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
if ($option == 'searchmovie') {
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $set = get_settings($mysql_tables);
    $json_assoc = array();
    $search_sql = 'SELECT id, title, rating, year, runtime, genre, director, originaltitle, country FROM ' . $mysql_tables[0] . ' WHERE (title LIKE "%' . $_GET['search'] . '%" OR originaltitle LIKE "%' . $_GET['search'] . '%") LIMIT 0, ' . $_SESSION['live_search_max_res'];
    $search_res = mysql_query($search_sql);
    while($searched = mysql_fetch_assoc($search_res)) {
        if (file_exists('cache/' . $mysql_tables[0] . '_' . $searched['id'] . '.jpg')) {
            $searched['poster'] = 'cache/' . $mysql_tables[0] . '_' . $searched['id'] . '.jpg';
        } else {
            $searched['poster'] = 'templates/' . $set['theme'] . '/img/d_poster.jpg';
        }
        $json_assoc[] = $searched;
    }
    echo json_encode($json_assoc);
}

if ($option == 'searchtvshow') {
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $set = get_settings($mysql_tables);
    $json_assoc = array();
    $search_sql = 'SELECT id, title, rating, genre, originaltitle FROM ' . $mysql_tables[1] . ' WHERE (title LIKE "%' . $_GET['search'] . '%" OR originaltitle LIKE "%' . $_GET['search'] . '%") LIMIT 0, ' . $_SESSION['live_search_max_res'];
    $search_res = mysql_query($search_sql);
    while($searched = mysql_fetch_assoc($search_res)) {
        if (file_exists('cache/' . $mysql_tables[1] . '_' . $searched['id'] . '.jpg')) {
            $searched['poster'] = 'cache/' . $mysql_tables[1] . '_' . $searched['id'] . '.jpg';
        } else {
            $searched['poster'] = 'templates/' . $set['theme'] . '/img/d_poster.jpg';
        }
        $json_assoc[] = $searched;
    }
    echo json_encode($json_assoc);
}

// delete movie
if ($option  == 'deletemovie') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $id = $_GET['id'];
    $delete_movie_sql = 'DELETE FROM ' . $mysql_tables[0] . ' WHERE id = "' . $id . '"';
    if (file_exists('cache/' . $mysql_tables[0] . '_' . $id . '.jpg')) {
        unlink('cache/' . $mysql_tables[0] . '_' . $id . '.jpg');
    }
    if (file_exists('cache/' . $mysql_tables[0] . '_' . $id . '_f.jpg')) {
        unlink('cache/' . $mysql_tables[0] . '_' . $id . '_f.jpg');
    }
    mysql_query($delete_movie_sql);
}

// delete tvshow
if ($option  == 'deletetvshow') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $id = $_GET['id'];
    $delete_movie_sql = 'DELETE FROM ' . $mysql_tables[1] . ' WHERE id = "' . $id . '"';
    if (file_exists('cache/' . $mysql_tables[1] . '_' . $id . '.jpg')) {
        unlink('cache/' . $mysql_tables[1] . '_' . $id . '.jpg');
    }
    if (file_exists('cache/' . $mysql_tables[1] . '_' . $id . '_f.jpg')) {
        unlink('cache/' . $mysql_tables[1] . '_' . $id . '_f.jpg');
    }
    mysql_query($delete_movie_sql);
    $delete_movie_sql = 'DELETE FROM ' . $mysql_tables[2] . ' WHERE tvshow = "' . $id . '"';
    mysql_query($delete_movie_sql);
}
?>