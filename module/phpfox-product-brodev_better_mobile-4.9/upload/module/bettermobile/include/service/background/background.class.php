<?php
class Bettermobile_Service_Background_Background extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('brodev_bettermobile_background');
        $this->_sFile = PHPFOX_DIR_MODULE . 'bettermobile'. PHPFOX_DS . 'include' . PHPFOX_DS . 'static' . PHPFOX_DS . 'sample.txt';
        Phpfox::getLib('setting')->setParam(array('bettermobile.image_folder' => PHPFOX_DIR_FILE . 'pic' . PHPFOX_DS . 'bettermobile' . PHPFOX_DS));
        Phpfox::getLib('setting')->setParam(array('bettermobile.image_url' => Phpfox::getParam('core.url_pic') . 'bettermobile/'));
    }

    /**
     * get all images
     * @return mixed
     */
    public function getList() {
        $aImages = $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->order('time_stamp desc')
            ->execute('getRows');
        foreach($aImages as $iKey => $aImage) {
            $this->_processImage($aImages[$iKey]);
        }
        return $aImages;
    }

    /**
     * get for edit
     * @param $iId
     * @return mixed
     */
    public function getForEdit($iId) {
        $aImage = $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->where('id = '. $iId)
            ->execute('getRow');
        return $aImage;
    }

    /**
     * get random image active
     * @return mixed
     */
    public function getActive() {
        if (Phpfox::getParam('bettermobile.is_background_random')) {
            $this->database()->order('rand()');
        } else {
            $this->database()->order('time_stamp desc');
        }
        $aImage = $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->where('active = 1 AND image <> \'\'')
            ->limit(1)
            ->execute('getRow');
        $this->_processImage($aImage);
        return $aImage;
    }

    /**
     * create Sample file
     * @return bool
     */
    public function createSample() {
        $aImage = $this->getList();
        $fExport = fopen($this->_sFile, "w");
        fwrite($fExport,json_encode($aImage));
        fclose($fExport);
        return true;
    }

    /**
     * import sample data
     * @return bool
     */
    public function importSample() {
        $sCode = file_get_contents($this->_sFile);
        $aImages = json_decode($sCode, true);
        ;
        foreach($aImages as $aImage) {
            $aVals['title'] = $aImage['title'];
            $aVals['image'] = $aImage['image'];
            $aVals['active'] = $aImage['active'];
            Phpfox::getService('bettermobile.background.process')->add($aVals);
        }
        return true;
    }

    private function _processImage(&$aVal) {
        $aVal['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
    }
}

