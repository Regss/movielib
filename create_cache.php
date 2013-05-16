<?php
$fanart_cache = 'cache/' . $_GET['id'] . '_f.jpg';
if (!file_exists($fanart_cache)) {
    include 'config.php';
    include 'function.php';
    connect($mysql_ml);
    $sql = 'SELECT id, fanart FROM ' . $mysql_tables[0] . ' WHERE id = "' . $_GET['id'] . '"';
    $result = mysql_query($sql);
    while ($fanart = mysql_fetch_array($result)) {
        $fanart_link = $fanart['fanart'];
    }
    $img = @imagecreatefromjpeg($fanart_link);
    if ($img) {
        $width = imagesx($img);
        $height = imagesy($img);
        $new_width = 1024;
        $new_height = 768;
        $img_temp = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($img_temp, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagejpeg($img_temp, $fanart_cache, 80);
    }
}
?>
