<?PHP
require_once 'function.php';

// Default language
$set_language = 'lang_en.php'; // The file that contains the language, file must be in the lang/ folder
require 'lang/' . $set_language;

if(!file_exists('db.php')) {
    if(!file_exists('install.php') {
        die('Installation file not exists!');
    } else {
        header('Location:install.php');
    }
}

// MovieLib database
$mysql_host_ml = '127.0.0.1'; // Database host
$mysql_port_ml = '3306'; // Database port, default is 3306
$mysql_login_ml = 'root'; // Database login
$mysql_pass_ml = 'vertrigo'; // Database password
$mysql_database_ml = 'movielib'; // Database name

// Database config to array
$mysql_ml = array($mysql_host_ml, $mysql_port_ml, $mysql_login_ml, $mysql_pass_ml, $mysql_database_ml);

// Tables
$mysql_table_ml = 'movies'; // Table name to create
$mysql_config_ml = 'config'; // Table name to create
$mysql_users_ml = 'users'; // Table name to create
$mysql_tables = array($mysql_table_ml, $mysql_config_ml, $mysql_users_ml);

// XBMC database column
$col['id_movie']        =   'idMovie';
$col['id_file']         =   'idFile';
$col['title']           =   'c00';
$col['plot']            =   'c01';
$col['outline']         =   'c02';
$col['tagline']         =   'c03';
$col['votes']           =   'c04';
$col['rating']          =   'c05';
$col['credits']         =   'c06';
$col['year']            =   'c07';
$col['poster']          =   'c08';
$col['imdb_id']         =   'c09';
$col['title_format']    =   'c10';
$col['runtime']         =   'c11';
$col['mpaa']            =   'c12';
$col['top250']          =   'c13';
$col['genre']           =   'c14';
$col['director']        =   'c15';
$col['originaltitle']   =   'c16';
$col['thumb_url']       =   'c17';
$col['studio']          =   'c18';
$col['trailer']         =   'c19';
$col['fanart']          =   'c20';
$col['country']         =   'c21';
$col['file_path']       =   'c22';
$col['id_path']         =   'c23';




$conn_ml = @mysql_connect($mysql_ml[0] . ':' . $mysql_ml[1], $mysql_ml[2], $mysql_ml[3]);
if (!$conn_ml) {
    die(mysql_error());
}
$sel_ml = mysql_select_db($mysql_ml[4]);
if (!$sel_ml) {
    $create_db_sql = 'CREATE DATABASE ' . $mysql_database_ml;
    $create_db_result = mysql_query($create_db_sql);
    if (!$create_db_result) {
        die(mysql_error());
    } else {
        $sel_ml = mysql_select_db($mysql_ml[4]);
    }
}

// Sets utf8 connections
mysql_query('SET CHARACTER SET utf8');
mysql_query('SET NAMES utf8');

// Check tables in database
$table_sql = 'SHOW TABLES';
$table_result = mysql_query($table_sql);
while ($table = mysql_fetch_array($table_result)) {
    $table_check[] = $table[0];

}
foreach ($mysql_tables as $table_val) {
    if (!in_array($table_val, $table_check)) {
        $output_sync = create_table($col, $mysql_table_ml, $mysql_config_ml, $lang);
    }
}
// Get Settings from sql
if (!isset($_SESSION['set_site_name'])) {
    $set_sql = 'SELECT * FROM config';
    $set_result = mysql_query($set_sql);
    while ($set = mysql_fetch_array($set_result)) {
        $_SESSION['set_mode']               = $set['set_mode']; // 1 - Synchronize witch XBMC database, 2 - Synchronize witch videodb.xml file
        $_SESSION['set_site_name']          = $set['set_site_name']; // Site title
        $_SESSION['set_language']           = $set['set_language']; // The file that contains the language, file must be in the lang/ folder
        $_SESSION['set_per_page']           = $set['set_per_page']; // Movies per page
        $_SESSION['set_recently_limit']     = $set['set_recently_limit']; // Movies in recently added panel
        $_SESSION['set_random_limit']       = $set['set_random_limit']; // Movies in random panel
        $_SESSION['set_last_played_limit']  = $set['set_last_played_limit']; // Movies in last played panel
        $_SESSION['set_top_rated_limit']    = $set['set_top_rated_limit']; // Movies in top rated panel
        $_SESSION['set_sync_time']          = $set['set_sync_time']; // Time in minutes after which the script will attempt to synchronize databases
        $_SESSION['set_panel_top_time']     = $set['set_panel_top_time']; // Time in second to change displayed item
        $_SESSION['set_panel_top']          = $set['set_panel_top']; // Show top panel
        $_SESSION['set_watched_status']     = $set['set_watched_status']; // Show watched status
        $_SESSION['set_overall_panel']      = $set['set_overall_panel']; // Show overall panel
        $_SESSION['set_protect_site']       = $set['set_protect_site']; // Protect access to site
        $_SESSION['set_mysql_host_xbmc']    = $set['set_mysql_host_xbmc']; // Database host
        $_SESSION['set_mysql_port_xbmc']    = $set['set_mysql_port_xbmc']; // Database port, default is 3306
        $_SESSION['set_mysql_login_xbmc']   = $set['set_mysql_login_xbmc']; // Database login
        $_SESSION['set_mysql_pass_xbmc']    = $set['set_mysql_pass_xbmc']; // Database password
        $_SESSION['set_mysql_database_xbmc']= $set['set_mysql_database_xbmc']; // Database name
    }
}
$set_mode                   = $_SESSION['set_mode']; // 1 - Synchronize witch XBMC database, 2 - Synchronize witch videodb.xml file
$set_site_name              = $_SESSION['set_site_name']; // Site title
$set_language               = $_SESSION['set_language']; // The file that contains the language, file must be in the lang/ folder
$set_per_page               = $_SESSION['set_per_page']; // Movies per page
$set_recently_limit         = $_SESSION['set_recently_limit']; // Movies in recently added panel
$set_random_limit           = $_SESSION['set_random_limit']; // Movies in random panel
$set_last_played_limit      = $_SESSION['set_last_played_limit']; // Movies in last played panel
$set_top_rated_limit        = $_SESSION['set_top_rated_limit']; // Movies in top rated panel
$set_sync_time              = $_SESSION['set_sync_time']; // Time in minutes after which the script will attempt to synchronize databases
$set_panel_top_time         = $_SESSION['set_panel_top_time']; // Time in second to change displayed item
$set_panel_top              = $_SESSION['set_panel_top']; // Show top panel
$set_watched_status         = $_SESSION['set_watched_status']; // Show watched status
$set_overall_panel          = $_SESSION['set_overall_panel']; // Show overall panel
$set_protect_site           = $_SESSION['set_protect_site']; // Protect access to site
$set_mysql_host_xbmc        = $_SESSION['set_mysql_host_xbmc']; // Database host
$set_mysql_port_xbmc        = $_SESSION['set_mysql_port_xbmc']; // Database port, default is 3306
$set_mysql_login_xbmc       = $_SESSION['set_mysql_login_xbmc']; // Database login
$set_mysql_pass_xbmc        = $_SESSION['set_mysql_pass_xbmc']; // Database password
$set_mysql_database_xbmc    = $_SESSION['set_mysql_database_xbmc']; // Database name

$set_protect_site_pass = 'b27bfe5ba5bec17f80de30b9f23ff658'; // Type password in md5 to protect access to site.
$set_admin_panel_pass = 'b27bfe5ba5bec17f80de30b9f23ff658'; // Type password in md5 to admin panel

// XBMC database to array
$mysql_xbmc = array($set_mysql_host_xbmc, $set_mysql_port_xbmc, $set_mysql_login_xbmc, $set_mysql_pass_xbmc, $set_mysql_database_xbmc);

// Language 
require 'lang/' . $set_language;

// Dir
$dir_assoc = array('import', 'cache');



// Video resolution
$vres_array = array('sd', 480, 576, 540, 720, 1080);
$width_height = array(0 => 0, 720 => 480, 768 => 576, 960 => 544, 1280 => 720, 1920 => 1080);

// Video codec
$vtype = array(
    '3iv2' => '3ivx',
    '3ivd' => '3ivx',
    '3ivx' => '3ivx',
    '8bps' => 'qt',
    'advj' => 'qt',
    'avrn' => 'qt',
    'rle' => 'qt',
    'rpza' => 'qt',
    'smc' => 'qt',
    'sv10' => 'qt',
    'svq' => 'qt',
    'qt' => 'qt',
    'zygo' => 'qt',
    'avc' => 'avc',
    'avc1' => 'avc1',
    'dca' => 'dts',
    'div1' => 'divx',
    'div2' => 'divx',
    'div3' => 'divx',
    'div4' => 'divx',
    'div5' => 'divx',
    'div6' => 'divx',
    'divx' => 'divx',
    'dm4v' => 'mpeg4',
    'dx50' => 'mpeg4',
    'geox' => 'mpeg4',
    'm4s2' => 'mpeg4',
    'mpeg4' => 'mpeg4',
    'mpeg-4' => 'mpeg4',
    'nds' => 'mpeg4',
    'ndx' => 'mpeg4',
    'pvmm' => 'mpeg4',
    'em2v' => 'mpeg2',
    'lmp2' => 'mpeg2',
    'mmes' => 'mpeg2',
    'mpeg-2' => 'mpeg2',
    'mpeg2' => 'mpeg2',
    'flv' => 'flv',
    'h264' => 'h264',
    'mp4' => 'mp4',
    'mpeg' => 'mpeg',
    'pim1' => 'mpeg',
    'vc1' => 'vc1',
    'wvc1' => 'vc1',
    'wmv' => 'wmv',
    'wmva' => 'wmva',
    'xvid' => 'xvid',
    'xvix' => 'xvid'
);

// Audio codec
$atype = array(
    'a_vorbis' => 'ogg',
    'ogg' => 'ogg',
    'vorbis' => 'ogg',
    'aac' => 'aac',
    'ac3' => 'ac3',
    'aif' => 'aif',
    'aifc' => 'aifc',
    'aiff' => 'aiff',
    'ape' => 'ape',
    'dca' => 'dts',
    'dts' => 'dts',
    'dd' => 'dd',
    'dolbydigital' => 'dd',
    'dtshr' => 'dtshd',
    'dtsma' => 'dtshd',
    'dtshd' => 'dtshd',
    'flac' => 'flac',
    'mp1' => 'mp1',
    'mp2' => 'mp2',
    'mp3' => 'mp3',
    'truehd' => 'truehd',
    'wma' => 'wma',
    'wmav2' => 'wma',
    'wmahd' => 'wmahd',
    'wmapro' => 'wmapro'
);

// Audio channel
$achan = array(
    '1' => '1',
    '2' => '2',
    '6' => '6',
    '8' => '8'
);

// Set var
$var = array(
    'sort' => 1,
    'genre' => 'all',
    'search' => '',
    'page' => 1,
    'output' => ''
    );
foreach ($var as $key => $val) {
    if (isset($_GET[$key])) {
        $$key = $_GET[$key];
    } else {
        $$key = $val;
    }
}

// Set id
if (!isset($_GET['id'])) {
    $id = 0;
    $id_mysql = '%';
} else {
    $id = $_GET['id'];
    $id_mysql = $_GET['id'];
}


?>