<?php

/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright       [PHPFOX_COPYRIGHT]
 * @author          Raymond Benc
 * @package         Phpfox_Service
 * @version         $Id: upload.class.php 67 2009-01-20 11:32:45Z Raymond_Benc $
 */

class Accountapi_Service_Upload extends Phpfox_Service
{
    /**
     * Upload pem file
     * @return bool|string
     */
    public function pemFile() {
        $sExtension = 'pem';
        $sDestination = PHPFOX_DIR . 'file/accountapi/certificate/';
        $sFileName = 'PushAppCerKey';
        $oFile = Phpfox::getLib('file');

        if ($sType = $this->request()->get('type')) {
            if ($sType == 'file') {
                //upload pem file
                if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                    $aImage = $oFile->load('file', array($sExtension));

                    if ($aImage) {
                        $sFileName = $oFile->upload('file', $sDestination, $sFileName, false, 0644, false);
                    }
                }
                Phpfox::getLib('url')->send('admincp.accountapi.certificate', null, Phpfox::getPhrase('accountapi.pem_file_is_uploaded'));
            }
        }

        if (file_exists($sDestination. $sFileName. '.' . $sExtension)) {
            $sCertificateFile =  $sFileName. '.' . $sExtension;
        } else {
            $sCertificateFile = false;
        }

        return  $sCertificateFile;
    }
}