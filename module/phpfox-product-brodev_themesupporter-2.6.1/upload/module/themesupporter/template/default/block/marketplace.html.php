<div class="main-marketplace">
    <div class="title">
        <h2>{phrase var='marketplace.listings'}</h2>
        <a href="{url link='marketplace'}" class="view_all">{phrase var='themesupporter.view_all'}</a>
    </div>
    <div class="main-content">
        {foreach from=$aRecords item=aItem}
        <div class="marketplace-item">
            <div class="img_box">
                <a href="{permalink module='marketplace' id=$aItem.listing_id title=$aItem.title}">
                    {img server_id=$aItem.server_id title=$aItem.title path='marketplace.url_image' file=$aItem.image_path suffix='_400'}</a>
            </div>
            <h3><a href="{permalink module='marketplace' id=$aItem.listing_id title=$aItem.title}">{$aItem.title|clean|shorten:18:'...'|split:20}</a>
            </h3>
            <a class="extra-info_time"
               href="{permalink module='marketplace' id=$aItem.listing_id title=$aItem.title}">{$aItem.time_stamp|convert_time}</a>

            <div class="clear"></div>
            <p>{$aItem.mini_description|clean|shorten:138:'...'|split:20}<br>
                <a href="{permalink module='marketplace' id=$aItem.listing_id title=$aItem.title}">{phrase
                    var='core.view_more'} »</a>
            </p>
        </div>
        {/foreach}
    </div>
</div>