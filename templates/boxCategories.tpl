<ol class="boxMenu forceOpen">
    {foreach from=$categoryList item=categoryItem}
        <li{if $activeCategory && $activeCategory->categoryID == $categoryItem->categoryID} class="active"{/if} data-category-id="{@$categoryItem->categoryID}">
            <a href="{@$categoryItem->getLink()}" class="boxMenuLink">
                <span class="boxMenuLinkTitle">{$categoryItem->getTitle()}</span>
                <span class="badge">{#$categoryItem->getPois()}</span>
            </a>

            {if $activeCategory && ($activeCategory->categoryID == $categoryItem->categoryID || $activeCategory->isParentCategory($categoryItem->getDecoratedObject())) && $categoryItem->hasChildren()}
                <ol class="boxMenuDepth1">
                    {foreach from=$categoryItem item=subCategoryItem}
                        <li{if $activeCategory && $activeCategory->categoryID == $subCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subCategoryItem->categoryID}">
                            <a href="{@$subCategoryItem->getLink()}" class="boxMenuLink">
                                <span class="boxMenuLinkTitle">{$subCategoryItem->getTitle()}</span>
                                <span class="badge">{#$subCategoryItem->getPois()}</span>
                            </a>

                            {if $activeCategory && ($activeCategory->categoryID == $subCategoryItem->categoryID || $activeCategory->isParentCategory($subCategoryItem->getDecoratedObject())) && $subCategoryItem->hasChildren()}
                                <ol class="boxMenuDepth2">
                                    {foreach from=$subCategoryItem item=subSubCategoryItem}
                                        <li{if $activeCategory && $activeCategory->categoryID == $subSubCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subSubCategoryItem->categoryID}">
                                            <a href="{@$subSubCategoryItem->getLink()}" class="boxMenuLink">
                                                <span class="boxMenuLinkTitle">{$subSubCategoryItem->getTitle()}</span>
                                                <span class="badge">{#$subSubCategoryItem->getPois()}</span>
                                            </a>

                                            <!-- additional level (3) -->
                                            {if $activeCategory && ($activeCategory->categoryID == $subSubCategoryItem->categoryID || $activeCategory->isParentCategory($subSubCategoryItem->getDecoratedObject())) && $subSubCategoryItem->hasChildren()}
                                                <ol class="boxMenuDepth2">
                                                    {foreach from=$subSubCategoryItem item=subSubSubCategoryItem}
                                                        <li{if $activeCategory && $activeCategory->categoryID == $subSubSubCategoryItem->categoryID} class="active"{/if} data-category-id="{@$subSubSubCategoryItem->categoryID}">
                                                            <a href="{@$subSubSubCategoryItem->getLink()}" class="boxMenuLink">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                <span class="boxMenuLinkTitle">{$subSubSubCategoryItem->getTitle()}</span>
                                                                <span class="badge">{#$subSubSubCategoryItem->getPois()}</span>
                                                            </a>
                                                        </li>
                                                    {/foreach}
                                                </ol>
                                            {/if}
                                        </li>
                                    {/foreach}
                                </ol>
                            {/if}
                        </li>
                    {/foreach}
                </ol>
            {/if}
        </li>
    {/foreach}

    {if $activeCategory}
        <li class="boxMenuResetFilter">
            <a href="{link application='poi' controller='PoiList'}{/link}" class="boxMenuLink">
                <span class="boxMenuLinkTitle">{lang}wcf.global.button.resetFilter{/lang}</span>
            </a>
        </li>
    {/if}
</ol>
