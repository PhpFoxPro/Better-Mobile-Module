<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_Photo
 * @version 		$Id: album-entry.html.php 5129 2013-01-14 12:38:16Z Raymond_Benc $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{literal}
<style type="text/css">
    .image_holder {
        height: 95px;
        overflow: hidden;
    }
    .image_holder img {
        width: 100%;
    }
</style>
{/literal}
<div class="photo_row" id="js_photo_id_{$aAlbum.album_id}">
    <div class="js_outer_photo_div js_mp_fix_holder photo_row_holder">

        <div class="photo_row_height">
			{if Phpfox::getParam('photo.auto_crop_photo')}
            <div class="photo_clip_holder_main">
			{/if}

				{if ($aAlbum.profile_id == '0' && ((Phpfox::getUserId() == $aAlbum.user_id && Phpfox::getUserParam('photo.can_delete_own_photo_album')) || Phpfox::getUserParam('photo.can_delete_other_photo_albums')))
					|| ($aAlbum.profile_id == '0' && Phpfox::getUserId() == $aAlbum.user_id)
					|| ((Phpfox::getUserId() == $aAlbum.user_id && Phpfox::getUserParam('photo.can_edit_own_photo_album')) || Phpfox::getUserParam('photo.can_edit_other_photo_albums'))
				}
				<a href="#" class="image_hover_menu_link">{phrase var='photo.link'}</a>
				<div class="image_hover_menu">
					<ul>
						{if $aAlbum.profile_id == '0' && ((Phpfox::getUserId() == $aAlbum.user_id && Phpfox::getUserParam('photo.can_delete_own_photo_album')) || Phpfox::getUserParam('photo.can_delete_other_photo_albums'))}
						<li class="item_delete"><a href="{url link='photo.albums' delete=$aAlbum.album_id}" id="js_delete_this_album" class="sJsConfirm">{phrase var='photo.delete'}</a></li>
						{/if}
						{if $aAlbum.profile_id == '0' && Phpfox::getUserId() == $aAlbum.user_id}
						<li><a href="{url link='photo.add' album=$aAlbum.album_id}">{phrase var='photo.upload_photo_s'}</a></li>
						{/if}
						{if (Phpfox::getUserId() == $aAlbum.user_id && Phpfox::getUserParam('photo.can_edit_own_photo_album')) || Phpfox::getUserParam('photo.can_edit_other_photo_albums')}
						<li><a href="{url link='photo.edit-album' id=$aAlbum.album_id}" id="js_edit_this_album">{phrase var='photo.edit'}</a></li>
						{/if}
					</ul>
				</div>
			{/if}

			{if Phpfox::getParam('photo.auto_crop_photo') && !Phpfox::getParam('photo.show_info_on_mouseover')}
                <div class="photo_clip_holder_border image_holder">
                    <a href="{$aAlbum.link}" class="thickbox photo_holder_image " rel="{$aAlbum.album_id}" title="{$aAlbum.name}">
                    {img server_id=$aAlbum.server_id path='photo.url_photo' file=$aAlbum.destination suffix='_150' }
                    </a>
                </div>
			{else}
                <div class="image_holder">
                    <a href="{$aAlbum.link}" title="{$aAlbum.name}" rel="{$aAlbum.album_id}">
                        {img server_id=$aAlbum.server_id path='photo.url_photo' file=$aAlbum.destination suffix='_150' }
                    </a>
			    </div>

			{/if}
			{if Phpfox::getParam('photo.auto_crop_photo')}
			</div>
			{/if}
        </div>

			<div class="photo_row_info photo_row_info_album">
				<a href="{permalink module='photo.album' id=$aAlbum.album_id title=$aAlbum.name}" id="js_album_inner_title_{$aAlbum.album_id}" class="row_sub_link">{$aAlbum.name|clean|shorten:150:'...'|split:40}</a>
				<div class="extra_info">
					{if !empty($aAlbum.total_photo)}
					{if $aAlbum.total_photo == '1'}
					1 photo
					{else}
					{$aAlbum.total_photo|number_format} photos
					{/if}
					{/if}
					{if !defined('PHPFOX_IS_USER_PROFILE')}
					<div>{$aAlbum|user:'':'':50|split:10}</div>
					{/if}
					{plugin call='photo.template_block_album_entry_extra_info'}
				</div>
			</div>
    </div>
</div>