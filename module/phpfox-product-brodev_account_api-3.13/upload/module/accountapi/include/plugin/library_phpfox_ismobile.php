<?php
// don't redirect to mobile if requesting api
if (strpos($_SERVER['SCRIPT_FILENAME'], 'api.php') !== false) {
    $bReturnFromPlugin = false;
}