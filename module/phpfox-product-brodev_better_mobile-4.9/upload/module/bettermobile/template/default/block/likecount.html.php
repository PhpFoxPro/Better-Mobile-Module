<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_Friend
 * @version 		$Id: accept.html.php 4941 2012-10-23 12:43:23Z Miguel_Espinoza $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{plugin call='feed.template_block_comment_border'}
{plugin call='core.template_block_comment_border_new'}
{if (isset($bNewLink) && $bNewLink) || (isset($bDefault) && $bDefault)}
{else}
<div class="like_count">
    {if Phpfox::isUser() && Phpfox::isModule('like') && isset($aVideo.like_type_id)}
    <div class="js_comment_like_holder" id="js_feed_like_holder_{$aVideo.feed_id}">
        <div id="js_like_body_{$aVideo.feed_id}" class="like_image">
            {if isset($aVideo.feed_total_like)}
            {template file='bettermobile.block.display'}
            {/if}
        </div>
    </div>
    {/if}
    {if $aVideo.type_id != 'friend'}
    {if isset($aVideo.total_comment)}
    <div class="comment_feed_link">
        <div class="image_normal ">
            <a href="{$aVideo.feed_link}">
                {img theme='feed/commet_icon.png'}
                {$aVideo.total_comment}asdasdsa
            </a>
        </div>
        <div class="image_retina">
            <a href="{$aVideo.feed_link}">
                {img theme='feed/commet_icon@2x.png'}
                {$aVideo.total_comment}ewtwetwe
            </a>
        </div>
    </div>
    <div class="share_feed_link">
        <div class="image_normal ">
            <a href="{$aVideo.feed_link}">
                {img theme='feed/share_icon.png'}
                {$aVideo.total_share}
            </a>
        </div>
        <div class="image_retina">
            <a href="{$aVideo.feed_link}">
                {img theme='feed/share_icon@2x.png'}
                {$aVideo.total_share}
            </a>
        </div>
    </div>
    {/if}
    {/if}
</div>
{/if}