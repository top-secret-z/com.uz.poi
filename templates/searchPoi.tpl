<dl>
    <dt><label for="poiCategoryID">{lang}poi.search.categories{/lang}</label></dt>
    <dd>
        <select name="poiCategoryID" id="poiCategoryID">
            <option value="">{lang}wcf.global.language.noSelection{/lang}</option>
            {foreach from=$poiCategoryList item=category}
                <option value="{@$category->categoryID}">{if $category->getDepth() > 1}{@'&nbsp;&nbsp;&nbsp;&nbsp;'|str_repeat:-1+$category->getDepth()}{/if}{$category->getTitle()}</option>
            {/foreach}
        </select>
    </dd>
</dl>

{event name='fields'}
