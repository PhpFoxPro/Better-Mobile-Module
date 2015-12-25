<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ADMIN
 * Date: 12/3/12
 * Time: 10:11 AM
 * To change this template use File | Settings | File Templates.
 */
$this->template()->assign(array(
        'sHeader' => '',
        'aFooter' => '',
        'sJanrainUrl' => (Phpfox::isModule('janrain') ? Phpfox::getService('janrain')->getUrl() : '')
    )
);