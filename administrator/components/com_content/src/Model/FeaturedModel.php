<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

\defined('_JEXEC') or die;

/**
 * Methods supporting a list of featured article records.
 *
 * @since  1.6
 */
class FeaturedModel extends ArticlesModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \Joomla\CMS\MVC\Controller\BaseController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'featured_up', 'fp.featured_up',
				'featured_down', 'fp.featured_down',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'fp.ordering',
				'published', 'a.published',
				'author_id',
				'category_id',
				'level',
				'tag',
				'rating_count', 'rating',
				'ws.title',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		parent::populateState($ordering, $direction);

		// Filter by featured articles.
		$this->setState('filter.featured', 1);
	}
}
