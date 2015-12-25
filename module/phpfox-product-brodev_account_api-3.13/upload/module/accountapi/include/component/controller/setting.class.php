<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright       [PHPFOX_COPYRIGHT]
 * @author          Raymond_Benc
 * @package         Phpfox_Component
 * @version         $Id: setting.class.php 4080 2012-03-28 15:08:47Z Miguel_Espinoza $
 */
 
class Accountapi_Component_Controller_Setting extends Phpfox_Component 
{
    public function process() 
    {
        header('Content-Type: text/json');
        //call service
        $oServiceAccountapiCore = Phpfox::getService('accountapi.core');

		$aRow = $oServiceAccountapiCore->getFacebookSetting();
		$aColor = $oServiceAccountapiCore->getColorForApp();

		$aResult['display_fb'] = $aRow['facebook.enable_facebook_connect'];
		$aResult['secret'] = $aRow['facebook.facebook_secret'];
		$aResult['app_id'] = $aRow['facebook.facebook_app_id'];

		$aResult['color_app'] = $aColor['accountapi.choose_color'];
        $aResult['bg_time'] = Phpfox::getService('accountapi')->getBGtime();

        if (Phpfox::isModule('bettermobile')) {
            $aImagePath = Phpfox::getService('bettermobile.background')->getActive();
            if (!empty($aImagePath)) {
                $aResult["login_background"] = Phpfox::getLib('image.helper')->display(array(
                        'path' => 'bettermobile.image_url',
                        'server_id' => isset($aImagePath['server_id']) ? $aImagePath['server_id'] : 0,
                        'file' => $aImagePath['image'],
                        'suffix' => '',
                        'return_url' => true
                    )
                );
            }

        }

        $aResult['enable_rate'] = Phpfox::getParam('accountapi.enable_rate');
        $aResult['phrases'] = $oServiceAccountapiCore->getPhrases('');

        (($sPlugin = Phpfox_Plugin::get('accountapi.component_controller_setting_get_json_value')) ? eval($sPlugin) : false);

		echo json_encode($aResult);
		exit();
    }
}