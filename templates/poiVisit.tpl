<dl>
	<dt></dt>
	<dd>
		<label><input type="checkbox" id="visitEnable"{if $visitTime} checked{/if}> {lang}poi.poi.visit.visited{/lang}</label>
	</dd>
</dl>

<dl id="visitTimeContainer"{if !$visitTime} style="display: none"{/if}>
	<dt><label for="visitTime">{lang}poi.poi.visit.visited.time{/lang}</label></dt>
	<dd>
		<input type="datetime" id="visitTime" value="{$visitTime}" class="medium">
		<small class="jsVisitError">{lang}poi.poi.visit.visited.time.description{/lang}</small>
	</dd>
</dl>

<div class="formSubmit">
	<button class="buttonPrimary jsSubmitVisit">{lang}wcf.global.button.submit{/lang}</button>
</div>
