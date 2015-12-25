<?php
$sControllerName = Phpfox::getLib('module')->getFullControllerName();
if ($sControllerName == 'admincp.product/index') {
    Phpfox::getService('brodev.product')->updateProduct();
}
