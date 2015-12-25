{module name='bettermobile.cover'}
<div id="mobile_profile_header">
	<div id="mobile_profile_photo">
		<div id="mobile_profile_photo_image" {if Phpfox::getParam('bettermobile.image_radius')} class="image_radius" {/if}>
			{$sProfileImage}
        </div>
		<div class="mobile_profile_name">
            <div class="mobile_profile_name_content">
                <a href="{url link=''$aUser.user_name'}">{$aUser.full_name|clean|shorten:50:'...'}</a>
            </div>
		</div>
        {module name='bettermobile.profile'}
        <div class="clear"></div>
        <div id="mobile_profile_photo_name">
        <ul>
            {if Phpfox::getUserId() != $aUser.user_id && Phpfox::isUser()}
                <li id="js_add_friend_on_profile" {if Phpfox::isModule('friend') && $aUser.is_friend} style="display:none;" {/if}><a href="#" onclick="return $Core.addAsFriend('{$aUser.user_id}');" title="{phrase var='profile.add_to_friends'}">{phrase var='profile.add_to_friends'}</a></li>
                <li id="js_remove_friend_on_profile" {if Phpfox::isModule('friend') && !$aUser.is_friend} style="display:none;" {/if}><a onclick="oBetterMobile.removeFriend({$aUser.user_id})" href="#"  title="{phrase var='friend.unfriend'}">{phrase var='friend.unfriend'}</a></li>

                {if Phpfox::isModule('mail') && Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'mail.send_message')}
                <li id="message_friend">
                    {if Phpfox::isModule('messenger')}
                    <a href="{url link='messenger' uid=$aUser.user_id}" >{phrase var='profile.message'}</a>
                    {else}
                    <a href="{url link='mail.compose' id=$aUser.user_id}" >{phrase var='profile.message'}</a>
                    {/if}

                </li>
                {/if}
                {if $bCanPoke && Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'poke.can_send_poke')}
                <li id="liPoke">
                    <a href="#" id="section_poke" onclick="$Core.box('poke.poke', 400, 'user_id={$aUser.user_id}'); return false;">{phrase var='poke.poke' full_name=''}</a>
                </li>
                {/if}
            {/if}
        </ul>
        </div>
	<div class="clear"></div>
</div>
</div>