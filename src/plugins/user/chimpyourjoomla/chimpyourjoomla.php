<?php
/**
 * @package   Chimp Your Joomla!
 * @copyright (C) 2001 by 'corePHP' - All rights reserved!
 * @license   GNU/GPL2
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgUserMailchimp extends JPlugin
{

	function plgUserMailchimp(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onUserAfterSave($user, $isnew, $success, $msg)
	{
		global $mainframe;


		if($user) {
			// Lets pull the API layer
			require_once(JPATH_PLUGINS.'/user/chimpyourjoomla/includes/MCAPI.class.php');

			// Lets grab all the required params now
			$mc_username     = $this->params->get('mc_username');
			$mc_listid       = $this->params->get('mc_listid');
			$mc_autoregister = $this->params->get('mc_autoregister', 1);

			// Lets put in our API key and set the $api value
			$api = new MCAPI($mc_username);

			// Create a First and Last Name and place it in the $mergeVars
			$name = explode( ' ', $user['name'] );

			$mergeVars = array(	'FNAME'=>$name[0],
								'LNAME'=>$name[1]);

			if( $mc_autoregister == 1 ) {
				$mc_autoregister = true;
			} else {
				$mc_autoregister = false;
			}

			if ($isnew) {
				$api->listSubscribe( $mc_listid,
					$user['email'], $mergeVars, 'html',
					$mc_autoregister );
			}
		}
	}
}
