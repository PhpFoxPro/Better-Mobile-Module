<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Huy Nguyen
 * Date: 8/22/13
 * Time: 6:27 PM
 * To change this template use File | Settings | File Templates.
 */
if (Phpfox::isMobile() && $sTemplate == 'feed.block.entry') {
   $sTemplate = 'bettermobile.block.feed.entry';
}
