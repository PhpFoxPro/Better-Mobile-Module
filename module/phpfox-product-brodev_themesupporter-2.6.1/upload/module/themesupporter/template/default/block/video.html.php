
{foreach from=$aRecords item=aItem}
    <div class="block">
        <div class="title">{$aItem.title}</div>
        <div class="image">
            <a href="{$aItem.link}" class="js_video_title_{$aItem.video_id}">
            {img server_id=$aItem.image_server_id path='video.url_image' file=$aItem.image_path suffix='_120' max_width=120 max_height=90 class='js_mp_fix_width video_image_border' title=$aItem.title}
            </a>
        </div>
        <div>
            {if $aItem.total_view == 1}
            {phrase var='video.1_view'}<br />
            {else}
            {phrase var='video.total_views' total=$aItem.total_view}<br />
            {/if}
        </div>
        <div>
            {phrase var='video.by_full_name' full_name=$aItem|user:'':'':20}
        </div>
    </div>
        {foreachelse}
{/foreach}