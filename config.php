<?PHP
if (file_exists('db.php')) {
    include_once 'db.php';
}

// Tables to array
$mysql_tables = array('movies', 'config', 'users');

// Config name to array
$settings_name = array('mode', 'site_name', 'language', 'theme', 'per_page', 'recently_limit', 'random_limit', 'last_played_limit', 'top_rated_limit', 'sync_time', 'panel_top_time', 'panel_top', 'watched_status', 'overall_panel', 'show_fanart', 'protect_site', 'mysql_host_xbmc', 'mysql_port_xbmc', 'mysql_login_xbmc', 'mysql_pass_xbmc', 'mysql_database_xbmc');

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

// Dir
$dir_assoc = array('import', 'cache');

// Output panel
$output_panel_info = '';

// Video resolution
$vres_assoc = array(0, 480, 576, 540, 720, 768, 1080);

// Video codec
$vtype_assoc['3ivx']    =   array('3ivx', '3iv2', '3ivd');
$vtype_assoc['qt']      =   array('qt', '8bps', 'advj', 'avrn', 'rle', 'rpza', 'smc', 'sv10', 'svq', 'zygo');
$vtype_assoc['avc']     =   array('avc', 'avc1');
$vtype_assoc['divx']    =   array('divx', 'div1', 'div2', 'div3', 'div4', 'div5', 'div6');
$vtype_assoc['mpeg4']   =   array('mpeg4', 'dm4v', 'dx50', 'geox', 'm4s2', 'mpeg-4', 'nds', 'ndx', 'pvmm');
$vtype_assoc['mpeg2']   =   array('mpeg2', 'em2v', 'lmp2', 'mmes', 'mpeg-2');
$vtype_assoc['flv']     =   array('flv');
$vtype_assoc['h264']    =   array('h264');
$vtype_assoc['mp4']     =   array('mp4');
$vtype_assoc['mpeg']    =   array('mpeg', 'pim1');
$vtype_assoc['wmv']     =   array('wmv', 'wma');
$vtype_assoc['xvid']    =   array('xvid', 'xvix');

// Audio codec
$atype_assoc['ogg']     =   array('ogg', 'a_vorbis', 'vorbis');
$atype_assoc['aac']     =   array('aac');
$atype_assoc['ac3']     =   array('ac3');
$atype_assoc['aif']     =   array('aif', 'aifc', 'aiff');
$atype_assoc['dts']     =   array('dts', 'dca');
$atype_assoc['dd']      =   array('dd', 'dtshd', 'dtsma', 'dtshr');
$atype_assoc['flac']    =   array('flac');
$atype_assoc['mp3']     =   array('mp3', 'mp2', 'mp1');
$atype_assoc['truehd']  =   array('truehd');
$atype_assoc['wma']     =   array('wma', 'wmav2', 'wmahd', 'wmapro');

// Audio channel
$achan_assoc = array(
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