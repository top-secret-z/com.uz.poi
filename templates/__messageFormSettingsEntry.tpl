{if $__poi->isActiveApplication()}
    {if $enableComments|isset && $__wcf->session->getPermission('user.poi.canDisableCommentFunction')}
        <dt></dt>
        <dd>
            <label><input name="enableComments" type="checkbox" value="1"{if $enableComments} checked{/if}> {lang}poi.poi.enableComments{/lang}</label>
            <small>{lang}poi.poi.enableComments.description{/lang}</small>
        </dd>
    {/if}
{/if}
