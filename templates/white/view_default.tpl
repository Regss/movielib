<div id="{mysql_table}_{id}" class="movie">
    <div class="title">
        <a href="index.php?id={id}&video={video}&view={view}&sort={sort}&filter={filter}&filterid={filterid}">{title}</a>
    </div>
    <div class="title_org">{originaltitle}</div>
    {SHOW.xbmc}
        <div id="{id}" class="xbmc_hide">
            <img class="play animate" src="templates/{SET.theme}/img/play.png" title="{LANG.i_xbmc_play}">
            <a href="{file}"><img class="download animate" src="templates/{SET.theme}/img/download.png" title="{LANG.i_xbmc_download}"></a>
            <a id="{file}" href="cache/list.m3u"><img class="list animate" src="templates/{SET.theme}/img/list.png" title="{LANG.i_xbmc_m3u}"></a>
        </div>
    {/SHOW.xbmc}
    {trailer_img}
    <div class="poster_container">
        <img id="poster_movie_{id}" class="poster" src="{poster}" alt="">
        {watched_img}
    </div>
    {SHOW.facebook_button}
    <div class="fb">
        <div class="fb-like" data-href="{fb_url}" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>
    </div>
    {/SHOW.facebook_button}
    {studio_art}
    {ribbon_new}
    <div class="desc">
        <table class="table">
            {SHOW.year}
            <tr>
                <td class="left">{LANG.i_year}:</td>
                <td class="right">{year}</td>
            </tr>
            {/SHOW.year}
            {SHOW.premiered}
            <tr>
                <td class="left">{LANG.i_premiered}:</td>
                <td class="right">{premiered}</td>
            </tr>
            {/SHOW.premiered}
            {SHOW.genre}
            <tr>
                <td class="left">{LANG.i_genre}:</td>
                <td class="right">{genre}</td>
            </tr>
            {/SHOW.genre}
            {SHOW.rating}
            <tr>
                <td class="left">{LANG.i_rating}:</td>
                <td class="right">{rating_star} ({rating})</td>
            </tr>
            {/SHOW.rating}
            {SHOW.country}
            <tr>
                <td class="left">{LANG.i_country}:</td>
                <td class="right">{country}</td>
            </tr>
            {/SHOW.country}
            {SHOW.runtime}
            <tr>
                <td class="left">{LANG.i_runtime}:</td>
                <td class="right">{runtime} {LANG.i_minute}</td>
            </tr>
            {/SHOW.runtime}
            {SHOW.director}
            <tr>
                <td class="left">{LANG.i_director}:</td>
                <td class="right">{director}</td>
            </tr>
            {/SHOW.director}
            {SHOW.set}
            <tr>
                <td class="left">{LANG.i_set}:</td>
                <td class="right">{set}</td>
            </tr>
            {/SHOW.set}
            {SHOW.studio}
            <tr>
                <td class="left">{LANG.i_studio}:</td>
                <td class="right">{studio}</td>
            </tr>
            {/SHOW.studio}
            {SHOW.seasons}
            <tr>
                <td class="left">{LANG.i_seasons}:</td>
                <td class="right">{seasons}</td>
            </tr>
            {/SHOW.seasons}
            {SHOW.actor}
            <tr>
                <td class="left">{LANG.i_cast}:</td>
                <td class="right">{actor}</td>
            </tr>
            {/SHOW.actor}
            {SHOW.plot}
            <tr>
                <td class="left">{LANG.i_plot}:</td>
                <td class="right">{plot}</td>
            </tr>
            {/SHOW.plot}
        </table>
        {episodes}
        <img class="img_space" src="templates/{SET.theme}/img/space.png" alt="">
        <table class="table_flags">
            <tr>
                <td>{img_flag_v}</td>
                <td>{img_flag_a}</td>
                <td>{img_flag_s}</td>
            </tr>
        </table>
    </div>
    {extra_thumbs}
    {SHOW.trailer}
    <div id="trailer" class="trailer">{trailer}</div>
    {/SHOW.trailer}
</div>