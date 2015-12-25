<?php
/**
 * User: quanmt
 * Date: 12/21/11
 * Time: 2:03 AM
 */

class Brodev_Service_Theme extends Phpfox_Service
{

    function __construct()
    {
        $this->_sTable = Phpfox::getT('theme');
    }

    /**
     * Get themes list
     * @return array
     */
    public function getList()
    {
        $aThemes = $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->order('created DESC')
            ->execute('getRows');

        $aList = array();
        foreach ($aThemes as $aTheme) {
            $aList[$aTheme['theme_id']] = $aTheme['name'];
        }

        return $aList;
    }

    /**
     * Export product files
     * @param $iThemeId
     */
    public function exportToFile($iThemeId)
    {
        $aTheme = Phpfox::getService('theme')->getForEdit($iThemeId);
        $aParams = array(
            'theme_id' => $iThemeId,
            'styles' => array(),
        );
        $aStyles = Phpfox::getService('theme.style')->get(array(
                'theme_id = ' .  $iThemeId,
            ));
        foreach ($aStyles as $aStyle) {
            $aParams['styles'][] = $aStyle['style_id'];
        }

        $aResult = Phpfox::getService('theme')->export($aParams);

        $sFolder = PHPFOX_DIR . 'file/cache/' . $aResult['folder'];

        $sSourceTheme = $sFolder . '/upload/theme/frontend/' . $aTheme['folder'] . '/';
        $sDestinationTheme = PHPFOX_DIR . 'theme/frontend/' . $aTheme['folder'] . '/';

        // overwrite product xml
        $sSource = $sSourceTheme . 'phpfox.xml';
        $sDestination = $sDestinationTheme . 'phpfox.xml';
        copy($sSource, $sDestination);

        // get modules list

        // overwrite modules xml
        foreach ($aStyles as $aStyle) {
            $sSource = $sSourceTheme . 'style/' . $aStyle['folder'] . '/phpfox.xml';
            $sDestination = $sDestinationTheme . 'style/' . $aStyle['folder'] . '/phpfox.xml';
            copy($sSource, $sDestination);
        }
    }

    public function getTestList()
    {
        $oUrl = Phpfox::getLib('url');
        $aSections = array(
            'Homepage / Shout box' => $oUrl->makeUrl(''),
            'Profile' => $oUrl->makeUrl(Phpfox::getUserBy('user_name')),
            'Event' => array(
                $oUrl->makeUrl('event'),
                $this->getRandomLink('event'),
            ),
            'Quiz' => array(
                $oUrl->makeUrl('quiz'),
                $this->getRandomLink('quiz'),
            ),
            'Pages' => array(
                $oUrl->makeUrl('pages'),
                $this->getRandomLink('pages'),
            ),
            'Music' => array(
                $oUrl->makeUrl('music'),
                $this->getRandomLink('music_song'),
            ),
            'Mail' => array(
                $oUrl->makeUrl('mail'),
                $this->getRandomLink('mail'),
            ),
            'Forum' => array(
                $oUrl->makeUrl('forum'),
                $this->getRandomLink('forum_thread'),
            ),
            'Event' => array(
                $oUrl->makeUrl('event'),
                $this->getRandomLink('event'),
            ),
            'Blog' => array(
                $oUrl->makeUrl('blog'),
                $this->getRandomLink('blog'),
            ),
        );

        foreach ($aSections as $iKey => $aSection) {
            if (!is_array($aSection)) {
                $aSections[$iKey] = array(
                    $aSection,
                );
            }
        }

        return $aSections;
    }

    protected function getRandomLink($sType)
    {
        $sField = $sType . '_id';
        $sTable = Phpfox::getT($sType);
        $sLink = $sType;

        switch ($sType) {
            case 'pages':
                $sField = 'page_id';
                break;
            case 'music_song':
                $sField = 'song_id';
                $sLink = 'music';
                break;
            case 'mail':
                $this->database()
                    ->where('owner_user_id = ' . Phpfox::getUserId() . ' OR ' . 'viewer_user_id = ' . Phpfox::getUserId());
                $sLink = 'mail.view';
                break;
            case 'forum_thread':
                $sField = 'thread_id';
                $sLink = 'forum.thread';
                break;
        }
        $id = $this->database()
            ->select($sField)
            ->from($sTable)
            ->order('RAND()')
            ->execute('getField');

        if (!$id) {
            return false;
        }

        $sUrl = Phpfox::getLib('url')->makeUrl($sLink, array(
                $id,
            ));

        return $sUrl;
    }

}
