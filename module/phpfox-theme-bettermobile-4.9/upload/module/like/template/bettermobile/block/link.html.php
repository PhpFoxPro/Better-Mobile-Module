{if (isset($bNewLink) && $bNewLink) || isset($bDefault) && $bDefault}
<li class="add-like">
    <a href="#" onclick="$(this).parents('div:first').find('.js_like_link_unlike:first').show(); $(this).hide(); $.ajaxCall('like.add', 'type_id={$aLike.like_type_id}&amp;item_id={$aLike.like_item_id}&amp;parent_id={if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}{/if}{if $aLike.like_is_custom}&amp;custom_inline=1{/if}', 'GET'); return false;" class="js_like_link_like js_like_link_toggle"{if $aLike.like_is_liked} style="display:none;"{/if}>{phrase var='feed.like'}</a>
    <a href="#" onclick="$(this).parents('div:first').find('.js_like_link_like:first').show(); $(this).hide(); $.ajaxCall('like.delete', 'type_id={$aLike.like_type_id}&amp;item_id={$aLike.like_item_id}&amp;parent_id={if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}{/if}{if $aLike.like_is_custom}&amp;custom_inline=1{/if}', 'GET'); return false;" class="js_like_link_unlike js_like_link_toggle"{if $aLike.like_is_liked}{else}style="display:none;"{/if}>{phrase var='feed.unlike'}</a>
</li>
{else}
<li class="add-like">
    <a href="#" onclick="$(this).parents('div:first').find('.js_like_link_unlike:first').show(); oLikeCount.add('{$aFeed.feed_id}') ; $(this).hide(); $.ajaxCall('bettermobile.add', 'type_id={$aLike.like_type_id}&amp;item_id={$aLike.like_item_id}&amp;parent_id={if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}{/if}{if $aLike.like_is_custom}&amp;custom_inline=1{/if}', 'GET'); return false;" class="js_like_link_like js_like_link_toggle"{if $aLike.like_is_liked} style="display:none;"{/if}>{phrase var='feed.like'}</a>
    <a href="#" onclick="$(this).parents('div:first').find('.js_like_link_like:first').show(); oLikeCount.remove('{$aFeed.feed_id}'); $(this).hide(); $.ajaxCall('bettermobile.delete', 'type_id={$aLike.like_type_id}&amp;item_id={$aLike.like_item_id}&amp;parent_id={if isset($aFeed.feed_id)}{$aFeed.feed_id}{else}{/if}{if $aLike.like_is_custom}&amp;custom_inline=1{/if}', 'GET'); return false;" class="js_like_link_unlike js_like_link_toggle"{if $aLike.like_is_liked}{else}style="display:none;"{/if}>{phrase var='feed.unlike'}</a>
</li>
{/if}
{if Phpfox::getParam('like.allow_dislike')&& isset($aActions) && is_array($aActions) && !empty($aActions)}

{if (isset($bNewLink) && $bNewLink) || isset($bDefault) && $bDefault}
<li class="add-dislike">
    {foreach from=$aActions name=action item=aAction}
    {if isset($aAction.action_type_id)}
    <a href="#" onclick="$(this).parents('div:first').find('.js_dislike_link_unlike:first').show(); $(this).hide();oLikeCount.addDislike('{$aFeed.feed_id}'); $.ajaxCall('bettermobile.addAction', 'action_type_id={$aAction.action_type_id}&amp;item_type_id={if isset($aLike.like_type_id)}{$aLike.like_type_id}{else}{$aAction.item_type_id}{/if}&amp;item_id={$aAction.item_id}&amp;module_name={if $aLike.like_type_id == 'feed_mini'}comment{else}{$aAction.module_name}{/if}&amp;parent_id={$aFeed.feed_id}', 'GET'); return false;" class="js_dislike_link_like "{if $aAction.is_marked}style="display:none;"{/if}>{$aAction.phrase}</a>
    <a href="#" onclick="$(this).parents('div:first').find('.js_dislike_link_like:first').show(); $(this).hide(); oLikeCount.removeDislike('{$aFeed.feed_id}');$.ajaxCall('bettermobile.removeAction', 'ation_type_id={$aAction.action_type_id}&amp;item_type_id={if isset($aLike.like_type_id)}{$aLike.like_type_id}{else}{$aAction.item_type_id}{/if}&amp;item_id={$aAction.item_id}&amp;module_name={if $aLike.like_type_id == 'feed_mini'}comment{else}{$aAction.module_name}{/if}&amp;parent_id={$aFeed.feed_id}', 'GET'); return false;" class="js_dislike_link_unlike"{if !$aAction.is_marked}style="display:none;"{/if}>{phrase var='like.remove'} {$aAction.phrase}</a>
    {/if}
    {/foreach}
</li>
{else}
<li class="add-dislike">
    {foreach from=$aActions name=action item=aAction}
    {if isset($aAction.action_type_id)}
    <a href="#" onclick="$(this).parents('div:first').find('.js_dislike_link_unlike:first').show(); $(this).hide();oLikeCount.addDislike('{$aFeed.feed_id}'); $.ajaxCall('bettermobile.addAction', 'action_type_id={$aAction.action_type_id}&amp;item_type_id={if isset($aLike.like_type_id)}{$aLike.like_type_id}{else}{$aAction.item_type_id}{/if}&amp;item_id={$aAction.item_id}&amp;module_name={if $aLike.like_type_id == 'feed_mini'}comment{else}{$aAction.module_name}{/if}&amp;parent_id={$aFeed.feed_id}', 'GET'); return false;" class="js_dislike_link_like "{if $aAction.is_marked}style="display:none;"{/if}>{$aAction.phrase}</a>
    <a href="#" onclick="$(this).parents('div:first').find('.js_dislike_link_like:first').show(); $(this).hide(); oLikeCount.removeDislike('{$aFeed.feed_id}');$.ajaxCall('bettermobile.removeAction', 'ation_type_id={$aAction.action_type_id}&amp;item_type_id={if isset($aLike.like_type_id)}{$aLike.like_type_id}{else}{$aAction.item_type_id}{/if}&amp;item_id={$aAction.item_id}&amp;module_name={if $aLike.like_type_id == 'feed_mini'}comment{else}{$aAction.module_name}{/if}&amp;parent_id={$aFeed.feed_id}', 'GET'); return false;" class="js_dislike_link_unlike"{if !$aAction.is_marked}style="display:none;"{/if}>{phrase var='like.remove'} {$aAction.phrase}</a>
    {/if}
    {/foreach}
</li>
{/if}
{else}

{/if}