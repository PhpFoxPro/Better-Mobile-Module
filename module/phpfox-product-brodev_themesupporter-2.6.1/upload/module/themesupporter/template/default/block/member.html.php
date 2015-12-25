{foreach from=$aRecords item=aItem}
        <div class="block">
            <div class="image">
                <a href="{url link=$aItem.user_name}">
                    {img server_id=$aItem.server_id title=$aItem.full_name path='core.url_user' file=$aItem.user_image suffix='_50_square' max_width='50' max_height='50' is_page_image=true}
                </a>
            </div>
            {if Phpfox::getParam('themesupporter.block_member_show_name')}
            <div>
                <a href="{url link=$aItem.user_name}">
                    {$aItem.full_name}
                </a>
            </div>
            {/if}
        </div>
{foreachelse}
{/foreach}
{if (count($aRecords) >0) && (Phpfox::getParam('themesupporter.block_member_view_more')) }
     <a href="{url link='user.browse'}">{php} echo Phpfox::getPhrase('themesupporter.block_member_view_more');{/php}</a>
{/if}