{if !PHPFOX_IS_AJAX}
<div id="js_basic_info_data">
    {/if}
    <div class="info first">Basic Info</div>
    {if !Phpfox::isMobile() && Phpfox::isModule('rate') && Phpfox::getParam('profile.can_rate_on_users_profile') && Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'rate.can_rate')}
    <div class="info">
        <div class="info_left">
            {phrase var='profile.rating'}:
        </div>
        <div class="info_right">
            {module name='rate.display'}
        </div>
    </div>
    {/if}
    {if Phpfox::getParam('user.enable_relationship_status') && $sRelationship != ''}
    <div class="info">
        <div class="info_left">
            {phrase var='profile.relationship_status'}:
        </div>
        <div class="info_right">
            {$sRelationship}
        </div>
    </div>
    {/if}
    {foreach from=$aUserDetails key=sKey item=sValue name=profile}
    {if !empty($sValue)}
    <div class="info">
        <div class="info_left">
            {$sKey}:
        </div>
        <div class="info_right">
            {if Phpfox::isMobile()}{$sValue|strip_tags}{else}{$sValue}{/if}
        </div>
    </div>
    {/if}
    {/foreach}
    {module name='custom.display' type_id='user_panel' template='info'}
    {plugin call='profile.template_block_info'}
    {if !PHPFOX_IS_AJAX}
</div>
<div id="js_basic_info_form"></div>
{/if}