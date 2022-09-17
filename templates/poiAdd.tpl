{capture assign='pageTitle'}{lang}poi.poi.{@$action}{/lang}{/capture}

{capture assign='contentTitle'}{lang}poi.poi.{@$action}{/lang}{/capture}

{include file='header'}

{if $action == 'add' && !$__wcf->session->getPermission('user.poi.canAddPoiWithoutModeration')}
	<p class="info">{lang}poi.poi.moderation.info{/lang}</p>
{/if}

{include file='formError'}

<form id="messageContainer" class="jsFormGuard" method="post" action="{if $action == 'add'}{link application='poi' controller='PoiAdd'}{/link}{else}{link application='poi' controller='PoiEdit' id=$poiID}{/link}{/if}">
	<section class="section">
		<h2 class="sectionTitle">{lang}poi.poi.information{/lang}</h2>
		
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
		
		{include file='messageFormMultilingualism'}
		
		<dl{if $errorField == 'subject'} class="formError"{/if}>
			<dt><label for="subject">{lang}wcf.global.title{/lang}</label></dt>
			<dd>
				<input type="text" id="subject" name="subject" value="{$subject}" required maxlength="255" class="long">
				{if $errorField == 'subject'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'censoredWordsFound'}
							{lang}wcf.message.error.censoredWordsFound{/lang}
						{else}
							{lang}poi.poi.subject.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'teaser'} class="formError"{/if}>
			<dt><label for="teaser">{lang}poi.poi.teaser{/lang}</label></dt>
			<dd>
				<textarea id="teaser" name="teaser" rows="5" cols="40">{$teaser}</textarea>
				{if $errorField == 'teaser'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'tooLong'}
							{lang maxTextLength=POI_MAX_TEASER_LENGTH}wcf.message.error.tooLong{/lang}
						{elseif $errorType == 'censoredWordsFound'}
							{lang}wcf.message.error.censoredWordsFound{/lang}
						{else}
							{lang}poi.poi.teaser.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}poi.poi.teaser.description{/lang}</small>
			</dd>
		</dl>
		
		{if MODULE_TAGGING}{include file='tagInput'}{/if}
		
		{if MODULE_EDIT_HISTORY && $action == 'edit'}
			<dl{if $errorField == 'editReason'} class="formError"{/if}>
				<dt><label for="editReason">{lang}poi.poi.editReason{/lang}</label></dt>
				<dd>
					<textarea rows="3" cols="40" id="editReason" name="editReason">{$editReason}</textarea>
					{if $errorField == 'editReason'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}poi.poi.editReason.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
		{/if}
	</section>
	
	{if GOOGLE_MAPS_API_KEY}
		<section class="section">
			<h2 class="sectionTitle">{lang}poi.poi.geodata{/lang}</h2>
			
			<dl>
				<dt><label for="elevation">{lang}poi.poi.geodata.elevation{/lang}</label></dt>
				<dd>
					<input type="number" name="elevation" id="elevation" value="{$elevation}" class="small" min="-9999" max="99999"/>
					<small>{lang}poi.poi.geodata.elevation.description{/lang}</small>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="geocode">{lang}poi.poi.geodata.location{/lang}</label></dt>
				<dd>
					<input type="text" id="geocode" name="geocode" class="long" value="{$geocode}">
					<small>{lang}poi.poi.geodata.location.description{/lang}</small>
				</dd>
			</dl>
			<dl>
				<dt></dt>
				<dd>
					<input type="text" id="dirLatitude" name="latitude" value="{$latitude}">
					<input type="text" id="dirLongitude" name="longitude" value="{$longitude}">
					<span class="button small jsButtonFind jsOnly">{lang}poi.poi.geodata.find{/lang}</span>
					<small>{lang}poi.poi.geodata.find.description{/lang}</small>
				</dd>
			</dl>
			
			<p class="innerError" id="coordError">{lang}poi.poi.geodata.find.error{/lang}</p>
			
			<dl class="wide">
				<dt></dt>
				<dd id="mapContainer" class="googleMap poiLocationInput"></dd>
			</dl>
		</section>
	{else}
		<section class="section">
			<h2 class="sectionTitle">{lang}poi.poi.geodata{/lang}</h2>
			
			<p class="warning">{lang}poi.poi.geodata.keyMissing{/lang}</p>
		</section>
	{/if}
	
	{if $options|count}
		<section class="section">
			<h2 class="sectionTitle">{lang}poi.poi.options{/lang}</h2>
			
			{include application='poi' file='poiOptionFieldList'}
		</section>
	{/if}
	
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.image.coverPhoto{/lang}</h2>
		
		<div id="coverPhotoUploadPreview">
			{if $coverPhoto && $coverPhoto->coverPhotoID}
				<img src="{$coverPhoto->getCoverPhotoUrl()}" class="contentItemImagePreview" alt="" />
			{/if}
		</div>
		
		<div class="contentItemImagePreviewButtons" id="coverPhotoUploadButtonContainer" data-max-size="{@$__wcf->session->getPermission('user.poi.maxCoverPhotoSize')}">
			{* The upload button is inserted here *}
			
			<a href="#" class="button jsButtonDeleteCoverPhoto"{if !$coverPhoto || !$coverPhoto->coverPhotoID} style="display:none"{/if} role="button">{lang}wcf.image.coverPhoto.delete{/lang}</a>
		</div>
		{if $errorField == 'coverPhoto'}
			<small class="innerError">
				{if $errorType == 'empty'}
					{lang}wcf.global.form.error.empty{/lang}
				{/if}
			</small>
		{/if}
		<small>{lang coverPhotoPermissionMaxSize='user.poi.coverPhoto.maxSize'}poi.image.coverPhoto.upload.limits{/lang}</small>
	</section>
	
	<section class="section">
		<h2 class="sectionTitle">{lang}poi.poi.message{/lang}</h2>
		
		<dl class="wide{if $errorField == 'text'} formError{/if}">
			<dt><label for="text">{lang}poi.poi.message{/lang}</label></dt>
			<dd>
				<textarea id="text" name="text" rows="20" cols="40">{$text}</textarea>
				{if $errorField == 'text'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'tooLong'}
							{lang}wcf.message.error.tooLong{/lang}
						{elseif $errorType == 'censoredWordsFound'}
							{lang}wcf.message.error.censoredWordsFound{/lang}
						{elseif $errorType == 'disallowedBBCodes'}
							{lang}wcf.message.error.disallowedBBCodes{/lang}
						{else}
							{lang}poi.poi.message.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='messageFields'}
	</section>
	
	{event name='sections'}
	
	{include file='messageFormTabs' wysiwygContainerID='text'}
	
	<div class="formSubmit">
		<input type="hidden" name="coverPhotoID" value="{if $coverPhoto}{@$coverPhoto->coverPhotoID}{else}0{/if}">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		
		{if $action == 'edit'}
			{include file='messageFormPreviewButton' previewMessageObjectType='com.uz.poi.poi' previewMessageObjectID=$poi->poiID}
		{else}
			{include file='messageFormPreviewButton' previewMessageObjectType='com.uz.poi.poi' previewMessageObjectID=0}
		{/if}
		{csrfToken}
	</div>
</form>

{if GOOGLE_MAPS_API_KEY}
	{include file='googleMapsJavaScript'}
{/if}
<script data-relocate="true">
	require(['Language', 'UZ/Poi/CoverPhotoUpload'], function (Language, PoiCoverPhotoUpload) {
		Language.addObject({
			'wcf.image.coverPhoto.delete.confirmMessage': 		'{jslang}wcf.image.coverPhoto.delete.confirmMessage{/jslang}',
			'wcf.image.coverPhoto.upload.error.fileExtension': 	'{jslang}wcf.image.coverPhoto.upload.error.fileExtension{/jslang}',
			'wcf.image.coverPhoto.upload.error.uploadFailed': 	'{jslang}wcf.image.coverPhoto.upload.error.uploadFailed{/jslang}',
			'wcf.image.coverPhoto.upload.error.badImage': 		'{jslang}wcf.image.coverPhoto.upload.error.badImage{/jslang}'
		});
		
		new PoiCoverPhotoUpload();
	});

	$(function() {
		{if GOOGLE_MAPS_API_KEY}
			$locationInput = new Poi.Map.GoogleMaps.LocationInput('mapContainer', undefined, '#geocode', {if $latitude || $longitude}{@$latitude}, {@$longitude}{else}null, null{/if}, 'poi\\data\\poi\\PoiAction');
			{if !$latitude && !$longitude}
				WCF.Location.Util.getLocation($.proxy(function(latitude, longitude) {
					if (latitude !== undefined && longitude !== undefined) {
						WCF.Location.GoogleMaps.Util.moveMarker($locationInput.getMarker(), latitude, longitude, true);
						
						google.maps.event.trigger($locationInput.getMap().getMap(), 'resize');
						WCF.Location.GoogleMaps.Util.focusMarker($locationInput.getMarker());
					}
				}, this));
			{/if}
			
			google.maps.event.trigger($locationInput.getMap().getMap(), 'resize');
			WCF.Location.GoogleMaps.Util.focusMarker($locationInput.getMarker());
			
			new Poi.Poi.Coordinates.Handler($locationInput);
		{/if}
		
		new WCF.Message.FormGuard();
	});
</script>



{include file='footer'}
{include file='wysiwyg' wysiwygSelector='text'}
