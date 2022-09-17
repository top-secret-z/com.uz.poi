{include file='header' pageTitle='poi.acp.poi.option.'|concat:$action}

<script data-relocate="true">
	$(function() {
		var $optionTypesUsingSelectOptions = [{implode from=$optionTypesUsingSelectOptions item=optionTypeUsingSelectOptions}'{@$optionTypeUsingSelectOptions}'{/implode}];
		
		$('#optionType').change(function(event) {
			var $value = $(event.currentTarget).val();
			if (WCF.inArray($value, $optionTypesUsingSelectOptions)) {
				$('#selectOptionsDL').show();
			}
			else {
				$('#selectOptionsDL').hide();
			}
		});
		$('#optionType').trigger('change');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}poi.acp.poi.option.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link application='poi' controller='OptionList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}poi.acp.menu.link.poi.option.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link application='poi' controller='OptionAdd'}{/link}{else}{link application='poi' controller='OptionEdit' id=$optionID}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'optionTitle'} class="formError"{/if}>
			<dt><label for="optionTitle">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="optionTitle" name="optionTitle" value="{$i18nPlainValues['optionTitle']}" required autofocus class="long">
				{if $errorField == 'optionTitle'}
					<small class="innerError">
						{if $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{else}
							{lang}poi.acp.poi.option.name.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='multipleLanguageInputJavascript' elementIdentifier='optionTitle' forceSelection=false}
		
		<dl{if $errorField == 'optionDescription'} class="formError"{/if}>
			<dt><label for="optionDescription">{lang}wcf.global.description{/lang}</label></dt>
			<dd>
				<textarea name="optionDescription" id="optionDescription" cols="40" rows="10">{$i18nPlainValues[optionDescription]}</textarea>
				{if $errorField == 'optionDescription'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}poi.acp.poi.option.description.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='multipleLanguageInputJavascript' elementIdentifier='optionDescription' forceSelection=false}
		
		<dl>
			<dt><label for="showOrder">{lang}poi.acp.poi.option.showOrder{/lang}</label></dt>
			<dd>
				<input type="number" id="showOrder" name="showOrder" value="{@$showOrder}" class="short">
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}poi.acp.poi.option.typeData{/lang}</h2>
		
		<dl{if $errorField == 'optionType'} class="formError"{/if}>
			<dt><label for="optionType">{lang}poi.acp.poi.option.optionType{/lang}</label></dt>
			<dd>
				<select name="optionType" id="optionType">
					{foreach from=$availableOptionTypes item=availableOptionType}
						<option value="{$availableOptionType}"{if $availableOptionType == $optionType} selected{/if}>{lang}poi.acp.poi.option.optionType.{$availableOptionType}{/lang}</option>
					{/foreach}
				</select>
				{if $errorField == 'optionType'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}poi.acp.poi.option.optionType.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt><label for="defaultValue">{lang}poi.acp.poi.option.defaultValue{/lang}</label></dt>
			<dd>
				<input type="text" id="defaultValue" name="defaultValue" value="{$defaultValue}" class="long">
				<small>{lang}poi.acp.poi.option.defaultValue.description{/lang}</small>
			</dd>
		</dl>
		
		<dl id="selectOptionsDL"{if $errorField == 'selectOptions'} class="formError"{/if}>
			<dt><label for="selectOptions">{lang}poi.acp.poi.option.selectOptions{/lang}</label></dt>
			<dd>
				<textarea name="selectOptions" id="selectOptions" cols="40" rows="10">{$selectOptions}</textarea>
				{if $errorField == 'selectOptions'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}poi.acp.poi.option.selectOptions.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}poi.acp.poi.option.selectOptions.description{/lang}</small>
			</dd>
		</dl>
		
		<dl{if $errorField == 'validationPattern'} class="formError"{/if}>
			<dt><label for="validationPattern">{lang}poi.acp.poi.option.validationPattern{/lang}</label></dt>
			<dd>
				<input type="text" id="validationPattern" name="validationPattern" value="{$validationPattern}" class="long">
				{if $errorField == 'validationPattern'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}poi.acp.poi.option.validationPattern.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}poi.acp.poi.option.validationPattern.description{/lang}</small>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="required" id="required" value="1"{if $required == 1} checked{/if}> {lang}poi.acp.poi.option.required{/lang}</label>
			</dd>
		</dl>
		
		{event name='typeDataFields'}
	</section>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer'}
