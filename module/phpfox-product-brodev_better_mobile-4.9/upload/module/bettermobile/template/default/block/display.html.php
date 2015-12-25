{if isset($bDefault) && $bDefault}
{if Phpfox::getParam('like.show_user_photos')}
<div class="activity_like_holder comment_mini">

    {foreach from=$aFeed.likes name=likes item=aLikeRow}{img user=$aLikeRow suffix='_50_square' max_width=32
    max_height=32 class='js_hover_title v_middle'}&nbsp;{/foreach}
</div>
{else}
{php}
    $this->_aVars['likeTotal'] = count($this->_aVars['aFeed']['likes']);
{/php}
{if $aFeed.feed_total_like>Phpfox::getParam('feed.total_likes_to_display')}
{php}
    $this->_aVars['likeTotal'] = count($this->_aVars['aFeed']['likes']) + 1;
{/php}
{/if}

<div class="activity_like_holder comment_mini">{if Phpfox::isMobile()}<a href="#" onclick="return $Core.box('like.browse', 400, 'type_id={$aFeed.like_type_id}&amp;item_id={$aFeed.item_id}');">{img theme='feed/like_icon@2x.png' class='v_middle'}{$aFeed.total_like}</a>{else}{img theme='layout/like.png' class='v_middle'}{/if}&nbsp;{if
    $aFeed.feed_is_liked}{if !count($aFeed.likes) == 1}{elseif count($aFeed.likes) == 1}
    {if !Phpfox::isMobile()}



    {/if}
    {/if}{else}{phrase
    var='like.article_to_upper'}{/if}



    {if $aFeed.feed_total_like>Phpfox::getParam('feed.total_likes_to_display')}
    <!--and-->
    <!--<a href="#" onclick="return $Core.box('like.browse', 400, 'type_id={$aFeed.like_type_id}&amp;item_id={$aFeed.item_id}');">{if
        $iTotalLeftShow = ($aFeed.feed_total_like - Phpfox::getParam('feed.total_likes_to_display'))}{/if}{if
        $iTotalLeftShow == 1}&nbsp;{phrase var='like.and'}&nbsp;{phrase var='like.1_other_person'}&nbsp;{else}&nbsp;{phrase
        var='like.and'}&nbsp;{$iTotalLeftShow|number_format}&nbsp;{phrase var='like.others'}&nbsp;{/if}</a>-->

    {if !Phpfox::isMobile()}
        {phrase var='like.likes_this'}{else}{if (count($aFeed.likes) > 1)}&nbsp;
        {if !Phpfox::isMobile()}{phrase var='like.like_this'}{/if}
        {else}{if
        $aFeed.feed_is_liked}{if count($aFeed.likes) == 1}&nbsp;
        {if !Phpfox::isMobile()}
        {phrase var='like.like_this'}
        {/if}
        {else}{if count($aFeed.likes)
        == 0}&nbsp;{phrase var='like.you_like'}{else}{phrase var='like.likes_this'}{/if}{/if}{else}{if count($aFeed.likes)
        == 1}&nbsp;{phrase var='like.likes_this'}{else}
        {if !Phpfox::isMobile()}
        {phrase var='like.like_this'}
        {/if}
        {/if}{/if}{/if}{/if}
    {/if}
</div>{/if}
{else}

<div class="image_normal">
    <a href="{$aFeed.feed_link}">
        {img theme='feed/like_icon.png' align='left'}
    </a>
</div>
<div class="image_retina">
    <a href="{$aFeed.feed_link}">
        {img theme='feed/like_icon@2x.png' align='left'}

    </a>
</div>
<div class="like_icon">
    {$aFeed.feed_total_like|number_format}
</div>

{/if}