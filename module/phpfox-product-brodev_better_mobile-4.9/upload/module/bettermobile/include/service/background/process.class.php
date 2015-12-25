<?php
class Bettermobile_Service_Background_Process extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('brodev_bettermobile_background');
        Phpfox::getLib('setting')->setParam(array('bettermobile.image_folder' => PHPFOX_DIR_FILE . 'pic' . PHPFOX_DS . 'bettermobile' . PHPFOX_DS));
        Phpfox::getLib('setting')->setParam(array('bettermobile.image_url' => Phpfox::getParam('core.url_pic') . 'bettermobile/'));
    }
    private $_aPhotoSizes = array(100);

    /**
     * add new image
     * @param $aVals
     * @return bool
     */
    public function add($aVals) {
            $aVals['time_stamp'] = PHPFOX_TIME;
            $aVals['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            $iId = $this->database()->insert($this->_sTable, $aVals);
        if (!empty($_FILES['image']['name']))
        {
            $aImage = Phpfox::getLib('file')->load('image', array(
                    'jpg',
                    'gif',
                    'png'
                )
            );
            if ($aImage === false)
            {
                return $iId;
            }
            $oImage = Phpfox::getLib('image');

            $sFileName= Phpfox::getLib('file')->upload('image', Phpfox::getParam('bettermobile.image_folder'), $iId);

            $iFileSizes = filesize(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, ''));

            foreach ($this->_aPhotoSizes as $iSize)
            {
                $oImage->createThumbnail(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, ''), Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
                $oImage->createThumbnail(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, ''), Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);

                $iFileSizes += filesize(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, '_' . $iSize));
            }

            $this->database()
                ->update($this->_sTable, array('image' => $sFileName), 'id = ' . $iId);
        }
    }

    /**
     * update database
     * @param $aVals
     * @param $iId
     * @return mixed
     */
    public function update($aVals, $iId) {

        if (!empty($_FILES['image']['name']))
        {
            $this->deleteImage($iId);
            $aImage = Phpfox::getLib('file')->load('image', array(
                    'jpg',
                    'gif',
                    'png'
                )
            );
            if ($aImage === false)
            {
                return $iId;
            }
            $oImage = Phpfox::getLib('image');

            $sFileName= Phpfox::getLib('file')->upload('image', Phpfox::getParam('bettermobile.image_folder'), $iId);

            $iFileSizes = filesize(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, ''));

            foreach ($this->_aPhotoSizes as $iSize)
            {
                $oImage->createThumbnail(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, ''), Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
                $oImage->createThumbnail(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, ''), Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);

                $iFileSizes += filesize(Phpfox::getParam('bettermobile.image_folder') . sprintf($sFileName, '_' . $iSize));
            }

            $this->database()
                ->update($this->_sTable, array('image' => $sFileName), 'id = ' . $iId);
        }
        $aVals['time_stamp'] = PHPFOX_TIME;
        return $this->database()->update($this->_sTable, $aVals, 'id = '. $iId);
    }

    /**
     * delete image by id
     * @param $iId
     * @return bool
     */
    public function deleteImage($iId) {

        $sImage = $this->database()
            ->select('image')
            ->from($this->_sTable)
            ->where('id = '. $iId)
            ->execute('getSlaveField');
        $oFile = Phpfox::getLib('file');
        if (!empty($sImage)) {
            // delete image
            if (file_exists(Phpfox::getParam('bettermobile.image_folder') . sprintf($sImage, ''))) {
                $oFile->unlink(Phpfox::getParam('bettermobile.image_folder') . sprintf($sImage, ''));
            }
            foreach ($this->_aPhotoSizes as $iSize) {
                if (file_exists(Phpfox::getParam('bettermobile.image_folder') . sprintf($sImage, '_' . $iSize))) {
                    $oFile->unlink(Phpfox::getParam('bettermobile.image_folder') . sprintf($sImage, '_' . $iSize));
                }
                if (file_exists(Phpfox::getParam('bettermobile.image_folder') . sprintf($sImage, '_' . $iSize . '_square'))) {
                    $oFile->unlink(Phpfox::getParam('bettermobile.image_folder') . sprintf($sImage, '_' . $iSize . '_square'));
                }
            }

        }
        return true;
    }

    /**
     * delete from database
     * @param $iId
     * @return mixed
     */
    public function delete($iId) {
        $this->deleteImage($iId);
        return $this->database()->delete($this->_sTable, 'id = '. $iId);
    }
}

