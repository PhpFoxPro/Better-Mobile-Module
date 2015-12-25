<?php
foreach ($aRows as $iKey => $aRow) {
    if ($aRow['theme_folder'] == 'bettermobile') {
        unset($aRows[$iKey]);
    }
}