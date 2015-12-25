{if Phpfox::getParam('like.show_user_photos')}
<a href="#" class="dislike_count_link js_hover_title reaalllyyy" onclick="return $Core.box('like.browse', 400, 'dislike=1&amp;type_id={$sType}&amp;item_id={$iId}');">
    {$iCount}<span class="js_hover_info">{phrase var='like.people_who_disliked_this'}</span>
</a>
{else}
<div class="display_actions" id="display_actions_{if isset($aLike)}{$aLike.like_item_id}{else}{if isset($aFeed)}{$aFeed.feed_id}{/if}{/if}">
    <div class="comment_mini_content_holder">
        <div class="comment_mini_content_holder_icon"></div>
        <div class="comment_mini_content_border">
            <div class="js_comment_like_holder" id="">
                {module name='like.displayactions' aFeed=$aFeed}
            </div>
        </div>
    </div>
</div>
{/if}
