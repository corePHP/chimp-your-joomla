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
		$chimp_layout = '<p><input type="checkbox" id="chimpyourjoomlapro" name="chimpyourjoomlapro" value="1" checked="checked" /> [caption]</p>';
		$chimp_layout = str_replace( '[caption]', $this->params->get( 'chimp_caption', JText::_( 'CYJ_SUBSCRIBE' ) ) , $chimp_layout );

		// Include required files
		include_once( JPATH_PLUGINS . '/system/chimpyourjoomlapro/includes/simple_html_dom.php' );

		$output = JResponse::getBody();

		$html = str_get_html($output);

		// Get the login button (at least normally there's only one button)
		$login_button = $html->find( '#josForm button', 0); // Make this configurable in the XML

		// In the place of the login button place the checkbox and the button
		$login_button->outertext = $chimp_layout.$login_button->outertext;

		// Outputs the changed HTML
		JResponse::setBody($html);
	}
}