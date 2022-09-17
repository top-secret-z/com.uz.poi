{include file='header' pageTitle='poi.acp.poi.option.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}poi.acp.poi.option.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link application='poi' controller='OptionAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}poi.acp.poi.option.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks application='poi' controller='OptionList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="poi\data\poi\option\PoiOptionAction">
			<thead>
				<tr>
					<th class="columnID columnOptionID{if $sortField == 'optionID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link application='poi' controller='OptionList'}pageNo={@$pageNo}&sortField=optionID&sortOrder={if $sortField == 'optionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnOptionTitle{if $sortField == 'optionTitle'} active {@$sortOrder}{/if}"><a href="{link application='poi' controller='OptionList'}pageNo={@$pageNo}&sortField=optionTitle&sortOrder={if $sortField == 'optionTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnOptionType{if $sortField == 'optionType'} active {@$sortOrder}{/if}"><a href="{link application='poi' controller='OptionList'}pageNo={@$pageNo}&sortField=optionType&sortOrder={if $sortField == 'optionType' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}poi.acp.poi.option.optionType{/lang}</a></th>
					<th class="columnText columnRequired{if $sortField == 'required'} active {@$sortOrder}{/if}"><a href="{link application='poi' controller='OptionList'}pageNo={@$pageNo}&sortField=required&sortOrder={if $sortField == 'required' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}poi.acp.poi.option.required{/lang}</a></th>
					<th class="columnDigits columnShowOrder{if $sortField == 'showOrder'} active {@$sortOrder}{/if}"><a href="{link application='poi' controller='OptionList'}pageNo={@$pageNo}&sortField=showOrder&sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}poi.acp.poi.option.showOrder{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=option}
					<tr class="jsOptionRow jsObjectActionObject" data-object-id="{@$option->getObjectID()}">
						<td class="columnIcon">
							{objectAction action="toggle" isDisabled=$option->isDisabled}
							<a href="{link application='poi' controller='OptionEdit' id=$option->optionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							{objectAction action="delete" objectTitle=$option->getOptionTitle()}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID">{@$option->optionID}</td>
						<td class="columnTitle columnoptionTitle"><a href="{link application='poi' controller='OptionEdit' id=$option->optionID}{/link}">{$option->optionTitle|language}</a></td>
						<td class="columnText columnOptionType">{lang}poi.acp.poi.option.optionType.{$option->optionType}{/lang}</td>
						<td class="columnText columnRequired">{lang}poi.acp.poi.option.optionType.boolean.{if $option->required}yes{else}no{/if}{/lang}</td>
						<td class="columnDigits columnShowOrder">{#$option->showOrder}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link application='poi' controller='OptionAdd'}{/link}" class="button"><span class="icon icon16 facon-plus"></span> <span>{lang}poi.acp.poi.option.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
