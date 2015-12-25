<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond_Benc
 * @package 		Phpfox
 * @version 		$Id: controller.html.php 64 2009-01-19 15:05:54Z Raymond_Benc $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>


<ul class="mobile_profile_header_menu profile_bottom">
		{foreach from=$aPageLinks item=aPageLink}
            {if ($aPageLink.landing == '') || ($aPageLink.landing == 'info') || ($aPageLink.landing == 'photo')  }
            <li><a href="{$aPageLink.url}" {if isset($aPageLink.is_selected)} class="active"{/if}>{$aPageLink.phrase}{if isset($aPageLink.total)}<span>({$aPageLink.total|number_format})</span>{/if}</a></li>
		    {/if}
        {/foreach}
</ul>
<div class="clear"></div>
