{if $mimeType === 'text/plain'}
{lang}poi.poi.notification.mail.plaintext{/lang}

{@$event->getUserNotificationObject()->getMailText($mimeType)} {* this line ends with a space *}
{else}
    {lang}poi.poi.notification.mail.html{/lang}
    {assign var='user' value=$event->getAuthor()}
    {assign var='poi' value=$event->getUserNotificationObject()}

    {if $notificationType == 'instant'}{assign var='avatarSize' value=48}
    {else}{assign var='avatarSize' value=32}{/if}
    {capture assign='poiContent'}
    <table cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td><a href="{link controller='User' object=$user isHtmlEmail=true}{/link}" title="{$user->username}">{@$user->getAvatar()->getImageTag($avatarSize)}</a></td>
            <td class="boxContent">
                <div class="containerHeadline">
                    <h3>
                        {if $user->userID}
                            <a href="{link controller='User' object=$user isHtmlEmail=true}{/link}">{$user->username}</a>
                        {else}
                            {$user->username}
                        {/if}
                        &#xb7;
                        <a href="{$poi->getLink()}"><small>{$poi->time|plainTime}</small></a>
                    </h3>
                </div>
                <div>
                    {@$poi->getMailText($mimeType)}
                </div>
            </td>
        </tr>
    </table>
    {/capture}
    {include file='email_paddingHelper' block=true class='box'|concat:$avatarSize content=$poiContent sandbox=true}
{/if}
