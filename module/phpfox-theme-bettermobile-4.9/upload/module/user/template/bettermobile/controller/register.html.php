{literal}
<script type="text/javascript">
    $Behavior.termsAndPrivacy = function()
    {
        $('#js_terms_of_use').click(function()
        {
            {/literal}
                tb_show('{phrase var='user.terms_of_use' phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true}', $.ajaxBox('page.view', 'height=410&width=600&title=terms'));
                {literal}
                return false;
            });

            $('#js_privacy_policy').click(function()
            {
                {/literal}
                    tb_show('{phrase var='user.privacy_policy' phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true}', $.ajaxBox('page.view', 'height=410&width=600&title=policy'));
                    {literal}
                    return false;
                });
            }
</script>
{/literal}
{if Phpfox::getLib('module')->getFullControllerName() == 'user.register' && Phpfox::isModule('invite')}

<div id="main_registration_form">

	<div id="main_registration_form_holder">

{/if}
{if Phpfox::getLib('module')->getFullControllerName() != 'user.register'}
<div class="user_register_holder">
	<div class="holder">
		<div class="user_register_intro">		
			{module name='user.welcome'}
		</div>
		<div class="user_register_form">

			{if Phpfox::getParam('user.allow_user_registration')}
			<div class="user_register_title">
				{phrase var='user.sign_up'}
				<div class="extra_info">
					{phrase var='user.it_s_free_and_always_will_be'}
				</div>
			</div>
			{/if}
{/if}
		{if Phpfox::isModule('invite') && Phpfox::getService('invite')->isInviteOnly()}
		<div class="main_break">
			<div class="extra_info">				
				{phrase var='user.ssitetitle_is_an_invite_only_community_enter_your_email_below_if_you_have_received_an_invitation' sSiteTitle=$sSiteTitle}
			</div>
			<div class="main_break">
				<form method="post" action="{url link='user.register'}">
					<div class="table">
						<div class="table_left">
							{phrase var='user.email'}:
						</div>
						<div class="table_right">
							<input type="text" name="val[invite_email]" value="{if !empty($sUserEmailCookie)}{$sUserEmailCookie|clean}{/if}" />
						</div>
					</div>
					<div class="table_clear">
						<input type="submit" value="{phrase var='user.submit'}" class="button_register" />
					</div>
				</form>
			</div>
		</div>
		{else}
		{if isset($sCreateJs)}
		{$sCreateJs}
		{/if}
		<div id="js_registration_process" class="t_center" style="display:none;">
			<div class="p_top_8">				
				{img theme='ajax/add.gif' alt=''}
			</div>
		</div>
		<div id="js_signup_error_message" style="width:350px;"></div>
		{if Phpfox::getParam('user.allow_user_registration')}
			<div id="js_registration_holder">
				<form method="post" action="{url link='user.register'}" id="js_form" enctype="multipart/form-data">	
				{token}

					<div id="js_signup_block">
						{if isset($bIsPosted) || !Phpfox::getParam('user.multi_step_registration_form')}

							{template file='bettermobile.block.user.step1'}
							{template file='bettermobile.block.user.step2'}

						{else}
							{template file='bettermobile.block.user.step1'}
						{/if}
					</div>
                    {plugin call='user.template_controller_register_pre_captcha'}
					{if Phpfox::isModule('captcha') && Phpfox::getParam('user.captcha_on_signup')}
					<div id="js_register_capthca_image"{if Phpfox::getParam('user.multi_step_registration_form') && !isset($bIsPosted)} style="display:none;"{/if}>
						{module name='captcha.form'}
					</div>
					{/if}			
					
					{if Phpfox::getParam('user.new_user_terms_confirmation')}
					<div id="js_register_accept">
                        <div class="table">
								<input type="checkbox" name="val[agree]" id="agree" value="1"  {value type='checkbox' id='agree' default='1'}/> {required}{phrase var='user.i_have_read_and_agree_to_the_a_href_id_js_terms_of_use_terms_of_use_a_and_a_href_id_js_privacy_policy_privacy_policy_a'}
                        </div>
					</div>					
					{/if}
					
					<div class="register_submit">
					{if isset($bIsPosted) || !Phpfox::getParam('user.multi_step_registration_form')}
						<input type="submit" value="{phrase var='user.sign_up'}" class="button_sign_up" id="js_registration_submit" />
					{else}
						<input type="button" value="{phrase var='user.sign_up'}" class="button_sign_up" id="js_registration_submit" onclick="$Core.registration.submitForm();" />
					{/if}
					</div>
				</form>
			</div>
			{/if}
		{/if}
{if Phpfox::getLib('module')->getFullControllerName() != 'user.register'}
		</div>
		<div class="clear"></div>
	</div>
	{module name='user.images'}
</div>
{/if}
{if Phpfox::getLib('module')->getFullControllerName() == 'user.register'}
	</div>
</div>
{/if}