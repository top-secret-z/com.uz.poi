{include file='header' pageTitle='poi.acp.menu.link.poi.marker.add'}

<header class="contentHeader">
    <div class="contentHeaderTitle">
        <h1 class="contentTitle">{lang}poi.acp.menu.link.poi.marker.add{/lang}</h1>
    </div>

    <nav class="contentHeaderNavigation">
        <ul>
            <li><a href="{link controller='MarkerList' application='poi'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}poi.acp.menu.link.poi.marker.list{/lang}</span></a></li>

            {event name='contentHeaderNavigation'}
        </ul>
    </nav>
</header>

{include file='formError'}

{if $success|isset}
    <p class="success">{lang}wcf.global.success.add{/lang}</p>
{/if}

<form method="post" action="{link controller='MarkerAdd' application='poi'}{/link}" enctype="multipart/form-data">
    <div class="section">
        <h2 class="sectionTitle">{lang}poi.acp.marker.file{/lang}</h2>

        <dl>
            <dt><label for="fileUpload">{lang}poi.acp.marker.file.upload{/lang}</label></dt>
            <dd>
                <input type="file" id="fileUpload" name="fileUpload" value="">

                {if $errorField == 'fileUpload'}
                    <small class="innerError">
                        {if $errorType == 'empty'}
                            {lang}wcf.global.form.error.empty{/lang}
                        {else}
                            {lang}poi.acp.marker.file.upload.error.{@$errorType}{/lang}
                        {/if}
                    </small>
                {/if}
                <small>{lang}poi.acp.marker.file.upload.description{/lang}</small>
            </dd>
        </dl>
    </div>

    <div class="formSubmit">
        <input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
        {csrfToken}
    </div>
</form>

{include file='footer'}
