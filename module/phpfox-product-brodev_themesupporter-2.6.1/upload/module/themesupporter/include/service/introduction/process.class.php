<?php
class Themesupporter_Service_Introduction_Process extends Phpfox_Service {
    public function __construct() {
        $this->_sTable = Phpfox::getT('brodev_block_introduction');
        Phpfox::getLib('setting')->setParam(array('themesupporter.image_folder' => PHPFOX_DIR_FILE . 'pic' . PHPFOX_DS . 'block' . PHPFOX_DS. 'introduction' . PHPFOX_DS));
        Phpfox::getLib('setting')->setParam(array('themesupporter.image_url' => Phpfox::getParam('core.url_pic') . 'block/introduction/'));
    }
    private $_aPhotoSizes = array(50, 100, 150, 200);
    /**
     * get record without paging in database
     * @param string $sFields
     * @param string $Condition
     * @param int $iLimit
     * @return mixed
     */
    public function getListNoPage($sFields = '', $Condition = '', $iLimit = 0) {

        $oDatabase = $this->database();
        $oDatabase->from ($this->_sTable);

        if ($sFields != '') {
            $oDatabase -> select ($sFields);
        }
        else {
            $oDatabase -> select('*');
        }
        $oDatabase->where($this->getWhere($Condition));
        $oDatabase->order('ordering');
        if ($iLimit != 0) {
            $oDatabase->limit($iLimit);
        }
        $aRecords = $oDatabase->execute('getRows');
        return $aRecords;
    }

    /**
     * get all size of photo
     * @return mixed
     */
    public function aPhotoSizes() {
        return $this->_aPhotoSizes;
    }
    /**
     * insert or update to database
     * @param $aVal
     * @return int
     */
    public function saveDatabase ($aVal) {
        $iId = (int) $aVal['id'];
        if (($iId == 0) || (!$this->isExist('id = '. $iId))) {
            $iId = $this->database()->insert($this->_sTable, $aVal);
        }
        else {
            $this->database()->update($this->_sTable, $aVal, 'id = '. $iId);
        }
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
            $oFile = Phpfox::getLib('file');

            $sFileName= Phpfox::getLib('file')->upload('image', Phpfox::getParam('themesupporter.image_folder'), $iId);

            $iFileSizes = filesize(Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, ''));

            foreach ($this->_aPhotoSizes as $iSize)
            {
                $oImage->createThumbnail(Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, ''), Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
                $oImage->createThumbnail(Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, ''), Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);

                $iFileSizes += filesize(Phpfox::getParam('themesupporter.image_folder') . sprintf($sFileName, '_' . $iSize));
            }

            $this->database()
                ->update($this->_sTable, array('image' => $sFileName), 'id = ' . $iId);
        }
        return $iId;
    }

    /**
     * get string Where for sql query
     * @param $Condition
     * @return string
     */
    private  function getWhere ($Condition) {
        $sWhere ="1 = 1 ";
        if (!is_array($Condition)) {
            if (trim($Condition) != "") {
                $sWhere .= "AND ". $Condition;
            }
        }
        else {
            foreach ($Condition as $iKey => $Val) {
                $sOperation = strstr($iKey, " ");
                if (trim($sOperation) != "") {
                    $sWhere .= $iKey ." " . $Val;
                }
                else {
                    $sWhere .= $iKey ." = ". $Val;
                }
            }
        }
        return $sWhere;
    }

    /**
     * check record is exist in database
     * @param $Condition
     * @return bool
     */
    public function isExist ($Condition) {
        $iCount = $this->database()
            ->select('count(*)')
            ->from($this->_sTable)
            ->where($this->getWhere($Condition))
            ->execute('getField');
        if ($iCount > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * delete from database with condition
     * @param $Condition
     * @return mixed
     */
    public function delete ($Condition) {
        return $this->database()->delete($this->_sTable, $this->getWhere($Condition));
    }

    /**
     *  update order
     * @param $aOrder
     * @return bool
     */
    public function setOrder ($aOrder) {

        foreach ($aOrder as $iId => $iOrder) {

            $this->database()->update($this->_sTable, array('ordering' => $iOrder), 'id = ' . (int) $iId);
        }
        return true;
    }

    /**
     * get 1 introduction
     * @param $Condition
     * @return mixed
     */
    public function getForEdit($Condition) {
        $aRecords = $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->where($this->getWhere($Condition))
            ->execute('getRow');
        return $aRecords;
    }
}