<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * 
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond_Benc
 * @package 		Phpfox_Component
 * @version 		$Id: block.class.php 103 2009-01-27 11:32:36Z Raymond_Benc $
 */
class Bettermobile_Component_Block_Pages_Photo extends Phpfox_Component
{
	/**
	 * Class process method wnich is used to execute this component.
	 */
	public function process()
	{
		$aPage = $this->getParam('aPage');

		$this->template()->assign(array(
				'aPageLinks' => Phpfox::getService('pages')->getMenu($aPage)
			)
		);

	}
	
	/**
	 * Garbage collector. Is executed after this class has completed
	 * its job and the template has also been displayed.
	 */
	public function clean()
	{
		(($sPlugin = Phpfox_Plugin::get('pages.component_block_photo_clean')) ? eval($sPlugin) : false);
	}
}

?>