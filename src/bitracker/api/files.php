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
 * @Updated     14 FEB 2018
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

namespace IPS\bitracker\api;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * @brief	BitTracker Files API
 */
class _files extends \IPS\Content\Api\ItemController
{
	/**
	 * Class
	 */
	protected $class = 'IPS\bitracker\File';
	
	/**
	 * GET /bitracker/files
	 * Get list of files
	 *
	 * @apiparam	string	categories		Comma-delimited list of category IDs
	 * @apiparam	string	authors			Comma-delimited list of member IDs - if provided, only files started by those members are returned
	 * @apiparam	int		locked			If 1, only files which are locked are returned, if 0 only unlocked
	 * @apiparam	int		hidden			If 1, only files which are hidden are returned, if 0 only not hidden
	 * @apiparam	int		pinned			If 1, only files which are pinned are returned, if 0 only not pinned
	 * @apiparam	int		featured		If 1, only files which are featured are returned, if 0 only not featured
	 * @apiparam	string	sortBy			What to sort by. Can be 'date' for creation date, 'title' or leave unspecified for ID
	 * @apiparam	string	sortDir			Sort direction. Can be 'asc' or 'desc' - defaults to 'asc'
	 * @apiparam	int		page			Page number
	 * @return		\IPS\Api\PaginatedResponse<IPS\bitracker\File>
	 */
	public function GETindex()
	{
		/* Where clause */
		$where = array();
				
		/* Return */
		return $this->_list( $where, 'categories' );
	}
	
	/**
	 * GET /bitracker/files/{id}
	 * View information about a specific file
	 *
	 * @param		int		$id				ID Number
	 * @apiparam	int		version			If specified, will show a previous version of a file (see GET /bitracker/files/{id}/versions)
	 * @throws		2S303/1	INVALID_ID		The file ID does not exist
	 * @throws		2S303/1	INVALID_VERSION	The version ID does not exist
	 * @return		\IPS\bitracker\File
	 */
	public function GETitem( $id )
	{
		try
		{
			$file = \IPS\bitracker\File::load( $id );
			
			if ( isset( \IPS\Request::i()->version ) )
			{
				try
				{
					$backup = \IPS\Db::i()->select( '*', 'bitracker_filebackup', array( 'b_id=? AND b_fileid=?', \IPS\Request::i()->version, $file->id ) )->first();
					return new \IPS\Api\Response( 200, $file->apiOutput( $backup ) );
				}
				catch ( \UnderflowException $e )
				{
					throw new \IPS\Api\Exception( 'INVALID_VERSION', '2S303/6', 404 );
				}
			}
			else
			{
				return new \IPS\Api\Response( 200, $file->apiOutput() );
			}
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2S303/1', 404 );
		}
	}
	
	/**
	 * POST /bitracker/files
	 * Upload a file
	 *
	 * @reqapiparam	int					category		The ID number of the category the file should be created in
	 * @reqapiparam	int					author			The ID number of the member creating the file (0 for guest)
	 * @reqapiparam	string				title			The file name
	 * @reqapiparam	string				description		The description as HTML (e.g. "<p>This is an file.</p>")
	 * @apiparam	string				version			The version number
	 * @reqapiparam	object				files			Files. Keys should be filename (e.g. 'file.txt') and values should be file content
	 * @apiparam	object				screenshots		Screenshots. Keys should be filename (e.g. 'screenshot1.png') and values should be file content
	 * @apiparam	string				prefix			Prefix tag
	 * @apiparam	string				tags			Comma-separated list of tags (do not include prefix)
	 * @apiparam	datetime			date			The date/time that should be used for the file post date. If not provided, will use the current date/time
	 * @apiparam	string				ip_address		The IP address that should be stored for the file. If not provided, will use the IP address from the API request
	 * @apiparam	int					locked			1/0 indicating if the file should be locked
	 * @apiparam	int					hidden			0 = unhidden; 1 = hidden, pending moderator approval; -1 = hidden (as if hidden by a moderator)
	 * @apiparam	int					pinned			1/0 indicating if the file should be featured
	 * @apiparam	int					featured		1/0 indicating if the file should be featured
	 * @throws		1S303/7				NO_CATEGEORY	The category ID does not exist
	 * @throws		1S303/8				NO_AUTHOR		The author ID does not exist
	 * @throws		1S303/9				NO_TITLE		No title was supplied
	 * @throws		1S303/A				NO_DESC			No description was supplied
	 * @throws		1S303/B				NO_FILES		No files were supplied
	 * @return		\IPS\bitracker\File
	 */
	public function POSTindex()
	{
		/* Get category */
		try
		{
			$category = \IPS\bitracker\Category::load( \IPS\Request::i()->category );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'NO_CATEGEORY', '1S303/7', 400 );
		}
		
		/* Get author */
		if ( \IPS\Request::i()->author )
		{
			$author = \IPS\Member::load( \IPS\Request::i()->author );
			if ( !$author->member_id )
			{
				throw new \IPS\Api\Exception( 'NO_AUTHOR', '1S303/8', 400 );
			}
		}
		else
		{
			$author = new \IPS\Member;
		}
		
		/* Check we have a title and a description */
		if ( !\IPS\Request::i()->title )
		{
			throw new \IPS\Api\Exception( 'NO_TITLE', '1S303/9', 400 );
		}
		if ( !\IPS\Request::i()->description )
		{
			throw new \IPS\Api\Exception( 'NO_DESC', '1S303/A', 400 );
		}
		
		/* Validate files */
		if ( !isset( \IPS\Request::i()->files ) or !is_array( \IPS\Request::i()->files ) or empty( \IPS\Request::i()->files ) )
		{
			throw new \IPS\Api\Exception( 'NO_FILES', '1L296/B', 400 );
		}
		
		/* Create file record */
		$file = $this->_create( $category, $author );
				
		/* Save records */
		foreach ( \IPS\Request::i()->files as $name => $content )
		{
			$fileObject = \IPS\File::create( 'bitracker_Files', $name, $content );
			
			\IPS\Db::i()->insert( 'bitracker_files_records', array(
				'record_file_id'	=> $file->id,
				'record_type'		=> 'upload',
				'record_location'	=> (string) $fileObject,
				'record_realname'	=> $fileObject->originalFilename,
				'record_size'		=> $fileObject->filesize(),
				'record_time'		=> time(),
			) );
		}
		if ( isset( \IPS\Request::i()->nfo) )
		{
			foreach ( \IPS\Request::i()->nfo as $name => $content )
			{
				$fileObject = \IPS\File::create( 'bitracker_Nfo', $name, $content );
				
				\IPS\Db::i()->insert( 'bitracker_files_records', array(
					'record_file_id'		=> $file->id,
					'record_type'			=> 'nfoupload',
					'record_location'		=> (string) $fileObject,
					'record_realname'		=> $fileObject->originalFilename,
					'record_size'			=> \strlen( $fileObject->contents() ),
					'record_time'			=> time(),
				) );
			}
		}
		if ( isset( \IPS\Request::i()->screenshots ) )
		{
			$primary = 1;
			foreach ( \IPS\Request::i()->screenshots as $name => $content )
			{
				$fileObject = \IPS\File::create( 'bitracker_Screenshots', $name, $content );
				
				\IPS\Db::i()->insert( 'bitracker_files_records', array(
					'record_file_id'		=> $file->id,
					'record_type'			=> 'ssupload',
					'record_location'		=> (string) $fileObject,
					'record_thumb'			=> (string) $fileObject->thumbnail( 'bitracker_Screenshots' ),
					'record_realname'		=> $fileObject->originalFilename,
					'record_size'			=> \strlen( $fileObject->contents() ),
					'record_time'			=> time(),
					'record_no_watermark'	=> NULL,
					'record_default'		=> $primary
				) );
				
				$primary = 0;
			}
		}
		
		/* Recaluclate properties */
		$file = $this->_recalculate( $file );
		
		/* Return */
		$file->save();
		return new \IPS\Api\Response( 201, $file->apiOutput() );
	}
	
	/**
	 * POST /bitracker/files/{id}
	 * Edit a file
	 *
	 * @apiparam	int					category		The ID number of the category the file should be created in
	 * @apiparam	int					author			The ID number of the member creating the file (0 for guest)
	 * @apiparam	string				title			The file name
	 * @apiparam	string				description		The description as HTML (e.g. "<p>This is an file.</p>")
	 * @apiparam	string				prefix			Prefix tag
	 * @apiparam	string				tags			Comma-separated list of tags (do not include prefix)
	 * @apiparam	datetime			date			The date/time that should be used for the file post date. If not provided, will use the current date/time
	 * @apiparam	string				ip_address		The IP address that should be stored for the file. If not provided, will use the IP address from the API request
	 * @apiparam	int					locked			1/0 indicating if the file should be locked
	 * @apiparam	int					hidden			0 = unhidden; 1 = hidden, pending moderator approval; -1 = hidden (as if hidden by a moderator)
	 * @apiparam	int					featured		1/0 indicating if the file should be featured
	 * @throws		2S303/C				INVALID_ID		The file ID is invalid
	 * @throws		1S303/D				NO_CATEGORY		The category ID does not exist
	 * @throws		1S303/E				NO_AUTHOR		The author ID does not exist
	 * @return		\IPS\bitracker\File
	 */
	public function POSTitem( $id )
	{
		try
		{
			$file = \IPS\bitracker\File::load( $id );
			
			/* Move file to another category */
			if ( isset( \IPS\Request::i()->category ) and \IPS\Request::i()->category != $file->category_id )
			{
				try
				{
					$newCategory = \IPS\bitracker\Category::load( \IPS\Request::i()->category );
					$file->move( $newCategory );
				}
				catch ( \OutOfRangeException $e )
				{
					throw new \IPS\Api\Exception( 'NO_CATEGORY', '1S303/D', 400 );
				}
			}
			
			/* New author */
			if ( isset( \IPS\Request::i()->author ) )
			{				
				try
				{
					$member = \IPS\Member::load( \IPS\Request::i()->author );
					if ( !$member->member_id )
					{
						throw new \OutOfRangeException;
					}
					
					$file->changeAuthor( $member );
				}
				catch ( \OutOfRangeException $e )
				{
					throw new \IPS\Api\Exception( 'NO_AUTHOR', '1S303/E', 400 );
				}
			}
						
			/* Everything else */
			$this->_createOrUpdate( $file );
			
			/* Save and return */
			$file->save();
			return new \IPS\Api\Response( 200, $file->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2S303/C', 404 );
		}
	}
	
	/**
	 * Create or update file
	 *
	 * @param	\IPS\Content\Item	$item	The item
	 * @return	\IPS\Content\Item
	 */
	protected function _createOrUpdate( \IPS\Content\Item $item )
	{		
		/* Description */
		if ( isset( \IPS\Request::i()->description ) )
		{
			$item->desc = \IPS\Request::i()->description;
		}
		
		/* Version */
		if ( isset( \IPS\Request::i()->version ) )
		{
			$item->version = \IPS\Request::i()->version;
		}

		/* Changelog */
		if ( isset( \IPS\Request::i()->changelog ) )
		{
			$item->changelog = \IPS\Request::i()->changelog;
		}
		
		/* Pass up */
		return parent::_createOrUpdate( $item );
	}
	
	/**
	 * Recalculate stored properties
	 *
	 * @param	\IPS\bitracker\File	$file	The file
	 * @return	\IPS\bitracker\File
	 */
	protected function _recalculate( $file )
	{
		/* File size */
		$file->size = floatval( \IPS\Db::i()->select( 'SUM(record_size)', 'bitracker_files_records', array( 'record_file_id=? AND record_type=? AND record_backup=0', $file->id, 'upload' ) )->first() );
		
		/* Work out the new primary screenshot */
		try
		{
			$file->primary_screenshot = \IPS\Db::i()->select( 'record_id', 'bitracker_files_records', array( 'record_file_id=? AND ( record_type=? OR record_type=? ) AND record_backup=0', $file->id, 'ssupload', 'sslink' ), 'record_default DESC, record_id ASC' )->first();
		}
		catch ( \UnderflowException $e ) { }
		
		/* Return */
		return $file;
	}
	
	/**
	 * GET /bitracker/files/{id}/comments
	 * Get comments on an file
	 *
	 * @param		int		$id			ID Number
	 * @apiparam	int		hidden		If 1, only comments which are hidden are returned, if 0 only not hidden
	 * @apiparam	string	sortDir		Sort direction. Can be 'asc' or 'desc' - defaults to 'asc'
	 * @apiparam	int		page		Page number
	 * @throws		2S303/2	INVALID_ID	The file ID does not exist
	 * @return		\IPS\Api\PaginatedResponse<IPS\bitracker\File\Comment>
	 */
	public function GETitem_comments( $id )
	{
		try
		{
			return $this->_comments( $id, 'IPS\bitracker\File\Comment' );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2S303/2', 404 );
		}
	}
	
	/**
	 * GET /bitracker/files/{id}/reviews
	 * Get reviews on an file
	 *
	 * @param		int		$id			ID Number
	 * @apiparam	int		hidden		If 1, only comments which are hidden are returned, if 0 only not hidden
	 * @apiparam	string	sortDir		Sort direction. Can be 'asc' or 'desc' - defaults to 'asc'
	 * @apiparam	int		page		Page number
	 * @throws		2S303/3	INVALID_ID	The file ID does not exist
	 * @return		\IPS\Api\PaginatedResponse<IPS\bitracker\File\Review>
	 */
	public function GETitem_reviews( $id )
	{
		try
		{
			return $this->_comments( $id, 'IPS\bitracker\File\Review' );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2S303/3', 404 );
		}
	}
	
	/**
	 * GET /bitracker/files/{id}/history
	 * Get previous versions for a file
	 *
	 * @param		int		$id			ID Number
	 * @throws		2S303/4	INVALID_ID	The file ID does not exist
	 * @return		array
	 * @apiresponse	int		id			The version ID number (use to get more information about this version in GET /bitracker/files/{id})
	 * @apiresponse	string	version		The version number provided by the user
	 * @apiresponse	string	changelog	What was new in this version
	 * @apiresponse	bool	hidden		If this version is hidden
	 */
	public function GETitem_history( $id )
	{
		try
		{
			$file = \IPS\bitracker\File::load( $id );
			
			foreach ( \IPS\Db::i()->select( '*', 'bitracker_filebackup', array( 'b_fileid=?', $id ), 'b_backup DESC' ) as $backup )
			{
				$versions = array(
					'id'		=> $backup['b_id'],
					'version'	=> $backup['b_version'],
					'changelog'	=> $backup['b_changelog'],
					'hidden'	=> (bool) $backup['b_hidden'],
				);
			}
			
			return new \IPS\Api\Response( 200, $versions );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2S303/4', 404 );
		}
	}
	
	/**
	 * POST /bitracker/files/{id}/history
	 * Upload a new file version
	 *
	 * @apiparam	string				title			The file name
	 * @apiparam	string				description		The description as HTML (e.g. "<p>This is an file.</p>")
	 * @apiparam	string				version			The version number
	 * @apiparam	string				changelog		What changed in this version
	 * @apiparam	int					save			If 1 this will be saved as a new version and the previous version available in the history. If 0, will simply replace the existing files/screenshots. Defaults to 1 if versioning is enabled in the category.
	 * @reqapiparam	object				files			Files. Keys should be filename (e.g. 'file.txt') and values should be file content - will replace all current files
	 * @apiparam	object				screenshots		Screenshots. Keys should be filename (e.g. 'screenshot1.png') and values should be file content - will replace all current screenshots
	 * @throws		2S303/F				INVALID_ID		The file ID is invalid
	 * @throws		1S303/G				NO_FILES		No files were supplied
	 * @return		\IPS\bitracker\File
	 */
	public function POSTitem_history( $id )
	{
		try
		{
			/* Load file */
			$file = \IPS\bitracker\File::load( $id );
			$category = $file->container();
			
			/* Validate files */
			if ( !isset( \IPS\Request::i()->files ) or !is_array( \IPS\Request::i()->files ) or empty( \IPS\Request::i()->files ) )
			{
				throw new \IPS\Api\Exception( 'NO_FILES', '1L296/B', 400 );
			}
			
			/* Save current version? */
			if ( !isset( \IPS\Request::i()->save ) )
			{
				$save = (bool) $category->versioning !== 0;
			}
			else
			{
				$save = (bool) \IPS\Request::i()->save;
			}
			if ( $save )
			{
				$file->saveVersion();
			}
			else
			{
				foreach ( \IPS\Db::i()->select( 'record_location', 'bitracker_files_records', array( 'record_file_id=?', $file->id ) ) as $record )
				{
					if ( in_array( $record['record_type'], array( 'upload', 'ssupload' ) ) )
					{
						try
						{
							\IPS\File::get( $record['record_type'] == 'upload' ? 'bitracker_Files' : 'bitracker_Screenshots', $url )->delete();
						}
						catch ( \Exception $e ) { }
					}
				}
				\IPS\Db::i()->delete( 'bitracker_files_records', array( 'record_file_id=?', $file->id ) );
			}
			
			/* Insert the new records */
			foreach ( \IPS\Request::i()->files as $name => $content )
			{
				$fileObject = \IPS\File::create( 'bitracker_Files', $name, $content );
				
				\IPS\Db::i()->insert( 'bitracker_files_records', array(
					'record_file_id'	=> $file->id,
					'record_type'		=> 'upload',
					'record_location'	=> (string) $fileObject,
					'record_realname'	=> $fileObject->originalFilename,
					'record_size'		=> $fileObject->filesize(),
					'record_time'		=> time(),
				) );
			}
			if ( isset( \IPS\Request::i()->nfo ) )
			{
				foreach ( \IPS\Request::i()->nfo as $name => $content )
				{
					$fileObject = \IPS\File::create( 'bitracker_Nfo', $name, $content );
					
					\IPS\Db::i()->insert( 'bitracker_files_records', array(
						'record_file_id'		=> $file->id,
						'record_type'			=> 'nfoupload',
						'record_location'		=> (string) $fileObject,
						'record_realname'		=> $fileObject->originalFilename,
						'record_size'			=> \strlen( $fileObject->contents() ),
						'record_time'			=> time(),
					) );
				}
			} 
			if ( isset( \IPS\Request::i()->screenshots ) )
			{
				$primary = 1;
				foreach ( \IPS\Request::i()->screenshots as $name => $content )
				{
					$fileObject = \IPS\File::create( 'bitracker_Screenshots', $name, $content );
					
					\IPS\Db::i()->insert( 'bitracker_files_records', array(
						'record_file_id'		=> $file->id,
						'record_type'			=> 'ssupload',
						'record_location'		=> (string) $fileObject,
						'record_thumb'			=> (string) $fileObject->thumbnail( 'bitracker_Screenshots' ),
						'record_realname'		=> $fileObject->originalFilename,
						'record_size'			=> \strlen( $fileObject->contents() ),
						'record_time'			=> time(),
						'record_no_watermark'	=> NULL,
						'record_default'		=> $primary
					) );
					
					$primary = 0;
				}
			} 
			
			/* Update */
			$file = $this->_createOrUpdate( $file );
			$file = $this->_recalculate( $file );
			
			/* Save */
			$file->updated = time();
			$file->save();
			
			/* Send notifications */
			if ( $file->open )
			{
				$file->sendUpdateNotifications();
			}
			
			/* Return */
			return new \IPS\Api\Response( 200, $file->apiOutput() );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2S303/F', 404 );
		}
	}
	
	/**
	 * DELETE /bitracker/files/{id}
	 * Delete a file
	 *
	 * @param		int		$id			ID Number
	 * @throws		2S303/5	INVALID_ID	The file ID does not exist
	 * @return		void
	 */
	public function DELETEitem( $id )
	{
		try
		{
			\IPS\bitracker\File::load( $id )->delete();
			
			return new \IPS\Api\Response( 200, NULL );
		}
		catch ( \OutOfRangeException $e )
		{
			throw new \IPS\Api\Exception( 'INVALID_ID', '2S303/5', 404 );
		}
	}
}