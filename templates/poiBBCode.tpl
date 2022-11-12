{foreach from=$pois item=poi}
    <article class="poiEmbeddedEvent embeddedContent" aria-labelledby="{$titleHash}_poiTitle{@$poi->poiID}">

        <div class="embeddedContentLink">
            {if $poi->hasCoverPhoto()}
                <img
                    class="embeddedContentImageElement"
                    src="{$poi->getCoverPhotoImage()->getUrl()}"
                    height="{@$poi->getCoverPhotoImage()->getWidth()}"
                    width="{@$poi->getCoverPhotoImage()->getHeight()}"
                    loading="lazy"
                    alt="">
            {/if}

            <div class="embeddedContentCategory">{lang}poi.poi.bbcode.type{/lang}</div>

            <div class="embeddedContentTitle" id="{$titleHash}_poiTitle{@$poi->poiID}">{$poi->getSubject()}</div>

            <div class="embeddedContentDescription">
                {@$poi->getExcerpt(500)}
            </div>

            <a href="{@$poi->getLink()}" class="embeddedContentLinkShadow"></a>
        </div>

        <div class="embeddedContentMeta">
            {user object=$poi->getUserProfile() type='avatar32' class='embeddedContentMetaImage' ariaHidden='true' tabindex='-1'}

            <div class="embeddedContentMetaContent">
                <div class="embeddedContentMetaAuthor">
                    {user object=$poi->getUserProfile() class='username'}
                </div>

                <div class="embeddedContentMetaTime">
                    {@$poi->lastChangeTime|time}
                </div>
            </div>
        </div>
    </article>
{/foreach}
