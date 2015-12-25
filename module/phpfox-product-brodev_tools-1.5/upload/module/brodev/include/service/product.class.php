<?php
/**
 * User: quanmt
 * Date: 12/21/11
 * Time: 2:03 AM
 */

class Brodev_Service_Product extends Phpfox_Service
{

    /**
     * Get products list
     * @return array
     */
    public function getList()
    {
        $aProducts = $this->database()
            ->select('p.product_id, p.title')
            ->from(Phpfox::getT('product'), 'p')
            ->order('p.product_id')
            ->execute('getRows');

        $aList = array();
        foreach ($aProducts as $aProduct) {
            $aList[$aProduct['product_id']] = $aProduct['title'];
        }

        return $aList;
    }

    /**
     * Export product files
     * @param $sProductId
     */
    public function exportToFile($sProductId)
    {
        $aResult = Phpfox::getService('admincp.product')->export($sProductId);
        $sName = $aResult['name'];
        $sFolder = PHPFOX_DIR . 'file/cache/' . $aResult['folder'];

        // overwrite product xml
        $sSource = $sFolder . '/upload/include/xml/' . $sProductId . '.xml';
        $sDestination = PHPFOX_DIR . 'include/xml/' . $sProductId . '.xml';
        copy($sSource, $sDestination);

        // get modules list
        $aModules = Phpfox::getService('brodev.module')->getList($sProductId);

        // overwrite modules xml
        foreach ($aModules as $sModuleId) {
            $sSource = $sFolder . '/upload/module/' . $sModuleId . '/phpfox.xml';
            $sDestination = PHPFOX_DIR . 'module/' . $sModuleId . '/phpfox.xml';
            copy($sSource, $sDestination);
        }
    }

    /**
     * auto update product info once for 1 day
     * @return boolean
     */
    public function updateProduct() {
        $sCacheId = $this->cache()->set('brodev_tool_product');
        if (!$aProducts = $this->cache()->get($sCacheId, 1440)) {
            $aProducts = Phpfox::getService('admincp.product')->get(false);
            foreach ($aProducts as $iKey => $aProduct) {
                if (substr($aProduct['product_id'], 0, 7) != "brodev_") {
                    continue;
                }
                //check exist file xml
                if (file_exists(PHPFOX_DIR_XML . $aProduct['product_id'] . '.xml')) {
                    $aParams = Phpfox::getLib('xml.parser')->parse(file_get_contents(PHPFOX_DIR_XML . $aProduct['product_id'] . '.xml'));
                    $aData = array(
                        'title' => $aParams['data']['title'],
                        'description' => $aParams['data']['description'],
                        'url' => $aParams['data']['url'],
                        'url_version_check' => $aParams['data']['url_version_check'],
                    );
                    $this->database()->update(Phpfox::getT('product'), $aData, 'product_id = "' . $aParams['data']['product_id'] . '"');
                }
            }
            $this->cache()->save($sCacheId, $aProducts);
        }
        return true;
    }

    /**
     * check Field is exist from database
     * @param $sTable
     * @param $sField
     * @return bool
     */
    public function isFieldExist($sTable, $sField) {

        $aRows = Phpfox::getLib('phpfox.database')->select('COLUMN_NAME')
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where('table_name = \'' . Phpfox::getT($sTable). '\' AND table_schema = \'' . Phpfox::getParam(array('db', 'name')) . '\' AND column_name LIKE \''.  $sField .'\'')
            ->execute('getRows');
        if(count($aRows) > 0){
            $bIsExist = true;
        }else{
            $bIsExist = false;
        }
        return $bIsExist;
    }
}
