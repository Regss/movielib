{SHOW.season_title}
<div id="season_{season}" class="season">{LANG.i_season} {season}</div>
{/SHOW.season_title}
<div class="episode" id="season_{season}_episode_{episode}">
    <div class="poster_container">{thumbnail}</div>
    {SHOW.xbmc}
    <div id="{episode}" class="xbmc_hide xbmc_e">
        <img class="play animate" src="templates/{SET.theme}/img/play.png" title="{LANG.i_xbmc_play}">
        <a href="{file}"><img class="download animate" src="templates/{SET.theme}/img/download.png" title="{LANG.i_xbmc_download}"></a>
        <a id="{file}" href="cache/list.m3u"><img class="list animate" src="templates/{SET.theme}/img/list.png" title="{LANG.i_xbmc_m3u}"></a>
    </div>
    {/SHOW.xbmc}
    {watched_img}
    {ribbon_new}
    <div class="episode_desc">
        <div class="episode_title">{title}</div>
        <table>
            <tr>
                <td class="left">{LANG.i_aired}</td>
                <td class="right">{aired}</td>
            </tr>
            <tr>
                <td class="left">{LANG.i_plot}</td>
                <td class="right">{plot}</td>
            </tr>
        </table>
        <img class="img_space" src="templates/{SET.theme}/img/space.png" alt="">
        <table class="table_flags">
            <tr>
                <td>{img_flag_v}</td>
                <td>{img_flag_a}</td>
                <td>{img_flag_s}</td>
            </tr>
        </table>    
    </div>
</div>