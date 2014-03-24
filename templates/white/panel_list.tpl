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
            {output_year}
            {output_premiered}
            {output_genre}
            {output_rating}
            {output_country}
            {output_runtime}
            {output_director}
            {output_sets}
            {output_season}
            {output_cast}
            {output_plot}
        </table>
        {output_episodes}
        <img class="img_space" src="css/{SET.theme}/img/space.png" alt="">
        {img_flag_vres}
        {img_flag_vtype}
        {img_flag_atype}
        {img_flag_achan}
    </div>
    {output_trailer}
</div>