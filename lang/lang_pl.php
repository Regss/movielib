<?PHP
/* ###################
 * # POLISH LANGUAGE #
 */###################

/* #############
 * # INDEX.PHP #
 */#############

$lang['i_title']                        =   'Tytuł';
$lang['i_year']                         =   'Rok';
$lang['i_rating']                       =   'Ocena';
$lang['i_added']                        =   'Dodane';
$lang['i_all']                          =   'Wszystkie';
$lang['i_page']                         =   'Strona';
$lang['i_genre']                        =   'Gatunek';
$lang['i_sort']                         =   'Sortuj';
$lang['i_plot']                         =   'Opis';
$lang['i_runtime']                      =   'Czas trwania';
$lang['i_director']                     =   'Reżyser';
$lang['i_country']                      =   'Kraj';
$lang['i_minute']                       =   'min.';
$lang['i_first']                        =   'Pierwsza';
$lang['i_previous']                     =   'Poprzednia';
$lang['i_next']                         =   'Następna';
$lang['i_last']                         =   'Ostatnia';
$lang['i_search']                       =   'Szukaj';
$lang['i_search_del']                   =   'Usuń wyniki wyszukiwania';
$lang['i_result']                       =   'Wynik dla frazy';
$lang['i_list_title']                   =   'Lista filmów';
$lang['i_recently']                     =   'Ostatnio dodane';
$lang['i_random']                       =   'Losowe filmy';

/* #############
 * # PANEL.PHP #
 */#############

// Sets script mode
$lang['p_mode_safe']                    =   'Skrypt używa bazy danych XBMC, i pracuje w trybie safe mode. Nie możesz importować plików.';
$lang['p_mode_normal']                  =   'Skrypt używa własnej bazy danych, możesz importować pliki xml i nfo.';
$lang['p_mode_safe_orange']             =   'Safe Mode';

// Check table in database
$lang['p_tab_no_exists']                =   'nie istnieje';
$lang['p_tab_create']                   =   'utwórz';
$lang['p_tab_show']                     =   'pokaż';
$lang['p_tab_confirm']                  =   'Wszystkie dane z bazy danych zostaną usunięte.<br>Chcesz kontynuować?';
$lang['p_tab_delete']                   =   'usuń';

// Find xml file
$lang['p_xml_mode_1']                   =   'Tryb bazy danych XBMC';
$lang['p_xml_show']                     =   'pokaż';
$lang['p_xml_import']                   =   'importuj';
$lang['p_xml_not_found']                =   'nie znaleziono';

// Find nfo files
$lang['p_nfo_not_found']                =   'nie znaleziono';
$lang['p_nfo_show']                     =   'pokaż';

// Check remote connection
$lang['p_synch_no_conn']                =   'brak połączenia';
$lang['p_synch_conn_to']                =   'Połączono z';
$lang['p_synch_cant_select']            =   'Baza nie istnieje';
$lang['p_synch_database_synch']         =   'Baza jest zsynchronizowana';
$lang['p_synch_movie_to_remove']        =   'Filmy do usunięcia';
$lang['p_synch_movie_to_add']           =   'Filmy do dodana';
$lang['p_synch_synch']                  =   'synchronizuj';

// Check chmod
$lang['p_chmod_change']                 =   'zmień uprawnienia';
$lang['p_chmod_no_exists']              =   'nie istnieje';

// Check cache
$lang['p_cache_poster']                 =   'Okładki';
$lang['p_cache_fanart']                 =   'Plakaty';
$lang['p_cache_confirm']                =   'Chcesz wyczyścić całą pamięć podręczną?';
$lang['p_cache_delete']                 =   'usuń pamięć podręczną';
$lang['p_cache_create']                 =   'utwórz pliki pamięci podręcznej';
$lang['p_cache_clear']                  =   'uporządkuj pamięć podręczną';

// jquery
$lang['p_jquery_yes']                   =   'TAK';
$lang['p_jquery_no']                    =   'NIE';

// html
$lang['p_html_admin_panel']             =   'Panel Administratora';
$lang['p_html_library']                 =   'Bibioteka';
$lang['p_html_admin']                   =   'Admin';
$lang['p_html_logout']                  =   'Wyloguj';
$lang['p_html_database']                =   'Baza danych';
$lang['p_html_table']                   =   'Tabela';
$lang['p_html_movies']                  =   'Filmy';
$lang['p_html_founded_files']           =   'Znalezione pliki';
$lang['p_html_single_files']            =   'Pliki nfo';
$lang['p_html_remote_con']              =   'Połączenie zdalne';
$lang['p_html_lib']                     =   'Bilblioteki';
$lang['p_html_gd_stat']                 =   'Status GD';
$lang['p_html_curl_stat']               =   'Status CURL';
$lang['p_html_chmod_stat']              =   'CHMOD';
$lang['p_html_cache']                   =   'Pamięć podręczna';

/* ################
 * # FUNCTION.PHP #
 */################

// Delete table
$lang['f_tab_cant_del_xbmc']            =   'Tabela zawiera dane z XBMC nie można jej usunąć. Usuwając ją stracisz wszystkie filmy z biblioteki głuptasie.';
$lang['f_tab_cant_delete']              =   'Nie można usunąć';
$lang['f_tab_deleted']                  =   'Usunięto tabelę';

// Create empty table
$lang['f_tab_cant_create']              =   'Nie można utworzyć';
$lang['f_tab_created']                  =   'Utworzono tabelę';

// Database movie list
$lang['f_list_tab_not_exist']           =   'Tabela nie istnieje';
$lang['f_list_select_all']              =   'Zaznacz wszystko';
$lang['f_list_unselect_all']            =   'Odznacz wszystko';
$lang['f_list_movies']                  =   'Lista filmów';
$lang['f_list_poster']                  =   'Okładka URL';
$lang['f_list_fanart']                  =   'Plakat URL';
$lang['f_list_poster_thumb']            =   'Miniaturka okładki';
$lang['f_list_fanart_thumb']            =   'Miniaturka plakatu';
$lang['f_list_empty_tab']               =   'Tabela jest pusta';
$lang['f_list_cant_del']                =   'Nie można usunąć filmu';
$lang['f_list_successful_del']          =   'Poprawnie usunięto film';
$lang['f_list_cant_del_mode_1']         =   'Nie można usunąć. To jest baza danych XBMC';

// List movies from xml file
$lang['f_xml_file_error_format']        =   'Nieprawidłowy format pliku';
$lang['f_xml_file_error_title']         =   'Tytuł i nazwa pliku nie zgadzają się';
$lang['f_xml_movie_to_import']          =   'Filmy do importowania';
$lang['f_xml_poster']                   =   'Okładka URL';
$lang['f_xml_fanart']                   =   'Plakat URL';

// List nfo files
$lang['f_nfo_select_all']               =   'Zaznacz wszystko';
$lang['f_nfo_unselect_all']             =   'Odznacz wszystko';
$lang['f_nfo_files']                    =   'Lista plików nfo';
$lang['f_nfo_poster']                   =   'Okładka URL';
$lang['f_nfo_fanart']                   =   'Plakat URL';

// Import movie from file
$lang['f_import_renamed']               =   'Rozszerzenie zaimportowanch plików zostalo zmienione na .bak';
$lang['f_import_succes']                =   'Poprawnie zaimportowano';
$lang['f_import_error']                 =   'Błędy';
$lang['f_import_imported_movie']        =   'Zaimportowano';
$lang['f_import_mysql_info']            =   'informacje MySql';

// Synch remote database to local
$lang['f_synch_could_connect']          =   'Nie można połączyć';
$lang['f_synch_could_select']           =   'Nie można bazy';
$lang['f_synch_remained']               =   'Pozostało filmów';
$lang['f_synch_id']                     =   'Id filmu';
$lang['f_synch_error']                  =   'Błąd';
$lang['f_synch_ok']                     =   'Baza jest zsynchronizowana';

// GD conversion, create poster and fanart cache
$lang['f_cache_deleted']                =   'Usunięto pamięć podręczną';

// Create Cache files
$lang['f_cache_created']                =   'Przetworzono plików';
$lang['f_cache_wait']                   =   'Proszę czekać';

// Clear cache file
$lang['f_cache_cleared']                =   'Uporządkowano pamięć podręczną';

/* #############
 * # LOGIN.PHP #
 */#############

// Login panel
$lang['l_panel_pass']                   =   'Podaj hasło';
$lang['l_panel_login']                  =   'Zaloguj';
$lang['l_panel_wrong']                  =   'Nieprawidłowe hasło';
$lang['l_panel_again']                  =   'Spróbuj jeszcze raz';

// html
$lang['l_html_login']                   =   'Panel logowania';

/* ###################
 * # POLISH LANGUAGE #
 */###################
?>