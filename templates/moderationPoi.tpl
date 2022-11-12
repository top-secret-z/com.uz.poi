<article class="message messageReduced">
    <section class="messageContent">
        <header class="messageHeader">
            <div class="box32 messageHeaderWrapper">
                {if $poi->userID}
                    <a href="{link controller='User' object=$poi->getUserProfile()}{/link}" aria-hidden="true">{@$poi->getUserProfile()->getAvatar()->getImageTag(32)}</a>
                {else}
                    <span>{@$poi->getUserProfile()->getAvatar()->getImageTag(32)}</span>
                {/if}

                <div class="messageHeaderBox">
                    <h2 class="messageTitle">
                        <a href="{link application='poi' controller='Poi' object=$poi}{/link}">{$poi->getSubject()}</a>
                    </h2>

                    <ul class="messageHeaderMetaData">
                        <li>{if $poi->userID}<a href="{link controller='User' object=$poi->getUserProfile()}{/link}" class="username">{$poi->username}</a>{else}<span class="username">{$poi->username}</span>{/if}</li>
                        <li><span class="messagePublicationTime">{@$poi->time|time}</span></li>

                        {event name='messageHeaderMetaData'}
                    </ul>

                    <ul class="messageStatus">
                        {if $poi->isDeleted}<li><span class="badge label red jsIconDeleted">{lang}wcf.message.status.deleted{/lang}</span></li>{/if}
                        {if $poi->isDisabled}<li><span class="badge label green jsIconDisabled">{lang}wcf.message.status.disabled{/lang}</span></li>{/if}

                        {event name='messageStatus'}
                    </ul>
                </div>
            </div>
        </header>

        <div class="messageBody">
            {event name='beforeMessageText'}

            <div class="messageText">
                {@$poi->getFormattedMessage()}
            </div>

            {event name='afterMessageText'}
        </div>

        <footer class="messageFooter">
            {event name='messageFooter'}

            <div class="messageFooterGroup">
                <ul class="messageFooterButtons buttonList smallButtons">

                    {event name='messageFooterButtons'}
                </ul>
            </div>
        </footer>
    </section>
</article>
