<?php

class Accountapi_Service_Mail extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mail');
    }

    public function get($aConds = array(), $sSort = 'm.time_updated DESC', $iPage = '', $iLimit = '', $bIsSentbox = false, $bIsTrash = false)
    {
        $aRows = array();
        $aInputs = array(
            'unread',
            'read'
        );

        if (Phpfox::getParam('mail.threaded_mail_conversation')) {
            $iArchiveId = ($bIsTrash ? 1 : 0);
        }

        if (Phpfox::getParam('mail.threaded_mail_conversation')) {
            $iCnt = $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('mail_thread_user'), 'th')
                ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = th.thread_id')
                ->where('th.user_id = ' . (int)Phpfox::getUserId())
                ->execute('getSlaveField');
        } else {
            $iCnt = $this->database()->select('COUNT(*)')
                ->from($this->_sTable, 'm')
                ->join(Phpfox::getT('mail_text'), 'mt', 'mt.mail_id = m.mail_id')
                ->leftjoin(Phpfox::getT('user'), 'u', 'u.user_id ' . (!$bIsSentbox ? '= m.owner_user_id' : '= m.viewer_user_id'))
                ->where($aConds)
                ->execute('getSlaveField');
        }

        if ($iCnt) {
            (($sPlugin = Phpfox_Plugin::get('mail.service_mail_get')) ? eval($sPlugin) : false);

            if (Phpfox::getParam('mail.threaded_mail_conversation')) {
                if ($bIsSentbox) {
                    $this->database()->where('th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_sent = 1');
                } else {
                    $this->database()->where('th.user_id = ' . (int)Phpfox::getUserId() . ' AND th.is_archive = ' . (int)$iArchiveId . ' AND th.is_sent_update = 0');
                }

                $aRows = $this->database()->select('th.*, tt.text AS preview, tt.time_stamp, tt.user_id AS last_user_id')
                    ->from(Phpfox::getT('mail_thread_user'), 'th')
                    ->join(Phpfox::getT('mail_thread'), 't', 't.thread_id = th.thread_id')
                    ->join(Phpfox::getT('mail_thread_text'), 'tt', 'tt.message_id = t.last_id')
                    ->limit($iPage, $iLimit, $iCnt)
                    ->order('t.time_stamp DESC')
                    ->execute('getSlaveRows');

                $aFields = Phpfox::getService('user')->getUserFields();

                foreach ($aRows as $iKey => $aRow) {
                    $aRows[$iKey]['viewer_is_new'] = ($aRow['is_read'] ? false : true);
                    $aRows[$iKey]['users'] = $this->database()->select('th.is_read, ' . Phpfox::getUserField())
                        ->from(Phpfox::getT('mail_thread_user'), 'th')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
                        ->where('th.thread_id = ' . (int)$aRow['thread_id'])
                        ->execute('getSlaveRows');

                    $aRows[$iKey]['preview_html'] =  $aRows[$iKey]['preview'];
                    $aRows[$iKey]['preview'] = Phpfox::getService('accountapi.emoticon')->processEmoticon($aRow['preview']);

                    $iUserCnt = 0;
                    foreach ($aRows[$iKey]['users'] as $iUserKey => $aUser) {
                        if ($aUser['user_id'] == Phpfox::getUserId()) {
                            unset($aRows[$iKey]['users'][$iUserKey]);

                            continue;
                        }

                        $iUserCnt++;

                        if ($iUserCnt == 1) {
                            foreach ($aFields as $sField) {
                                if ($sField == 'server_id') {
                                    $sField = 'user_server_id';
                                }
                                $aRows[$iKey][$sField] = $aUser[$sField];
                            }
                        }
                    }
                }
            } else {
                if ($bIsTrash) {
                    $this->database()
                        ->select(Phpfox::getUserField('u2', 'other_') . ', ')
                        ->join(Phpfox::getT('user'), 'u2', 'u2.user_id = m.viewer_user_id');
                }

                $aRows = $this->database()->select('m.*, mt.*, ' . Phpfox::getUserField())
                    ->from($this->_sTable, 'm')
                    ->join(Phpfox::getT('mail_text'), 'mt', 'mt.mail_id = m.mail_id')
                    ->leftjoin(Phpfox::getT('user'), 'u', 'u.user_id ' . (!$bIsSentbox ? '= m.owner_user_id' : '= m.viewer_user_id'))
                    ->where($aConds)
                    ->limit($iPage, $iLimit, $iCnt)
                    ->order($sSort)
                    ->execute('getSlaveRows');

                if (!$bIsSentbox) {
                    foreach ($aRows as $iKey => $aRow) {
                        if ($aRow['viewer_is_new']) {
                            $aInputs['unread'][] = $aRow['mail_id'];
                        } else {
                            $aInputs['read'][] = $aRow['mail_id'];
                        }
                    }
                }
            }
        }

        return array($iCnt, $aRows, $aInputs);
    }


    public function add($aVals)
    {
        if (isset($aVals['copy_to_self']) && $aVals['copy_to_self'] == 1) {
            $aVals['to'][] = Phpfox::getUserId();
            unset($aVals['copy_to_self']);
            return $this->add($aVals);
        }

        $bIsThreadReply = false;

        // Send email when enable threaded mail conversation
        if (!isset($aVals['to']) && !empty($aVals['thread_id']) && Phpfox::getParam('mail.threaded_mail_conversation')) {
            $bIsThreadReply = true;
            $aPastThread = $this->database()->select('mt.*')
                ->from(Phpfox::getT('mail_thread'), 'mt')
                ->join(Phpfox::getT('mail_thread_user'), 'mtu', 'mtu.thread_id = mt.thread_id AND mtu.user_id = ' . Phpfox::getUserId())
                ->where('mt.thread_id = ' . (int)$aVals['thread_id'])
                ->execute('getSlaveRow');

            if (!isset($aPastThread['thread_id'])) {
                return Phpfox_Error::set('Unable to find this conversation');
            }

            $aThreadUsers = $this->database()->select('*')
                ->from(Phpfox::getT('mail_thread_user'))
                ->where('thread_id = ' . (int)$aPastThread['thread_id'])
                ->execute('getSlaveRows');

            $aOriginal = array();
            foreach ($aThreadUsers as $aThreadUser) {
                if ($aThreadUser['user_id'] == Phpfox::getUserId()) {
                    continue;
                }
                $aOriginal[] = $aThreadUser['user_id'];
            }
        }

        $iSentTo = 0;
        // Send email to list user
        if (isset($aVals['to']) && is_array($aVals['to']) && !Phpfox::getParam('mail.threaded_mail_conversation')) {
            $aCache = array();
            foreach ($aVals['to'] as $mTo) {
                if ($mTo != Phpfox::getUserId()) {
                    ++$iSentTo;
                }

                if (Phpfox::getUserParam('mail.send_message_to_max_users_each_time') > 0
                    && $iSentTo > Phpfox::getUserParam('mail.send_message_to_max_users_each_time')
                ) {
                    return Phpfox_Error::set(Phpfox::getPhrase('mail.too_many_users_this_message_was_sent_to_the_first_total_users', array('total' => Phpfox::getUserParam('mail.send_message_to_max_users_each_time'))));
                }

                if (strstr($mTo, ',')) {
                    $aParts = explode(',', $mTo);
                    foreach ($aParts as $mUser) {
                        $aVals['to'] = trim($mUser);

                        if (empty($aVals['to'])) {
                            continue;
                        }

                        // Make sure we found a user
                        if (($iTemp = $this->add($aVals, true)) && is_numeric($iTemp)) {
                            $aCache[] = $iTemp;
                        }
                    }
                } else {
                    $aVals['to'] = $mTo;

                    if (empty($aVals['to'])) {
                        continue;
                    }

                    // Make sure we found a user
                    if (($iTemp = $this->add($aVals, true)) && is_numeric($iTemp)) {
                        $aCache[] = $iTemp;
                    }
                }

            }

            if ((Phpfox::getUserParam('mail.can_add_attachment_on_mail') && !empty($aVals['attachment'])) && count($aCache)) {
                $aLastCache = array_reverse($aCache);

                foreach ($aCache as $iMailId) {
                    $this->database()->update($this->_sTable, array('mass_id' => $aLastCache[0]), 'mail_id = ' . (int)$iMailId);
                }
            }

            return $aCache;
        }


        if (!$bIsThreadReply && Phpfox::getParam('mail.threaded_mail_conversation')) {
            $aOriginal = $aVals['to'];
            $aVals['to'] = $aVals['to'][0];
        }

        if (!$bIsThreadReply) {
            $aDetails = Phpfox::getService('user')->getUser($aVals['to'], Phpfox::getUserField() . ', u.email, u.language_id, u.user_group_id', (is_numeric($aVals['to']) ? false : true));
            if (!isset($aDetails['user_id'])) {
                return false;
            }

            if (!Phpfox::getService('user.privacy')->hasAccess($aDetails['user_id'], 'mail.send_message')) {
                return Phpfox_Error::set(Phpfox::getPhrase('mail.unable_to_send_a_private_message_to_full_name_as_they_have_disabled_this_option_for_the_moment', array('full_name' => $aDetails['full_name'])));
            }

            // Check if user is allowed to receive messages: http://forums.phpfox.com/project.php?issueid=2216
            if (Phpfox::getService('user.group.setting')->getGroupParam($aDetails['user_group_id'], 'mail.override_mail_box_limit') == false) {
                $iMailBoxLimit = Phpfox::getService('user.group.setting')->getGroupParam($aDetails['user_group_id'], 'mail.mail_box_limit');
                $iCurrentMessages = $this->database()
                    ->select('COUNT(viewer_user_id)')
                    ->from($this->_sTable)
                    ->where('viewer_user_id = ' . (int)$aVals['to'] . ' AND viewer_type_id != 3 AND viewer_type_id != 1')
                    ->execute('getSlaveField');

                if ($iCurrentMessages >= $iMailBoxLimit) {
                    return Phpfox_Error::set(Phpfox::getPhrase('mail.user_has_reached_their_inbox_limit'));
                }
            }

            if ($aVals['to'] == Phpfox::getUserId() && !Phpfox::getUserParam('mail.can_message_self')) {
                return Phpfox_Error::set(Phpfox::getPhrase('mail.you_cannot_message_yourself'));
            }
            // check if user can send message to non friends: http://forums.phpfox.com/project.php?issueid=2216
            if (Phpfox::getUserParam('mail.restrict_message_to_friends') && !(Phpfox::getService('user.group.setting')->getGroupParam($aDetails['user_group_id'], 'mail.override_restrict_message_to_friends'))) {
                (($sPlugin = Phpfox_Plugin::get('mail.service_process_add_1')) ? eval($sPlugin) : false);
                if (isset($sPluginError)) {
                    return false;
                }
                if (!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aVals['to']))
                    return Phpfox_Error::set(Phpfox::getPhrase('mail.you_can_only_message_your_friends'));
            }

            $aVals = array_merge($aVals, $aDetails);
        }

        $oFilter = Phpfox::getLib('parse.input');
        $oParseOutput = Phpfox::getLib('parse.output');

        $bHasAttachments = (Phpfox::getUserParam('mail.can_add_attachment_on_mail') && !empty($aVals['attachment']));

        if (isset($aVals['parent_id'])) {
            $aMail = $this->database()->select('m.mail_id, m.owner_user_id, m.subject, u.email, u.language_id')
                ->from($this->_sTable, 'm')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.owner_user_id')
                ->where('m.mail_id = ' . (int)$aVals['parent_id'] . ' AND viewer_user_id = ' . Phpfox::getUserId())
                ->execute('getSlaveRow');

            if (!isset($aMail['mail_id'])) {
                return Phpfox_Error::set(Phpfox::getPhrase('mail.not_a_valid_message'));
            }

            $aVals['user_id'] = $aMail['owner_user_id'];
            $aVals['subject'] = $aMail['subject'];
            $aVals['email'] = $aMail['email'];
            $aVals['language_id'] = $aMail['language_id'];
        }
        Phpfox::getService('ban')->checkAutomaticBan((isset($aVals['subject']) ? $aVals['subject'] : '') . ' ' . $aVals['message']);
        $aVals['subject'] = (isset($aVals['subject']) ? $oFilter->clean($aVals['subject'], 255) : null);

        if (Phpfox::getParam('mail.threaded_mail_conversation')) {
            if (!is_array($aOriginal)) {
                $aOriginal = array($aOriginal);
            }
            $aUserInsert = array_merge(array(Phpfox::getUserId()), $aOriginal);

            sort($aUserInsert, SORT_NUMERIC);

            if (!$bIsThreadReply) {
                $sHashId = md5(implode('', $aUserInsert));

                $aPastThread = $this->database()->select('*')
                    ->from(Phpfox::getT('mail_thread'))
                    ->where('hash_id = \'' . $this->database()->escape($sHashId) . '\'')
                    ->execute('getSlaveRow');
            }

            $aThreadUsers = $this->database()->select(Phpfox::getUserField() . ', u.email, u.language_id, u.user_group_id')
                ->from(Phpfox::getT('mail_thread_user'), 'mtu')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtu.user_id')
                ->where('mtu.user_id IN(' . implode(', ', $aUserInsert) . ')')
                ->group('u.user_id')
                ->execute('getSlaveRows');

            if (isset($aPastThread['thread_id'])) {
                $iId = $aPastThread['thread_id'];

                $this->database()->update(Phpfox::getT('mail_thread'), array(
                        'time_stamp' => PHPFOX_TIME
                    ), 'thread_id = ' . (int)$iId
                );

                $this->database()->update(Phpfox::getT('mail_thread_user'), array('is_sent_update' => '0', 'is_read' => '0'), 'thread_id = ' . (int)$iId);
                $this->database()->update(Phpfox::getT('mail_thread_user'), array('is_read' => '1'), 'thread_id = ' . (int)$iId . ' AND user_id = ' . Phpfox::getUserId());
            } else {
                $iId = $this->database()->insert(Phpfox::getT('mail_thread'), array(
                        'hash_id' => $sHashId,
                        'time_stamp' => PHPFOX_TIME
                    )
                );

                foreach ($aUserInsert as $iUserId) {
                    $this->database()->insert(Phpfox::getT('mail_thread_user'), array(
                            'thread_id' => $iId,
                            'is_read' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                            'is_sent' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                            'is_sent_update' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                            'user_id' => (int)$iUserId
                        )
                    );
                }
            }

            $iTextId = $this->database()->insert(Phpfox::getT('mail_thread_text'), array(
                    'thread_id' => $iId,
                    'time_stamp' => PHPFOX_TIME,
                    'user_id' => Phpfox::getUserId(),
                    'text' => $oFilter->prepare($aVals['message']),
                    'is_mobile' => (Phpfox::isMobile() ? '1' : '0')
                )
            );

            $this->database()->update(Phpfox::getT('mail_thread'), array('last_id' => (int)$iTextId), 'thread_id = ' . (int)$iId);

            // Send the user an email
            $sLink = Phpfox::getLib('url')->makeUrl('mail.thread', array('id' => $iId));

            foreach ($aThreadUsers as $aThreadUser) {
                if ($aThreadUser['user_id'] == Phpfox::getUserId()) {
                    continue;
                }

                Phpfox::getLib('mail')->to($aThreadUser['user_id'])
                    ->subject(array('mail.full_name_sent_you_a_message_on_site_title', array('full_name' => Phpfox::getUserBy('full_name'), 'site_title' => Phpfox::getParam('core.site_title')), false, null, $aThreadUser['language_id']))
                    ->message(array('mail.full_name_sent_you_a_message_no_subject', array(
                        'full_name' => Phpfox::getUserBy('full_name'),
                        'message' => $oFilter->clean(strip_tags(Phpfox::getLib('parse.bbcode')->cleanCode(str_replace(array('&lt;', '&gt;'), array('<', '>'), $aVals['message'])))),
                        'link' => $sLink
                    )
                    )
                )
                    ->notification('mail.new_message')
                    ->send();

            }

            // If we uploaded any attachments make sure we update the 'item_id'
            if ($bHasAttachments) {
                Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iTextId);

                $this->database()->update(Phpfox::getT('mail_thread_text'), array('total_attachment' => Phpfox::getService('attachment')->getCountForItem($iTextId, 'mail')), 'message_id = ' . (int)$iTextId);
            }

            if (isset($aVals['forward_thread_id']) && !empty($aVals['forwards'])) {
                $bHasForward = false;
                $aForwards = explode(',', $aVals['forwards']);
                foreach ($aForwards as $iForward) {
                    $iForward = (int)trim($iForward);
                    if (empty($iForward)) {
                        continue;
                    }

                    $bHasForward = true;
                    $this->database()->insert(Phpfox::getT('mail_thread_forward'), array(
                            'message_id' => $iTextId,
                            'copy_id' => $iForward
                        )
                    );
                }

                if ($bHasForward) {
                    $this->database()->update(Phpfox::getT('mail_thread_text'), array('has_forward' => '1'), 'message_id = ' . (int)$iTextId);
                }
            }
        } else {
            $aInsert = array(
                'parent_id' => (isset($aVals['parent_id']) ? $aVals['parent_id'] : 0),
                'subject' => $aVals['subject'],
                'preview' => $oFilter->clean(strip_tags(Phpfox::getLib('parse.bbcode')->cleanCode(str_replace(array('&lt;', '&gt;'), array('<', '>'), $aVals['message']))), 255),
                'owner_user_id' => Phpfox::getUserId(),
                'viewer_user_id' => $aVals['user_id'],
                'viewer_is_new' => 1,
                'time_stamp' => PHPFOX_TIME,
                'time_updated' => PHPFOX_TIME,
                'total_attachment' => ($bHasAttachments ? Phpfox::getService('attachment')->getCount($aVals['attachment']) : 0),
            );

            $iId = $this->database()->insert($this->_sTable, $aInsert);

            $this->database()->insert(Phpfox::getT('mail_text'), array(
                    'mail_id' => $iId,
                    'text' => $oFilter->clean($aVals['message']),
                    'text_parsed' => $oFilter->prepare($aVals['message'])
                )
            );

            // Send the user an email
            $sLink = Phpfox::getLib('url')->makeUrl('mail.view', array('id' => $iId));
            Phpfox::getLib('mail')->to($aVals['user_id'])
                ->subject(array('mail.full_name_sent_you_a_message_on_site_title', array('full_name' => Phpfox::getUserBy('full_name'), 'site_title' => Phpfox::getParam('core.site_title')), false, null, $aVals['language_id']))
                ->message(array('mail.full_name_sent_you_a_message_subject_subject', array(
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'subject' => $aVals['subject'],
                    'message' => $oFilter->clean(strip_tags(Phpfox::getLib('parse.bbcode')->cleanCode(str_replace(array('&lt;', '&gt;'), array('<', '>'), $aVals['message'])))),
                    'link' => $sLink
                )
                )
            )
                ->notification('mail.new_message')
                ->send();

            // If we uploaded any attachments make sure we update the 'item_id'
            if ($bHasAttachments) {
                Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
            }
        }

        (($sPlugin = Phpfox_Plugin::get('mail.service_process_add')) ? eval($sPlugin) : false);

        return $iId;
    }

    public function getMailWithUser($iUserId, $iWithUserId, $iPage)
    {
        $iCount = $this->database()
            ->select('COUNT(*)')
            ->from(Phpfox::getT('mail'), 'm')
            ->leftJoin(Phpfox::getT('mail_text'), 'mt', 'm.mail_id = mt.mail_id')
            ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = m.owner_user_id')
            ->where('(m.owner_user_id = ' . $iUserId . ' AND m.viewer_user_id = ' . $iWithUserId . ') OR (m.owner_user_id = ' . $iWithUserId . ' AND m.viewer_user_id = ' . $iUserId . ')')
            ->execute('getField');

        $iSize = 10;

        $aMessages = $this->database()
            ->select('m.*, mt.*, u.*')
            ->from(Phpfox::getT('mail'), 'm')
            ->leftJoin(Phpfox::getT('mail_text'), 'mt', 'm.mail_id = mt.mail_id')
            ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = m.owner_user_id')
            ->where('(m.owner_user_id = ' . $iUserId . ' AND m.viewer_user_id = ' . $iWithUserId . ') OR (m.owner_user_id = ' . $iWithUserId . ' AND m.viewer_user_id = ' . $iUserId . ')')
            ->limit($iPage, $iSize, $iCount)
            ->order('m.mail_id DESC')
            ->execute('getRows');

        return array($iCount, $aMessages);
    }

    public function getNewestMail($iUserId)
    {
        $aMails = Phpfox::getService('mail')->getLatest();
        if (count($aMails) <= 0) {
            return false;
        }
        $aMail = $aMails[0];
        if (Phpfox::getParam('mail.threaded_mail_conversation')) {
            $aMail['link'] = array(
                'route' => 'mail/thread',
                'request' => array(
                    'thread_id' => $aMail['thread_id'],
                    'subject' => $aMail['full_name']
                )
            );
        } else {
            $aMail['link'] = array(
                'route' => 'mail/thread',
                'request' => array(
                    'mail_id' => $aMail['mail_id'],
                )
            );
        }

        return $aMail;
    }
}