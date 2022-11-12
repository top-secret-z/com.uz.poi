{if $controllerName == 'PoiList'}
    {if !$__wcf->isLandingPage()}
        {capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}
    {/if}

    {assign var='catID' value=0}

{elseif $controllerName == 'CategoryPoiList'}
    {capture assign='pageTitle'}{$category->getTitle()}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}
    {capture assign='contentTitle'}{$category->getTitle()}{/capture}
    {capture assign='contentDescription'}
        {if $category->getDescription()}
            {if $category->descriptionUseHtml}{@$category->getDescription()}{else}{$category->getDescription()}{/if}
            <br>
        {/if}
    {/capture}

    {assign var='catID' value=$category->categoryID}

{elseif $controllerName == 'UserPoiList'}
    {capture assign='pageTitle'}{lang}poi.poi.userPois{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}
    {capture assign='contentTitle'}{lang}poi.poi.userPois{/lang}{/capture}
{/if}

{capture assign='headContent'}
    {if !$feedControllerName|empty}
        {if $__wcf->getUser()->userID}
            <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link application='poi' controller=$feedControllerName object=$controllerObject appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}">
        {else}
            <link rel="alternate" type="application/rss+xml" title="{lang}wcf.global.button.rss{/lang}" href="{link application='poi' controller=$feedControllerName object=$controllerObject appendSession=false}{/link}">
        {/if}
    {/if}
    {if $pageNo < $pages}
        <link rel="next" href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo+1}{/link}">
    {/if}
    {if $pageNo > 1}
        <link rel="prev" href="{link application='poi' controller=$controllerName object=$controllerObject}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
    {/if}
    <link rel="canonical" href="{link application='poi' controller=$controllerName object=$controllerObject}{if $pageNo > 1}pageNo={@$pageNo}{/if}{/link}">
{/capture}

{capture assign='contentHeaderNavigation'}
    {if $controllerName == 'PoiList' || $controllerName == 'CategoryPoiList' || $controllerName == 'MyPoiList' || $controllerName == 'UserPoiList'}
        {if $__wcf->session->getPermission('user.poi.canAddPoi') && $controllerName != 'UserPoiList'}
            <li><a href="{link application='poi' controller='PoiAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}poi.poi.add{/lang}</span></a></li>
        {/if}
    {/if}
{/capture}

{assign var='additionalLinkParameters' value=''}
{assign var='linkParameters' value=''}

{capture assign='contentInteractionPagination'}
    {pages print=true assign=pagesLinks application='poi' controller=$controllerName  object=$controllerObject link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$additionalLinkParameters"}
{/capture}

{capture assign='contentInteractionButtons'}
    {if $items}
        {if $controllerName == 'PoiList' || $controllerName == 'CategoryPoiList' || $controllerName == 'MyPoiList' || $controllerName == 'UserPoiList'}
            <div class="contentInteractionButton dropdown jsOnly">
                <a href="#" class="button small dropdownToggle"><span class="icon icon16 fa-sort-amount-asc"></span> <span>{lang}poi.poi.sort{/lang}</span></a>
                <ul class="dropdownMenu">
                    <li><a href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=subject&sortOrder={if $sortField == 'subject' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}poi.poi.sort.subject{/lang}{if $sortField == 'subject'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}poi.poi.sort.username{/lang}{if $sortField == 'username'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=lastChangeTime&sortOrder={if $sortField == 'lastChangeTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}poi.poi.sort.lastChangeTime{/lang}{if $sortField == 'lastChangeTime'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=views&sortOrder={if $sortField == 'views' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}poi.poi.sort.views{/lang}{if $sortField == 'views'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    {if POI_VISITS_ENABLE && $__wcf->session->getPermission('user.poi.canSeeVisit')}
                        <li><a href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=visits&sortOrder={if $sortField == 'visits' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}poi.poi.sort.visits{/lang}{if $sortField == 'visits'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    {/if}
                    <li><a href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=comments&sortOrder={if $sortField == 'comments' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}poi.poi.sort.comments{/lang}{if $sortField == 'comments'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>
                    <li><a href="{link application='poi' controller=$controllerName object=$controllerObject}pageNo={@$pageNo}&sortField=cumulativeLikes&sortOrder={if $sortField == 'cumulativeLikes' && $sortOrder == 'DESC'}ASC{else}DESC{/if}{@$linkParameters}{/link}">{lang}poi.poi.sort.cumulativeLikes{/lang}{if $sortField == 'cumulativeLikes'} <span class="icon icon16 fa-caret-{if $sortOrder == 'ASC'}up{else}down{/if}"></span>{/if}</a></li>

                    {event name='sortOptions'}
                </ul>
            </div>
        {/if}
    {/if}

    {if $controllerName == 'CategoryPoiList'}
        {if $__wcf->user->userID}
            <a href="#" class="contentInteractionButton jsSubscribeButton jsOnly button small{if $category->isSubscribed()} active{/if}" data-object-type="com.uz.poi.category" data-object-id="{@$category->categoryID}"><span class="icon icon16 fa-bookmark{if !$category->isSubscribed()}-o{/if}"></span> <span>{lang}wcf.user.objectWatch.button.subscribe{/lang}</span></a>
        {/if}
    {/if}

    <a href="#" class="markAllAsReadButton contentInteractionButton button small jsOnly"><span class="icon icon16 fa-check"></span> <span>{lang}poi.category.markAllAsRead{/lang}</span></a>
{/capture}

{capture assign='contentInteractionDropdownItems'}
    {if $category|isset && $items}
        <li><a href="{link application='poi' controller='Map' id=$category->categoryID}{/link}">{lang}poi.poi.category.map{/lang}</a></li>
    {/if}

    {if $__wcf->session->getPermission('user.poi.canDownloadPois') && $items > 0}
        {if $controllerName == 'PoiList' || $controllerName == 'CategoryPoiList'}
            <li><a href="{link application='poi' controller='Download' catID=$catID}{/link}">{lang}poi.poi.download{/lang}</a></li>
        {/if}

        {if $controllerName == 'UserPoiList'}
            <li><a href="{link application='poi' controller='Download' userID=$userID}{/link}">{lang}poi.poi.download{/lang}</a></li>
        {/if}

        {if $controllerName == 'MyPoiList'}
            <li><a href="{link application='poi' controller='Download' userID=$__wcf->getUser()->userID}{/link}">{lang}poi.poi.download{/lang}</a></li>
        {/if}
    {/if}

    {if $__wcf->session->getPermission('user.poi.canImportPois') && $controllerName != 'UserPoiList'}
        <li><a href="{link application='poi' controller='PoiImport'}{/link}">{lang}poi.poi.import.button{/lang}</a></li>
    {/if}

    <li><a rel="alternate" href="{if $__wcf->getUser()->userID}{link application='poi' controller=$feedControllerName object=$controllerObject appendSession=false}at={@$__wcf->getUser()->userID}-{@$__wcf->getUser()->accessToken}{/link}{else}{link application='poi' controller=$feedControllerName object=$controllerObject appendSession=false}{/link}{/if}" class="rssFeed">{lang}wcf.global.button.rss{/lang}</a></li>
{/capture}

{include file='header'}

{if $items}
    <div class="section jsClipboardContainer" data-type="com.uz.poi.poi">
        <div class="section">
            {if $controllerName == 'WatchedPoiList'}
                {assign var='enableEditMode' value=false}
                {assign var='enableWatchMode' value=true}
            {/if}

            <ol class="poiList">
                {include file='poiListItems' application='poi'}
            </ol>
        </div>
    </div>
{else}
    <p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<footer class="contentFooter">
    {hascontent}
        <div class="paginationBottom">
            {content}{@$pagesLinks}{/content}
        </div>
    {/hascontent}

    {hascontent}
        <nav class="contentFooterNavigation">
            <ul>
                {content}
                    {if $controllerName == 'PoiList' || $controllerName == 'CategoryPoiList' || $controllerName == 'MyPoiList' || $controllerName == 'UserPoiList'}
                        {if $__wcf->session->getPermission('user.poi.canAddPoi') && $controllerName != 'UserPoiList'}
                            <li><a href="{link application='poi' controller='PoiAdd'}{/link}" class="button buttonPrimary"><span class="icon icon16 fa-plus"></span> <span>{lang}poi.poi.add{/lang}</span></a></li>
                        {/if}
                    {/if}

                    {event name='contentFooterNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</footer>

{if $controllerName == 'PoiList'}
    {capture assign='footerBoxes'}
        {if POI_INDEX_ENABLE_STATS}
            <section class="box">
                <h2 class="boxTitle">{lang}poi.index.stats{/lang}</h2>

                <div class="boxContent">
                    {lang}poi.index.stats.detail{/lang}
                </div>
            </section>
        {/if}
    {/capture}
{/if}

{if $controllerName == 'WatchedPoiList'}
    <script data-relocate="true">
        $(function() {
            WCF.Language.addObject({
                'wcf.user.objectWatch.manageSubscription':                     '{jslang}wcf.user.objectWatch.manageSubscription{/jslang}',
                'poi.poi.watchedPois.stopWatchingAll':                         '{jslang}poi.poi.watchedPois.stopWatchingAll{/jslang}',
                'poi.poi.watchedPois.stopWatchingAll.confirmMessage':         '{jslang}poi.poi.watchedPois.stopWatchingAll.confirmMessage{/jslang}',
                'poi.poi.watchedPois.stopWatchingMarked':                     '{jslang}poi.poi.watchedPois.stopWatchingMarked{/jslang}',
                'poi.poi.watchedPois.stopWatchingMarked.confirmMessage':     '{jslang}poi.poi.watchedPois.stopWatchingMarked.confirmMessage{/jslang}'
            });

            new Poi.Poi.WatchedPoiList();
            new WCF.User.ObjectWatch.Subscribe();
        });
    </script>
{else}
    <script data-relocate="true">
        $(function() {
            WCF.Language.addObject({
                'poi.poi.edit.delete':                         '{jslang}poi.poi.edit.delete{/jslang}',
                'poi.poi.edit.disable':                     '{jslang}poi.poi.edit.disable{/jslang}',
                'poi.poi.edit.enable':                         '{jslang}poi.poi.edit.enable{/jslang}',
                'poi.poi.edit.restore':                     '{jslang}poi.poi.edit.restore{/jslang}',
                'poi.poi.edit.setAsFeatured':                 '{jslang}poi.poi.edit.setAsFeatured{/jslang}',
                'poi.poi.edit.trash':                         '{jslang}poi.poi.edit.trash{/jslang}',
                'poi.poi.edit.unsetAsFeatured':             '{jslang}poi.poi.edit.unsetAsFeatured{/jslang}',
                'poi.poi.featured':                         '{jslang}poi.poi.featured{/jslang}',
                'poi.poi.confirmDelete':                     '{jslang}poi.poi.confirmDelete{/jslang}',
                'poi.poi.version.confirmDelete':             '{jslang}poi.poi.version.confirmDelete{/jslang}',
                'poi.poi.confirmTrash':                     '{jslang}poi.poi.confirmTrash{/jslang}',
                'poi.poi.confirmTrash.reason':                 '{jslang}poi.poi.confirmTrash.reason{/jslang}',
                'wcf.user.objectWatch.manageSubscription':     '{jslang}wcf.user.objectWatch.manageSubscription{/jslang}',

            });

            {if $__wcf->session->getPermission('mod.poi.canEditPoi')}
                var $updateHandler = new Poi.Poi.UpdateHandler.Category();

                var $inlineEditor = new Poi.Poi.InlineEditor('.poiPoi');
                $inlineEditor.setEnvironment('category');
                $inlineEditor.setUpdateHandler($updateHandler);
                $inlineEditor.setPermissions({
                    canDeletePoi:             {@$__wcf->session->getPermission('mod.poi.canDeletePoi')},
                    canDeletePoiCompletely: {@$__wcf->session->getPermission('mod.poi.canDeletePoiCompletely')},
                    canEnablePoi:             {@$__wcf->session->getPermission('mod.poi.canModeratePoi')},
                    canRestorePoi:             {@$__wcf->session->getPermission('mod.poi.canRestorePoi')},
                    canSetAsFeatured:         {@$__wcf->session->getPermission('mod.poi.canEditPoi')}
                });

                var $poiClipboard = new Poi.Poi.Clipboard($updateHandler);

                WCF.Clipboard.init('poi\\page\\PoiListPage', {@$hasMarkedItems}, { }, {if !$category|empty}{@$category->categoryID}{else}0{/if});
            {/if}

            {if $controllerName == 'UnreadPoiList'}
                new Poi.Category.MarkAllAsRead(function() {
                    window.location = '{link application='poi' controller='PoiList'}{/link}';
                });
            {else}
                new Poi.Category.MarkAllAsRead();
            {/if}

            {if $controllerName == 'CategoryPoiList'}
                new WCF.User.ObjectWatch.Subscribe();
            {/if}
        });
    </script>
{/if}

{include file='footer'}
