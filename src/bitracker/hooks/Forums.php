//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class bitracker_hook_Forums extends _HOOK_CLASS_
{
	/**
	 * Delete Record
	 *
	 * @return	void
	 */
	public function delete()
	{
		foreach( new \IPS\Patterns\ActiveRecordIterator( \IPS\Db::i()->select( '*', 'bitracker_categories', array( "cforum_id=?", $this->_id ) ), 'IPS\bitracker\Category' ) AS $category )
		{
			$category->forum_id = 0;
			$category->save();
		}
		
		return call_user_func_array( 'parent::delete', func_get_args() );
	}

}
