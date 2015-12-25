
<div class="js_comment_like_holder" id="js_feed_like_holder_{$aFeed.feed_id}">
    <div id="js_like_body_{$aFeed.feed_id}" class="like_image">
        {if Phpfox::getParam('like.show_user_photos')}
        <a href="#" class="dislike_count_link js_hover_title reaalllyyy" onclick="return $Core.box('like.browse', 400, 'dislike=1&amp;type_id={$sType}&amp;item_id={$iId}');">
            {$iCount}<span class="js_hover_info">{phrase var='like.people_who_disliked_this'}</span>
        </a>
        {else}

        <div class="image_normal">
            <a href="{$aFeed.feed_link}">
                {img theme='feed/dislike.png' align='left'}
            </a>
        </div>
        <div class="image_retina">
            <a href="{$aFeed.feed_link}">
                {img theme='feed/dislike.png' align='left'}

            </a>
        </div>
        <div class="dislike_icon">
            {if isset($aFeed.call_displayactions)}
            {module name='like.displayactions' aFeed=$aFeed}
            {else}
            0
            {/if}
        </div>
        {/if}
    </div>
</div>