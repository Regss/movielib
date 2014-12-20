<?PHP

$version = '2.7.2';

if (file_exists('db.php')) {
    include('db.php');
}

// Dir
$dir_assoc = array('cache', 'cache/actors');

// Output panel
$output_panel_info = '';
$output_panel_error = '';

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
$langs = array(
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
    'ru' => 'Russian',
    'hr' => 'Croatian'
);

// MimeType
$mimetype_assoc['video/mp4']            =   array('mp4');
$mimetype_assoc['video/ogg']            =   array('ogg', 'ogv');
$mimetype_assoc['video/webm']           =   array('webm');
$mimetype_assoc['video/flv']            =   array('flv');

// tables
$mysql_tables['movies'] = array(
    'id'                    => 'int(6) NOT NULL PRIMARY KEY',
    'title'                 => 'varchar(100) NOT NULL',
    'plot'                  => 'varchar(5000) NOT NULL',
    'rating'                => 'float NOT NULL',
    'trailer'               => 'varchar(255) NOT NULL',
    'runtime'               => 'int(4) NOT NULL',
    'originaltitle'         => 'varchar(255) NOT NULL',
    'year'                  => 'varchar(4) NOT NULL',
    'set'                   => 'varchar(255) NOT NULL',
    'file'                  => 'varchar(255) NOT NULL',
    'play_count'            => 'int(11) NOT NULL',
    'last_played'           => 'varchar(20) NOT NULL',
    'date_added'            => 'varchar(20) NOT NULL',
    'hide'                  => 'int(1) NOT NULL DEFAULT 0',
    'hash'                  => 'varchar(32) NOT NULL'
);
$mysql_tables['tvshows'] = array(
    'id'                    => 'int(6) NOT NULL PRIMARY KEY',
    'title'                 => 'varchar(100) NOT NULL',
    'plot'                  => 'varchar(5000) NOT NULL',
    'rating'                => 'float NOT NULL',
    'originaltitle'         => 'varchar(255) NOT NULL',
    'premiered'             => 'varchar(20) NOT NULL',
    'play_count'            => 'int(11) NOT NULL',
    'last_played'           => 'varchar(20) NOT NULL',
    'date_added'            => 'varchar(20) NOT NULL',
    'hide'                  => 'int(1) NOT NULL DEFAULT 0',
    'hash'                  => 'varchar(32) NOT NULL'
);
$mysql_tables['episodes'] = array(
    'id'                    => 'int(6) NOT NULL PRIMARY KEY',
    'title'                 => 'varchar(100) NOT NULL',
    'plot'                  => 'varchar(5000) NOT NULL',
    'episode'               => 'int(6) NOT NULL',
    'season'                => 'int(6) NOT NULL',
    'tvshow'                => 'int(6) NOT NULL',
    'firstaired'            => 'varchar(20) NOT NULL',
    'file'                  => 'varchar(255) NOT NULL',
    'play_count'            => 'int(11) NOT NULL',
    'last_played'           => 'varchar(20) NOT NULL',
    'date_added'            => 'varchar(20) NOT NULL',
    'hash'                  => 'varchar(32) NOT NULL'
);
$mysql_tables['actor'] = array(
    'id'                    => 'int(6) NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'actor'                 => 'varchar(255) NOT NULL'
);
$mysql_tables['movies_actor'] = array(
    'id'                    => 'int(6) NOT NULL',
    'actorid'               => 'int(6) NOT NULL',
    'order'                 => 'int(2) NOT NULL'
);
$mysql_tables['tvshows_actor'] = array(
    'id'                    => 'int(6) NOT NULL',
    'actorid'               => 'int(6) NOT NULL',
    'order'                 => 'int(2) NOT NULL'
);
$mysql_tables['genre'] = array(
    'id'                    => 'int(6) NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'genre'                 => 'varchar(255) NOT NULL'
);
$mysql_tables['movies_genre'] = array(
    'id'                    => 'int(6) NOT NULL',
    'genreid'               => 'int(6) NOT NULL'
);
$mysql_tables['tvshows_genre'] = array(
    'id'                    => 'int(6) NOT NULL',
    'genreid'               => 'int(6) NOT NULL'
);
$mysql_tables['country'] = array(
    'id'                    => 'int(6) NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'country'               => 'varchar(255) NOT NULL'
);
$mysql_tables['movies_country'] = array(
    'id'                    => 'int(6) NOT NULL',
    'countryid'             => 'int(6) NOT NULL'
);
$mysql_tables['studio'] = array(
    'id'                    => 'int(6) NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'studio'                => 'varchar(255) NOT NULL'
);
$mysql_tables['movies_studio'] = array(
    'id'                    => 'int(6) NOT NULL',
    'studioid'              => 'int(6) NOT NULL'
);
$mysql_tables['director'] = array(
    'id'                    => 'int(6) NOT NULL AUTO_INCREMENT PRIMARY KEY',
    'director'              => 'varchar(255) NOT NULL'
);
$mysql_tables['movies_director'] = array(
    'id'                    => 'int(6) NOT NULL',
    'directorid'            => 'int(6) NOT NULL'
);
$mysql_tables['movies_stream'] = array(
    'id'                    => 'int(6) NOT NULL',
    'type'                  => 'varchar(1) NOT NULL',
    'v_codec'               => 'varchar(255)',
    'v_aspect'              => 'varchar(10)',
    'v_width'               => 'int(11)',
    'v_height'              => 'int(11)',
    'v_duration'            => 'int(11)',
    'a_codec'               => 'varchar(255)',
    'a_chan'                => 'int(11)',
    'a_lang'                => 'varchar(10)',
    's_lang'                => 'varchar(10)'
);
$mysql_tables['episodes_stream'] = array(
    'id'                    => 'int(6) NOT NULL',
    'type'                  => 'varchar(1) NOT NULL',
    'v_codec'               => 'varchar(255)',
    'v_aspect'              => 'varchar(10)',
    'v_width'               => 'int(11)',
    'v_height'              => 'int(11)',
    'v_duration'            => 'int(11)',
    'a_codec'               => 'varchar(255)',
    'a_chan'                => 'int(11)',
    'a_lang'                => 'varchar(10)',
    's_lang'                => 'varchar(10)'
);
$mysql_tables['config'] = array(
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
    'panel_set'             => 'int(1) DEFAULT 1',
    'panel_studio'          => 'int(1) DEFAULT 1',
    'show_fanart'           => 'int(1) DEFAULT 1',
    'fadeout_fanart'        => 'int(1) DEFAULT 0',
    'show_trailer'          => 'int(1) DEFAULT 1',
    'show_facebook'         => 'int(1) DEFAULT 1',
    'banner'                => 'varchar(200) DEFAULT 0',
    'protect_site'          => 'int(1) DEFAULT 0',
    'token'                 => 'varchar(6) DEFAULT ""',
    'xbmc_actors'           => 'int(1) DEFAULT 1',
    'xbmc_posters'          => 'int(1) DEFAULT 1',
    'xbmc_fanarts'          => 'int(1) DEFAULT 1',
    'xbmc_thumbs'           => 'int(1) DEFAULT 1',
    'xbmc_thumbs_q'         => 'varchar(10) DEFAULT "853x480"',
    'xbmc_master'           => 'int(1) DEFAULT 0',
    'xbmc_host'             => 'varchar(30) DEFAULT ""',
    'xbmc_port'             => 'varchar(5) DEFAULT ""',
    'xbmc_login'            => 'varchar(30) DEFAULT ""',
    'xbmc_pass'             => 'varchar(30) DEFAULT ""',
    'version'               => 'varchar(6) DEFAULT "' . $version . '"'
);
$mysql_tables['users'] = array(
    'id'                    => 'int(2) NOT NULL PRIMARY KEY',
    'login'                 => 'varchar(5) DEFAULT NULL',
    'password'              => 'varchar(32) DEFAULT NULL'
);
$mysql_tables['hash'] = array(
    'movies'                => 'varchar(32) DEFAULT ""',
    'tvshows'               => 'varchar(32) DEFAULT ""',
    'episodes'              => 'varchar(32) DEFAULT ""',
    'actor'                 => 'varchar(32) DEFAULT ""',
    'genre'                 => 'varchar(32) DEFAULT ""',
    'country'               => 'varchar(32) DEFAULT ""',
    'studio'                => 'varchar(32) DEFAULT ""',
    'director'              => 'varchar(32) DEFAULT ""'
);

// views
$views = array('view_default', 'view_list', 'view_sposter', 'view_bposter');

//outputs
$item = array(
    'select_media',
    'view',
    'include_view',
    'sort',
    'watch',
    'filter',
    'filterid',
    'meta_title',
    'meta_img',
    'meta_url',
    'meta_desc',
    'meta_type',
    'facebook',
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
    'panel_set',
    'panel_studio',
    'panel_live_search',
    'panel_sort',
    'panel_view',
    'panel_watch',
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
    'file',
    'xbmc',
    'xbmc_episode',
    'watched_img',
    'genre',
    'rating',
    'rating_star',
    'actor',
    'plot',
    'year',
    'country',
    'runtime',
    'director',
    'set',
    'studio',
    'studio_art',
    'ribbon_new',
    'img_flag_v',
    'img_flag_a',
    'img_flag_s',
    'facebook_button',
    'extra_thumbs',
    'trailer_img',
    'trailer',
    'premiered',
    'seasons',
    'episodes',
    'episodes_plot',
    'fb_url'
);
$item_episode = array(
    'episode',
    'season',
    'season_title',
    'thumbnail',
    'file',
    'xbmc',
    'plot',
    'aired',
    'img_flag_v',
    'img_flag_a',
    'img_flag_s',
    'watched_img',
    'ribbon_new'
);

// array for language audio and subs
$iso_lang = array(
    'ces' => array('ces', 'cze', 'czech'),
    'dan' => array('dan', 'danish'),
    'deu' => array('deu', 'ger', 'german', 'deutch'),
    'dut' => array('dut', 'nld', 'dutch'),
    'egy' => array('egy', 'egyptian'),
    'ell' => array('ell', 'gre', 'greek'),
    'eng' => array('eng', 'english'),
    'est' => array('est', 'estonian'),
    'fin' => array('fin', 'finnish'),
    'fra' => array('fra', 'fre', 'french'),
    'gle' => array('gle', 'irish'),
    'heb' => array('heb', 'hebrew'),
    'hun' => array('hun', 'hungarian'),
    'ind' => array('ind', 'indonesian'),
    'ira' => array('ira', 'iranian'),
    'isl' => array('isl', 'ice', 'icelandic'),
    'ita' => array('ita', 'italian'),
    'jpn' => array('jpn', 'japanese'),
    'kat' => array('kat', 'geo', 'georgian'),
    'khm' => array('khm', 'khmer'),
    'kor' => array('kor', 'korean'),
    'mlt' => array('mlt', 'maltese'),
    'mol' => array('mol'),
    'mon' => array('mon', 'mongolian'),
    'nep' => array('nep', 'nepali'),
    'nno' => array('nno', 'norwegian'),
    'pol' => array('pol', 'polish'),
    'por' => array('por', 'portuguese'),
    'ron' => array('ron', 'rum', 'romanian'),
    'rus' => array('rus', 'russian'),
    'slk' => array('slk', 'slo', 'slovak'),
    'slv' => array('slv', 'slovenian'),
    'spa' => array('spa', 'spanish'),
    'srp' => array('srp', 'serbian'),
    'swe' => array('swe', 'swedish'),
    'tur' => array('tur', 'turkish'),
    'ukr' => array('ukr', 'ukrainian'),
    'zho' => array('zho', 'chi', 'chinese')
);

// array for language facebook buttons
$lang_fb_assoc = array(
    'sq' => 'sq_AL',
    'bg' => 'bg_BG',
    'cs' => 'cs_CZ',
    'da' => 'da_DK',
    'nl' => 'nl_NL',
    'en' => 'en_GB',
    'en' => 'en_US',
    'et' => 'et_EE',
    'fr' => 'fr_FR',
    'de' => 'de_DE',
    'el' => 'el_GR',
    'hu' => 'hu_HU',
    'it' => 'it_IT',
    'nb' => 'nb_NO',
    'pl' => 'pl_PL',
    'pt' => 'pt_PT',
    'ro' => 'ro_RO',
    'ru' => 'ru_RU',
    'sk' => 'sk_SK',
    'sl' => 'sl_SI',
    'es' => 'es_LA',
    'uk' => 'uk_UA'
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
    'watch'     =>  0,
    'search'    =>  '',
    'page'      =>  1,
    'token'     =>  '',
    'option'    =>  '',
    'filter'    =>  '',
    'filterid'  =>  '',
    'fb_link'   =>  ''
    );
foreach ($var as $key => $val) {
    if (isset($_GET[$key])) {
        $$key = $_GET[$key];
    } else {
        $$key = $val;
    }
}

?>