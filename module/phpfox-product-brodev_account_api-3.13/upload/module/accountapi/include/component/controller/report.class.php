<?php

class Accountapi_Component_Controller_Report extends Phpfox_Component {

    public function process()
    {
        Phpfox::isUser(true);

        $oReport = Phpfox::getService('report');

        $sType = $this->request()->get('type');
        $iItemId = $this->request()->get('item');

        $bCanReport = $oReport->canReport($sType, $iItemId);

        $this->template()->assign(array(
                'aOptions' => ($bCanReport ? $oReport->getOptions($sType) : null),
                'sType' => $sType,
                'iItemId' => $iItemId,
                'bCanReport' => $bCanReport,
                'sTermsUrl' => $this->url()->makeUrl('terms')
            )
        );

        //action submit
        if (($aVals = $this->request()->getArray('val')))
        {
            if (Phpfox::getService('report.data.process')->add($aVals['reason'], $aVals['type'], $aVals['item'], $aVals['feedback']))
            {
                $this->url()->send('accountapi.report', null, Phpfox::getPhrase('report.you_have_already_reported_this_item'));
            }
        }
    }
}
