
<ul class="mobile_profile_header_menu profile_bottom" style="">
    <li><a href="{url link=$aUser.user_name'.wall'}"{if $bIsInfo == 1} class="active"{/if}>{phrase var='profile.wall'}</a></li>
    <li><a href="{url link=$aUser.user_name'.info'}"{if $bIsInfo == 2} class="active"{/if}>{phrase var='profile.info'}</a></li>
    <li><a href="{url link=$aUser.user_name'.photo'}"{if $bIsInfo == 3} class="active"{/if}>{phrase var='user.photos'}</a></li>
</ul>
