<?php

/**
* @package JFusion_mediawiki
* @author JFusion development team
* @copyright Copyright (C) 2008 JFusion. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC' ) or die('Restricted access' );

/**
 * JFusion User Class for mediawiki 1.1.x
 * For detailed descriptions on these functions please check the model.abstractuser.php
 * @package JFusion_mediawiki
 */
//require_once(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jfusion'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'model.jfusion.php');
//require_once(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jfusion'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'model.abstractuser.php');
require_once(JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_jfusion'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'model.jplugin.php');
/**
 * JFusionUser_mediawiki class
 *
 * @category   JFusion
 * @package    Plugin
 * @subpackage JFusionUser_mediawiki
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
class JFusionUser_mediawiki extends JFusionUser {

    /**
     * @param object $userinfo
     * @return null|object
     */
    function getUser($userinfo)
    {
	    try {
		    // get the username
		    if (is_object($userinfo)){
			    $username = $userinfo->username;
		    } else {
			    $username = $userinfo;
		    }
		    $username = ucfirst($username);
		    // initialise some objects
		    $db = JFusionFactory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->select('user_id as userid, user_name as username, user_token, user_real_name as name, user_email as email, user_password as password, NULL as password_salt, NULL as activation, TRUE as is_activated, NULL as reason, user_touched as lastvisit')
			    ->from('#__user')
		        ->where('user_name = ' . $db->Quote($username));

		    $db->setQuery($query);
		    $result = $db->loadObject();

		    if ($result) {
			    $query = $db->getQuery(true)
				    ->select('ug_group')
				    ->from('#__user_groups')
				    ->where('ug_user = ' . $db->Quote($result->userid));

			    $db->setQuery( $query );
			    $grouplist = $db->loadObjectList();
			    $groups = array();
			    foreach($grouplist as $group) {
				    $groups[] = $group->ug_group;
			    }
			    $result->group_id = implode( ',' , $groups );
			    $result->groups = $groups;

			    $query = $db->getQuery(true)
				    ->select('ipb_user, ipb_expiry')
				    ->from('#__ipblocks')
				    ->where('ipb_user = ' . $db->Quote($result->userid));

			    $db->setQuery($query);
			    $block = $db->loadObject();

			    if (isset($block->ipb_user)) {
				    if ($block->ipb_expiry ) {
					    $result->block = 1;
				    } else {
					    $result->block = 0;
				    }
			    } else {
				    $result->block = 0;
			    }

			    $result->activation = '';
		    }
	    } catch (Exception $e) {
		    JFusionFunction::raiseError($e, $this->getJname());
		    $result = null;
	    }
        return $result;
    }

    /**
     * @return string
     */
    function getJname()
    {
        return 'mediawiki';
    }

    /**
     * @param object $userinfo
     * @return array
     */
    function deleteUser($userinfo) {
	    try {
	        //setup status array to hold debug info and errors
	        $status = array('error' => array(),'debug' => array());

	        $db = JFusionFactory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->delete('#__user')
			    ->where('user_name = ' .  $db->quote($userinfo->username));

			$db->setQuery($query);
		    $db->execute();

		    $query = $db->getQuery(true)
			    ->delete('#__user_groups')
			    ->where('ug_user = ' .  $db->quote($userinfo->userid));

		    $db->setQuery($query);
		    $db->execute();

		    $status['debug'][] = JText::_('USER_DELETION'). ' ' . $userinfo->username;
	    } catch (Exception $e) {
		    $status['error'][] = JText::_('USER_DELETION_ERROR') . ' ' .  $e->getMessage();
	    }
		return $status;
    }

    /**
     * @param object $userinfo
     * @param array $options
     *
     * @return array
     */
    function destroySession($userinfo, $options){
        $cookie_path = $this->params->get('cookie_path');
        $cookie_domain = $this->params->get('cookie_domain');
        $cookie_secure = $this->params->get('secure');
        $cookie_httponly = $this->params->get('httponly');
        $cookie_name = $this->helper->getCookieName();
        $expires = -3600;

	    $this->helper->startSession($options);
   		$_SESSION['wsUserID'] = 0;
   		$_SESSION['wsUserName'] = '';
   		$_SESSION['wsToken'] = '';
	    $this->helper->closeSession();

        $status['debug'][] = JFusionFunction::addCookie($cookie_name  . 'UserName', '', $expires, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
        $status['debug'][] = JFusionFunction::addCookie($cookie_name  . 'UserID', '', $expires, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
        $status['debug'][] = JFusionFunction::addCookie($cookie_name  . 'Token', '', $expires, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);

   		$now = time();
        $expiration = 86400;

        $status['debug'][] = JFusionFunction::addCookie('LoggedOut', $now, $expiration, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
		return $status;
     }

    /**
     * @param object $userinfo
     * @param array $options
     * @return array
     */
    function createSession($userinfo, $options){
        $status = array('error' => array(),'debug' => array());

		//do not create sessions for blocked users
		if (!empty($userinfo->block) || !empty($userinfo->activation)) {
            $status['error'][] = JText::_('FUSION_BLOCKED_USER');
		} else {
            $cookie_path = $this->params->get('cookie_path');
            $cookie_domain = $this->params->get('cookie_domain');
            $cookie_secure = $this->params->get('secure');
            $cookie_httponly = $this->params->get('httponly');
			$expires = $this->params->get('cookie_expires', 3100);
            $cookie_name = $this->helper->getCookieName();
			$this->helper->startSession($options);

			$status['debug'][] = JFusionFunction::addCookie($cookie_name  . 'UserName', $userinfo->username, $expires, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
            $_SESSION['wsUserName'] = $userinfo->username;

			$status['debug'][] = JFusionFunction::addCookie($cookie_name  . 'UserID', $userinfo->userid, $expires, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
            $_SESSION['wsUserID'] = $userinfo->userid;

            $_SESSION[ 'wsToken'] = $userinfo->user_token;
            if (!empty($options['remember'])) {
	            $status['debug'][] = JFusionFunction::addCookie($cookie_name  . 'Token', $userinfo->user_token, $expires, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
            }

			$this->helper->closeSession();
        }
		return $status;
	}


    /**
     * @param string $username
     *
     * @return string
     */
    function filterUsername($username)
    {
        //no username filtering implemented yet
        return $username;
    }

    /**
     * @param object $userinfo
     * @param object $existinguser
     * @param array $status
     *
     * @return void
     */
    function updatePassword($userinfo, &$existinguser, &$status) {
	    try {
	        $existinguser->password = ':A:' . md5( $userinfo->password_clear);
	        $db = JFusionFactory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->update('#__user')
			    ->set('user_password = ' . $db->quote($existinguser->password))
			    ->where('user_id = ' . (int)$existinguser->userid);

	        $db->setQuery($query);
		    $db->execute();

		    $status['debug'][] = JText::_('PASSWORD_UPDATE') . ' ' . substr($existinguser->password,0,6) . '********';
	    } catch (Exception $e) {
		    $status['error'][] = JText::_('PASSWORD_UPDATE_ERROR')  . $e->getMessage();
	    }
    }

    /**
     * @param object $userinfo
     * @param object $existinguser
     * @param array $status
     *
     * @return void
     */
    function updateUsername($userinfo, &$existinguser, &$status)
    {

    }

    /**
     * @param object $userinfo
     * @param object $existinguser
     * @param array $status
     *
     * @return void
     */
    function updateEmail($userinfo, &$existinguser, &$status)
    {
	    try {
		    //we need to update the email
		    $db = JFusionFactory::getDatabase($this->getJname());
		    $query = $db->getQuery(true)
			    ->update('#__user')
			    ->set('user_email = ' . $db->quote($userinfo->email))
			    ->where('user_id = ' . (int)$existinguser->userid);

		    $db->setQuery($query);
		    $db->execute();

		    $status['debug'][] = JText::_('EMAIL_UPDATE'). ': ' . $existinguser->email . ' -> ' . $userinfo->email;
	    } catch (Exception $e) {
		    $status['error'][] = JText::_('EMAIL_UPDATE_ERROR') . $e->getMessage();
	    }
    }

    /**
     * @param object $userinfo
     * @param object $existinguser
     * @param array $status
     *
     * @return void
     */
    function updateUsergroup($userinfo, &$existinguser, &$status)
	{
		try {
			$usergroups = JFusionFunction::getCorrectUserGroups($this->getJname(),$userinfo);
			if (empty($usergroups)) {
				$status['error'][] = JText::_('GROUP_UPDATE_ERROR') . ': ' . JText::_('USERGROUP_MISSING');
			} else {
				$db = JFusionFactory::getDatabase($this->getJname());
				try {
					$query = $db->getQuery(true)
						->delete('#__user_groups')
						->where('ug_user = ' .  $db->quote($userinfo->userid));

					$db->setQuery($query);
					$db->execute();
				} catch (Exception $e) {
				}
				foreach($usergroups as $usergroup) {
					//prepare the user variables
					$ug = new stdClass;
					$ug->ug_user = $existinguser->userid;
					$ug->ug_group = $usergroup;

					$db->insertObject('#__user_groups', $ug, 'ug_user' );

					$status['debug'][] = JText::_('GROUP_UPDATE'). ': ' . implode (' , ', $existinguser->groups) . ' -> ' . $usergroup;
				}
			}
		} catch (Exception $e) {
			$status['error'][] = JText::_('GROUP_UPDATE_ERROR') . $e->getMessage();
		}
	}

    /**
     * @param object $userinfo
     * @param object $existinguser
     * @param array $status
     *
     * @return void
     */
    function blockUser($userinfo, &$existinguser, &$status)
    {
	    try {
		    $db = JFusionFactory::getDatabase($this->getJname());
		    $ban = new stdClass;
		    $ban->ipb_id = NULL;
		    $ban->ipb_address = NULL;
		    $ban->ipb_user = $existinguser->userid;
		    $ban->ipb_by = $existinguser->userid;
		    $ban->ipb_by_text = $existinguser->username;

		    $ban->ipb_reason = 'You have been banned from this software. Please contact your site admin for more details';
		    $ban->ipb_timestamp = gmdate( 'YmdHis', time() );

		    $ban->ipb_auto = 0;
		    $ban->ipb_anon_only = 0;
		    $ban->ipb_create_account = 1;
		    $ban->ipb_enable_autoblock = 1;
		    $ban->ipb_expiry = 'infinity';
		    $ban->ipb_range_start = NULL;
		    $ban->ipb_range_end = NULL;
		    $ban->ipb_deleted = 0;
		    $ban->ipb_block_email = 0;
		    $ban->ipb_allow_usertalk = 0;

		    //now append the new user data
		    $db->insertObject('#__ipblocks', $ban, 'ipb_id' );

		    $status['debug'][] = JText::_('BLOCK_UPDATE'). ': ' . $existinguser->block . ' -> ' . $userinfo->block;
	    } catch (Exception $e) {
		    $status['error'][] = JText::_('BLOCK_UPDATE_ERROR'). ': ' . $e->getMessage();
	    }
    }

    /**
     * @param object $userinfo
     * @param object $existinguser
     * @param array $status
     *
     * @return void
     */
    function unblockUser($userinfo, &$existinguser, &$status)
    {
	    try {
		    $db = JFusionFactory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->delete('#__ipblocks')
			    ->where('ipb_user = ' .  $db->quote($userinfo->userid));

		    $db->setQuery($query);
		    $db->execute();

		    $status['debug'][] = JText::_('BLOCK_UPDATE'). ': ' . $existinguser->block . ' -> ' . $userinfo->block;
	    } catch (Exception $e) {
		    $status['error'][] = JText::_('BLOCK_UPDATE_ERROR'). ': ' . $e->getMessage();
	    }
    }
/*
    function activateUser($userinfo, &$existinguser, &$status)
    {
		try {
	        $db = JFusionFactory::getDatabase($this->getJname());
		    $query = $db->getQuery(true)
			    ->update('#__user')
			    ->set('is_activated = 1')
				->set('validation_code = ' . $db->quote(''))
			    ->where('user_id = ' . (int)$existinguser->userid);

	        $db->setQuery($query);
			$db->execute():
			$status['debug'][] = JText::_('ACTIVATION_UPDATE'). ': ' . $existinguser->activation . ' -> ' . $userinfo->activation;
		} catch (Exception $e) {
		    $status['error'][] = JText::_('ACTIVATION_UPDATE_ERROR'). ': ' . $e->getMessage();
	    }
    }

    function inactivateUser($userinfo, &$existinguser, &$status)
    {
		try {
	        $db = JFusionFactory::getDatabase($this->getJname());

		    $query = $db->getQuery(true)
			    ->update('#__user')
			    ->set('is_activated = 0')
				->set('validation_code = ' . $db->quote($userinfo->activation))
			    ->where('user_id = ' . (int)$existinguser->userid);

	        $db->setQuery($query);
			$db->execute();
			$status['debug'][] = JText::_('ACTIVATION_UPDATE'). ': ' . $existinguser->activation . ' -> ' . $userinfo->activation;
		} catch (Exception $e) {
		    $status['error'][] = JText::_('ACTIVATION_UPDATE_ERROR'). ': ' . $e->getMessage();
	    }
    }
*/

    /**
     * @param object $userinfo
     * @param array $status
     *
     * @return void
     */
    function createUser($userinfo, &$status)
    {
	    try {
		    //we need to create a new SMF user
		    $db = JFusionFactory::getDatabase($this->getJname());

		    $usergroups = JFusionFunction::getCorrectUserGroups($this->getJname(),$userinfo);
		    if (empty($usergroups)) {
			    throw new RuntimeException(JText::_('USERGROUP_MISSING'));
		    } else {
			    //prepare the user variables
			    $user = new stdClass;
			    $user->user_id = NULL;
			    $user->user_name = ucfirst($userinfo->username);
			    $user->user_real_name = $userinfo->name;
			    $user->user_email = $userinfo->email;
			    $user->user_email_token_expires = null;
			    $user->user_email_token = '';

			    if (isset($userinfo->password_clear)) {
				    $user->user_password = ':A:' . md5( $userinfo->password_clear);
			    } else {
				    $user->user_password = ':A:' . $userinfo->password;
			    }
			    $user->user_newpass_time = $user->user_newpassword = null;

			    $db->setQuery('SHOW COLUMNS FROM #__user LIKE \'user_options\'');
			    $db->execute();

			    if ($db->getNumRows() ) {
				    $user->user_options = ' ';
			    }

			    $user->user_email_authenticated = $user->user_registration = $user->user_touched = gmdate( 'YmdHis', time() );
			    $user->user_editcount = 0;
			    /*
					if ($userinfo->activation){
						$user->is_activated = 0;
						$user->validation_code = $userinfo->activation;
					} else {
						$user->is_activated = 1;
						$user->validation_code = '';
					}
			*/
			    //now append the new user data
			    $db->insertObject('#__user', $user, 'user_id' );

			    $wgDBprefix = $this->params->get('database_prefix');
			    $wgDBname = $this->params->get('database_name');

			    if ( $wgDBprefix ) {
				    $wfWikiID = $wgDBname.'-'.$wgDBprefix;
			    } else {
				    $wfWikiID = $wgDBname;
			    }

			    $wgSecretKey = $this->helper->getConfig('wgSecretKey');
			    $wgProxyKey = $this->helper->getConfig('wgProxyKey');

			    if ( $wgSecretKey ) {
				    $key = $wgSecretKey;
			    } elseif ( $wgProxyKey ) {
				    $key = $wgProxyKey;
			    } else {
				    $key = microtime();
			    }
			    //update the stats
			    $mToken = md5( $key . mt_rand( 0, 0x7fffffff ) . $wfWikiID . $user->user_id );

			    $query = $db->getQuery(true)
				    ->update('#__user')
				    ->set('is_activated = 0')
				    ->set('user_token = ' . $db->quote($mToken))
				    ->where('user_id = ' . $db->Quote($user->user_id));

			    $db->setQuery($query);
			    $db->execute();

			    //prepare the user variables
			    foreach($usergroups as $usergroup) {
				    //prepare the user variables
				    $ug = new stdClass;
				    $ug->ug_user = $user->user_id;
				    $ug->ug_group = $usergroup;

				    $db->insertObject('#__user_groups', $ug, 'ug_user' );
			    }
			    //return the good news
			    $status['debug'][] = JText::_('USER_CREATION');
			    $status['userinfo'] = $this->getUser($userinfo);
		    }
	    } catch (Exception $e) {
		    $status['error'][] = JText::_('GROUP_UPDATE_ERROR').': ' . $e->getMessage();
	    }
    }
}
