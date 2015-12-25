<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 12/27/12
 * Time: 1:35 PM
 * To change this template use File | Settings | File Templates.
 */
if(!Phpfox::getService('bettermobile.template')->isPhotoShare()){

    $sCacheMobile = $this->cache()->set('feed_share_link_mobile');

    if(Phpfox::isMobile()){
        if (!($this->cache()->get($sCacheMobile)))
        {
            $sCacheId = $this->cache()->set('feed_share_link');

            if ($this->cache()->get($sCacheId))
            {
                $this->cache()->remove($sCacheId);
            }
            $aLinks = $this->database()->select('fs.*')
                ->from(Phpfox::getT('feed_share'), 'fs')
                ->join(Phpfox::getT('module'), 'm', 'm.module_id = fs.module_id AND m.is_active = 1')
                ->order('fs.ordering ASC')
                ->execute('getSlaveRows');

            $aLinkPhoto = array(
                'product_id' => 'phpfox',
                'module_id'=>  'photo',
                'title' => "{phrase var='photo.photo'}",
                'description' => "{phrase var='photo.say_something_about_this_photo'}",
                'block_name' => 'share',
                'no_input' => 0,
                'is_frame' => 1,
                'ajax_request' => null,
                'no_profile' => 0,
                'icon' => 'photo.png',
                'ordering' => 1,
                'module_block' => 'photo.share'
            );


            foreach ($aLinks as $iKey => $aLink)
            {
                $aLinks[$iKey]['module_block'] = $aLink['module_id'] . '.' . $aLink['block_name'];
                if($aLinks[$iKey]['module_id'] == 'advancedphoto'){
                    $aLinks[$iKey] = $aLinkPhoto;
                }
            }
            $sCacheId = $this->cache()->set('feed_share_link');
            $this->cache()->save($sCacheMobile, $aLinks);
            $this->cache()->save($sCacheId, $aLinks);
        }
    }elseif($this->cache()->get($sCacheMobile)){
        $sCacheId = $this->cache()->set('feed_share_link');
        $this->cache()->remove($sCacheId);
        $this->cache()->remove($sCacheMobile);
    }

}
