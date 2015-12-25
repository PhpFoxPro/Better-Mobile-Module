	<div id="js_register_step1">
		{plugin call='user.template_default_block_register_step1_3'}
		{if Phpfox::getParam('user.split_full_name')}
		<div class="" style="display: none;"><input type="hidden" name="val[full_name]" id="full_name" value="stock"/></div>
		<div class="signup_input first">
				<input type="text" name="val[first_name]" id="first_name" value="{value type='input' id='first_name'}" size="30" placeholder="{phrase var='user.first_name'}"/>
		</div>		
		<div class="signup_input">
				<input type="text" name="val[last_name]" id="last_name" value="{value type='input' id='last_name'}" size="30" placeholder="{phrase var='user.last_name'}"/>
		</div>
		{else}
		<div class="signup_input first">
				<input type="text" name="val[full_name]" id="full_name" value="{value type='input' id='full_name'}" placeholder="{phrase var='user.full_name'}"/>
		</div>
		{/if}
		{if !Phpfox::getParam('user.profile_use_id') && !Phpfox::getParam('user.disable_username_on_sign_up')}
		<div class="signup_input">
				<input type="text" name="val[user_name]" id="user_name" placeholder="{phrase var='user.choose_a_username'}" onkeyup="$('.bt-wrapper').remove(); $(this).ajaxCall('user.showUserName');" {if Phpfox::getParam('user.suggest_usernames_on_registration')}onfocus="$('#btn_verify_username').slideDown()"{/if} title="{phrase var='user.your_username_is_used_to_easily_connect_to_your_profile'}" value="{value type='input' id='user_name'}" size="30" autocomplete="off" />

        </div>
		{/if}
		{if Phpfox::getParam('user.reenter_email_on_signup')}
		{/if}
        <div class="signup_input">
        <input type="text" name="val[email]" id="email" value="{value type='input' id='email'}" size="30" placeholder="{phrase var='user.email'}"/>
        </div>
		{if Phpfox::getParam('user.reenter_email_on_signup')}
        <div class="signup_input">
					<input type="text" name="val[confirm_email]" id="confirm_email" value="{value type='input' id='confirm_email'}" size="30" onblur="$('#js_form').ajaxCall('user.confirmEmail');" placeholder="{phrase var='user.please_reenter_your_email_again_below'}"/>
				<div id="js_confirm_email_error1" style="display:none;"><div class="error_message">{phrase var='user.email_s_do_not_match'}</div></div>
		</div>
		{/if}
		{plugin call='user.template_default_block_register_step1_5'}
        <div class="signup_input">

				{if isset($bIsPosted)}
				<input type="password" name="val[password]" id="password" value="{value type='input' id='password'}" placeholder="{phrase var='user.password'}"/>
				{else}
				<input type="password" name="val[password]" id="password" value="" placeholder="{phrase var='user.password'}" />				{/if}

		</div>
		{plugin call='user.template_default_block_register_step1_4'}
	</div>