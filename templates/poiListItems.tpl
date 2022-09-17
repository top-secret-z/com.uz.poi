{* default values *}
{if !$enableEditMode|isset}{assign var='enableEditMode' value=true}{/if}
{if !$enableWatchMode|isset}{assign var='enableWatchMode' value=false}{/if}

{foreach from=$objects item=poi}
	<li class="poiPoi poi{@$poi->poiID} jsClipboardObject {if $poi->isDeleted}messageDeleted{else}{if $poi->isDisabled}messageDisabled{/if}{/if}" data-poi-id="{@$poi->poiID}" data-element-id="{@$poi->poiID}" data-object-id="{@$poi->poiID}" data-is-deleted="{@$poi->isDeleted}" data-is-disabled="{@$poi->isDisabled}" data-is-featured="{@$poi->isFeatured}">
		<div class="poiListPoi">
			<a href="{$poi->getLink()}" class="box128">
				<div class="poiListPoiIcon {if $poi->isDisabled || $poi->isDeleted}dimmed{/if}">
					<span>{@$poi->getIconTag(64)}</span>
					
					{if $poi->isFeatured}
						<span class="badge label green jsLabelFeatured poiListPoiFeatured">{lang}poi.poi.featured{/lang}</span>
					{/if}
				</div>
				
				<div class="poiListPoiData">
					<div class="containerHeadline">
						<h3 class="poiListPoiSubject">
							{if $enableEditMode && $__wcf->session->getPermission('mod.poi.canEditPoi')}
								<input type="checkbox" class="jsClipboardItem" data-object-id="{@$poi->poiID}">
							{elseif $enableWatchMode}
								<input type="checkbox" class="jsWatchedPoi" data-object-id="{@$poi->poiID}"
							{/if}
							
							{if $poi->isNew()}
								<span class="badge label">{lang}poi.poi.new{/lang}</span>
							{/if}
							<span>{$poi->getSubject()}</span>
						</h3>
						<ul class="inlineList dotSeparated poiListPoiDataMeta">
							<li>{$poi->username}</li>
							<li>{@$poi->lastChangeTime|time}</li>
						</ul>
					</div>
					
					<div class="containerContent">
						{$poi->getTeaser()}
					</div>
				</div>
			</a>
			
			<div class="poiListPoiFooter">
				{if $enableEditMode && $__wcf->session->getPermission('mod.poi.canEditPoi')}
					<div class="jsOnly poiListPoiEdit"><a href="{link controller='PoiEdit' object=$poi application='poi'}{/link}" class="jsPoiInlineEditor">{lang}wcf.global.button.edit{/lang}</a></div>
				{elseif $enableWatchMode}
					<div class="jsOnly poiListPoiEdit"><a class="jsSubscribeButton" data-object-id="{@$poi->poiID}" data-object-type="com.uz.poi.poi">{lang}wcf.user.objectWatch.manageSubscription{/lang}</a></div>
				{/if}
				
				<ul class="inlineList poiListPoiStats">
					{if $poi->views}
						<li>
							<span class="icon icon16 fa-eye"></span>
							{lang}poi.poi.views{/lang}
						</li>
					{/if}
					
					{if POI_VISITS_ENABLE && $__wcf->session->getPermission('user.poi.canSeeVisit')}
						<li>
							<span class="icon icon16 fa-{POI_VISITS_ICON}"></span>
							{lang}poi.poi.visits{/lang}
						</li>
					{/if}
					
					{if $poi->enableComments}
						<li>
							<span class="icon icon16 fa-comments"></span>
							{lang}poi.poi.poiComments{/lang}
						</li>
					{/if}
					
					{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $poi->cumulativeLikes}
						<li>
							{include file='__topReaction' cachedReactions=$poi->cachedReactions render='short'}
						</li>
					{/if}
				</ul>
			</div>
		</div>
	</li>
{/foreach}
