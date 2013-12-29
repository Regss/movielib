<?PHP
session_start();
require_once 'config.php';

// create cache for background
if ($option == 'bg') {
    $fanart_cache = 'cache/' . $_GET['id'] . '_f.jpg';
    if (!file_exists($fanart_cache)) {
        include 'config.php';
        include 'function.php';
        connect($mysql_ml);
        $fanart_sql = 'SELECT id, fanart FROM ' . $mysql_tables[0] . ' WHERE id = "' . $_GET['id'] . '"';
        $fanart_result = mysql_query($fanart_sql);
        while ($fanart = mysql_fetch_array($fanart_result)) {
            $fanart_link = $fanart['fanart'];
        }
        gd_convert($fanart_cache, $fanart_link, 1280, 720);
    }
}

// save panel status
if ($option == 'panel') {
    echo $_GET['id'] . ' - ' . $_GET['opt'];
    $_SESSION[$_GET['id']] = $_GET['opt'];
    $set[$_GET['id']] = $_GET['opt'];
}

// admin permission
if (!isset($_SESSION['logged_admin']) or $_SESSION['logged_admin'] !== true) {
    die('no permission');
}

// delete movie
if ($option  == 'delete') {
    include 'config.php';
    include 'function.php';
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