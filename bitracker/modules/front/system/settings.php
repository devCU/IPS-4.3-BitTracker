<?php
/**
 * @brief       BitTracker Application Class
 * @author      Gary Cornell for devCU Software Open Source Projects
 * @copyright   (c) <a href='https://www.devcu.com'>devCU Software Development</a>
 * @license     GNU General Public License v3.0
 * @package     Invision Community Suite 4.2x/4.3x
 * @subpackage	BitTracker
 * @version     1.0.0 Beta 3
 * @source      https://github.com/GaalexxC/IPS-4.2-BitTracker
 * @Issue Trak  https://www.devcu.com/forums/devcu-tracker/ips4bt/
 * @Created     11 FEB 2018
 * @Updated     13 DEC 2018
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
 
namespace IPS\bitracker\modules\front\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * User CP Controller
 */
class _settings extends \IPS\Dispatcher\Controller
{

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		/* Only logged in members */
		if ( !\IPS\Member::loggedIn()->member_id )
		{
			\IPS\Output::i()->error( 'no_module_permission_guest', '2C122/1', 403, '' );
		}

		\IPS\Output::i()->sidebar['enabled'] = FALSE;
		parent::execute();
	}

	/**
	 * Settings
	 *
	 * @return	void
	 */
	protected function manage()
	{
		/* Work out output */
		$area = \IPS\Request::i()->area ?: 'overview';
		if ( method_exists( $this, "_{$area}" ) )
		{
			$output = call_user_func( array( $this, "_{$area}" ) );
		}
		
		/* Display */
		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('settings');
		\IPS\Output::i()->breadcrumb[] = array( NULL, \IPS\Member::loggedIn()->language()->addToStack('settings') );
		if ( !\IPS\Request::i()->isAjax() )
		{
			if ( \IPS\Request::i()->service )
			{
				$area = "{$area}_" . \IPS\Request::i()->service;
			}
            
            \IPS\Output::i()->cssFiles	= array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/settings.css' ) );
            
            if ( \IPS\Theme::i()->settings['responsive'] )
            {
                \IPS\Output::i()->cssFiles	= array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/settings_responsive.css' ) );
            }
            
            if ( $output )
            {
				\IPS\Output::i()->output .= $this->_wrapOutputInTemplate( $area, $output );
			}
		}
		elseif ( $output )
		{
			\IPS\Output::i()->output .= $output;
		}
	}
	
	/**
	 * Wrap output in template
	 *
	 * @param	string	$area	Active area
	 * @param	string	$output	Output
	 * @return	string
	 */
	protected function _wrapOutputInTemplate( $area, $output )
	{
		/* What can we do? */

				
		/* Return */
		return \IPS\Theme::i()->getTemplate( 'system' )->settings( $tab, $area, $output );
	}
	
	/**
	 * Build and return the settings form: Overview
	 *
	 * @note	Abstracted to allow third party devs to extend easier
	 * @return	\IPS\Helpers\Form
	 */
	protected function _overview()
	{
		return \IPS\Theme::i()->getTemplate( 'system' )->settingsOverview( );
	}

	/**
	 * Build and return the settings form: Configure
	 *
	 * @note	Abstracted to allow third party devs to extend easier
	 * @return	\IPS\Helpers\Form
	 */
	protected function _configure()
	{
		/* Build Form */
		$form = new \IPS\Helpers\Form;
		$form->class = 'ipsForm_collapseTablet';
        $form->add( new \IPS\Helpers\Form\Text( 'bit_open', \IPS\Member::loggedIn()->bit_open, FALSE) );

		/* Handle submissions */
		if ( $values = $form->values() )
		{
			
			\IPS\Member::loggedIn()->bit_open = $values['bit_open'];
			
			\IPS\Member::loggedIn()->save();
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=bitracker&module=settings&controller=settings&area=configure', 'front', 'settings' ), 'bit_open_changed' );
	   }

		return \IPS\Theme::i()->getTemplate( 'system' )->settingsConfigure( $form );
   }

	/**
	 * Build and return the settings form: Stations
	 *
	 * @note	Abstracted to allow third party devs to extend easier
	 * @return	\IPS\Helpers\Form
	 */
	protected function _security()
	{
		/* Build Form */
		$form = new \IPS\Helpers\Form;
		$form->class = 'ipsForm_collapseTablet';
        $form->add( new \IPS\Helpers\Form\Text( 'bit_open', \IPS\Member::loggedIn()->bit_open, FALSE) );

		/* Handle submissions */
		if ( $values = $form->values() )
		{
			
			\IPS\Member::loggedIn()->bit_open = $values['bit_open'];
			
			\IPS\Member::loggedIn()->save();
			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=bitracker&module=settings&controller=settings&area=configure', 'front', 'settings' ), 'bit_open_changed' );
	   }

		return \IPS\Theme::i()->getTemplate( 'system' )->settingsSecurity( $form );
	}

}