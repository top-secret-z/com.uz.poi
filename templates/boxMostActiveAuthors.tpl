<ul class="sidebarItemList">
	{foreach from=$mostActiveAuthors item=activeAuthor}
		<li class="box24">
			<a href="{link application='poi' controller='UserPoiList' object=$activeAuthor}{/link}">{@$activeAuthor->getAvatar()->getImageTag(24)}</a>
			
			<div class="sidebarItemTitle">
				<h3><a href="{link application='poi' controller='UserPoiList' object=$activeAuthor}{/link}">{$activeAuthor->username}</a></h3>
				<small>{lang}poi.poi.userCount{/lang}</small>
			</div>
		</li>
	{/foreach}
</ul>
