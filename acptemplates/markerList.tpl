{include file='header' pageTitle='poi.acp.menu.link.poi.marker.list'}

<header class="contentHeader">
    <div class="contentHeaderTitle">
        <h1 class="contentTitle">{lang}poi.acp.menu.link.poi.marker.list{/lang}</h1>
    </div>

    {hascontent}
        <nav class="contentHeaderNavigation">
            <ul>
                {content}
                    <li><a href="{link controller='MarkerAdd' application='poi'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}poi.acp.menu.link.poi.marker.add{/lang}</span></a></li>

                    {event name='contentHeaderNavigation'}
                {/content}
            </ul>
        </nav>
    {/hascontent}
</header>

{if $markers|count}
    <div class="section tabularBox">
        <table class="table">
            <thead>
                <tr>
                    <th class="columnText columnSource" colspan="2">{lang}poi.acp.marker{/lang}</th>
                    <th class="columnText columnSize">{lang}poi.acp.marker.size{/lang}</th>
                    <th class="columnText columnName">{lang}poi.acp.marker.filename{/lang}</th>
                    <th class="columnText columnCategory">{lang}poi.acp.marker.category{/lang}</th>
                </tr>
            </thead>

            <tbody>
                {foreach from=$markers item=marker}
                    <tr>
                        <td class="columnIcon">
                            {if $marker.used}
                                <span class="icon icon16 fa-remove jsTooltip pointer disabled" title="{lang}poi.acp.used{/lang}"></span>
                            {else}
                                <span class="icon icon16 fa-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" id="{$marker.name}" data-confirm-message="{lang}poi.acp.delete.sure{/lang}"></span>
                            {/if}
                        </td>
                        <td class="columnSource">{@$marker.link}</td>
                        <td class="columnSize">{$marker.size}</td>
                        <td class="columnName">{$marker.name}</td>
                        <td class="columnCategory">{$marker.categories}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>

    </div>

    <footer class="contentFooter">

        {hascontent}
            <nav class="contentFooterNavigation">
                <ul>
                    {content}
                        <li><a href="{link controller='MarkerAdd' application='poi'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}poi.acp.menu.link.poi.marker.add{/lang}</span></a></li>

                        {event name='contentHeaderNavigation'}
                    {/content}
                </ul>
            </nav>
        {/hascontent}
    </footer>
{else}
    <p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<script data-relocate="true">
    require(['Language', 'UZ/Poi/Acp/MarkerDelete'], function(Language, UZPoiAcpMarkerDelete) {
        Language.addObject({
            'poi.acp.marker.delete.sure': '{jslang}poi.acp.marker.delete.sure{/jslang}'
        });

        UZPoiAcpMarkerDelete.init();
    });
</script>

{include file='footer'}
