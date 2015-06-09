<div id="{mysql_table}_{id}" class="movie">
    {SHOW.xbmc}
    <div id="{id}" class="xbmc_hide">
        <img class="play animate" src="templates/{SET.theme}/img/play.png" title="{LANG.i_xbmc_play}">
        <a href="{file}"><img class="download animate" src="templates/{SET.theme}/img/download.png" title="{LANG.i_xbmc_download}"></a>
        <a id="{file}" href="cache/list.m3u"><img class="list animate" src="templates/{SET.theme}/img/list.png" title="{LANG.i_xbmc_m3u}"></a>
    </div>
    {/SHOW.xbmc}
    <a href="{url_title}"><img id="poster_movie_{id}" class="poster" src="{poster}" alt="" title="{title}">{ribbon_new}</a>
    {watched_img}
</div>
