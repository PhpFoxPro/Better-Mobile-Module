<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package 		Phpfox
 * @version 		$Id: controller.html.php 64 2009-01-19 15:05:54Z Raymond_Benc $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
		{foreach from=$aSettings item=aSetting}
            <div class="signup_input">
				{template file='bettermobile.block.custom.register'}
            </div>
		{/foreach}
		{plugin call='user.template_controller_profile_form'}