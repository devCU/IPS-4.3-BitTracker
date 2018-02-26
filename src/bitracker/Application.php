<?php
/**
 * @brief       BitTracker Application Class
 * @author      Gary Cornell for devCU Software Open Source Projects
 * @copyright   (c) <a href='https://www.devcu.com'>devCU Software Development</a>
 * @license     GNU General Public License v3.0
 * @package     Invision Community Suite 4.2x
 * @subpackage	BitTracker
 * @version     1.0.0 Beta 1
 * @source      https://github.com/GaalexxC/IPS-4.2-BitTracker
 * @Issue Trak  https://www.devcu.com/forums/devcu-tracker/ips4bt/
 * @Created     11 FEB 2018
 * @Updated     26 FEB 2018
 *
 *                    GNU General Public License v3.0
 *    This program is free software: you can redistribute it and/or modify       
 *    it under the terms of the GNU General Public License as published by       
 *    the Free Software Foundation, either version 3 of the License, or          
 *    (at your option) any later version.                                        
 *                                                                               
 *    This program is distributed in the hope that it will be useful,            
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of             
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *                                                                               
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see http://www.gnu.org/licenses/
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
		/* Can view bitracker app? */
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
	 * Group Permissions
	 *
	 * @return void
	 */
	public function installOther()
	{
	    /* Give non guests what permissions they need */
		foreach( \IPS\Member\Group::groups( TRUE, FALSE ) as $group )
		{
		    /* P */
            $group->g_bit_add_torrent     = TRUE;
            $group->g_bit_edit_torrent    = TRUE;
            $group->g_bit_add_comments    = TRUE;
            $group->g_bit_edit_comments   = TRUE;
            $group->g_bit_rate_torrent    = TRUE;                        
            $group->save();
            
		    /* VIP */
            if( $group->g_access_offline )
            {
                $group->g_bit_delete_torrent      = TRUE;
                $group->g_bit_delete_comments     = TRUE;
                $group->g_bit_m_edit_torrent      = TRUE;
                $group->g_bit_m_delete_torrent    = TRUE;
                $group->g_bit_m_edit_comments     = TRUE;   
                $group->g_bit_m_delete_comments   = TRUE;                 
                $group->g_bit_m_manage            = TRUE;                                      
                $group->save();
            }            
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
		return 'cloud';
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
}