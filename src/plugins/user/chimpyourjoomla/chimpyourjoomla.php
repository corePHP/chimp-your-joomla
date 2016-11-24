<?php
/**
 * @package   Chimp Your Joomla!
 * @copyright (C) 2011-2014 by 'corePHP' - All rights reserved!
 * @license   GNU/GPL2
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

jimport('joomla.plugin.plugin');

class plgUserChimpYourJoomla extends JPlugin
{
	function onUserAfterSave( $user, $isnew, $success, $msg )
	{
		if( $user ) {
			// Lets pull the API layer
			require_once( JPATH_PLUGINS . '/user/chimpyourjoomla/includes/mailchimp.php' );

			// Lets grab all the required params now
			$chimp_api       = $this->params->get( 'chimp_api' );
			$list_id      = $this->params->get( 'chimp_list' );
			$chimp_auto      = $this->params->get( 'chimp_auto', 0 );

			// Just a quick check
			if( !$chimp_api || !$list_id ) return;

			// Lets put in our API key and set the $api value
			$MailChimp = new MailChimp( $chimp_api );

			// Create a First and Last Name and place it in the $mergeVars
			$name = explode( ' ', $user['name'] );

			$mergeVars = array(	
								'email_address'=> $user['email'],
								'status'=>'subscribed',
								"merge_fields"=> ["FNAME"=> $name[0],  "LNAME"=> $name[1]]
							);

			if( $chimp_auto == 1 ) {
				$chimp_auto = true;
			} else {
				$chimp_auto = false;
			}

			if ( $isnew ) {
				$added = $MailChimp->post("lists/$list_id/members", $mergeVars);
			} else {
				$subscriber_hash = $MailChimp->subscriberHash($user['email']);
				$MailChimp->patch("lists/$list_id/members/$subscriber_hash", ['merge_fields' => ['FNAME'=>$name[0], 'LNAME'=>$name[1]]]);
			}
		}
	}
}