{if $selectedMarker|isset}
	<dl>
		<dt><label for="selectedMarker">{lang}poi.acp.category.marker{/lang}</label></dt>
		<dd class="floated">
			{foreach from=$markers key=name item=link}
				<label><input type="radio" name="selectedMarker" value={$name}{if $selectedMarker == $name} checked{/if} /> <span>{@$link}</span></label>
			{/foreach}
		</dd>
	</dl>
{/if}
