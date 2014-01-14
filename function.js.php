<?PHP
session_start();
require('config.php');

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