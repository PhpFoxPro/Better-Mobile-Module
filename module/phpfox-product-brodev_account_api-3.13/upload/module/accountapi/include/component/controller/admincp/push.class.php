<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package 		Phpfox_Component
 * @version 		$Id: push.html.php 6456 2013-08-13 12:47:12Z Raymond_Benc $
 */

class Accountapi_Component_Controller_Admincp_Push extends Phpfox_Component
{
    public function process()
    {
        $bProcessing = false;

        $aValidation = array(
            'message' => Phpfox::getPhrase('accountapi.provide_a_content_for_your_notification')
        );

        $oValid = Phpfox::getLib('validator')->set(array(
                'sFormName' => 'js_form',
                'aParams' => $aValidation
            )
        );

        //request sumbit
        if (($aVals = $this->request()->getArray('val')))
        {
            if ($oValid->isValid($aVals))
            {
                $aData = array(
                    'notify' => 'admin',
                    'sound' => 'default',
                    'alert' => $aVals['message'],
                    'action' => (!empty($aVals['link']) ? array('link' => $aVals['link']) : array('link' => 'http://'))
                );

                $bProcessing = true;
                Phpfox::getService('accountapi.admincp')->pushNotification($aData);

                $this->url()->send('admincp.accountapi.push', null, Phpfox::getPhrase('accountapi.notification_sent_successfully'));
            }
        }

        $this->template()->assign(array(
                'bProcess' => $bProcessing
            )
        );
    }
}