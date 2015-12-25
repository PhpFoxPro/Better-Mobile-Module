<?php

if (Phpfox::getLib('session')->get('social_app')) {
    Phpfox::getLib('template')->setHeader('
        <style type="text/css">
    #mobile_header {
        display: none !important;
    }
    #main_content_holder a.mobile_main_sub_menu, #main_content_holder a.mobile_search_button {
        display: none !important;
    }
	#holder {
		padding-top: 0px !important;
	}
</style>
    ');
}