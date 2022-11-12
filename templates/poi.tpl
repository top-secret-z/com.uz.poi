{capture assign='pageTitle'}{$poi->getSubject()}{/capture}

{assign var='__mainItemScope' value='itemprop="mainEntity" itemscope itemtype="http://schema.org/CreativeWork"'}

{capture assign='contentHeader'}
    <header class="contentHeader messageGroupContentHeader poiPoi poi{@$poi->poiID} {if $poi->isDeleted}messageDeleted{else}{if $poi->isDisabled}messageDisabled{/if}{/if}"
        data-object-id="{@$poi->poiID}"
        data-poi-id="{@$poi->poiID}"
        data-is-deleted="{if $poi->isDeleted}true{else}false{/if}"
        data-is-disabled="{if $poi->isDisabled}true{else}false{/if}"
        data-is-featured="{if $poi->isFeatured}true{else}false{/if}"
    >
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

                {if LOG_IP_ADDRESS && $poi->ipAddress && $__wcf->session->getPermission('admin.user.canViewIpAddress')}
                    <li>
                        <span class="icon icon16 fa-globe"></span>
                        {$poi->getIpAddress()}
                    </li>
                {/if}

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

                <li class="poiPoiLikesBadge"></li>
            </ul>
        </div>

        {hascontent}
            <nav class="contentHeaderNavigation">
                <ul>
                    {content}
                        {if POI_VISITS_ENABLE && $__wcf->session->getPermission('user.poi.canVisitPoi') && $__wcf->user->userID}
                            <li class="jsPoiVisit jsOnly" data-object-id="{@$poi->poiID}"><a href="#" title="{lang}poi.poi.visit.manage{/lang}" class="button {if $poi->isVisitor()}active {/if}jsTooltip"><span class="icon icon16 fa-{POI_VISITS_ICON}"></span></a></li>
                        {/if}

                        {event name='contentHeaderNavigation'}
                    {/content}
                </ul>
            </nav>
        {/hascontent}
    </header>
{/capture}

{capture assign='sidebarRight'}
    {event name='poiBoxes'}

    <section class="box">
        <h2 class="boxTitle">{lang}poi.poi.box.map{/lang}</h2>

        <div class="boxContent">
            <small>{$poi->location}</small>
            <div class="sidebarGoogleMap" id="imageMap"></div>
            <br>
            <dl class="plain dataList">
                <dt>{lang}poi.poi.box.information.latitude{/lang}</dt>
                <dd>{lang}poi.poi.box.information.latitude.value{/lang}<br>{$poi->getFormattedLatitude()}</dd>

                <dt>{lang}poi.poi.box.information.longitude{/lang}</dt>
                <dd>{lang}poi.poi.box.information.longitude.value{/lang}<br>{$poi->getFormattedLongitude()}</dd>

                <dt>{lang}poi.poi.box.information.elevation{/lang}</dt>
                <dd>{lang}poi.poi.box.information.elevation.value{/lang}</dd>
                {if POI_VISITS_ENABLE && $__wcf->session->getPermission('user.poi.canSeeVisit')}
                    <dt>{lang}poi.poi.visit.visits{/lang}</dt>
                    {if !$poi->visits}
                        <dd>{lang}poi.poi.visit.visits.value{/lang}</dd>
                    {else}
                        <dd><a class="jsVisitors jsOnly">{lang}poi.poi.visit.visits.value{/lang}</a></dd>
                    {/if}
                {/if}
            </dl>
        </div>
    </section>

    {if $userPoiList|count}
        <section class="box">
            <h2 class="boxTitle">{lang}poi.poi.morePois.user{/lang}</h2>

            <div class="boxContent">
                <ul class="sidebarItemList">
                    {foreach from=$userPoiList item=userPoi}
                        <li class="box24">
                            <a href="{link application='poi' controller='Poi' object=$userPoi}{/link}">{@$userPoi->getIconTag(24)}</a>

                            <div class="sidebarItemTitle">
                                <h3><a href="{link application='poi' controller='Poi' object=$userPoi}{/link}" class="poiPoiLink" data-poi-id="{@$userPoi->poiID}" title="{$userPoi->getSubject()}">{$userPoi->getSubject()}</a></h3>
                                <small>{user object=$userPoi->getUserProfile()} <span class="separatorLeft">{@$userPoi->time|time}</span></small>
                            </div>
                        </li>
                    {/foreach}
                </ul>

                {if $userPoiList|count >= 5}
                    <a href="{link application='poi' controller='UserPoiList' object=$poi->getUserProfile()}{/link}" class="button small more">{lang}poi.poi.morePois.all{/lang}</a>
                {/if}
            </div>
        </section>
    {/if}

    {if $tags|count}
        <section class="box">
            <h2 class="boxTitle">{lang}wcf.tagging.tags{/lang}</h2>

            <div class="boxContent">
                <ul class="tagList">
                    {foreach from=$tags item=tag}
                        <li><a href="{link controller='Tagged' object=$tag}objectType=com.uz.poi.poi{/link}" class="jsTooltip tag" title="{lang}wcf.tagging.taggedObjects.com.uz.poi.poi{/lang}">{$tag->name}</a></li>
                    {/foreach}
                </ul>
            </div>
        </section>
    {/if}

    {event name='boxes'}
{/capture}

{capture assign='contentInteractionButtons'}
    {if $__wcf->user->userID && $__wcf->user->userID != $poi->userID}
        <a href="#" class="jsSubscribeButton contentInteractionButton button small jsOnly{if $poi->isSubscribed()} active{/if}" data-object-type="com.uz.poi.poi" data-object-id="{@$poi->poiID}"><span class="icon icon16 fa-bookmark{if !$poi->isSubscribed()}-o{/if}"></span> <span>{lang}wcf.user.objectWatch.button.subscribe{/lang}</span></a>
    {/if}
{/capture}

{capture assign='contentInteractionDropdownItems'}
    <li><a href="https://www.google.com/maps/search/?api=1&amp;query={$poi->latitude},{$poi->longitude}" {if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}{if EXTERNAL_LINK_REL_NOFOLLOW} rel="nofollow"{/if}>{lang}poi.poi.open.google{/lang}</a></li>
    {if $__wcf->session->getPermission('user.poi.canDownloadPois')}
        <li><a href="{link application='poi' controller='Download' poiID=$poi->poiID}{/link}">{lang}poi.poi.download{/lang}</a></li>
    {/if}

    {if $__wcf->session->getPermission('mod.poi.canEditPoi')}
        <li><a href="{link application='poi' controller='PoiLog' id=$poi->poiID}{/link}">{lang}poi.poi.log{/lang}</a></li>
    {/if}
{/capture}

{include file='header'}

{if $poi->isDisabled && !$__wcf->session->getPermission('mod.poi.canModeratePoi')}
    <p class="info">{lang}poi.poi.moderation.disabledPoi{/lang}</p>
{/if}

<div id="overview" class="section poiPoiContent{if $poi->getUserProfile()->userOnlineGroupID} userOnlineGroupMarking{@$poi->getUserProfile()->userOnlineGroupID}{/if}"
    {@$__wcf->getReactionHandler()->getDataAttributes('com.uz.poi.likeablePoi', $poi->poiID)}
    >
    {assign var='objectID' value=$poi->poiID}

    <div class="poiPoiTeaser">
        {$poi->getTeaser()}
    </div>

    {if $poi->hasCoverPhoto()}
        <div class="section articleImageContainer" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
            {assign var=coverPhotoImage value=$poi->getCoverPhotoImage()}

            <figure class="contentCoverPhotoImage">
                <div class="contentCoverPhotoImageWrapper"><img src="{@$coverPhotoImage->getUrl()}" alt=""></div>
            </figure>
            <meta itemprop="url" content="{$coverPhotoImage->getUrl()}">
            <meta itemprop="width" content="{@$coverPhotoImage->getWidth()}">
            <meta itemprop="height" content="{@$coverPhotoImage->getHeight()}">
        </div>
    {/if}

    {hascontent}
        <div class="section">
            <dl>
                {content}
                    {if !$options|empty}
                        {foreach from=$options item=poiOptionData}
                            {assign var=poiOption value=$poiOptionData[object]}
                            {if $poiOption->getOptionValue()}
                                <dt>{lang}{$poiOption->optionTitle}{/lang}</dt>
                                <dd>{@$poiOption->getFormattedOptionValue()}</dd>
                            {/if}
                        {/foreach}
                    {/if}
                {/content}
            </dl>
        </div>
    {/hascontent}

    <div class="section">
        <div class="htmlContent">
            {@$poi->getFormattedMessage()}
        </div>
    </div>

    {include file='attachments'}

    <div id="poiDeleteNoteDiv">
        {if $poi->getDeleteNote()}
            <div class="section">
                <p class="poiPoiDeleteNote">&nbsp;{@$poi->getDeleteNote()}</p>
            </div>
        {/if}
    </div>

    {if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $poiLikeData|isset}
        <div class="section">
            <div class="section poiPoiLikesSummery">{include file="reactionSummaryList" reactionData=$poiLikeData objectType="com.uz.poi.likeablePoi" objectID=$poi->poiID}</div>
        </div>
    {/if}

    <div class="section">
        <ul id="poiButtonContainer" class="buttonList smallButtons poiPoiButtons buttonGroup jsPoiInlineEditorContainer" data-poi-id="{@$poi->poiID}">
            {if $poi->canEdit()}<li><a href="{link application='poi' controller='PoiEdit' id=$poi->poiID}{/link}" class="button jsPoiInlineEditor" title="{lang}poi.poi.edit{/lang}"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.global.button.edit{/lang}</span></a></li>{/if}
            {if $poi->canEdit() && $poi->hasOldVersions()}
                <li><a href="{link controller='EditHistory' objectType='com.uz.poi.poi' objectID=$poi->poiID}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}poi.poi.hasOldVersions{/lang}</span></a></li>
            {/if}
            <li>
                <a href="{$poi->getLink()}" class="button wsShareButton jsOnly" data-link-title="{$poi->getSubject()}" data-bbcode="[poi]{@$poi->getObjectID()}[/poi]">
                    <span class="icon icon16 fa-share-alt"></span> <span>{lang}wcf.message.share{/lang}</span>
                </a>
            </li>
            {if MODULE_USER_INFRACTION && $userProfile->userID && $__wcf->session->getPermission('mod.infraction.warning.canWarn') && !$userProfile->getPermission('mod.infraction.warning.immune')}<li class="jsWarnPoi jsOnly" data-object-id="{@$poi->poiID}" data-user-id="{@$userProfile->userID}"><a href="#" title="{lang}wcf.infraction.warn{/lang}" class="button jsTooltip"><span class="icon icon16 fa-legal"></span> <span class="invisible">{lang}wcf.infraction.warn{/lang}</span></a></li>{/if}
            {if $__wcf->session->getPermission('user.profile.canReportContent')}<li class="jsReportPoi jsOnly" data-object-id="{@$poi->poiID}"><a href="#" title="{lang}wcf.moderation.report.reportContent{/lang}" class="button jsTooltip"><span class="icon icon16 fa-warning"></span> <span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span></a></li>{/if}
            {if MODULE_LIKE && $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canViewLike') && $poi->userID != $__wcf->user->userID}<li><a href="#" id="poiReactButton" class="reactButton jsTooltip button{if $poiLikeData[$poi->poiID]|isset && $poiLikeData[$poi->poiID]->reactionTypeID} active{/if}" title="{lang}wcf.reactions.react{/lang}" data-reaction-type-id="{if $poiLikeData[$poi->poiID]|isset && $poiLikeData[$poi->poiID]->reactionTypeID}{$poiLikeData[$poi->poiID]->reactionTypeID}{else}0{/if}"><span class="icon icon16 fa-smile-o"></span> <span class="invisible">{lang}wcf.reactions.react{/lang}</span></a></li>{/if}
        </ul>
    </div>
</div>

{if $poi->enableComments}
    {if $commentList|count || $commentCanAdd}
        <section id="comments" class="section sectionContainerList">
            <header class="sectionHeader">
                <h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $poi->comments} <span class="badge">{#$poi->comments}</span>{/if}</h2>
            </header>

            {include file='__commentJavaScript' commentContainerID='poiPoiCommentList'}

            <div class="poiComments">
                <ul id="poiPoiCommentList" class="commentList containerList" data-can-add="{if $commentCanAdd}true{else}false{/if}" data-object-id="{@$poiID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{if $poi->comments}{@$commentList->countObjects()}{else}0{/if}" data-last-comment-time="{@$lastCommentTime}">
                    {include file='commentListAddComment' wysiwygSelector='poiPoiCommentListAddComment'}
                    {include file='commentList'}
                </ul>
            </div>
        </section>
    {/if}
{/if}

{include file='googleMapsJavaScript'}

<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Infraction{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@LAST_UPDATE_TIME}"></script>
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
            'poi.poi.isDeleted':                         '{jslang}poi.poi.isDeleted{/jslang}',
            'poi.poi.isDisabled':                         '{jslang}poi.poi.isDisabled{/jslang}',
            'poi.poi.confirmDelete':                     '{jslang}poi.poi.confirmDelete{/jslang}',
            'poi.poi.confirmTrash':                     '{jslang}poi.poi.confirmTrash{/jslang}',
            'poi.poi.confirmTrash.reason':                 '{jslang}poi.poi.confirmTrash.reason{/jslang}',
            'poi.poi.log.poi.trash.summary.js':         '{jslang}poi.poi.log.poi.trash.summary.js{/jslang}',
            'wcf.infraction.warn':                         '{jslang}wcf.infraction.warn{/jslang}',
            'wcf.infraction.warn.success':                 '{jslang}wcf.infraction.warn.success{/jslang}',
            'wcf.moderation.report.reportContent':         '{jslang}wcf.moderation.report.reportContent{/jslang}',
            'wcf.moderation.report.success':             '{jslang}wcf.moderation.report.success{/jslang}',
            'wcf.message.bbcode.code.copy':             '{jslang}wcf.message.bbcode.code.copy{/jslang}',
            'wcf.user.objectWatch.manageSubscription':     '{jslang}wcf.user.objectWatch.manageSubscription{/jslang}'

        });

        var $updateHandler = new Poi.Poi.UpdateHandler.Poi();
        var $inlineEditor = new Poi.Poi.InlineEditor('.jsPoiInlineEditorContainer');
        $inlineEditor.setEnvironment('poi', '{link application='poi' controller='PoiList' encode=false}{/link}');
        $inlineEditor.setUpdateHandler($updateHandler);
        $inlineEditor.setPermissions({
            canDeletePoi:                 {if $poi->canDelete()}1{else}0{/if},
            canDeletePoiCompletely:     {@$__wcf->session->getPermission('mod.poi.canDeletePoiCompletely')|intval},
            canEnablePoi:                 {@$__wcf->session->getPermission('mod.poi.canModeratePoi')|intval},
            canRestorePoi:                 {@$__wcf->session->getPermission('mod.poi.canRestorePoi')|intval},
            canSetAsFeatured:             {@$__wcf->session->getPermission('mod.poi.canEditPoi')|intval},
            canViewDeletedPoi:             {@$__wcf->session->getPermission('mod.poi.canViewDeletedPoi')|intval}
        });

        {if $__wcf->session->getPermission('user.profile.canReportContent')}
            new WCF.Moderation.Report.Content('com.uz.poi.poi', '.jsReportPoi');
        {/if}

        {if MODULE_USER_INFRACTION && $__wcf->session->getPermission('mod.infraction.warning.canWarn')}
            new WCF.Infraction.Warning.Content('com.uz.poi.warnablePoi', '.jsWarnPoi');
        {/if}

        new WCF.User.ObjectWatch.Subscribe();
        new WCF.Message.BBCode.CodeViewer();

        WCF.Clipboard.init('poi\\page\\PoiPage', {@$hasMarkedItems}, { }, 0);

        var $map = new WCF.Location.GoogleMaps.Map('imageMap');
        WCF.Location.GoogleMaps.Util.focusMarker($map.addMarker({@$poi->latitude}, {@$poi->longitude}, '{$poi->subject|encodeJS}'));

    });
</script>

{if MODULE_LIKE && $__wcf->getUser()->userID && $__wcf->getSession()->getPermission('user.like.canViewLike')}
    <script data-relocate="true">
        require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
            new UiReactionHandler('com.uz.poi.likeablePoi', {
                // settings
                isSingleItem: true,

                // selectors
                buttonSelector: '#poiReactButton',
                containerSelector: '#overview'
            });
        });
    </script>
{/if}

{if POI_VISITS_ENABLE && $__wcf->session->getPermission('user.poi.canVisitPoi') && $__wcf->user->userID}
    <script data-relocate="true">
        require(['Language', 'UZ/Poi/Visit'], function (Language, UzPoiVisit) {
            Language.addObject({
                'poi.poi.visit.dialog.title':    '{jslang}poi.poi.visit.dialog.title{/jslang}',
                'poi.poi.visit.success':        '{jslang}poi.poi.visit.success{/jslang}'
            });

            UzPoiVisit.init({@$poi->poiID}, {@$poi->visitTime});
        });
    </script>
{/if}

{if POI_VISITS_ENABLE && $__wcf->session->getPermission('user.poi.canSeeVisit')}
    <script data-relocate="true">
        $(function() {
            var $visitors = null;
            $('.jsVisitors').click(function() {
                if ($visitors === null) {
                    $visitors = new WCF.User.List('poi\\data\\poi\\PoiAction', '{jslang}poi.poi.visit.visitors{/jslang}', { poiID: '{@$poi->poiID}' });
                }
                $visitors.open();
            });
        });
    </script>
{/if}

{include file='footer'}
