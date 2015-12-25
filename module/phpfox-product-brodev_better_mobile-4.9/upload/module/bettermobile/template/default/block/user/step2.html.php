	<div id="js_register_step2">
		{plugin call='user.template_default_block_register_step2_6'}
	{if !isset($bIsPosted) && Phpfox::getParam('user.multi_step_registration_form')}
		<div class="p_bottom_10">{phrase var='user.complete_this_step_to_setup_your_personal_profile'}</div>
	{/if}
	{if Phpfox::getParam('core.registration_enable_dob')}
        <div class="signup_input">
				{select_date start_year=$sDobStart end_year=$sDobEnd field_separator='  ' field_order='MDY' bUseDatepicker=false sort_years='DESC'}

		</div>
	{/if}
        {if Phpfox::getParam('core.registration_enable_gender')}
        <div class="signup_input">
            {select_gender value_title='phrase var=user.i_am'}
        </div>
        {/if}
	{if Phpfox::getParam('core.registration_enable_location')}
        <div class="signup_input">
				{select_location value_title='phrase var=user.location'}
		</div>
        {module name='core.country-child' country_force_div=true}
        {/if}
        {if Phpfox::getLib('setting')->isParam('core.city_in_registration') && Phpfox::getParam('core.city_in_registration')}
        <div class="signup_input">
            <input type="text" name="val[city_location]" id="city_location" value="{value type='input' id='city_location'}" size="30" placeholder="{phrase var='user.city'}"/>
        </div>
        {/if}
	{if Phpfox::getParam('core.registration_enable_timezone')}
        <div class="signup_input">
				<select name="val[time_zone]">
				{foreach from=$aTimeZones key=sTimeZoneKey item=sTimeZone}
					<option value="{$sTimeZoneKey}"{if (Phpfox::getTimeZone() == $sTimeZoneKey && !isset($iTimeZonePosted)) || (isset($iTimeZonePosted) && $iTimeZonePosted == $sTimeZoneKey)} selected="selected"{/if}>{$sTimeZone}</option>
				{/foreach}
				</select>
		</div>		
	{/if}
		{plugin call='user.template_default_block_register_step2_7'}
        {template file='bettermobile.block.user.custom'}
		{plugin call='user.template_default_block_register_step2_8'}
		{if Phpfox::isModule('subscribe') && Phpfox::getParam('subscribe.enable_subscription_packages') && count($aPackages)}

        <div class="signup_input">
				<select name="val[package_id]" id="js_subscribe_package_id">
				{if Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up')}				
					<option value=""{value type='select' id='package_id' default='0'}>{phrase var='user.select'}:</option>
				{else}
					<option value=""{value type='select' id='package_id' default='0'}>{phrase var='user.free_normal'}</option>
				{/if}
				{foreach from=$aPackages item=aPackage}
					<option value="{$aPackage.package_id}"{value type='select' id='package_id' default=''$aPackage.package_id''}>{if $aPackage.show_price}({if $aPackage.default_cost == '0.00'}{phrase var='subscribe.free'}{else}{$aPackage.default_currency_id|currency_symbol}{$aPackage.default_cost}{/if}) {/if}{$aPackage.title|convert|clean}</option>
				{/foreach}
				</select>
				<div class="extra_info">
					<a href="#" onclick="tb_show('{phrase var='user.membership_upgrades' phpfox_squote=true}', $.ajaxBox('subscribe.listUpgradesOnSignup', 'height=400&width=500')); return false;">{phrase var='user.click_here_to_learn_more_about_our_membership_upgrades'}</a>

			</div>
			<div class="clear"></div>
		</div>				
		{/if}
	</div>
    {if $bNewVersion}
    {module name='user.showspamquestion'}
    {/if}
	{if Phpfox::getParam('user.force_user_to_upload_on_sign_up')}
    <div class="signup_input">
				<input type="file" name="image" />
		</div>
		{/if}