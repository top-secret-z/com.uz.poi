{if $__poi->isActiveApplication() && $__searchAreaInitialized|empty}
	{if $category|isset}
		{capture assign='__searchTypeLabel'}{$category->getTitle()}{/capture}
	{else}
		{capture assign='__searchTypeLabel'}{lang}wcf.search.type.com.uz.poi.poi{/lang}{/capture}
	{/if}
	
	{assign var='__searchObjectTypeName' value='com.uz.poi.poi'}
	
	{capture assign='__searchTypesScoped'}
		{if $category|isset}<li><a href="#" data-extended-link="{link controller='Search'}extended=1&type=com.uz.poi.poi{/link}" data-object-type="com.uz.poi.poi" data-parameters='{ "poiCategoryID": {@$category->categoryID} }'>{$category->getTitle()}</a></li>{/if}
	{/capture}
	{assign var='__searchAreaInitialized' value=true}
{/if}
