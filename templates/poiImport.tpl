{include file='header'}

{if !$__wcf->session->getPermission('user.poi.canAddPoiWithoutModeration')}
	<p class="info">{lang}poi.poi.moderation.info{/lang}</p>
{/if}

{include file='formError'}

{if $success|isset}
	<p class="success" role="status">{lang}poi.poi.import.success{/lang}</p>
{/if}

<form id="messageContainer" enctype="multipart/form-data" class="jsFormGuard" method="post" action="{link application='poi' controller='PoiImport'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}poi.category.categories{/lang}</h2>
		
		<dl{if $errorField == 'categoryID'} class="formError"{/if}>
			<dt><label for="categoryID">{lang}poi.poi.category{/lang}</label></dt>
			<dd>
				<select name="categoryID" id="categoryID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>
					
					{foreach from=$categoryNodeList item=category}
						<option value="{@$category->categoryID}"{if $category->categoryID == $categoryID} selected{/if}>{if $category->getDepth() > 1}{@"&nbsp;&nbsp;&nbsp;&nbsp;"|str_repeat:($category->getDepth() - 1)}{/if}{$category->getTitle()}</option>
					{/foreach}
				</select>
				{if $errorField == 'categoryID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'invalid'}
							{lang}wcf.global.form.error.noValidSelection{/lang}
						{else}
							{lang}poi.poi.category.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
	</section>
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}poi.poi.import{/lang}</h2>
			<p class="sectionDescription">{lang}poi.poi.import.description{/lang}</p>
		</header>
		
		<dl{if $errorField == 'upload'} class="formError"{/if}>
			<dt><label for="upload">{lang}poi.poi.import.upload{/lang}</label></dt>
			<dd>
				<input type="file" id="upload" name="upload">
				{if $errorField == 'upload'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}poi.poi.import.upload.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='importFields'}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

<script data-relocate="true">
	$(function() {
		new WCF.Message.FormGuard();
	});
</script>

{include file='footer'}
