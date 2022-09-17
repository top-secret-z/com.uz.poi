{foreach from=$options item=optionData}
	{assign var=option value=$optionData[object]}
	<dl class="{if $errorType|is_array && $errorType[$option->optionName]|isset} formError{/if}">
		<dt{if $optionData[cssClassName]} class="{$optionData[cssClassName]}"{/if}><label for="{$option->optionName}">{lang}{$option->optionTitle}{/lang}</label></dt>
		<dd>{@$optionData[html]}
			<small>{lang __optional=true}{$option->optionDescription}{/lang}</small>
			
			{if $errorType|is_array && $errorType[$option->optionName]|isset}
				<small class="innerError">
					{if $errorType[$option->optionName] == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}	
						{lang}poi.poi.option.error.{$errorType[$option->optionName]}{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
{/foreach}
