{foreach from=$aRecords item=aItem }
    <div class="block">
        <div class="image"><a href="{$aItem.url}">{img server_id=$aItem.server_id title=$aItem.title path='event.url_image' file=$aItem.image_path suffix='_120' max_width='120' max_height='120'}</a></div>
        <div><a href="{$aItem.url}">{img user=$aItem suffix='_50_square' max_width='50' max_height='50'}</a></div>
        <div>
            <a href="{$aItem.url}" class="link">{$aItem.title|clean|split:25}</a>
            <div class="extra_info">
                <ul class="extra_info_middot"><li>{$aItem.start_time_phrase} {phrase var='event.at'} {$aItem.start_time_phrase_stamp}</li><li><span>&middot;</span></li><li>{$aItem|user}</li></ul>
            </div>

            {if Phpfox::isMobile()}
            <a href="{$aItem.url}">{img server_id=$aItem.server_id title=$aItem.title path='event.url_image' file=$aItem.image_path suffix='_120' max_width='120' max_height='120'}</a>
            {/if}

            {module name='feed.comment' aFeed=$aItem.aFeed}

        </div>
    </div>
{/foreach}