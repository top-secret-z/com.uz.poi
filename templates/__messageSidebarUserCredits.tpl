{if POI_MESSAGE_SIDEBAR_ENABLE_USER_POIS && $userProfile->poiPois}
    <dt><a href="{link application='poi' controller='UserPoiList' object=$userProfile}{/link}" title="{lang user=$userProfile}poi.poi.userPois{/lang}" class="jsTooltip">{lang}poi.poi.pois{/lang}</a></dt>
    <dd>{#$userProfile->poiPois}</dd>
{/if}
