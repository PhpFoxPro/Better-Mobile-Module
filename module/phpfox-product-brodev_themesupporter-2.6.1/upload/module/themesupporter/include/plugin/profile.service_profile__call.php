<?php
if ($sMethod == 'timeline') {
    Phpfox::getLib('template')->assign(array(
        'bOldVersion' => true,
    ));
    return false;
}