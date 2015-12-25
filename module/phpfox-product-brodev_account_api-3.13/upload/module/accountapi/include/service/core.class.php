<?php

class Accountapi_Service_Core extends Phpfox_Service {
	
	var $_aPhraseNeeded = array(
		'profile.lives_in', 'feed.status', 'user.photo','friend.message', 'user.add_friend', 'friend.friend', 'feed.others', 'pages.unlike', 'pages.like', 'feed.comment', 'photo.recent_photos', 'profile.basic_info',
		'search.results_for', 'friend.search','search.all_results', 'search.members', 'pages.view_more', 'pages.likes', 'feed.comments', 'user.notifications', 'mail.mesages_sent', 'friend.all_friends','profile.send_a_message', 
		'user.view_profile', 'friend.accept', 'request.confirm_requests', 'comment.view_previous_comments', 'mail.send', 'user.cancel_uppercase', 'mail.to', 'mail.search_friends_by_their_name', 'feed.news_feed', 'core.cancel', 
		'privacy.everyone', 'privacy.friends', 'privacy.friends_of_friends', 'privacy.only_me', 'profile.wall', 'user.info', 'user.logout', 'user.are_you_sure', 'user.yes', 'user.no', 'notification.notifications', 'core.uploading',
		'mail.new_message', 'mail.subject', 'profile.born_on_birthday', 'friend.sent_from', 'profile.confirm_friend_request', 'profile.add_to_friends', 'photo.no_photos_found', 'feed.share', 'pages.no_pages_found', 'feed.report',
		'profile.pending_friend_confirmation', 'profile.pending_friend_request', 'friend.deny', 'photo.photo_successfully_uploaded', 'pages.members_total', 'pages.info', 'core.search_dot', 'pages.pages', 'mail.messages',
        'like.people_who_like_this', 'feed.there_are_no_new_feeds_to_view_at_this_time', 'mail.messages_title', 'friend.friends', 'feed.unlike', 'feed.like', 'search.no_search_results_found', 'feed.write_a_comment', 'core.loading', 'accountapi.choose_from_gallery',
        'invite.find_friends', 'photo.skip', 'photo.title', 'photo.description', 'accountapi.take_or_choose_picture', 'feed.shared_a_photo', 'friend.confirm', 'feed.what_s_on_your_mind', 'profile.gender', 'profile.member_since', 'accountapi.take_new_photo',
        'accountapi.choose_who_you_d_like_to_add_as_friends', 'accountapi.invite_all', 'feed.shared', 'photo.no_albums_found_here', 'like.likes', 'profile.age', 'profile.location', 'profile.last_login', 'profile.membership', 'profile.rss_subscribers',
        'accountapi.contact_find_friends_info', 'link.next', 'accountapi.contacts_found', 'accountapi.add_all', 'input.add', 'pages.invite', 'emoticon.import', 'accountapi.syncing', 'accountapi.add_profile_picture_and_other_info_to_contacts_contacts_will_be_synced_from_now_on',
        'music.music', 'music.all_songs', 'music.my_songs', 'music.friends_songs', 'music.all_albums', 'music.my_albums', 'music.music_genres', 'blog.blog', 'blog.categories', 'blog.add_a_new_blog', 'blog.title', 'blog.post', 'blog.privacy',
        'video.video', 'blog.all_blogs', 'blog.my_blogs', 'blog.friends_blogs', 'comment.add_some_text_to_your_comment',
        'accountapi.contact_find_friends_info', 'link.next', 'accountapi.contacts_found', 'accountapi.add_all', 'input.add', 'pages.invite', 'emoticon.import', 'accountapi.syncing', 'accountapi.add_profile_picture_and_other_info_to_contacts_contacts_will_be_synced_from_now_on', 'profile.albums',
        'accountapi.select_10_files', 'pages.account', 'accountapi.favourite', 'admincp.modules', 'forum.forums', 'forum.my_threads', 'forum.new_posts', 'forum.threads',
        'forum.posts', 'forum.replies', 'forum.views', 'forum.sub_forum', 'forum.subscribe', 'forum.post_new_thread', 'forum.post_a_reply', 'core.quote', 'share.check_out', 'forum.viewing_single_post',
        'music.music', 'music.all_albums', 'music.my_albums', 'music.music_genres', 'blog.blog', 'blog.categories', 'blog.add_a_new_blog', 'blog.title', 'blog.post', 'blog.privacy',
        'video.video', 'video.categories', 'feed.shared_a_few_photos', 'photo.in_this_album', 'photo.provide_a_name_for_your_album', 'music.all_songs', 'music.my_songs', 'music.friends_songs',
        'marketplace.all_listings', 'marketplace.my_listings', 'marketplace.friends_listings', 'marketplace.categories', 'marketplace.free', 'marketplace.marketplace', 'marketplace.create_a_listing',
        'marketplace.what_are_you_selling', 'marketplace.description', 'marketplace.category', 'marketplace.posted_on', 'marketplace.posted_by', 'marketplace.location',
        'accountapi.invite_successfully', 'accountapi.invite', 'accountapi.conversation', 'accountapi.block', 'accountapi.delete', 'accountapi.no_new_messages', 'accountapi.failed_to_block_contact',
        'profile.friends', 'accountapi.contact_is_blocked', 'accountapi.unblock', 'accountapi.seen', 'accountapi.failed_to_unblock_contact', 'accountapi.failed_to_delete_conversation',
        'friend.add_friend', 'accountapi.sync', 'accountapi.sync_contact_successfully', 'user.account_settings', 'user.preferred_currency', 'user.primary_language', 'user.time_zone', 'user.email_address', 'user.user_name', 'user.full_name',
        'user.account_settings_updated', 'user.change_password', 'user.old_password', 'user.new_password', 'user.confirm_password', 'user.password_successfully_updated',
        'user.missing_old_password', 'user.missing_new_password', 'user.missing_new_password', 'user.your_confirmed_password_does_not_match_your_new_password', 'admincp.import',
        'mail.unable_to_send_a_private_message_to_this_user_at_the_moment', 'core.close', 'event.view_guest_list',
        'user.login_button', 'user.password', 'user.email', 'profile.info', 'profile.photos', 'video.all_videos', 'video.my_videos', 'video.friends_videos', 'share.message', 'photo.name', 'photo.album_s_privacy', 'photo.comment_privacy', 'photo.recent_photos',
        'photo.no_photos_found', 'user.sign_up', 'event.update_your_rsvp', 'event.submit_your_rsvp', 'event.no_events_found',
        'event.all_events', 'event.my_events', 'event.friends_events', 'event.event', 'event.category', 'event.time', 'event.categories', 'event.location', 'event.created_by', 'event.your_rsvp',
        'event.attending', 'event.maybe_attending', 'event.not_attending', 'event.awaiting_reply', 'event.create_new_event', 'event.what_are_you_planning', 'event.description', 'event.event_privacy', 'event.share_privacy',
        'event.select', 'event.select_a_sub_category', 'event.location_venue', 'event.start_time', 'event.end_time', 'event.provide_a_name_for_this_event', 'event.provide_a_location_for_this_event',
        'event.address', 'event.city', 'event.zip_postal_code', 'event.country', 'event.add_address_city_zip_country', 'event.add_end_time','share.on_your_wall', 'share.on_a_friend_s_wall', 'admincp.view_more',
        'musicsite.music_site', 'blog.fill_title_for_blog', 'blog.add_content_to_blog', 'feed.at_location',
        'share.post', 'user.done', 'friend.no_new_requests', 'friend.friend_requests', 'mail.no_new_messages', 'mail.messages_title', 'notification.no_new_notifications', 'feed.delete_this_feed',
        'feed.delete', 'feed.cancel', 'user.forgot_password', 'friend.view_profile', 'user.or_login_with', 'profile.profile', 'core.back', 'admincp.add'
	);
	
	var $_aSettingNeeded = array(
		'mail.threaded_mail_conversation'
	);
	
	var $_aSettingUserGroup = array(
		'accountapi.accountapi_show_ad'
	);
	
	//facebook setting
	var $_aSettingFacebook = array(
		'facebook.enable_facebook_connect',
		'facebook.facebook_secret',
		'facebook.facebook_app_id'
	);
	
	var $_aSettingColor = array(
		'accountapi.choose_color'
	);
	
	function __construct() {
		
	}
	
	/**	
	 * get all phrases
	 */
	function getPhrases($sDefautLanguage = '') {
		$oServicePhrase = Phpfox::getService('language.phrase');

            $aLanguages = Phpfox::getService('language')->get(array('l.user_select = 1'));
            list($iCnt, $aOrgPhrases) = $oServicePhrase->get(array('lp.module_id = \'accountapi\' AND lp.language_id = \'en\''));

            $aPhrases = array();
            foreach ($aOrgPhrases as $aPhrase) {
                $aPhrases[$aPhrase['module_id']. '.'. $aPhrase['var_name']] = Phpfox::getPhrase($aPhrase['module_id']. '.'. $aPhrase['var_name']);
            }

		foreach ($this->_aPhraseNeeded as $sVarname) {
            list($sModule, $sPhrase) = explode('.', $sVarname);
            if (Phpfox::isModule($sModule)) {
                $aPhrases[$sVarname] = Phpfox::getPhrase($sVarname);
            }
		}
        $aPhrases['user.sign_up_for_ssitetitle'] = Phpfox::getPhrase('user.sign_up_for_ssitetitle', array('sSiteTitle' => Phpfox::getParam('core.site_title')));
		return $aPhrases;
	}

	/**	
	 * get all settings
	 */
	function getSettings() {
		$oServiceSetting = Phpfox::getService('admincp.setting');
		
		$aOrgSettings = $oServiceSetting->get(array(' AND setting.module_id = \'accountapi\' AND setting.is_hidden = 0 '));		
		$aSettings = array();
		foreach ($aOrgSettings as $aSetting) {
			$aSettings[$aSetting['module_id']. '.'. $aSetting['var_name']] = $aSetting['value_actual'];
		}
		
		foreach ($this->_aSettingNeeded as $sVarname) {
			$aSettings[$sVarname] = Phpfox::getParam($sVarname);
		}
		
		return $aSettings;
	}
	
	/**
	 * get Notify Status
	 */
	public function getNotifyStatus() {
		$aTotalNotify = array(
			'friend' => Phpfox::getService('friend.request')->getUnseenTotal(),
			'mail' => Phpfox::getService('mail')->getUnseenTotal(),
			'notification' => Phpfox::getService('notification')->getUnseenTotal(),
            'total_friend' => Phpfox::getService('accountapi.friend')->getTotalFriend()
		);
		
		return $aTotalNotify;
	}
	/**	
	 * get user group settings
	 */
	function getUserSettings() {
		foreach($this->_aSettingUserGroup as $sVarname) {
			$aUserSettings[$sVarname] = Phpfox::getUserParam($sVarname);
		}
		
		return $aUserSettings;
	}
	
	/**
	 * Get facebook setting
	 */
	function getFacebookSetting() {
		foreach($this->_aSettingFacebook as $sVarname) {
			$_aSettingFacebook[$sVarname] = Phpfox::getParam($sVarname);
		}
		
		return $_aSettingFacebook;
	}
	
	function getColorForApp() {
		foreach($this->_aSettingColor as $sVarname) {
			$_aSettingColor[$sVarname] = Phpfox::getParam($sVarname);
		}
		
		return $_aSettingColor;
	}
}
