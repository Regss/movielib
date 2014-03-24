<div id="{mysql_table}_{id}" class="movie">
    <div class="title">
        <a href="index.php?video={video}&id={id}">{title}</a>
    </div>
    <div class="title_org">{originaltitle}</div>
    {watched_img}
    {trailer_img}
    <img id="poster_movie_{id}" class="poster" src="{poster}" alt="">
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
                <td class="right">{rating}</td>
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
            {SHOW.sets}
            <tr>
                <td class="left">{LANG.i_sets}:</td>
                <td class="right">{sets}</td>
            </tr>
            {/SHOW.sets}
            {SHOW.seasons}
            <tr>
                <td class="left">{LANG.i_seasons}:</td>
                <td class="right">{seasons}</td>
            </tr>
            {/SHOW.seasons}
            {SHOW.cast}
            <tr>
                <td class="left">{LANG.i_cast}:</td>
                <td class="right">{cast}</td>
            </tr>
            {/SHOW.cast}
            {SHOW.plot}
            <tr>
                <td class="left">{LANG.i_plot}:</td>
                <td class="right">{plot}</td>
            </tr>
            {/SHOW.plot}
        </table>
        {episodes}
        <img class="img_space" src="templates/{SET.theme}/img/space.png" alt="">
        {img_flag_vres}{img_flag_vtype}{img_flag_atype}{img_flag_achan}
    </div>
    {SHOW.trailer}
    <div class="trailer">{trailer}</div>
    {/SHOW.trailer}
</div>