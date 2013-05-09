<?PHP
if (file_exists('db.php')) {
    include_once 'db.php';
}

// Tables to array
$mysql_tables = array('movies', 'config', 'users');

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