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
			$chimp_list      = $this->params->get( 'chimp_list' );
			$chimp_auto      = $this->params->get( 'chimp_auto', 0 );

			// Just a quick check
			if( !$chimp_api || !$chimp_list ) return;

			// Lets put in our API key and set the $api value
			$mc = new MailChimp( $chimp_api );

			// Create a First and Last Name and place it in the $mergeVars
			$name = explode( ' ', $user['name'] );

			$mergeVars = array(	'FNAME'=>$name[0],
								'LNAME'=>$name[1]
								);

			if( $chimp_auto == 0 ) {
				$chimp_auto = true;
			} else {
				$chimp_auto = false;
			}

			if ( $isnew ) {
				$added = mc::add( $mc, $chimp_list, $user['email'], $mergeVars, $chimp_auto );
				return true;
			} else {
				// Need to check to see if the users exists first
				$exists = mc::memberinfo( $mc, $chimp_list, $user['email'] );
				if( !empty( $exists['data'] ) ) {
					// Lets test to see if we are in any of the lists
					foreach( $exists['data'] as $data ) {
						if( $data['list_id'] === $chimp_list ) {
							$update = mc::update( $mc, $chimp_list, $user['email'], $mergeVars );
						} else {
							$added = mc::add( $mc, $chimp_list, $user['email'], $mergeVars, $chimp_auto );
						}
					}
					return true;
				} else {
					$added = mc::add( $mc, $chimp_list, $user['email'], $mergeVars, $chimp_auto );
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
			$chimp_list      = $this->params->get( 'chimp_list' );
			$chimp_goodbye   = $this->params->get( 'chimp_goodbye' );

			// Just a quick check
			if( !$chimp_api || !$chimp_list ) return;

			// Lets put in our API key and set the $api value
			$mc = new MailChimp( $chimp_api );

			// Lets check some stuff.
			if( $this->params->get( 'chimp_delete_user' ) ) {
				$deleted = mc::unsubscribe( $mc, $chimp_list, $user['email'], true, $chimp_goodbye );
				return;
			}

			if( $this->params->get( 'chimp_unsubscribe_user' ) ) {
				$unsubscribe = mc::unsubscribe( $mc, $chimp_list, $user['email'], false, $chimp_goodbye );
				return;
			}
		}
	}
}