<?php
/**
 * User: quanmt
 * Date: 12/21/11
 * Time: 2:20 AM
 */

class Brodev_Service_Module extends Phpfox_Service
{

    /**
     * Get modules list by product_id
     * @param $sProductId
     * @return array
     */
    public function getList($sProductId)
    {
        $aModules = $this->database()
            ->select('m.module_id')
            ->from(Phpfox::getT('module'), 'm')
            ->where('m.product_id = \'' . $sProductId . '\'')
            ->execute('getRows');

        $aList = array();
        foreach ($aModules as $aModule) {
            $aList[] = $aModule['module_id'];
        }

        return $aList;
    }

}
