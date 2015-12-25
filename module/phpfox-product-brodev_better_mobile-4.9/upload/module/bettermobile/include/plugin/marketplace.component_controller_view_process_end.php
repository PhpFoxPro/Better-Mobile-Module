<?php
/**
 * Created by Phpfox Pro.
 * User: Huy Nguyen
 * Date: 3/11/14
 * Time: 2:59 PM
 */
Phpfox::getLib('template')->assign(array(
    'aImages' => Phpfox::getService('marketplace')->getImages($aListing['listing_id'])
));