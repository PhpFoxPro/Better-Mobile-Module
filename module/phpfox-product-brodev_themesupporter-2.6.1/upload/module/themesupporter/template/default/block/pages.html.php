{foreach from=$aRecords item=aItem}
<div class="block">
    <div class="image">
        <a href="{$aItem.link}">
        {img server_id=$aItem.profile_server_id title=$aItem.title path='core.url_user' file=$aItem.profile_user_image suffix='_50_square' max_width='50' max_height='50' is_page_image=true}
        </a>
    </div>
    <div class="title">
        <a href="{$aItem.link}">
            {$aItem.title}
        </a>
    </div>
    <div>
        {module name='feed.comment' aFeed=$aItem.aFeed}
    </div>

</div>
{foreachelse}
{/foreach}
{if (Phpfox::getParam('themesupporter.block_pages_view_all') && (count($aRecords) > 0))}
<div><a href="{url link='pages'}">{phrase var='themesupporter.view_all'}</a> </div>
{/if}