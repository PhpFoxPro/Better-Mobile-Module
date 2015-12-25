    {foreach from=$aRecords item=aItem}
        <div class="block">
            <div class="title">{$aItem.title}</div>
            <div class="detail">{$aItem.detail}</div>
            {if $aItem.image != ""}
            {img title=$aItem.title path='themesupporter.image_url' file=$aItem.image max_width='100' max_height='100' is_page_image=true  suffix='_100_square'}
            {/if}
            <div class="button"><a href="{$aItem.link}">{$aItem.button}</a> </div>
        </div>
    {foreachelse}

    {/foreach}
