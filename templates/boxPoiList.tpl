{if $boxPosition == 'sidebarLeft' || $boxPosition == 'sidebarRight'}
    <ol class="sidebarItemList">
        {foreach from=$boxPoiList item=poi}
            <li>
                <a href="{link application='poi' controller='Poi' object=$poi}{/link}" class="poiPoiLink box24" data-poi-id="{@$poi->poiID}" title="{$poi->getSubject()}">
                    <span>{@$poi->getUserProfile()->getAvatar()->getImageTag(24)}</span>

                    <div>
                        <h3>{$poi->getSubject()}</h3>

                        {if $boxSortField == 'time'}
                            <small>{$poi->username} <span class="separatorLeft">{@$poi->time|time}</span></small>
                        {elseif $boxSortField == 'lastChangeTime'}
                            <small>{$poi->username} <span class="separatorLeft">{@$poi->lastChangeTime|time}</span></small>
                        {elseif $boxSortField == 'cumulativeLikes'}
                            <small>{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $poi->getCumulativeLikes()}{include file='__topReaction' cachedReactions=$poi->cachedReactions render='full'}{/if}</small>
                        {/if}
                    </div>
                </a>
            </li>
        {/foreach}
    </ol>
{else}
    <ol class="poiList">
        {assign var='enableEditMode' value=false}

        {include file='poiListItems' application='poi' objects=$boxPoiList}
    </ol>
{/if}
