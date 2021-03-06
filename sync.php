<?PHP
session_start();
header('Content-type: text/html; charset=utf-8');

include('config.php');
include('function.php');

// connect to database
connect($mysql_ml);

// get settings from db
$setting = get_settings();
include('lang/' . $setting['language'] . '/lang.php');

/* #################
 * # SYNC DATABASE #
 */#################

// check settings
if ($option == 'checksettings') {
    $s = $setting;
    $s['token_md5']              = md5($setting['token']);
    $s['version']                = $version;
    $s['GD']                     = (extension_loaded('gd') && function_exists('gd_info') ? 'On' : 'Off');
    $s['CURL']                   = (function_exists('curl_version') ? 'On' : 'Off');
    $s['MAX_EXECUTION_TIME']     = ini_get('max_execution_time');
    $s['UPLOAD_MAX_FILESIZE']    = ini_get('upload_max_filesize');
    $s['POST_MAX_SIZE']          = ini_get('post_max_size');
    $s['ALLOW_URL_FOPEN']        = (ini_get('allow_url_fopen') == 1 ? 'true' : 'false');
    unset($s['token']);
    echo json_encode($s);
}

if ($token == $setting['token']) {

    switch ($option) {
        // auto config remote
        case 'autoconfremote':
            auto_conf_remote($_POST);
            break;
        
        // generate banner
        case 'generatebanner':
            if (isset($_POST['id']) && isset($_POST['type'])) {
                create_banner($lang, 'banner.jpg', $setting['banner'], $_POST['id'], $_POST['type']);
            } else {
                create_banner($lang, 'banner.jpg', $setting['banner']);
            }
            break;
        
        // get hash
        case 'showhash':
            $hash_sql = 'SELECT * FROM hash';
            $hash_res = mysql_q($hash_sql);
            $hash = mysqli_fetch_assoc($hash_res);
            echo json_encode($hash);
            break;
        
        // update hash
        case 'updatehash':
            foreach ($_POST as $table => $hash) {
                $update_sql = 'UPDATE `hash` SET ' . $table . ' = "' . $hash . '"';
            }
            $update_res = mysql_q($update_sql);
            break;
        
        // clean dataabse
        case 'cleandb':
            clean_db();
            break;
        
        /* #########
         * # VIDEO #
         */#########
        case 'showvideo':
            $cols = array('id', 'hash');
            echo show($cols, $_GET['table']);
            break;
        
        case 'addvideo':
            if (isset($_POST['id'])) {
                sync_delete(array($_POST['id']), $_GET['t']);
                sync_add($mysql_tables);
            } else {
                echo 'No POST data';
            }
            break;
        
        case 'removevideo':
            sync_delete($_POST, $_GET['t']);
            break;
        
        case 'updatevideo':
            if (isset($_POST['id'])) {
                sync_delete(array($_POST['id']), $_GET['t']);
                sync_add($mysql_tables);
            } else {
                echo 'No POST data';
            }
            break;
        
        /* ##########
         * # IMAGES #
         */##########
        case 'showimages':
            show_images();
            break;
        
        case 'addimages':
            add_images($_POST);
            break;
        
        case 'removeimages':
            remove_images($_POST);
            break;
    }
}
?>