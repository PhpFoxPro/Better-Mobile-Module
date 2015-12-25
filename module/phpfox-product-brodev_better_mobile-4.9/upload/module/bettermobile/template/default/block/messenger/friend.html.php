<?php
/**
 * Created by Phpfox Pro.
 * User: Huy Nguyen
 * Date: 3/9/14
 * Time: 1:32 PM
 */
defined('PHPFOX') or exit('NO DICE!');

?>

<div id="better_messenger">
    <div id="searchTool">
        <div class="chat_search_bar">
            <input class="chat_input search_input" type="text" value="" placeholder="{phrase var='messenger.search_friend'}">
        </div>
    </div>
    <div data-page="1"  id="mobile_chat_friend"></div>
    <div id="mobile_search_friend" style="display: none;"></div>
</div>

<div class="hidden_info">
    <input type="hidden" id="owner_user_id" value="{$aOwner.user_id}">
    <input type="hidden" id="owner_full_name" value="{$aOwner.full_name}">
    <div id="owner_avatar">{img user=$aOwner suffix='_50_square' height=40 width=40}</div>
</div>

