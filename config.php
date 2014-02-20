<?PHP

$version = '2.3.1';

if (file_exists('db.php')) {
    require('db.php');
}

// Tables to array
$mysql_tables = array('movies', 'config', 'users');

// Dir
$dir_assoc = array('cache', 'cache/actors');

// Output panel
$output_panel_info = '';

// Video resolution
$vres_assoc = array(0, 480, 576, 540, 720, 768, 1080);

// Video codec
$vtype_assoc['3ivx']    =   array('3ivx', '3iv2', '3ivd');
$vtype_assoc['avc']     =   array('avc', 'avc1');
$vtype_assoc['divx']    =   array('divx', 'div1', 'div2', 'div3', 'div4', 'div5', 'div6');
$vtype_assoc['flv']     =   array('flv');
$vtype_assoc['h264']    =   array('h264');
$vtype_assoc['mp4']     =   array('mp4');
$vtype_assoc['mpeg']    =   array('mpeg', 'pim1');
$vtype_assoc['mpeg2']   =   array('mpeg2', 'em2v', 'lmp2', 'mmes', 'mpeg-2');
$vtype_assoc['mpeg4']   =   array('mpeg4', 'dm4v', 'dx50', 'geox', 'm4s2', 'mpeg-4', 'nds', 'ndx', 'pvmm');
$vtype_assoc['qt']      =   array('qt', '8bps', 'advj', 'avrn', 'rle', 'rpza', 'smc', 'sv10', 'svq', 'zygo');
$vtype_assoc['wmv']     =   array('wmv', 'wma');
$vtype_assoc['xvid']    =   array('xvid', 'xvix');

// Audio codec
$atype_assoc['aac']     =   array('aac');
$atype_assoc['ac3']     =   array('ac3');
$atype_assoc['aif']     =   array('aif', 'aifc', 'aiff');
$atype_assoc['dd']      =   array('dd', 'dtshd', 'dtsma', 'dtshr');
$atype_assoc['dts']     =   array('dts', 'dca');
$atype_assoc['flac']    =   array('flac');
$atype_assoc['mp3']     =   array('mp3', 'mp2', 'mp1');
$atype_assoc['ogg']     =   array('ogg', 'a_vorbis', 'vorbis');
$atype_assoc['truehd']  =   array('truehd');
$atype_assoc['wma']     =   array('wma', 'wmav2', 'wmahd', 'wmapro');

// Audio channel
$achan_assoc = array(
    '1' => '1',
    '2' => '2',
    '6' => '6',
    '8' => '8'
);

// Language
$language = array(
    'bg' => 'Bulgarian',
    'cs' => 'Czech',
    'da' => 'Danish',
    'de' => 'German',
    'en' => 'English',
    'es' => 'Spanish',
    'fr' => 'French',
    'hu' => 'Hungarian',
    'it' => 'Italian',
    'nl' => 'Dutch',
    'no' => 'Norwegian',
    'pl' => 'Polish',
    'pt' => 'Portuguese',
    'ru' => 'Russian'
);

// MimeType
$mimetype_assoc['video/mp4']            =   array('mp4');
$mimetype_assoc['video/ogg']            =   array('ogg', 'ogv');
$mimetype_assoc['video/webm']           =   array('webm');
$mimetype_assoc['video/flv']            =   array('flv');

// Set var
$var = array(
    'id'        =>  0,
    'sort'      =>  1,
    'genre'     =>  'all',
    'year'      =>  'all',
    'country'   =>  'all',
    'cast'      =>  'all',    
    'v_codec'   =>  'all',
    'a_codec'   =>  'all',
    'a_chan'    =>  'all',
    'search'    =>  '',
    'page'      =>  1,
    'output'    =>  '',
    'token'     =>  '',
    'option'    =>  ''
    );
foreach ($var as $key => $val) {
    if (isset($_GET[$key])) {
        $$key = $_GET[$key];
    } else {
        $$key = $val;
    }
}

?>