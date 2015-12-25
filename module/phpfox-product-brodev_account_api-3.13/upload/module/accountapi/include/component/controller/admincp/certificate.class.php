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
 * @version 		$Id: certificate.class.php 6456 2013-08-13 12:47:12Z Raymond_Benc $
 */

class Accountapi_Component_Controller_Admincp_Certificate extends Phpfox_Component
{
    public function process()
    {
        $sCertificateFile = Phpfox::getService('accountapi.upload')->pemFile();

        $this->template()
            ->setBreadcrumb('Brodev Mobile API')
            ->assign(array(
                'sCertificate' => $sCertificateFile,
        ));
    }
}