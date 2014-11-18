<div id="{mysql_table}_{id}" class="movie">
    <div class="text">
        <span class="title">
            <a href="index.php?id={id}&video={video}&view={view}&sort={sort}&filter={filter}&filterid={filterid}">{title}</a>
        </span>
        {SHOW.originaltitle}
        <span class="title_org"> / 
        {originaltitle}
        </span>
        {/SHOW.originaltitle}
        <span class="bold">
        {SHOW.year}
        ({year})
        {/SHOW.year}
        </span>
    </div>
    <div class="images">
        <div>{trailer_img}</div>
        <div>{watched_img}</div>
    </div>
</div>