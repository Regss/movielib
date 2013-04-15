<?PHP
/* ####################
 * # ENGLISH LANGUAGE #
 */####################

/* #############
 * # INDEX.PHP #
 */#############

$lang['i_title']                        =   'Title';
$lang['i_year']                         =   'Year';
$lang['i_rating']                       =   'Rating';
$lang['i_added']                        =   'Added';
$lang['i_all']                          =   'All';
$lang['i_page']                         =   'Page';
$lang['i_genre']                        =   'Genre';
$lang['i_sort']                         =   'Sort';
$lang['i_plot']                         =   'Plot';
$lang['i_runtime']                      =   'Runtime';
$lang['i_director']                     =   'Director';
$lang['i_country']                      =   'Country';
$lang['i_minute']                       =   'min.';
$lang['i_first']                        =   'First';
$lang['i_previous']                     =   'Previous';
$lang['i_next']                         =   'Next';
$lang['i_last']                         =   'Last';
$lang['i_search']                       =   'Search';
$lang['i_search_del']                   =   'Delete search results';
$lang['i_result']                       =   'Result for phrase';
$lang['i_list_title']                   =   'Movie list';
$lang['i_recently']                     =   'Recently added';
$lang['i_random']                       =   'Random movies';

/* #############
 * # PANEL.PHP #
 */#############

// Sets script mode
$lang['p_mode_safe']                    =   'Script use XBMC database, and is working in safe mode. You can\'t import files';
$lang['p_mode_normal']                  =   'Script use the own database, you can import xml and nfo files';
$lang['p_mode_safe_orange']             =   'Safe Mode';

// Check table in database
$lang['p_tab_no_exists']                =   'not exists';
$lang['p_tab_create']                   =   'create';
$lang['p_tab_show']                     =   'show';
$lang['p_tab_confirm']                  =   'This delete all data from database.<br>Do You want proceed?';
$lang['p_tab_delete']                   =   'delete';

// Find xml file
$lang['p_xml_mode_1']                   =   'XBMC database mode';
$lang['p_xml_show']                     =   'show';
$lang['p_xml_import']                   =   'import';
$lang['p_xml_not_found']                =   'not found';

// Find nfo files
$lang['p_nfo_not_found']                =   'not found';
$lang['p_nfo_show']                     =   'show';

// Check remote connection
$lang['p_synch_no_conn']                =   'no connection';
$lang['p_synch_conn_to']                =   'Connected to';
$lang['p_synch_cant_select']            =   'Database not exists';
$lang['p_synch_database_synch']         =   'Database is synchronized';
$lang['p_synch_movie_to_remove']        =   'Movies to remove';
$lang['p_synch_movie_to_add']           =   'Movies to add';
$lang['p_synch_synch']                  =   'synchronize';

// Check chmod
$lang['p_chmod_change']                 =   'change chmod';
$lang['p_chmod_no_exists']              =   'not exists';

// Check cache
$lang['p_cache_poster']                 =   'Poster';
$lang['p_cache_fanart']                 =   'Fanart';
$lang['p_cache_confirm']                =   'This delete all cache files. Do you want proceed?';
$lang['p_cache_delete']                 =   'delete cache';
$lang['p_cache_create']                 =   'create cache';
$lang['p_cache_clear']                  =   'clear cache';

// jquery
$lang['p_jquery_yes']                   =   'YES';
$lang['p_jquery_no']                    =   'NO';

// html
$lang['p_html_admin_panel']             =   'Admin Panel';
$lang['p_html_library']                 =   'Library';
$lang['p_html_admin']                   =   'Admin';
$lang['p_html_logout']                  =   'Logout';
$lang['p_html_database']                =   'Database';
$lang['p_html_table']                   =   'Table';
$lang['p_html_movies']                  =   'Movies';
$lang['p_html_founded_files']           =   'Founded files';
$lang['p_html_single_files']            =   'Nfo files';
$lang['p_html_remote_con']              =   'Remote connection';
$lang['p_html_lib']                     =   'Libraries';
$lang['p_html_gd_stat']                 =   'GD Status';
$lang['p_html_curl_stat']               =   'CURL Status';
$lang['p_html_chmod_stat']              =   'CHMOD Status';
$lang['p_html_cache']                   =   'Cache';

/* ################
 * # FUNCTION.PHP #
 */################

// Delete table
$lang['f_tab_cant_del_xbmc']            =   'The table contains XBMC data can not remove it. By removing it you will lose all movies from database you silly';
$lang['f_tab_cant_delete']              =   'Can\'t delete';
$lang['f_tab_deleted']                  =   'Deleted table';

// Create empty table
$lang['f_tab_cant_create']              =   'Can\'t create';
$lang['f_tab_created']                  =   'Created table';

// Database movie list
$lang['f_list_tab_not_exist']           =   'Table not exists';
$lang['f_list_select_all']              =   'Select all';
$lang['f_list_unselect_all']            =   'Unselect all';
$lang['f_list_movies']                  =   'Movie list';
$lang['f_list_poster']                  =   'Poster URL';
$lang['f_list_fanart']                  =   'Fanart URL';
$lang['f_list_poster_thumb']            =   'Poster cache';
$lang['f_list_fanart_thumb']            =   'Fanart cache';
$lang['f_list_empty_tab']               =   'Table is empty';
$lang['f_list_cant_del']                =   'Can\'t delete movie';
$lang['f_list_successful_del']          =   'Succesful deleted movie';
$lang['f_list_cant_del_mode_1']         =   'Can\'t delete, this is a XBMC database';

// List movies from xml file
$lang['f_xml_file_error_format']        =   'Invalid file format';
$lang['f_xml_file_error_title']         =   'Difrent movietitle and filename';
$lang['f_xml_movie_to_import']          =   'Movie to import';
$lang['f_xml_poster']                   =   'Poster URL';
$lang['f_xml_fanart']                   =   'Fanart URL';

// List nfo files
$lang['f_nfo_select_all']               =   'Select all';
$lang['f_nfo_unselect_all']             =   'Unselect all';
$lang['f_nfo_files']                    =   'nfo files';
$lang['f_nfo_poster']                   =   'Poster URL';
$lang['f_nfo_fanart']                   =   'Fanart URL';

// Import movie from file
$lang['f_import_renamed']               =   'All imported files changed extension to .bak';
$lang['f_import_succes']                =   'Successfully imported';
$lang['f_import_error']                 =   'Errors';
$lang['f_import_imported_movie']        =   'Imported movies';
$lang['f_import_mysql_info']            =   'MySql info';

// Synch remote database to local
$lang['f_synch_could_connect']          =   'Could not connect';
$lang['f_synch_could_select']           =   'Could not select';
$lang['f_synch_remained']               =   'Movies remained';
$lang['f_synch_id']                     =   'Movie id';
$lang['f_synch_error']                  =   'Error';
$lang['f_synch_ok']                     =   'Database is synchronized';

// GD conversion, create poster and fanart cache
$lang['f_cache_deleted']                =   'All cache files deleted';

// Create Cache files
$lang['f_cache_created']                =   'Created files';
$lang['f_cache_wait']                   =   'Please wait';

// Clear cache file
$lang['f_cache_cleared']                =   'Cache cleared';

/* #############
 * # LOGIN.PHP #
 */#############

// Login panel
$lang['l_panel_pass']                   =   'Enter password';
$lang['l_panel_login']                  =   'Login';
$lang['l_panel_wrong']                  =   'Wrong password';
$lang['l_panel_again']                  =   'Try again';

// html
$lang['l_html_login']                   =   'Login panel';

/* ####################
 * # ENGLISH LANGUAGE #
 */####################
?>