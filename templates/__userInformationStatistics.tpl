{if $user->poiPois}
	<dt><a href="{link application='poi' controller='UserPoiList' object=$user}{/link}" title="{lang}poi.poi.userPois{/lang}" class="jsTooltip">{lang}poi.poi.pois{/lang}</a></dt>
	<dd>{#$user->poiPois}</dd>
{/if}