<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_Feed
 * @version 		$Id: display.html.php 4176 2012-05-16 10:49:38Z Raymond_Benc $
 * This fileis called from the form.html.php template in the feed module
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if Phpfox::getService('bettermobile')->isCheckIn()}
<li>
    <a href="#" type="button" id="btn_display_check_in" style="background-image:url('{img theme='layout/feed_map.png' return_url=true}'); background-repeat:no-repeat; background-position:center left;" class="activity_feed_share_this_one_link parent feed_share_map" onclick="return false;" rel="global_attachment_status">
        <div class="status_button">Check-in</div>
        <div><span class="activity_feed_link_form_ajax">user.updateStatus</span></div>
    </a>
    <script type="text/javascript">
        $Behavior.prepareInit = function()
        {l}
        $Core.Feed.sIPInfoDbKey = '{param var="core.ip_infodb_api_key"}';
        $Core.Feed.sGoogleKey = '{param var="core.google_api_key"}';

        {if isset($aVisitorLocation)}
            $Core.Feed.setVisitorLocation({$aVisitorLocation.latitude}, {$aVisitorLocation.longitude} );
        {else}

        {/if}

            $Core.Feed.googleReady();
            $Core.Feed.init();
            {r}
    </script>
    <script type="text/javascript" src="{param var='core.url_module'}bettermobile/static/jscript/places.js"></script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key={param var="core.google_api_key"}&sensor=true&libraries=places"></script>

    </li>
{/if}