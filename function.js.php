<?PHP
session_start();
require('config.php');

// save panel status
if ($option == 'panel') {
    echo $_GET['id'] . ' - ' . $_GET['opt'];
    $_SESSION[$_GET['id']] = $_GET['opt'];
    $set[$_GET['id']] = $_GET['opt'];
}

// live search
if ($option == 'search') {
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $json_assoc = array();
    $search_sql = 'SELECT id, title, plot, rating, year, runtime, genre, director, originaltitle, country FROM ' . $mysql_tables[0] . ' WHERE (title LIKE "%' . $_GET['search'] . '%" OR originaltitle LIKE "%' . $_GET['search'] . '%") LIMIT 0, ' . $_SESSION['live_search_max_res'];
    $search_res = mysql_query($search_sql);
    while($searched = mysql_fetch_assoc($search_res)) {
        $json_assoc[] = $searched;
    }
    echo json_encode($json_assoc);
}

// delete movie
if ($option  == 'delete') {
    // admin permission
    if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
        die('no permission');
    }
    require('config.php');
    require('function.php');
    connect($mysql_ml);
    $id = $_GET['id'];
    $delete_movie_sql = 'DELETE FROM ' . $mysql_tables[0] . ' WHERE id = "' . $id . '"';
    if (file_exists('cache/' . $id . '.jpg')) {
        unlink('cache/' . $id . '.jpg');
    }
    if (file_exists('cache/' . $id . '_f.jpg')) {
        unlink('cache/' . $id . '_f.jpg');
    }
    mysql_query($delete_movie_sql);
}
?>