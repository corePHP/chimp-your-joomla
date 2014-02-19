<?php
/**
 * @package   Chimp Your Joomla! Pro
 * @copyright (C) 2014 by 'corePHP' - All rights reserved!
 * @license   GNU/GPL2
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

jimport('joomla.plugin.plugin');

class plgSystemChimpYourJoomlaPro extends JPlugin
{
	function onUserAfterSave( $user, $isnew, $success, $msg )
	{
		if( $user ) {
			// Lets pull the API layer
			require_once( JPATH_PLUGINS . '/system/chimpyourjoomlapro/includes/mailchimp.php' );

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

			if( $chimp_auto == 1 ) {
				$chimp_auto = true;
			} else {
				$chimp_auto = false;
			}

			if ( $isnew ) {
				// Check to see if they opts out of registering only on frontend
				if( !JFactory::getApplication()->input->get( 'chimpyourjoomlapro', 0, '', 'int' ) && JFactory::getApplication()->isSite() ) {
					return;
				}
				mc::add( $mc, $chimp_list, $user['email'], $mergeVars, $chimp_auto );
			} else {
				mc::update( $mc, $chimp_list, $user['email'], $mergeVars );
			}
		}
	}

	function onUserAfterDelete( $user, $success, $msg )
	{
		if( $user ) {
			// Lets pull the API layer
			require_once( JPATH_PLUGINS . '/system/chimpyourjoomlapro/includes/mailchimp.php' );

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
				mc::unsubscribe( $mc, $chimp_list, $user['email'], true, $chimp_goodbye );
				return;
			}

			if( $this->params->get( 'chimp_unsubscribe_user' ) ) {
				mc::unsubscribe( $mc, $chimp_list, $user['email'], false, $chimp_goodbye );
				return;
			}
		}
	}

	function onAfterRender()
	{
		// Don't run in administration
		if( JFactory::getApplication()->isAdmin() )
			return;

		// Get variables
		$option = JFactory::getApplication()->input->get( 'option' );
		$view   = JFactory::getApplication()->input->get( 'view' );
		$layout = JFactory::getApplication()->input->get( 'layout' );
		if( $option != 'com_users' || $view != 'registration' || $layout == 'complete' )
			return;

		// Get language file
		$lang = JFactory::getLanguage();
		$extension = 'plg_system_chimpyourjoomlapro';
		$base_dir = JPATH_ADMINISTRATOR;
		$language_tag = 'en-GB';
		$lang->load($extension, $base_dir, $language_tag, true);

		// Get the parameters
		$chimp_layout = $this->params->get('chimp_layout', '<p><input type="checkbox" id="chimpyourjoomlapro" name="chimpyourjoomlapro" value="1" checked="checked" /> [caption]</p>');
		$chimp_layout = str_replace( '[caption]', $this->params->get( 'chimp_caption', JText::_( 'CYJ_SUBSCRIBE' ) ) , $chimp_layout );

		// Include required files
		include_once( JPATH_PLUGINS . '/system/chimpyourjoomlapro/includes/simple_html_dom.php' );

		$output = JResponse::getBody();

		$html = str_get_html($output);

		// Get the login button (at least normally there's only one button)
		$login_button = $html->find( '#member-registration button', 0); // Make this configurable in the XML

		// In the place of the login button place the checkbox and the button
		$login_button->outertext = $chimp_layout.$login_button->outertext;

		// Outputs the changed HTML
		JResponse::setBody($html);
	}
}