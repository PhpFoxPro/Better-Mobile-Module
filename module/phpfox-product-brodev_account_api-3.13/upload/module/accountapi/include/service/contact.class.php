<?php

/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_Contact
 * @version 		$Id: contact.class.php 4709 2012-09-21 08:37:17Z Raymond_Benc $
 */
class Accountapi_Service_Contact extends Phpfox_Service {
	/**
	 * contructor
	 */
	public function __construct() {
		$this -> _sTable = Phpfox::getT('user');
	}

	/**
	 *  get list user
	 */
	public function getListContact($iUserId, $aContacts) {
		$_aContact = array();

		if (empty($aContacts)) {
			return $_aContact;
		}

		$iLimit = Phpfox::getParam('accountapi.contact_query_limit');

		$aResult = array();
		$aValid = array();
		$_aContacts = null;
		
		while (true) {
			$aValid = array_slice($aContacts, 0, $iLimit);
			$aContacts = array_splice($aContacts, $iLimit);
			if(empty($aValid)) {
				break;
			}
			$_aContacts = '\'' . implode('\',\'', $aValid) . '\'';
			$_aContact = $this -> database() 
							-> select('u.*')
							-> from($this -> _sTable, 'u') 
							-> where('u.email IN(' . $_aContacts . ')') 
							-> execute('getRows');
			if (!empty($_aContact)) {
				foreach ($_aContact as $iKey => $aValue) {
                    $_aContact[$iKey]['user_image_path'] = Phpfox::getLib('image.helper')->display(array(
                        'user' => $aValue,
                        'suffix' => '_50_square',
                        'return_url' => true,
                    ));
					$aResult[] = $_aContact[$iKey];
				}
			}
			
			$aValid = array();
			$_aContacts = null;
			
		}
		return ($aResult);
	}

	/**
	 * Get not registered user
	 */
	public function getNotUser($aContacts, $aListContact) {
		foreach ($aContacts as $iKey => $aValue) {
			foreach ($aListContact as $_iKey => $aListValue) {
				if ($aValue == $aListValue['email']) {
					unset($aContacts[$iKey]);
				}
			}
		}
		return array_values($aContacts);
	}

	/**
	 * get registered user but not is friend
	 */
	public function getUserNotFriend($iUserId, $aContacts) {
		//check is friend
		$aList = array();

		//check requested add friend?
		foreach ($aContacts as $iKey => $aContact) {
			if (!Phpfox::getService('friend') -> isFriend($aContact['user_id'], $iUserId)) {
				if (Phpfox::getService('friend.request')->isRequested($iUserId, $aContact['user_id']) ||
					Phpfox::getService('friend.request')->isRequested($aContact['user_id'], $iUserId)) {
					continue;
				}
				$aList[$iKey] = $aContact;
			}
		}
		
		return array_values($aList);
	}

	/**
	 *
	 */
	public function emailInviter($iUserId, $aMails) {

//		list($aMails, $aInvalid, $aCacheUsers) = Phpfox::getService('invite')->getValid($aMails, $iUserId);
		$bSent = true;

		if (!empty($aMails)) {
			foreach ($aMails as $sMail) {
				$sMail = trim($sMail);
				$iInvite = Phpfox::getService('invite.process') -> addInvite($sMail, $iUserId);
				$sLink = Phpfox::getLib('url') -> makeUrl('invite', array('id' => $iInvite));
				$bSent = Phpfox::getLib('mail') 
								-> to($sMail) 
								-> fromEmail(Phpfox::getParam('core.email_from_email')) 
								-> fromName(Phpfox::getUserBy('full_name')) 
								-> subject(array('invite.full_name_invites_you_to_site_title', array('full_name' => Phpfox::getUserBy('full_name'), 'site_title' => Phpfox::getParam('core.site_title'))))
								-> message(array('invite.full_name_invites_you_to_site_title_link', array('full_name' => Phpfox::getUserBy('full_name'), 'site_title' => Phpfox::getParam('core.site_title'), 'link' => $sLink)))
								-> send();
			}
		}

		return $bSent;
	}

}
