<div class="mobile_profile_info">
    <div class="mobile_profile_info_location">
        {if Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'profile.view_location') && (!empty($aUser.city_location) || !empty($aUser.country_child_id) || !empty($aUser.location))}
        {phrase var='profile.lives_in'} {if !empty($aUser.city_location)}{$aUser.city_location}{/if}
        {if !empty($aUser.city_location) && (!empty($aUser.country_child_id) || !empty($aUser.location))},{/if}
        {if !empty($aUser.country_child_id)}&nbsp;{$aUser.country_child_id|location_child}{/if} {if !empty($aUser.location)}{$aUser.location}{/if}
        {/if}
    </div>
    <div class="mobile_profile_info_birthday">
        {if is_array($aUser.birthdate_display) && count($aUser.birthdate_display)}
        {foreach from=$aUser.birthdate_display key=sAgeType item=sBirthDisplay}
        {if $aUser.dob_setting == '2'}
        {phrase var='profile.age_years_old' age=$sBirthDisplay}
        {else}
        {phrase var='profile.born_on_birthday' birthday=$sBirthDisplay}
        {/if}
        {/foreach}
        {/if}
        {if Phpfox::getParam('user.enable_relationship_status') && $sRelationship != ''}&middot; {$sRelationship} {/if}
    </div>

</div>