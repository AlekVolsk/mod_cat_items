<?php defined( '_JEXEC' ) or die;
/*
 * @package     mod_cat_items
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov ( AlekVolsk ). All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Router\Route;

require_once JPATH_SITE . '/components/com_content/helpers/route.php';

abstract class ModCatItemsHelper
{
	public static function getList( &$params )
	{
		$app = Factory::getApplication();

		$related = [];
		
		$option = $app->input->get( 'option' );
		$view = $app->input->get( 'view' );
		$catid = $app->input->get( 'catid' );

		$temp = $app->input->getString( 'id' );
		$temp = explode( ':', $temp );
		$id = $temp[0];
		
		if ( $option == 'com_content' && $view == 'article' && $id )
		{
			$user = Factory::getUser();
			$groups = implode( ',', $user->getAuthorisedViewLevels() );
			$date = Factory::getDate();
			$db = Factory::getDbo();

			$nullDate = $db->getNullDate();
			$now = $date->toSql();
			$query = $db->getQuery( true );
	
			$maximum = ( int ) $params->get( 'maximum', 5 );
			$query->clear()
				->select( 'a.id' )
				->select( 'a.title' )
				->select( 'DATE(a.created) as created' )
				->select( 'a.catid' )
				->select( 'a.images' )
				->select( 'a.language' )
				->select( 'cc.access AS cat_access' )
				->select( 'cc.published AS cat_state' );

			$a_id = $query->castAsChar( 'a.id' );
			$case_when = ' CASE WHEN ' . $query->charLength( 'a.alias', '!=', '0' ) . 
				' THEN ' . $query->concatenate( [ $a_id, 'a.alias' ], ':' ) .
				' ELSE ' . $a_id . ' END as slug';
			$query->select( $case_when );

			$c_id = $query->castAsChar( 'cc.id' );
			$case_when = ' CASE WHEN ' . $query->charLength( 'cc.alias', '!=', '0' ) .
				' THEN ' . $query->concatenate( [ $c_id, 'cc.alias' ], ':' ) .
				' ELSE ' . $c_id . ' END as catslug';
			$query->select( $case_when );

			$query
				->from( '#__content AS a' )
				->join( 'LEFT', '#__content_frontpage AS f ON f.content_id = a.id' )
				->join( 'LEFT', '#__categories AS cc ON cc.id = a.catid' )
				->where( 'a.id != ' . ( int ) $id )
				->where( 'a.catid = ' . ( int ) $catid )
				->where( 'a.state = 1' )
				->where( 'a.access IN ( ' . $groups . ' )' )
				->where( '(a.publish_up = ' . $db->quote( $nullDate ) . ' OR a.publish_up <= ' . $db->quote( $now ) . ')' )
				->where( '(a.publish_down = ' . $db->quote( $nullDate ) . ' OR a.publish_down >= ' . $db->quote( $now ) . ')' );

			if ( Multilanguage::isEnabled() )
			{
				$query->where( 'a.language in (' . $db->quote( Factory::getLanguage()->getTag() ) . ',' . $db->quote( '*' ) . ')' );
			}
				
			$query->order( 'a.hits desc,a.id desc' );

			$db->setQuery( $query, 0, $maximum );
			try
			{
				$temp = $db->loadObjectList();
			}
			catch ( RuntimeException $e )
			{
				Factory::getApplication()->enqueueMessage( Text::_( 'JERROR_AN_ERROR_HAS_OCCURRED' ), 'error' );
				return;
			}

			if ( isset( $temp ) && count( $temp ) )
			{
				foreach ( $temp as $row )
				{
					if ( $row->cat_state == 1 )
					{
						$row->route = Route::_( ContentHelperRoute::getArticleRoute( $row->slug, $row->catid, $row->language ) );
						$row->images = json_decode( $row->images );
						$related[] = $row;
					}
				}
			}

			unset( $temp );
		}

		return $related;
	}
}
