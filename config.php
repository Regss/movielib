<?PHP

$version = '2.5.5';

if (file_exists('db.php')) {
    require('db.php');
}

// Dir
$dir_assoc = array('cache', 'cache/actors');

// Output panel
$output_panel_info = '';

// Video resolution
$vres_assoc = array(
    0 => 0, 
    640 => 480,
    768 => 576,
    1280 => 720,
    1920 => 1080
);

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

// tables
$mysql_tables = array('movies', 'tvshows', 'episodes', 'config', 'users');
$movies_table = array(
    'id'                    => 'int(6) NOT NULL',
    'title'                 => 'varchar(100) NOT NULL',
    'plot'                  => 'text NOT NULL',
    'rating'                => 'float NOT NULL',
    'year'                  => 'int(4) NOT NULL',
    'trailer'               => 'varchar(255) NOT NULL',
    'runtime'               => 'int(4) NOT NULL',
    'genre'                 => 'varchar(255) NOT NULL',
    'director'              => 'varchar(255) NOT NULL',
    'originaltitle'         => 'varchar(255) NOT NULL',
    'country'               => 'varchar(255) NOT NULL',
    'cast'                  => 'varchar(255) NOT NULL',
    'sets'                  => 'varchar(255) NOT NULL',
    'v_codec'               => 'varchar(255) NOT NULL',
    'v_aspect'              => 'float NOT NULL',
    'v_width'               => 'int(11) NOT NULL',
    'v_height'              => 'int(11) NOT NULL',
    'v_duration'            => 'int(11) NOT NULL',
    'a_codec'               => 'varchar(255) NOT NULL',
    'a_chan'                => 'int(11) NOT NULL',
    'file'                  => 'varchar(255) NOT NULL',
    'play_count'            => 'int(11) NOT NULL',
    'last_played'           => 'varchar(20) NOT NULL',
    'date_added'            => 'varchar(20) NOT NULL',
    'hide'                  => 'int(1) NOT NULL'
);
$tvshows_table = array(
    'id'                    => 'int(6) NOT NULL',
    'title'                 => 'varchar(100) NOT NULL',
    'plot'                  => 'text NOT NULL',
    'rating'                => 'float NOT NULL',
    'genre'                 => 'varchar(255) NOT NULL',
    'originaltitle'         => 'varchar(255) NOT NULL',
    'cast'                  => 'varchar(255) NOT NULL',
    'premiered'             => 'varchar(20) NOT NULL',
    'play_count'            => 'int(11) NOT NULL',
    'last_played'           => 'varchar(20) NOT NULL',
    'date_added'            => 'varchar(20) NOT NULL',
    'hide'                  => 'int(1) NOT NULL'
);
$episodes_table = array(
    'id'                    => 'int(6) NOT NULL',
    'title'                 => 'varchar(100) NOT NULL',
    'plot'                  => 'text NOT NULL',
    'episode'               => 'int(6) NOT NULL',
    'season'                => 'int(6) NOT NULL',
    'tvshow'                => 'int(6) NOT NULL',
    'firstaired'            => 'varchar(20) NOT NULL',
    'file'                  => 'varchar(255) NOT NULL',
    'play_count'            => 'int(11) NOT NULL',
    'last_played'           => 'varchar(20) NOT NULL',
    'date_added'            => 'varchar(20) NOT NULL'
);
$config_table = array(
    'site_name'             => 'varchar(30) DEFAULT "MovieLib"',
    'language'              => 'varchar(2) DEFAULT "en"',
    'theme'                 => 'varchar(15) DEFAULT "default"',
    'view'                  => 'int(1) DEFAULT 0',
    'per_page'              => 'int(5) DEFAULT 50',
    'panel_top_limit'       => 'int(5) DEFAULT 10',
    'panel_top_time'        => 'int(5) DEFAULT 5',
    'panel_top'             => 'int(1) DEFAULT 1',
    'panel_view'            => 'int(1) DEFAULT 1',
    'watched_status'        => 'int(1) DEFAULT 1',
    'live_search'           => 'int(1) DEFAULT 1',
    'live_search_max_res'   => 'int(4) DEFAULT 10',
    'panel_overall'         => 'int(1) DEFAULT 1',
    'panel_genre'           => 'int(1) DEFAULT 1',
    'panel_year'            => 'int(1) DEFAULT 1',
    'panel_country'         => 'int(1) DEFAULT 1',
    'panel_sets'            => 'int(1) DEFAULT 1',
    'panel_v_codec'         => 'int(1) DEFAULT 1',
    'panel_a_codec'         => 'int(1) DEFAULT 1',
    'panel_a_chan'          => 'int(1) DEFAULT 1',
    'show_fanart'           => 'int(1) DEFAULT 1',
    'fadeout_fanart'        => 'int(1) DEFAULT 0',
    'show_trailer'          => 'int(1) DEFAULT 1',
    'banner'                => 'varchar(200) DEFAULT 0',
    'protect_site'          => 'int(1) DEFAULT 0',
    'token'                 => 'varchar(6) DEFAULT ""',
    'xbmc_host'             => 'varchar(30) DEFAULT ""',
    'xbmc_port'             => 'varchar(5) DEFAULT ""',
    'version'               => 'varchar(6) DEFAULT "' . $version . '"'
);
$users_table = array(
    'id'                    => 'int(2) NOT NULL',
    'login'                 => 'varchar(5) DEFAULT NULL',
    'password'              => 'varchar(32) DEFAULT NULL'
);
$tables = array(
    $mysql_tables[0] => $movies_table,
    $mysql_tables[1] => $tvshows_table,
    $mysql_tables[2] => $episodes_table,
    $mysql_tables[3] => $config_table,
    $mysql_tables[4] => $users_table
);

// views
$views = array('view_default', 'view_list', 'view_sposter', 'view_bposter');

//outputs
$item = array(
    'select_media',
    'view',
    'include_view',
    'sort',
    'filter',
    'filterid',
    'meta_title',
    'meta_originaltitle',
    'meta_img',
    'meta_url',
    'meta_plot',
    'version',
    'panel_top',
    'panel_top_last_added',
    'panel_top_most_watched',
    'panel_top_last_played',
    'panel_top_top_rated',
    'overall_all',
    'overall_watched',
    'overall_unwatched',
    'panel_remote',
    'panel_genre',
    'panel_year',
    'panel_country',
    'panel_sets',
    'panel_v_codec',
    'panel_a_codec',
    'panel_a_chan',
    'panel_live_search',
    'panel_sort',
    'panel_view',
    'panel_nav',
    'panel_filter'
);
$item_desc = array(
    'mysql_table',
    'id',
    'video',
    'view',
    'include_view',
    'sort',
    'filter',
    'filterid',
    'title',
    'originaltitle',
    'watched_img',
    'genre',
    'rating',
    'cast',
    'plot',
    'year',
    'country',
    'runtime',
    'director',
    'sets',
    'img_flag_vres',
    'img_flag_vtype',
    'img_flag_atype',
    'img_flag_achan',
    'trailer_img',
    'trailer',
    'premiered',
    'seasons',
    'episodes',
    'episodes_plot'
);

// JSON function
$json_f = array(
    'play'      => array('p' => '', 'm' => 'Player.Open'),
    'stop'      => array('p' => '"playerid": 1', 'm' => 'Player.Stop'),
    'pause'     => array('p' => '"playerid": 1', 'm' => 'Player.PlayPause'),
    'v_up'      => array('p' => '"action": "volumeup"', 'm' => 'Input.ExecuteAction'),
    'v_down'    => array('p' => '"action": "volumedown"', 'm' => 'Input.ExecuteAction'),
    'playing'   => array('p' => '"playerid": 1', 'm' => 'Player.GetItem')
);

// Set var
$var = array(
    'id'        =>  0,
    'sort'      =>  1,
    'search'    =>  '',
    'page'      =>  1,
    'token'     =>  '',
    'option'    =>  '',
    'filter'    =>  '',
    'filterid'  =>  ''
    );
foreach ($var as $key => $val) {
    if (isset($_GET[$key])) {
        $$key = $_GET[$key];
    } else {
        $$key = $val;
    }
}

?>