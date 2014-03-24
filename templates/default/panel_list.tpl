<div id="{mysql_table}_{id}" class="movie">
    <div class="title">
        <a href="index.php?video={video}&id={id}">{title}</a>
    </div>
    <div class="title_org">{originaltitle}</div>
    {watched}
    {trailer}
    <img id="poster_movie_{id}" class="poster" src="{poster}" alt="">
    <div class="desc">
        <table class="table">
            {SHOW.desc_year}
            <tr>
                <td class="left">{LANG.i_year}:</td>
                <td class="right">{output_year}</td>
            </tr>
            {/SHOW.desc_year}
            {SHOW.desc_premiered}
            <tr>
                <td class="left">{LANG.i_premiered}:</td>
                <td class="right">{output_premiered}</td>
            </tr>
            {/SHOW.desc_premiered}
            {SHOW.desc_genre}
            <tr>
                <td class="left">{LANG.i_genre}:</td>
                <td class="right">{output_genre}</td>
            </tr>
            {/SHOW.desc_genre}
            {SHOW.desc_rating}
            <tr>
                <td class="left">{LANG.i_rating}:</td>
                <td class="right">{output_rating}</td>
            </tr>
            {/SHOW.desc_rating}
            {SHOW.desc_country}
            <tr>
                <td class="left">{LANG.i_country}:</td>
                <td class="right">{output_country}</td>
            </tr>
            {/SHOW.desc_country}
            {SHOW.desc_runtime}
            <tr>
                <td class="left">{LANG.i_runtime}:</td>
                <td class="right">{output_runtime} {LANG.i_minute}</td>
            </tr>
            {/SHOW.desc_runtime}
            {SHOW.desc_director}
            <tr>
                <td class="left">{LANG.i_director}:</td>
                <td class="right">{output_director}</td>
            </tr>
            {/SHOW.desc_director}
            {SHOW.desc_sets}
            <tr>
                <td class="left">{LANG.i_sets}:</td>
                <td class="right">{output_sets}</td>
            </tr>
            {/SHOW.desc_sets}
            {SHOW.desc_seasons}
            <tr>
                <td class="left">{LANG.i_seasons}:</td>
                <td class="right">{output_season}</td>
            </tr>
            {/SHOW.desc_seasons}
            {SHOW.desc_cast}
            <tr>
                <td class="left">{LANG.i_cast}:</td>
                <td class="right">{output_cast}</td>
            </tr>
            {/SHOW.desc_cast}
            {SHOW.desc_plot}
            <tr>
                <td class="left">{LANG.i_plot}:</td>
                <td class="right">{output_plot}</td>
            </tr>
            {/SHOW.desc_plot}
        </table>
        {output_episodes}
        <img class="img_space" src="css/{SET.theme}/img/space.png" alt="">
        {img_flag_vres}{img_flag_vtype}{img_flag_atype}{img_flag_achan}
    </div>
    <div class="trailer">{output_trailer}</div>
</div>