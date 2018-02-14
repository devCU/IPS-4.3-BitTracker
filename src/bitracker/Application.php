<?php
/**
 * @brief		BitTracker Application Class
 * @author		Gary Cornell
 * @copyright		(c) <a href='https://www.devcu.com'>devCU Software Development</a>
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage		BitTracker
 * @since		11 FEB 2018
 */
 
namespace IPS\bitracker;

/**
 * BitTracker Application Class
 */
class _Application extends \IPS\Application
{
	/**
	 * Init
	 *
	 * @return	void
	 */
	public function init()
	{
		/* If the viewing member cannot view the board (ex: guests must login first), then send a 404 Not Found header here, before the Login page shows in the dispatcher */
		if ( !\IPS\Member::loggedIn()->group['g_view_board'] and ( \IPS\Request::i()->module == 'bitracker' and \IPS\Request::i()->controller == 'browse' and \IPS\Request::i()->do == 'rss' ) )
		{
			\IPS\Output::i()->error( 'node_error', '2D220/1', 404, '' );
		}
		
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'bitracker.css' ) );

		if ( \IPS\Theme::i()->settings['responsive'] )
		{
			\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'bitracker_responsive.css', 'bitracker', 'front' ) );
		}
	}

	/**
	 * [Node] Get Icon for tree
	 *
	 * @note	Return the class for the icon (e.g. 'globe')
	 * @return	string|null
	 */
	protected function get__icon()
	{
		return 'bitrack';
	}
	
	/**
	 * Default front navigation
	 *
	 * @code
	 	
	 	// Each item...
	 	array(
			'key'		=> 'Example',		// The extension key
			'app'		=> 'core',			// [Optional] The extension application. If ommitted, uses this application	
			'config'	=> array(...),		// [Optional] The configuration for the menu item
			'title'		=> 'SomeLangKey',	// [Optional] If provided, the value of this language key will be copied to menu_item_X
			'children'	=> array(...),		// [Optional] Array of child menu items for this item. Each has the same format.
		)
	 	
	 	return array(
		 	'rootTabs' 		=> array(), // These go in the top row
		 	'browseTabs'	=> array(),	// These go under the Browse tab on a new install or when restoring the default configuraiton; or in the top row if installing the app later (when the Browse tab may not exist)
		 	'browseTabsEnd'	=> array(),	// These go under the Browse tab after all other items on a new install or when restoring the default configuraiton; or in the top row if installing the app later (when the Browse tab may not exist)
		 	'activityTabs'	=> array(),	// These go under the Activity tab on a new install or when restoring the default configuraiton; or in the top row if installing the app later (when the Activity tab may not exist)
		)
	 * @endcode
	 * @return array
	 */
	public function defaultFrontNavigation()
	{
		return array(
			'rootTabs'		=> array(),
			'browseTabs'	=> array( array( 'key' => 'bitracker' ) ),
			'browseTabsEnd'	=> array(),
			'activityTabs'	=> array()
		);
	}
	
	/**
	 * Perform some legacy URL parameter conversions
	 *
	 * @return	void
	 */
	public function convertLegacyParameters()
	{
		if ( isset( \IPS\Request::i()->showfile ) AND is_numeric( \IPS\Request::i()->showfile ) )
		{
			try
			{
				$file = \IPS\bitracker\File::loadAndCheckPerms( \IPS\Request::i()->showfile );
				
				\IPS\Output::i()->redirect( $file->url() );
			}
			catch( \OutOfRangeException $e ) {}
		}

		if ( isset( \IPS\Request::i()->module ) AND \IPS\Request::i()->module == 'post' AND isset( \IPS\Request::i()->controller ) AND \IPS\Request::i()->controller == 'submit' )
		{
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( "app=bitracker&module=bitracker&controller=submit", "front", "bitracker_submit" ) );
		}
	}
}