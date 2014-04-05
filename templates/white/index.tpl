<!DOCTYPE HTML>
<html>
    <head>
        <title>{SET.site_name}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <![endif]-->
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
        <img src="templates/{SET.theme}/img/bg.jpg" id="background" alt="">
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
                {SHOW.panel_sets}
                <div id="sets" class="panel_box_title">{LANG.i_sets}</div>
                <div id="panel_sets" class="panel_box {SET.panel_sets}">
                    <ul>{panel_sets}</ul>
                </div>
                {/SHOW.panel_sets}
                {SHOW.panel_v_codec}
                <div id="v_codec" class="panel_box_title">{LANG.i_v_codec}</div>
                <div id="panel_v_codec" class="panel_box {SET.panel_v_codec}">
                    <ul>{panel_v_codec}</ul>
                </div>
                {/SHOW.panel_v_codec}
                {SHOW.panel_a_codec}
                <div id="a_codec" class="panel_box_title">{LANG.i_a_codec}</div>
                <div id="panel_a_codec" class="panel_box {SET.panel_a_codec}">
                    <ul>{panel_a_codec}</ul>
                </div>
                {/SHOW.panel_a_codec}
                {SHOW.panel_a_chan}
                <div id="a_chan" class="panel_box_title">{LANG.i_a_chan}</div>
                <div id="panel_a_chan" class="panel_box {SET.panel_a_chan}">
                    <ul>{panel_a_chan}</ul>
                </div>
                {/SHOW.panel_a_chan}
            </div>
            <div id="panel_right">
                <div id="panel_sort">{panel_sort}</div>
                {SHOW.panel_view}
                <div id="panel_view">{panel_view}</div>
                {/SHOW.panel_view}
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
                    <a href="index.php?video={video}&view={view}&sort={sort}&filter={filter}&filterid={filterid}"><img id="filter_delete_img" class="animate" src="templates/{SET.theme}/img/delete.png" title="{LANG.i_del_result}" alt=""></a>
                </div>
                {/SHOW.panel_filter}
                <div id="panel_list">
                    {panel_list}
                </div>
                <div id="panel_nav">
                    {panel_nav}
                </div>
            </div>
        </div>
        <div id="panel_bottom">
            <a href="http://github.com/Regss/movielib">MovieLib</a> {version} - Created by <a href="mailto:regss84@gmail.com">Regss</a>
        </div>
    </body>
</html>