<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond_Benc
 * @package 		Phpfox_Service
 * @version 		$Id: link.class.php 6496 2013-08-23 11:34:09Z Fern $
 */
class Accountapi_Service_Link extends Phpfox_Service
{
    /**
     * Check link
     * @param $sUrl
     * @return array|bool
     */
    public function checkLink($sUrl)
    {
        //convert to lower string
        $sUrl = lcfirst($sUrl);

        $sRegexUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        preg_match_all($sRegexUrl, $sUrl, $aOut);

        if (!empty($aOut[0])) {
            $aLink = $this->getLink($aOut[0][0]);
            if (is_array($aLink)) {
                return $aLink;
            }
        }

        return false;
    }

    /**
     * Add link
     * @param $aVals
     * @param $sUrl
     * @param $aLink
     * @return bool
     */
    public function add($aVals, $sUrl, $aLink)
    {
        $aCallback = null;
        if (isset($aVals['callback_module']) && Phpfox::hasCallback($aVals['callback_module'], 'addLink'))
        {
            $aCallback = Phpfox::callback($aVals['callback_module'] . '.addLink', $aVals);
        }

        $aVals['link'] = $aLink;
        $aVals['link']['url'] = $sUrl;
        $aVals['link']['image'] = $aLink['default_image'];

        if (($iId = Phpfox::getService('link.process')->add($aVals, false, $aCallback))) {
            return $iId;
        }

        return false;
    }

    /**
     * get link
     * @param $sUrl
     * @return array|bool
     */
    public function getLink($sUrl)
    {
        if (substr($sUrl, 0, 7) != 'http://' && substr($sUrl, 0, 8) != 'https://')
        {
            $sUrl = 'http://' . $sUrl;
        }

        $aParts = parse_url($sUrl);

        if (!isset($aParts['host']))
        {
            return false;
        }

        $aReturn = array();
        $oVideo = json_decode(Phpfox::getLib('request')->send('http://api.embed.ly/1/oembed?format=json&maxwidth=400&url=' . urlencode($sUrl), array(), 'GET', $_SERVER['HTTP_USER_AGENT']));

        if (isset($oVideo->provider_url) && (isset($oVideo->photo)))
        {
            $aReturn = array(
                'link' => $sUrl,
                'title' => (isset($oVideo->title) ? strip_tags($oVideo->title) : ''),
                'description' => (isset($oVideo->description) ? strip_tags($oVideo->description) : ''),
                'default_image' => ($oVideo->type == 'photo' ? $oVideo->url : (isset($oVideo->thumbnail_url) ? $oVideo->thumbnail_url : '')),
                'embed_code' => ($oVideo->type == 'video' ? $oVideo->html : '')
            );

            return $aReturn;
        }

        $aParseBuild = array();
        $sContent = Phpfox::getLib('request')->send($sUrl, array(), 'GET', $_SERVER['HTTP_USER_AGENT']);
        preg_match_all('/<(meta|link)(.*?)>/i', $sContent, $aRegMatches);
        if (isset($aRegMatches[2]))
        {
            foreach ($aRegMatches as $iKey => $aMatch)
            {
                if ($iKey !== 2)
                {
                    continue;
                }

                foreach ($aMatch as $sLine)
                {
                    $sLine = rtrim($sLine, '/');
                    $sLine = trim($sLine);

                    preg_match('/(property|name|rel)=("|\')(.*?)("|\')/ise', $sLine, $aType);
                    if (count($aType) && isset($aType[3]))
                    {
                        $sType = $aType[3];
                        preg_match('/(content|type)=("|\')(.*?)("|\')/i', $sLine, $aValue);
                        if (count($aValue) && isset($aValue[3]))
                        {
                            if ($sType == 'alternate')
                            {
                                $sType = $aValue[3];
                                preg_match('/href=("|\')(.*?)("|\')/i', $sLine, $aHref);
                                if (isset($aHref[2]))
                                {
                                    $aValue[3] = $aHref[2];
                                }
                            }
                            $aParseBuild[$sType] = $aValue[3];
                        }
                    }
                }
            }

            if (isset($aParseBuild['og:title']))
            {
                $aReturn['link'] = $sUrl;
                $aReturn['title'] = $aParseBuild['og:title'];
                $aReturn['description'] = (isset($aParseBuild['og:description']) ? $aParseBuild['og:description'] : '');
                $aReturn['default_image'] = (isset($aParseBuild['og:image']) ? $aParseBuild['og:image'] : '');
                if (isset($aParseBuild['application/json+oembed']))
                {
                    $oJson = json_decode(Phpfox::getLib('request')->send($aParseBuild['application/json+oembed'], array(), 'GET', $_SERVER['HTTP_USER_AGENT']));					if (isset($oJson->html))
                {
                    $aReturn['embed_code'] = $oJson->html;
                }
                }

                return $aReturn;
            }
        }


        $sContent = Phpfox::getLib('request')->send($sUrl, array(), 'GET', $_SERVER['HTTP_USER_AGENT'], null, true);

        if( function_exists('mb_convert_encoding') )
        {
            $sContent = mb_convert_encoding($sContent, 'HTML-ENTITIES', "UTF-8");
        }

        $aReturn['link'] = $sUrl;

        Phpfox_Error::skip(true);
        $oDoc = new DOMDocument();
        $oDoc->loadHTML($sContent);
        Phpfox_Error::skip(false);

        if (($oTitle = $oDoc->getElementsByTagName('title')->item(0)) && !empty($oTitle->nodeValue))
        {
            $aReturn['title'] = strip_tags($oTitle->nodeValue);
        }

        if (empty($aReturn['title']))
        {
            if (preg_match('/^(.*?)\.(jpg|png|jpeg|gif)$/i', $sUrl, $aImageMatches))
            {
                return array(
                    'link' => $sUrl,
                    'title' => '',
                    'description' => '',
                    'default_image' => $sUrl,
                    'embed_code' => ''
                );
            }

            return false;
        }

        $oXpath = new DOMXPath($oDoc);
        $oMeta = $oXpath->query("//meta[@name='description']")->item(0);
        if (method_exists($oMeta, 'getAttribute'))
        {
            $sMeta = $oMeta->getAttribute('content');
            if (!empty($sMeta))
            {
                $aReturn['description'] = strip_tags($sMeta);
            }
        }

        $aImages = array();
        $oMeta = $oXpath->query("//meta[@property='og:image']")->item(0);
        if (method_exists($oMeta, 'getAttribute'))
        {
            $aReturn['default_image'] = strip_tags($oMeta->getAttribute('content'));
            $aImages[] = strip_tags($oMeta->getAttribute('content'));
        }

        $oMeta = $oXpath->query("//link[@rel='image_src']")->item(0);
        if (method_exists($oMeta, 'getAttribute'))
        {
            if (empty($aReturn['default_image']))
            {
                $aReturn['default_image'] = strip_tags($oMeta->getAttribute('href'));
            }
            $aImages[] = strip_tags($oMeta->getAttribute('href'));
        }

        if (!isset($aReturn['default_image']))
        {
            $oMeta = $oXpath->query("//meta[@itemprop='image']")->item(0);
            if (method_exists($oMeta, 'getAttribute'))
            {
                $aReturn['default_image'] = strip_tags($oMeta->getAttribute('content'));
                if (strpos($aReturn['default_image'], $sUrl) === false)
                {
                    $aReturn['default_image'] = $sUrl . '/' . $aReturn['default_image'];
                }
            }
        }


        if (!isset($aReturn['default_image']))
        {
            $oImages = $oDoc->getElementsByTagName('img');
            $iIteration = 0;
            foreach ($oImages as $oImage)
            {
                $sImageSrc = $oImage->getAttribute('src');

                if (substr($sImageSrc, 0, 7) != 'http://' && substr($sImageSrc, 0, 1) != '/')
                {
                    continue;
                }

                if (substr($sImageSrc, 0, 2) == '//')
                {
                    continue;
                }

                $iIteration++;

                if (substr($sImageSrc, 0, 1) == '/')
                {
                    $sImageSrc = 'http://' . $aParts['host'] . $sImageSrc;
                }

                if ($iIteration === 1 && empty($aReturn['default_image']))
                {
                    $aReturn['default_image'] = strip_tags($sImageSrc);
                }

                if ($iIteration > 10)
                {
                    break;
                }

                $aImages[] = strip_tags($sImageSrc);
            }
        }

        if (count($aImages))
        {
            $aReturn['images'] = $aImages;
        }

        $oLink = $oXpath->query("//link[@type='text/xml+oembed']")->item(0);
        if (method_exists($oLink, 'getAttribute'))
        {
            $aXml = Phpfox::getLib('xml.parser')->parse(Phpfox::getLib('request')->send($oLink->getAttribute('href'), array(), 'GET', $_SERVER['HTTP_USER_AGENT']));
            if (isset($aXml['html']))
            {
                $aReturn['embed_code'] = $aXml['html'];
            }
        }

        return $aReturn;
    }
}