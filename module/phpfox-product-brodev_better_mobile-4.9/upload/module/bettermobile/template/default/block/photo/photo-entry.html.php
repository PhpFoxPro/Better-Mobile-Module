<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_Photo
 * @version 		$Id: photo-entry.html.php 4968 2012-10-30 11:55:11Z Miguel_Espinoza $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{foreach from=$aPhotos item=aPhoto name=photos}

<div class="{if $aPhoto.view_id == 1 && !isset($bIsInApproveMode)} row_moderate_image{/if} {if $aPhoto.is_sponsor} row_sponsored_image{/if}{if isset($sView) && $sView == 'featured'}{else}{if $aPhoto.is_featured} row_featured_image{/if}{/if} photo_row" id="js_photo_id_{$aPhoto.photo_id}">
    {if Phpfox::getUserParam('photo.can_approve_photos') || Phpfox::getUserParam('photo.can_delete_other_photos')}
    <div class="video_moderate_link"><a href="#{$aPhoto.photo_id}" class="moderate_link" rel="photo">{phrase var='photo.moderate'}</a></div>
    {/if}
    {if ($aPhoto.view_id == 1 && Phpfox::getUserParam('photo.can_approve_photos')) || ($aPhoto.user_id == Phpfox::getUserId() && (Phpfox::getUserParam('photo.can_edit_own_photo_album') || Phpfox::getUserParam('photo.can_edit_own_photo') || Phpfox::getUserParam('photo.can_delete_own_photo'))) || (Phpfox::getUserParam('photo.can_edit_other_photo_albums') || Phpfox::getUserParam('photo.can_edit_other_photo') || Phpfox::getUserParam('photo.can_delete_other_photos'))
    || (defined('PHPFOX_IS_PAGES_VIEW') && Phpfox::getService('pages')->isAdmin('' . $aPage.page_id . ''))
    }


    {/if}
    <div class="js_outer_photo_div js_mp_fix_holder photo_row_holder">

        <div class="photo_row_height image_hover_holder">
            {if Phpfox::getParam('photo.auto_crop_photo')}
            <div class="photo_clip_holder_main">
                {/if}
                {if ($aPhoto.view_id == 1 && Phpfox::getUserParam('photo.can_approve_photos')) || ($aPhoto.user_id == Phpfox::getUserId() && (Phpfox::getUserParam('photo.can_edit_own_photo_album') || Phpfox::getUserParam('photo.can_edit_own_photo') || Phpfox::getUserParam('photo.can_delete_own_photo'))) || (Phpfox::getUserParam('photo.can_edit_other_photo_albums') || Phpfox::getUserParam('photo.can_edit_other_photo') || Phpfox::getUserParam('photo.can_delete_other_photos'))
                || (defined('PHPFOX_IS_PAGES_VIEW') && Phpfox::getService('pages')->isAdmin('' . $aPage.page_id . ''))
                }

                <div class="image_hover_menu">
                    <ul>
                        {if ((Phpfox::getUserParam('photo.can_delete_own_photo') && $aPhoto.user_id == Phpfox::getUserId()) || Phpfox::getUserParam('photo.can_delete_other_photos'))
                        || (defined('PHPFOX_IS_PAGES_VIEW') && Phpfox::getService('pages')->isAdmin('' . $aPage.page_id . ''))
                        }
                        <li class="item_delete"><a href="#" title="{phrase var='photo.delete_this_photo'}" onclick="if (confirm('{phrase var='photo.are_you_sure' phpfox_squote=true}')) $.ajaxCall('photo.deletePhoto', 'photo_id={$aPhoto.photo_id}'); return false;">{phrase var='photo.delete_photo'}</a></li>
                        {/if}

                        {if Phpfox::getUserParam('photo.can_sponsor_photo')}
                        <li id="js_photo_sponsor_{$aPhoto.photo_id}">
                            <a href="#" onclick="$.ajaxCall('photo.sponsor', 'photo_id={$aPhoto.photo_id}&type={if $aPhoto.is_sponsor == 1}0{else}1{/if}');return false;"> {if $aPhoto.is_sponsor == 1}{phrase var='photo.unsponsor_this_photo'}{else}{phrase var='photo.sponsor_this_photo'}{/if}</a>
                        </li>
                        {/if}

                        {if isset($aPage) && isset($aPage.page_id)}
                        <li>
                            <a href="#" title="Set this as your Page's cover photo" onclick="$Core.Pages.setAsCover({$aPage.page_id},{$aPhoto.photo_id}); return false;">
                                Set as your page's cover photo
                            </a>
                        </li>
                        {/if}

                        {if Phpfox::getParam('photo.display_profile_photo_within_gallery') == false && ((isset($aPhoto.is_profile_photo) && !$aPhoto.is_profile_photo) || !isset($aPhoto.is_profile_photo))}
                        {if Phpfox::getUserParam('photo.can_feature_photo') && !$aPhoto.is_sponsor}
                        <li id="js_photo_feature_{$aPhoto.photo_id}">
                            {if $aPhoto.is_featured}
                            <a href="#" title="{phrase var='photo.un_feature_this_photo'}" onclick="$.ajaxCall('photo.feature', 'photo_id={$aPhoto.photo_id}&amp;type=0', 'GET'); return false;">{phrase var='photo.un_feature'}</a>
                            {else}
                            <a href="#" title="{phrase var='photo.feature_this_photo'}" onclick="$.ajaxCall('photo.feature', 'photo_id={$aPhoto.photo_id}&amp;type=1', 'GET'); return false;">{phrase var='photo.feature'}</a>
                            {/if}
                        </li>
                        {/if}
                        {/if}
                        {if $aPhoto.user_id == Phpfox::getUserId()}
                        <li>
                            <a href="#" title="Set this photo as your profile image." onclick="tb_show('', '', null, '{phrase var='photo.setting_this_photo_as_your_profile_picture_please_hold'}', true); $.ajaxCall('photo.makeProfilePicture', 'photo_id={$aPhoto.photo_id}', 'GET'); return false;">{phrase var='photo.make_profile_picture'}</a>
                        </li>
                        {if Phpfox::getUserParam('profile.can_change_cover_photo')}
                        <li>
                            <a href="#" title="{phrase var='user.set_this_photo_as_your_profile_cover_photo'}" onclick="$.ajaxCall('user.setCoverPhoto', 'photo_id={$aPhoto.photo_id}', 'GET'); return false;">{phrase var='user.set_as_cover_photo'}</a>
                        </li>
                        {/if}
                        {/if}

                        {if (Phpfox::getUserParam('photo.can_edit_own_photo') && $aPhoto.user_id == Phpfox::getUserId()) || Phpfox::getUserParam('photo.can_edit_other_photo')}
                        <li><a href="#" title="{phrase var='photo.edit_this_photo'}"onclick="$Core.box('photo.editPhoto', 700, 'photo_id={$aPhoto.photo_id}&amp;inline=true'); $('#js_tag_photo').hide();return false;">{phrase var='photo.edit_photo'}</a></li>
                        {/if}

                        {plugin call='photo.template_default_block_photo_entry_hover_end'}
                    </ul>
                </div>
                {/if}
                {plugin call='photo.template_default_block_photo_entry_tool'}


                {if isset($sView) && $sView == 'featured'}
                {else}
                <div class="js_featured_photo row_featured_link"{if !$aPhoto.is_featured} style="display:none;"{/if}>
                {phrase var='photo.featured'}
            </div>
            {/if}
            <div class="js_sponsor_photo row_sponsored_link"{if !$aPhoto.is_sponsor} style="display:none;"{/if}>
            {phrase var='photo.sponsored'}
        </div>
        {if isset($sView) && $sView == 'pending'}
        {else}
        <div class="js_pending_photo row_pending_link"{if $aPhoto.view_id != '1'} style="display:none;"{/if}>
        {phrase var='photo.pending'}
    </div>
    {/if}



    {if Phpfox::getParam('photo.auto_crop_photo')}
    <div class="photo_clip_holder_border image_holder">
        <a href="{$aPhoto.link}{if isset($iForceAlbumId)}albumid_{$iForceAlbumId}/{/if}" no-repeat;" class="thickbox photo_holder_image " rel="{$aPhoto.photo_id}" title="{phrase var='photo.title_by_full_name' title=$aPhoto.title|clean full_name=$aPhoto.full_name|clean}">
        {img server_id=$aPhoto.server_id path='photo.url_photo' file=$aPhoto.destination suffix='_150' }
        </a>
    </div>
    {else}
    <div class="image_holder">
        {if ($aPhoto.mature == 0 || (($aPhoto.mature == 1 || $aPhoto.mature == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aPhoto.user_id == Phpfox::getUserId()}
        <a href="{$aPhoto.link}{if isset($iForceAlbumId)}albumid_{$iForceAlbumId}/{/if}{if isset($sPhotoCategory)}category_{$sPhotoCategory}/{/if}" title="{phrase var='photo.title_by_full_name' title=$aPhoto.title|clean full_name=$aPhoto.full_name|clean}" class="thickbox photo_holder_image" rel="{$aPhoto.photo_id}">
            {img server_id=$aPhoto.server_id path='photo.url_photo' file=$aPhoto.destination suffix='_150' }
        </a>
        {else}
        <a href="{$aPhoto.link}{if isset($iForceAlbumId)}albumid_{$iForceAlbumId}/{/if}"{if $aPhoto.mature == 1} onclick="tb_show('{phrase var='photo.warning' phpfox_squote=true}', $.ajaxBox('photo.warning', 'height=300&amp;width=350&amp;link={$aPhoto.link}')); return false;"{/if} class="no_ajax_link">{img theme='misc/no_access.png' alt=''}</a>
        {/if}
    </div>
    {/if}

    {if Phpfox::getParam('photo.auto_crop_photo')}
</div>
{/if}
</div>

<div class="photo_row_info">
    {if !isset($bIsInAlbumMode)}
    <div class="extra_info_link">
        {phrase var='photo.by_user_info' user_info=$aPhoto|user|shorten:30:'...'|split:20}
        {if !empty($aPhoto.album_name)}
        <div>{phrase var='photo.in'} <a href="{permalink module='photo.album' id=$aPhoto.album_id title=$aPhoto.album_name}" title="{$aPhoto.album_name|clean}">{if $aPhoto.album_profile_id > 0}{phrase var='photo.profile_pictures'}{else}{$aPhoto.album_name|clean|shorten:12:'...'|split:12}{/if}</a></div>
        {/if}
    </div>
    {/if}
    {if isset($sView) && $sView == 'top-rated'}
    <div class="p_2">
        {phrase var='photo.total_rating_out_of_5' total_rating=$aPhoto.total_rating|round}
    </div>
    {elseif isset($sView) && $sView == 'top-battle'}
    <div class="p_2">
        {phrase var='photo.total_battle_win_s' total_battle=$aPhoto.total_battle}
    </div>
    {/if}
    {plugin call='photo.template_default_block_photo_entry_info'}
</div>
</div>
</div>
{/foreach}
<div class="clear"></div>
<div class="t_right">
    {pager}
</div>