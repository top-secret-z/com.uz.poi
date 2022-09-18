{if $categoryID}
    {capture assign='contentDescription'}
        {$category->getTitle()}
    {/capture}
{/if}

{capture assign='contentHeaderNavigation'}
    {if $categoryID || $poiSearch}
        <li><a href="{link application='poi' controller='Map'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}poi.poi.show.all{/lang}</span></a></li>
    {/if}
    {if $__wcf->session->getPermission('user.poi.canAddPoi')}
        <li><a href="{link application='poi' controller='PoiAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}poi.poi.add{/lang}</span></a></li>
    {/if}

    {event name='contentHeaderNavigation'}
{/capture}

{capture assign='sidebarRight'}
    <section class="box">
        <h2 class="boxTitle">{lang}poi.map.search.poi{if $poiSearch}.active{/if}{/lang}</h2>

        <div class="boxContent">
            <form method="post" action="{link application='poi' controller='Map'}{/link}">
                <div class="boxContent">
                    <dl>
                        <dt></dt>
                        <dd><input type="text" id="poiSearch" name="poiSearch" value="{$poiSearch}" class="long"></dd>
                    </dl>
                </div>

                <div class="formSubmit">
                    <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
                    {csrfToken}
                </div>
            </form>
        </div>
    </section>

    <section class="box" id="filterContainer">
        <h2 class="boxTitle">{lang}poi.map.filter{/lang} <span id="filterActive"></span></h2>

        <div class="boxContent">
            {foreach from=$categoryList item=categoryItem}
                {if $categoryItem->getPois()}
                    <label><input type="checkbox" name="category" id="category{$categoryItem->categoryID}" value={$categoryItem->categoryID} {if $categoryID && !$categoryItem->categoryID|in_array:$categoryIDs} disabled{else}checked{/if} /> {$categoryItem->getTitle()|truncate:POI_MAP_CATEGORY_SHORTEN}</label>
                    <small><span>({#$categoryItem->getPoisMap()})</span></small>

                    {if $categoryItem->hasChildren()}
                        <ol class="boxMenuDepth1">
                            {foreach from=$categoryItem item=subCategoryItem}
                                {if $subCategoryItem->getPois()}
                                    <li>
                                        <label>&nbsp;&nbsp;<input type="checkbox" name="category" id="category{$subCategoryItem->categoryID}" value={$subCategoryItem->categoryID} {if $categoryID && !$subCategoryItem->categoryID|in_array:$categoryIDs} disabled{else}checked{/if} /> {$subCategoryItem->getTitle()|truncate:POI_MAP_CATEGORY_SHORTEN}</label>
                                        <small><span>({#$subCategoryItem->getPoisMap()})</span></small>

                                        {if $subCategoryItem->hasChildren()}
                                            <ol class="boxMenuDepth2">
                                                {foreach from=$subCategoryItem item=subSubCategoryItem}
                                                    {if $subSubCategoryItem->getPois()}
                                                        <li>
                                                            <label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="category" id="category{$subSubCategoryItem->categoryID}" value={$subSubCategoryItem->categoryID} {if $categoryID && !$subSubCategoryItem->categoryID|in_array:$categoryIDs} disabled{else}checked{/if} /> {$subSubCategoryItem->getTitle()|truncate:POI_MAP_CATEGORY_SHORTEN}</label>
                                                            <small><span>({#$subSubCategoryItem->getPoisMap()})</span></small>

                                                            {if $subSubCategoryItem->hasChildren()}
                                                                <ol class="boxMenuDepth2">
                                                                    {foreach from=$subSubCategoryItem item=subSubSubCategoryItem}
                                                                        {if $subSubSubCategoryItem->getPois()}
                                                                            <li>
                                                                                <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="category" id="category{$subSubSubCategoryItem->categoryID}" value={$subSubSubCategoryItem->categoryID} {if $categoryID && !$subSubSubCategoryItem->categoryID|in_array:$categoryIDs} disabled{else}checked{/if} /> {$subSubSubCategoryItem->getTitle()|truncate:POI_MAP_CATEGORY_SHORTEN}</label>
                                                                                <small><span>({#$subSubSubCategoryItem->getPoisMap()})</span></small>
                                                                            </li>
                                                                        {/if}
                                                                    {/foreach}
                                                                </ol>
                                                            {/if}
                                                        </li>
                                                    {/if}
                                                {/foreach}
                                            </ol>
                                        {/if}
                                    </li>
                                {/if}
                            {/foreach}
                        </ol>
                    {/if}
                    <br>
                {/if}
            {/foreach}
        </div>

        <div class="boxContent">
            <dl>
                <dt></dt>
                <dd><input type="text" id="userFilter" name="userFilter" class="long" placeholder="{lang}poi.map.filter.user.placeholder{/lang}"></dd>
            </dl>
        </div>

        <div class="formSubmit">
            <button id="filterButton" class="button jsOnly"><span>{lang}poi.map.button.filter{/lang}</span></button>
            <button id="filterResetButton" class="button jsOnly"><span>{lang}poi.map.button.filter.reset{/lang}</span></button>
        </div>
    </section>

    <section class="box" id="controlContainer">
        <h2 class="boxTitle">{lang}poi.map.controls{/lang}</h2>

        <div class="formSubmit">
            <button id="centerButton" class="button jsOnly"><span>{lang}poi.map.button.center{/lang}</span></button>
            <button id="cleanupButton" class="button jsOnly"><span>{lang}poi.map.button.cleanup{/lang}</span></button>
        </div>
    </section>

    <section class="box">
        <h2 class="boxTitle">{lang}poi.map.search.location{/lang}</h2>

        <div class="boxContent">
            <dl>
                <dt></dt>
                <dd><input type="text" id="geocode" name="geocode" class="long" placeholder="{lang}poi.map.search.location.placeholder{/lang}"></dd>
            </dl>
        </div>
        <div class="formSubmit">
            <button id="searchButton" class="button jsOnly"><span>{lang}poi.map.button.search{/lang}</span></button>
            <button id="routeButton" class="button jsOnly"><span>{lang}poi.map.button.route{/lang}</span></button>
        </div>
    </section>

    {event name='boxes'}
{/capture}

{include file='header'}

<div class="section">
    <dl class="wide">
        <dt></dt>
        <dd>
            <div id="mapContainer" class="poiMap"></div>
        </dd>
    </dl>
</div>

{capture assign='footerBoxes'}
    {if POI_INDEX_ENABLE_STATS}
        <section class="box">
            <h2 class="boxTitle">{lang}poi.index.stats{/lang}</h2>

            <div class="boxContent">
                {lang}poi.index.stats.detail{/lang}
            </div>
        </section>
    {/if}

    {event name='infoBoxes'}
{/capture}

{include file='poiGoogleMapsJavaScript' application='poi'}
<script data-relocate="true">
    WCF.Language.addObject({
        'poi.map.filter.active':                     '{jslang}poi.map.filter.active{/jslang}',
        'poi.map.search.error.empty':                 '{jslang}poi.map.search.error.empty{/jslang}',
        'poi.map.search.error.locationNotFound':     '{jslang}poi.map.search.error.locationNotFound{/jslang}',
        'poi.map.search.error.direction':             '{jslang}poi.map.search.error.direction{/jslang}',
        'poi.map.route':                            '{jslang}poi.map.route{/jslang}',
        'poi.map.route.distance':                    '{jslang}poi.map.route.distance{/jslang}',
        'poi.map.route.error':                        '{jslang}poi.map.route.error{/jslang}',
        'poi.map.route.found':                        '{jslang}poi.map.route.found{/jslang}',
        'poi.map.route.open':                        '{jslang}poi.map.route.open{/jslang}',
        'poi.map.route.waypoints':                     '{jslang}poi.map.route.waypoints{/jslang}'
    });

    $(function() {
        new Poi.Map.LargeMap('mapContainer', '{$hideGooglePois}', '#geocode', { }, '{$poiSearch}', '{$categoryID}');
    });
</script>
<script data-relocate="true">
    require(['WoltLabSuite/Core/Ui/User/Search/Input'], function(UiUserSearchInput) {
        new UiUserSearchInput(elBySel('input[name="userFilter"]'));
    });
</script>

{include file='footer'}
