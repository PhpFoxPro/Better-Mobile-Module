<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond_Benc
 * @package 		Phpfox
 * @version 		$Id: like.html.php 3332 2011-10-20 12:50:29Z Raymond_Benc $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{literal}
<style type="text/css">

    #mobile_h1_main{
        display: none;
    }
</style>
{/literal}
{if !PHPFOX_IS_AJAX}

{/if}
	{if $aPage.page_type == '1'}
	{if count($aMembers)}
    <li id="pages_message_friend">
        <a href="#" onclick="return $Core.box('like.browse', 400, 'type_id=pages&amp;item_id={$aPage.page_id}&amp;force_like=1');">{phrase var='bettermobile.members_total' total=$iTotalMembers}</a>
    </li>
	{else}
	<div class="extra_info">
		{phrase var='pages.no_members_yet'}
	</div>
	{/if}
	{else}

    <li id="pages_message_friend">
		<a href="#" onclick="return $Core.box('like.browse', 400, 'type_id=pages&amp;item_id={$aPage.page_id}&amp;force_like=1');">{phrase var='bettermobile.likes_total_like' total_like=$aPage.total_like}</a>
	</li>
	{/if}
{if !PHPFOX_IS_AJAX}

{/if}