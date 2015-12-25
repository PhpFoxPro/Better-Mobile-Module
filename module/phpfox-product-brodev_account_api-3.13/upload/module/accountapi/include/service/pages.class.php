<?php

class Accountapi_Service_Pages extends Phpfox_Service {

	
    public function getPage($iPage) {
    	$aPage = Phpfox::getService('pages')->getForView($iPage);
    	
    	$aUser = $this->database()->select(Phpfox::getUserField('u'))->from(Phpfox::getT('user'), 'u')->where('u.user_id = '. $aPage['user_id'])->execute('getRow');
    	$aPage = array_merge($aPage, $aUser);
    	$aPage['profile_page_id'] = $this->database()->select('u.user_id')->from(Phpfox::getT('user'), 'u')->where('u.profile_page_id = '. $aPage['page_id'])->execute('getField');

		$aPage = $this->processPage($aPage);
		
		return $aPage;
    }
    
    public function processPage($aPage) {
    	$aSizes = array(50, 120, 200);
    	$aPage['photo_sizes'] = array();

        $aPage['category_name'] = Phpfox::getLib('locale')->convert($aPage['category_name']);

        //check if phpfox > 3.6.0
        if (version_compare(Phpfox::getVersion(), '3.6.0', '>=')) {
            $sUrlImage = 'core.url_user';
        } else {
            $sUrlImage = 'pages.url_image';
        }

    	foreach ($aSizes as $iSize) {
    		$aPage['photo_sizes'][$iSize] = Phpfox::getLib('image.helper')->display(array(
    			'file' => $aPage['image_path'],
    			'server_id' => $aPage['image_server_id'],
    			'path' => $sUrlImage,
		        'suffix' => '_'. $iSize,
		        'return_url' => true
    		));
    	}
    	 
    	return $aPage;
    }

    /**
     * Get my pages
     * @return mixed
     */
    function getPages() {

        $aPages = Phpfox::getService('pages')->getMyPages();

        $aItems = array();

        foreach($aPages as $iKey => $aPage)
        {
            if ($aPage['app_id']) {
                continue;
            }

            $sImage = Phpfox::getLib('image.helper')->display(array(
                'file' => $aPage['image_path'],
                'server_id' => $aPage['image_server_id'],
                'path' => 'pages.url_image',
                'suffix' => '_50_square',
                'return_url' => true,
            ));

            $aItems[] = array(
                'page_id' => $aPage['page_id'],
                'title' => $aPage['title'],
                'icon_image' => $sImage,
            );
        }

        return $aItems;

    }

    /**
     * Get all page
     * @return array
     */
     public function getAllPage($iPage) {
		Phpfox::getLib('request')->set('page', $iPage);
		
        $this->search()->set(array(
                 'type' => 'pages',
                 'field' => 'pages.page_id',
                 'search_tool' => array(
                     'table_alias' => 'pages',
                     'search' => array(
                         'action' => '',
                         'default_value' => Phpfox::getPhrase('pages.search_pages'),
                         'name' => 'search',
                         'field' => 'pages.title'
                     ),
                     'sort' => array(
                         'latest' => array('pages.time_stamp', Phpfox::getPhrase('pages.latest')),
                         'most-liked' => array('pages.total_like', Phpfox::getPhrase('pages.most_liked'))
                     ),
                     'show' => array(6)
                 )
             )
         );

         $aBrowseParams = array(
             'module_id' => 'pages',
             'alias' => 'pages',
             'field' => 'page_id',
             'table' => Phpfox::getT('pages'),
         );

         $this->search()->setCondition('AND pages.app_id = 0 AND pages.view_id = 0 AND pages.privacy IN(%PRIVACY%)');

         $this->search()->browse()->params($aBrowseParams)->execute();

         $aAllPages = $this->search()->browse()->getRows();
         $iCnt = $this->search()->browse()->getCount();	
         
         Phpfox::getLib('pager')->set(array('page' => $this->search()->getPage(), 'size' => $this->search()->getDisplay(), 'count' => $this->search()->browse()->getCount()));

         $aPages = array();

         foreach($aAllPages as $iKey => $aPage) {
//             $aPage['picture'] = Phpfox::getParam('core.url_user') . $aPage['profile_user_image'];
             if (version_compare(Phpfox::getVersion(), '3.6.0', '>=')) {
                 $sUrlImage = 'core.url_user';
             } else {
                 $sUrlImage = 'pages.url_image';
             }

             $aPage['picture'] = Phpfox::getLib('image.helper')->display(array(
                 'file' => $aPage['profile_user_image'],
                 'server_id' => $aPage['image_server_id'],
                 'path' => $sUrlImage,
                 'suffix' => '_120_square',
                 'return_url' => true,
             ));
             $aPages[$iKey] = array(
                 'page_id' => $aPage['page_id'],
                 'title' => $aPage['title'],
                 'category_name' => Phpfox::getLib('locale')->convert($aPage['category_name']),
                 'image' => $aPage['picture']
             );
         }

         return array($iCnt, $aPages);
     }
}
