<header class="contentHeader messageGroupContentHeader poiPoi">
    <div class="contentHeaderIcon">
        {@$poi->getIconTag(64)}
    </div>

    <div class="contentHeaderTitle">
        <h1 class="contentTitle">
            {if $poi->isFeatured}
                <span class="badge label green jsLabelFeatured">{lang}poi.poi.featured{/lang}</span>
            {/if}
            <span>{$poi->getSubject()}</span>
        </h1>
        <ul class="inlineList contentHeaderMetaData">
            <li>
                <span class="icon icon16 fa-user"></span>
                {if $poi->userID}
                    {user object=$poi->getUserProfile()}
                {else}
                    {$poi->username}
                {/if}
            </li>

            <li>
                <span class="icon icon16 fa-clock-o"></span>
                {@$poi->time|time}
            </li>

            {if $poi->views}
                <li>
                    <span class="icon icon16 fa-eye"></span>
                    {lang}poi.poi.views{/lang}
                </li>
            {/if}

            <li>
                <span class="icon icon16 fa-comments"></span>
                {lang}poi.poi.poiComments{/lang}
            </li>
        </ul>
    </div>
</header>

<div id="overview" class="section poiPoiContent">
    <div class="htmlContent poiPoiTeaser">
        {@$poi->getTeaser()}
    </div>

    <div class="htmlContent">
        {@$poi->getFormattedMessage()}
    </div>
</div>
