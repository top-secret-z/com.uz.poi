{if $user->poiPois && $__wcf->session->getPermission('user.poi.canViewPoi')}
	<li><a href="{link application='poi' controller='UserPoiList' object=$user}{/link}">{lang}poi.poi.pois{/lang}</a></li>
{/if}
