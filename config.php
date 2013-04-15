<?PHP
/* #############################################################
 * # This is the configuration file.                           #
   #                                                           #
 */#############################################################

/* #################
 * # OPTIONS START #
 */#################

/* ##############################################################
 * # Script can work in two mode:                               #
 * #                                                            #
 * # 1. XBMC mode work on database created and used by XBMC.    #
 * #    The content is read and displayed on the fly.           #
 * #    However, access is only possible when a XBMC machine    #
 * #    is running.                                             #
 * #                                                            #
 * # 2. Local mode work on separate MySql database and          #
 * #    can by run on other server. It connects to the XBMC     #
 * #    database and synchronize with it.                       #
   #                                                            #
 */##############################################################

// Set mode 1 or 2
$mode = 1;

// XBMC database
$mysql_host_xbmc = 'localhost'; // Database host
$mysql_port_xbmc = '3306'; // Database port, default is 3306
$mysql_login_xbmc = 'root'; // Database login
$mysql_pass_xbmc = 'vertrigo'; // Database password
$mysql_database_xbmc = 'movielib'; // Database name

// Second separate database if set mode 2
$mysql_host_sep = 'localhost'; // Database host
$mysql_port_sep = '3306'; // Database port, default is 3306
$mysql_login_sep = 'root'; // Database login
$mysql_pass_sep = 'vertrigo'; // Database password
$mysql_database_sep = 'xbmc58'; // Database name

// Config
$site_name = 'MovieLib'; // Site title
$language = 'lang_pl.php'; // The file that contains the language, file must be in the lang/ folder
$per_page = 50; // Movies per page, If you do not want to have pagination, type 0
$recently_limit = 10; // Movies in recently added panel, to turn off panel type 0

// Password
$protect_site = false; // Set true to protect acess to site
$pass = 'b27bfe5ba5bec17f80de30b9f23ff658'; // Type password in md5.


/* ########################################
 * # Don't edit anything below this line! #
 */########################################

// Language 
require 'lang/' . $language;

// Database config to array
$mysql_xbmc = array($mysql_host_xbmc, $mysql_port_xbmc, $mysql_login_xbmc, $mysql_pass_xbmc, $mysql_database_xbmc);
$mysql_sep = array($mysql_host_sep, $mysql_port_sep, $mysql_login_sep, $mysql_pass_sep, $mysql_database_sep);

if ($mode === 1) {
$mysql_database = $mysql_xbmc;
}
if ($mode === 2) {
$mysql_database = $mysql_sep;

}

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
$var = array('sort' => 1, 'genre' => 'all', 'search' => '', 'page' => 1);
foreach ($var as $key => $val) {
    if (isset($_GET[$key])) {
        $$key = $_GET[$key];
    } else {
        $$key = $val;
    }
}

// Database column
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
?>