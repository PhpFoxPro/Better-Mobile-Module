<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Hz
 * Date: 11/8/12
 * Time: 11:35 PM
 * To change this template use File | Settings | File Templates.
 */
class Themesupporter_Service_Image_Image extends Phpfox_Service
{
  public function __construct() {
    $this->_sTable = Phpfox::getT('photo');
  }
  // get Images function
  public function getNewImages() {
      $sType = Phpfox::getParam('themesupporter.type_of_photo');
      $sCacheId = $this->cache()->set('brodev_themesupporter_photo_'. $sType);
      if (!$aRecords = $this->cache()->get($sCacheId, 300)) {
          $iNumberPhotos = Phpfox::getParam('themesupporter.number_images_to_display');
          $sWhere = "p.view_id = 0 AND p.group_id = 0 AND p.type_id IN (0,1) AND p.privacy = 0 AND p.is_profile_photo = 0";
          switch ($sType){
              case ('All') : $sOrder = 'time_stamp';
              break;
              case ("Most") : $sOrder = 'total_view';
              break;
              case ("Featured") : $sOrder = 'time_stamp';
              $sWhere .= " AND p.is_featured=1";
              break;
          }

          $aRecords = $this->database()->select('p.*')
              ->from($this->_sTable, 'p')
              ->order( $sOrder . ' DESC')
              ->limit($iNumberPhotos)
              ->where($sWhere)
              ->execute('getRows');
          if (empty($aRecords)) {
              return false;
          }
          $this->cache()->save($sCacheId, $aRecords);
      }
      return $aRecords;
  }
}
