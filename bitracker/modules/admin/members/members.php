<?php
/**
 *     Support this Project... Keep it free! Become an Open Source Patron
 *                       https://www.patreon.com/devcu
 *
 * @brief       BitTracker Member Options
 * @author      Gary Cornell for devCU Software Open Source Projects
 * @copyright   (c) <a href='https://www.devcu.com'>devCU Software Development</a>
 * @license     GNU General Public License v3.0
 * @package     Invision Community Suite 4.2x/4.3x
 * @subpackage	BitTracker
 * @version     1.0.0
 * @source      https://github.com/GaalexxC/IPS-4.2-BitTracker
 * @Issue Trak  https://www.devcu.com/forums/devcu-tracker/
 * @Created     11 FEB 2018
 * @Updated     06 APR 2019
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

namespace IPS\bitracker\modules\admin\members;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Member Options
 */
class _members extends \IPS\Dispatcher\Controller
{

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'members_manage' );
		parent::execute();
	}
    
	/**
	 * Manage Member Settings
	 *
	 * @return	void
	 */
	protected function manage()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'members_manage' );

		$form = $this->_manageMembers();

		if ( $values = $form->values( TRUE ) )
		{
			$this->saveSettingsForm( $form, $values );

			/* Clear guest page caches */
			\IPS\Data\Cache::i()->clearAll();

			\IPS\Session::i()->log( 'acplogs__bitracker_settings' );
		}

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('head_members_options');
		\IPS\Output::i()->output = $form;
}

	/**
	 * Build and return the settings form
	 *
	 * @note	Abstracted to allow third party devs to extend easier
	 * @return	\IPS\Helpers\Form
	 */
	protected function _manageMembers()
	{
		/* Build Form */
		$form = new \IPS\Helpers\Form;

        /* Form Settings */
        $form->addTab( 'general_settings' );
        $form->addHeader( 'general_member_settings' );
        $form->add( new \IPS\Helpers\Form\YesNo( 'bit_member_ratio_enable', \IPS\Settings::i()->bit_member_ratio_enable, FALSE, array( 'togglesOn' => array( 'bit_member_ratio_rules' ) ), NULL, NULL, NULL, 'bit_member_ratio_enable' ) );
		$form->add( new \IPS\Helpers\Form\Stack( 'bit_member_ratio_rules', \IPS\Settings::i()->bit_member_ratio_rules ? json_decode( \IPS\Settings::i()->bit_member_ratio_rules, true ) : array(), FALSE, array( 'stackFieldType' => '\IPS\bitracker\Form\RatioRules', 'maxItems' => 5, 'key' => array( 'decimals' => '2', 'min' => 0.01, 'max' => '1.00' ) ) ) );

        $form->addTab( 'advanced_settings' );  
        $form->addHeader( 'advanced_member_settings' );

		/* Save values */
		if ( $values = $form->values() )
		{

			$values['bit_member_ratio_rules']	= json_encode( array_filter( $values['bit_member_ratio_rules'], function( $value ) {
				return (bool) $value['key'];
			} ) );
            
			$form->saveAsSettings( $values );

			\IPS\Output::i()->redirect( \IPS\Http\Url::internal( 'app=bitracker&module=members&controller=members' ), 'saved' );
		}

		return $form;
	}
}