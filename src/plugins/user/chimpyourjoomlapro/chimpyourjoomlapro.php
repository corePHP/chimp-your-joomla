<?php
/**
 * @package   Chimp Your Joomla! Pro
 * @copyright (C) 2014 by 'corePHP' - All rights reserved!
 * @license   GNU/GPL2
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

jimport('joomla.plugin.plugin');

class plgUserChimpYourJoomlaPro extends JPlugin
{
	function onUserAfterSave( $user, $isnew, $success, $msg )
	{
		if( $user ) {
			// Lets pull the API layer
			require_once( JPATH_PLUGINS . '/user/chimpyourjoomlapro/includes/mailchimp.php' );

			// Lets grab all the required params now
			$chimp_api       = $this->params->get( 'chimp_api' );
			$list_id =  $this->params->get( 'chimp_list' );
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

			if( $chimp_auto == 0 ) { 
				$chimp_auto = true;
			} else {
				$chimp_auto = false;
			}
		
			if ( $isnew ) {
			
				$added = $MailChimp->post("lists/$list_id/members", $mergeVars);
				
				return true;
			} else {
				// Need to check to see if the users exists first
				$exists = $MailChimp->get("lists/$list_id/members", $mergeVars);
				if( !empty( $exists['members'] ) ) {
					// Lets test to see if we are in any of the lists
					foreach( $exists['members'] as $data ) {
						if( $data['list_id'] === $list_id ) 
						{
							$subscriber_hash = $MailChimp->subscriberHash($user['email']);
							$update = $MailChimp->patch("lists/$list_id/members/$subscriber_hash", ['merge_fields' => ['FNAME'=>$name[0], 'LNAME'=>$name[1]]]);
						} else {
							$added = $MailChimp->post("lists/$list_id/members", $mergeVars);
						}
					}
					return true;
				} else {
					$added = $MailChimp->post("lists/$list_id/members", $mergeVars);
					return true;
				}
			}
		}
		return;
	}

	function onUserAfterDelete( $user, $success, $msg )
	{
		if( $user ) {
			// Lets pull the API layer
			require_once( JPATH_PLUGINS . '/user/chimpyourjoomlapro/includes/mailchimp.php' );

			// Lets grab all the required params now
			$chimp_api       = $this->params->get( 'chimp_api' );
			$list_id      = $this->params->get( 'chimp_list' );
			$chimp_goodbye   = $this->params->get( 'chimp_goodbye' );

			// Just a quick check
			if( !$chimp_api || !$list_id ) return;

			// Lets put in our API key and set the $api value
			$MailChimp = new MailChimp( $chimp_api );
			
			$subscriber_hash = $MailChimp->subscriberHash($user['email']);

			// Lets check some stuff.
			if( $this->params->get( 'chimp_delete_user' ) ) {
				$MailChimp->delete("lists/$list_id/members/$subscriber_hash");
				
				return;
			}

			if( $this->params->get( 'chimp_unsubscribe_user' ) ) {
				$MailChimp->delete("lists/$list_id/members/$subscriber_hash");
				return;
			}
		}
	}
}