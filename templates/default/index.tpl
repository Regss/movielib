<!DOCTYPE HTML>
<html>
    <head>
        <title>{SET.site_name}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
        <meta property="og:title" content="{meta_title}" />
        <meta property="og:type" content="{meta_type}" />
        <meta property="og:url" content="{meta_url}" />
        <meta property="og:site_name" content="{SET.site_name}" />
        <meta property="og:image" content="{meta_img}" />
        <meta property="og:description" content="{meta_desc}" />
        <link type="image/x-icon" href="templates/{SET.theme}/img/icon.ico" rel="icon" media="all" />
        <link type="text/css" href="templates/{SET.theme}/css/style.css" rel="stylesheet" media="all" />
        <link type="text/css" href="templates/{SET.theme}/css/{include_view}.css" rel="stylesheet" media="all" />
        <link type="text/css" href="templates/{SET.theme}/css/video.css" rel="stylesheet" media="all" />
        <script type="text/javascript" src="js/jquery-1.9.1.js"></script>
        <script type="text/javascript" src="js/jquery.cycle.lite.js"></script>
        <script type="text/javascript" src="js/jquery.script.js"></script>
        <script type="text/javascript" src="js/video.js"></script>
    </head>
    <body>
        <div id="wrapper">
            <div id="content">
                <img src="templates/{SET.theme}/img/bg.jpg" id="background" alt="">
                {SHOW.facebook}
                {facebook}
                {/SHOW.facebook}
                {SHOW.panel_remote}
                <div id="panel_remote">
                    <div id="r_left">
                        <div><img id="stepback" class="animate" src="templates/{SET.theme}/img/stepback.png" title="{LANG.i_xbmc_stepback} ( , )"></div>
                        <div><img id="pause" class="animate" src="templates/{SET.theme}/img/pause.png" title="{LANG.i_xbmc_pause} ( SPACE )"></div>
                        <div><img id="stepforward" class="animate" src="templates/{SET.theme}/img/stepforward.png" title="{LANG.i_xbmc_stepforward} ( . )"></div>
                        <div><img id="bigstepback" class="animate" src="templates/{SET.theme}/img/bigstepback.png" title="{LANG.i_xbmc_bigstepback} ( [ )"></div>
                        <div><img id="stop" class="animate" src="templates/{SET.theme}/img/stop.png" title="{LANG.i_xbmc_stop} ( X )"></div>
                        <div><img id="bigstepforward" class="animate" src="templates/{SET.theme}/img/bigstepforward.png" title="{LANG.i_xbmc_bigstepforward} ( ] )"></div>
                        <div><img id="v_up" class="animate" src="templates/{SET.theme}/img/v_up.png" title="{LANG.i_xbmc_v_up} ( = )"></div>
                        <div><img id="v_mute" class="animate" src="templates/{SET.theme}/img/v_mute.png" title="{LANG.i_xbmc_v_mute} ( 0 )"></div>
                        <div><img id="v_down" class="animate" src="templates/{SET.theme}/img/v_down.png" title="{LANG.i_xbmc_v_down} ( - )"></div>
                        <div><img id="info" class="animate" src="templates/{SET.theme}/img/info.png" title="{LANG.i_xbmc_info} ( I )"></div>
                        <div><img id="up" class="animate" src="templates/{SET.theme}/img/up.png" title="{LANG.i_xbmc_up} ( UP )"></div>
                        <div><img id="watch" class="animate" src="templates/{SET.theme}/img/watch.png" title="{LANG.i_xbmc_watched} ( W )"></div>
                        <div><img id="left" class="animate" src="templates/{SET.theme}/img/left.png" title="{LANG.i_xbmc_left} ( LEFT )"></div>
                        <div><img id="select" class="animate" src="templates/{SET.theme}/img/select.png" title="{LANG.i_xbmc_select} ( ENTER )"></div>
                        <div><img id="right" class="animate" src="templates/{SET.theme}/img/right.png" title="{LANG.i_xbmc_right} ( RIGHT )"></div>
                        <div><img id="context" class="animate" src="templates/{SET.theme}/img/context.png" title="{LANG.i_xbmc_context} ( C )"></div>
                        <div><img id="down" class="animate" src="templates/{SET.theme}/img/down.png" title="{LANG.i_xbmc_down} ( DOWN )"></div>
                        <div><img id="back" class="animate" src="templates/{SET.theme}/img/back.png" title="{LANG.i_xbmc_back} ( BACKSPACE )"></div>
                        <div><img id="power" class="animate" src="templates/{SET.theme}/img/power.png" title="{LANG.i_xbmc_power} ( S )"></div>
                        <div></div>
                        <div><img id="sync" class="animate" src="templates/{SET.theme}/img/sync.png" title="{LANG.i_xbmc_sync} ( R )"></div>
                    </div>
                    <div id="r_right">
                        <img src="templates/{SET.theme}/img/xbmc_vd.png">
                    </div>
                </div>
                <div id="now_playing"><div id="np_title">{LANG.i_now_playing}...</div><div id="np_details"></div></div>
                {/SHOW.panel_remote}
                <div class="container">
                    <div id="select_media">
                        {select_media}
                    </div>
                    {SHOW.panel_top}
                    <div id="panel_top">
                        <div class="panel_top_item">{top_item_last_added}</div>
                        <div class="panel_top_item">{top_item_most_watched}</div>
                        <div class="panel_top_item">{top_item_last_played}</div>
                        <div class="panel_top_item">{top_item_top_rated}</div>
                        <div id="panel_title">
                            <div class="panel_top_item_title">{LANG.i_last_added}</div>
                            <div class="panel_top_item_title">{LANG.i_most_watched}</div>
                            <div class="panel_top_item_title">{LANG.i_last_played}</div>
                            <div class="panel_top_item_title">{LANG.i_top_rated}</div>
                        </div>
                    </div>
                    {/SHOW.panel_top}
                    <div id="panel_left">
                        {SHOW.panel_overall}
                        <div id="overall" class="panel_box_title">{LANG.i_overall_title}</div>
                        <div id="panel_overall" class="panel_box {SET.panel_overall}">
                            <ul>
                                <li><span class="bold orange">{LANG.i_overall_all}: </span>{overall_all}</li>
                                <li><span class="bold orange">{LANG.i_overall_watched}: </span>{overall_watched}</li>
                                <li><span class="bold orange">{LANG.i_overall_notwatched}: </span>{overall_unwatched}</li>
                            </ul>
                        </div>
                        {/SHOW.panel_overall}
                        {SHOW.panel_genre}
                        <div id="genre" class="panel_box_title">{LANG.i_genre}</div>
                        <div id="panel_genre" class="panel_box {SET.panel_genre}">
                            <ul>{panel_genre}</ul>
                        </div>
                        {/SHOW.panel_genre}
                        {SHOW.panel_year}
                        <div id="year" class="panel_box_title">{LANG.i_year}</div>
                        <div id="panel_year" class="panel_box {SET.panel_year}">
                            <ul>{panel_year}</ul>
                        </div>
                        {/SHOW.panel_year}
                        {SHOW.panel_country}
                        <div id="country" class="panel_box_title">{LANG.i_country}</div>
                        <div id="panel_country" class="panel_box {SET.panel_country}">
                            <ul>{panel_country}</ul>
                        </div>
                        {/SHOW.panel_country}
                        {SHOW.panel_set}
                        <div id="set" class="panel_box_title">{LANG.i_set}</div>
                        <div id="panel_set" class="panel_box {SET.panel_set}">
                            <ul>{panel_set}</ul>
                        </div>
                        {/SHOW.panel_set}
                        {SHOW.panel_studio}
                        <div id="studio" class="panel_box_title">{LANG.i_studio}</div>
                        <div id="panel_studio" class="panel_box {SET.panel_studio}">
                            <ul>{panel_studio}</ul>
                        </div>
                        {/SHOW.panel_studio}
                        {SHOW.panel_director}
                        <div id="director" class="panel_box_title">{LANG.i_director}</div>
                        <div id="panel_director" class="panel_box {SET.panel_director}">
                            <ul>{panel_director}</ul>
                        </div>
                        {/SHOW.panel_director}
                    </div>
                    <div id="panel_right">
                        <div id="panel_sort">{panel_sort}</div>
                        <div id="panel_view">
                            {panel_watch}
                            {SHOW.panel_view}
                            {panel_view}
                            {/SHOW.panel_view}
                        </div>
                        <div id="panel_search">
                            <form method="get" action="index.php" autocomplete="off">
                                <div id="panel_input_search">
                                    <input type="hidden" name="video" value="{video}">
                                    <input id="search_{video}" class="search" type="text" name="search" value="{LANG.i_search}..." title="{LANG.i_search}...">
                                    {SHOW.panel_live_search}
                                    <div id="panel_live_search"></div>
                                    {/SHOW.panel_live_search}
                                </div>
                            </form>
                        </div>
                        <div id="panel_nav">
                            {panel_nav}
                        </div>
                        {SHOW.panel_filter}
                        <div id="panel_filter">
                            <div id="filter_text">{panel_filter}</div>
                            <a href="{url_delete_filter}"><img id="filter_delete_img" class="animate" src="templates/{SET.theme}/img/delete.png" title="{LANG.i_del_result}" alt=""></a>
                        </div>
                        {/SHOW.panel_filter}
                        <div id="panel_list" class="{video}">
                            {panel_list}
                        </div>
                        <div id="panel_nav">
                            {panel_nav}
                        </div>
                    </div>
                </div>
            </div>
            <div id="panel_bottom">
                <div id="bottom_info">
                    {SHOW.page_load_time}
                    {LANG.i_page_load_time}: {page_load_time} {LANG.i_sec}. | 
                    {/SHOW.page_load_time}
                    <a href="http://github.com/Regss/movielib">MovieLib</a> {version} - Created by <a href="mailto:regss84@gmail.com">Regss</a>
                </div>
            </div>
        </div>
    </body>
</html>