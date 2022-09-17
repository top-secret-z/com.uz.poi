<div class="section jsClipboardContainer" data-type="com.uz.poi.poi">
	<ol class="poiList">
		{include file='poiListItems' application='poi'}
	</ol>
</div>

<script data-relocate="true">
	$(function() {
		WCF.Language.addObject({
			'poi.poi.edit.delete':			'{jslang}poi.poi.edit.delete{/jslang}',
			'poi.poi.edit.disable':			'{jslang}poi.poi.edit.disable{/jslang}',
			'poi.poi.edit.enable':			'{jslang}poi.poi.edit.enable{/jslang}',
			'poi.poi.edit.restore':			'{jslang}poi.poi.edit.restore{/jslang}',
			'poi.poi.edit.setAsFeatured':	'{jslang}poi.poi.edit.setAsFeatured{/jslang}',
			'poi.poi.edit.trash':			'{jslang}poi.poi.edit.trash{/jslang}',
			'poi.poi.edit.unsetAsFeatured':	'{jslang}poi.poi.edit.unsetAsFeatured{/jslang}',
			'poi.poi.featured':				'{jslang}poi.poi.featured{/jslang}',
			'poi.poi.confirmDelete':		'{jslang}poi.poi.confirmDelete{/jslang}',
			'poi.poi.confirmTrash':			'{jslang}poi.poi.confirmTrash{/jslang}',
			'poi.poi.confirmTrash.reason':	'{jslang}poi.poi.confirmTrash.reason{/jslang}'
		});
		
		{if $__wcf->session->getPermission('mod.poi.canEditPoi')}
			var $updateHandler = new Poi.Poi.UpdateHandler.Category();
			
			var $inlineEditor = new Poi.Poi.InlineEditor('.poiPoi');
			$inlineEditor.setEnvironment('category');
			$inlineEditor.setUpdateHandler($updateHandler);
			$inlineEditor.setPermissions({
				canDeletePoi: 				{@$__wcf->session->getPermission('mod.poi.canDeletePoi')},
				canDeletePoiCompletely: 	{@$__wcf->session->getPermission('mod.poi.canDeletePoiCompletely')},
				canEnablePoi: 				{@$__wcf->session->getPermission('mod.poi.canModeratePoi')},
				canRestorePoi: 				{@$__wcf->session->getPermission('mod.poi.canRestorePoi')},
				canSetAsFeatured: 			{@$__wcf->session->getPermission('mod.poi.canEditPoi')}
			});
			
			var $poiClipboard = new Poi.Poi.Clipboard($updateHandler);
			WCF.Clipboard.init('wcf\\page\\DeletedContentListPage', {@$objects->getMarkedItems()}, { }, 0);
		{/if}
		
		new Poi.Poi.MarkAsRead();
	});
</script>
