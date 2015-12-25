<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_Profile
 * @version 		$Id: logo.html.php 4914 2012-10-22 07:52:17Z Raymond_Benc $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="cover_photo_link">
    <a href="{permalink module='photo' id=$aCoverPhoto.photo_id title=$aCoverPhoto.title}userid_{$aCoverPhoto.user_id}/" class="thickbox photo_holder_image cover_photo_link" rel="{$aCoverPhoto.photo_id}">
        {img id='js_photo_cover_position' server_id=$aCoverPhoto.server_id path='photo.url_photo' file=$aCoverPhoto.destination suffix='_1024' title=$aCoverPhoto.title style='width: 100%; min-width:386px;' data-position=$sLogoPosition}
    </a>
</div>
{literal}
<script type="text/javascript" language="javascript">
    $Behavior.positionCoverPhoto = function() {
        $('.cover_photo_link img').load(function() {
            $(this).width(980);
            var naturalHeight = $(this).height();
            var position = $(this).data('position');

            // set image to full width
            $(this).width('100%');
            // get new height
            var height = $(this).height();
            var percent = height / naturalHeight;

            var position = percent * position;
            $(this).css('top', position + 'px');
        });
    }
</script>

<style type="text/css">
    #mobile_profile_photo_image{
        position: absolute;
        top:-35px;
    }
    .mobile_profile_name{
        top:3px;
    }
    #mobile_profile_header{
        border-top: 1px #cacaca solid;
        min-height: 90px;
    }
</style>
{/literal}