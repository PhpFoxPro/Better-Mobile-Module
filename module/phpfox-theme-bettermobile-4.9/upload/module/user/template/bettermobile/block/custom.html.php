{foreach from=$aSettings item=aSetting}
		<div class="signup_input js_custom_groups{if isset($aSetting.group_id)} js_custom_group_{$aSetting.group_id}{/if}">
				{template file='custom.block.register'}
		</div>
		{/foreach}
		{plugin call='user.template_controller_profile_form'}