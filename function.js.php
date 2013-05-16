<?php
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
?>