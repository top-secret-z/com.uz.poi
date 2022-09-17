<div class="poiInfoWindow">
	<div class="box32">
		<a href="{$poi->getLink()}">{@$poi->getIconTag(32)}</a>
		
		<div>
			<h3>
				<a href="{link application='poi' controller='Poi' object=$poi}{/link}" class="jsTooltip" title="{$poi->getSubject}">{$poi->getSubject()}</a>
			</h3>
			
			<div>
				<ul class="inlineList dotSeparated poiListPoiDataMeta">
					<li>{if $poi->userID}{user object=$poi->getUserProfile()}{else}{$poi->username}{/if}</li>
					<li>{@$poi->lastChangeTime|time}</li>
					{if $poi->enableComments}
						<li>{lang}poi.poi.poiComments{/lang}</li>
					{/if}
				</ul>
			</div>
			<br>
			<div>
				<strong>{$poi->location}</strong>
			</div>
			<br>
			<div>
				{$poi->getTeaser()}
			</div>
		</div>
	</div>
</div>