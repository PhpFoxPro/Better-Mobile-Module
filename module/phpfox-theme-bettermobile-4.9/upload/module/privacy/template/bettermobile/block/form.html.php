<div class="{if $sPrivacyFormType == 'mini'}privacy_setting_mini{else}privacy_setting{/if} privacy_setting_div">
    <div><input type="hidden" id="{$sPrivacyFormName}"
                name="val{if !empty($sPrivacyArray)}[{$sPrivacyArray}]{/if}[{$sPrivacyFormName}]"
                value="{$aSelectedPrivacyControl.value}"/></div>
    <li class="menu_privacy_drop"><a href="#"
                                     class="privacy_setting_active{if $sPrivacyFormType == 'mini'} {/if}">{$aSelectedPrivacyControl.phrase}<span
        class="js_hover_info">{$aSelectedPrivacyControl.phrase}</span></a><i></i>

        <div class="privacy_setting_holder">
            <ul class="menu_drop">
                {foreach from=$aPrivacyControls name=privacycontrol item=aPrivacyControl}
                <li><a href="#"{if isset($aPrivacyControl.onclick)} onclick="{$aPrivacyControl.onclick} return
                    false;"{/if} rel="{$aPrivacyControl.value}" {if (isset($aPrivacyControl.is_active)) ||
                    (isset($bNoActive) && $bNoActive && $phpfox.iteration.privacycontrol ==
                    1)}class="is_active_image"{/if}>{$aPrivacyControl.phrase}</a></li>
                {/foreach}
            </ul>
        </div>
    </li>
</div>


{if !empty($sPrivacyFormInfo)}
<div class="extra_info">
    {$sPrivacyFormInfo}
</div>
{/if}