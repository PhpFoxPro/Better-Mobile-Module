{literal}
<style type="text/css">
    .parent_item_feed .js_feed_comment_border ul, .item_view .js_feed_comment_border ul {
        margin: 0px;
    }
    .js_feed_comment_border ul li, .comment_mini_action ul li, .activity_feed_content_status ul li {
        line-height: 23px;
    }
</style>
{/literal}

{if !count($aItems)}
<div class="extra_info">
	{phrase var='blog.no_blogs_found'}
</div>
{else}
{foreach from=$aItems name=blog item=aItem}
	{template file='blog.block.entry'}
{/foreach}
{if Phpfox::getUserParam('blog.can_approve_blogs') || Phpfox::getUserParam('blog.delete_user_blog')}
{moderation}
{/if}
{unset var=$aItems}
{pager}
{/if}