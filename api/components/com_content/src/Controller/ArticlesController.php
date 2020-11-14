<?php
/**
 * @package     Joomla.API
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * The article controller
 *
 * @since  4.0.0
 */
class ArticlesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'articles';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'articles';

	/**
	 * Article list view amended to add filtering of data
	 *
	 * @return  static  A BaseController object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$apiFilterInfo = $this->input->get('filter', [], 'array');
		$filter        = InputFilter::getInstance();

		if (array_key_exists('author', $apiFilterInfo))
		{
			$this->modelState->set('filter.author_id', $filter->clean($apiFilterInfo['author'], 'INT'));
		}

		if (array_key_exists('category', $apiFilterInfo))
		{
			$this->modelState->set('filter.category_id', $filter->clean($apiFilterInfo['category'], 'INT'));
		}

		if (array_key_exists('search', $apiFilterInfo))
		{
			$this->modelState->set('filter.search', $filter->clean($apiFilterInfo['search'], 'STRING'));
		}

		if (array_key_exists('state', $apiFilterInfo))
		{
			$this->modelState->set('filter.published', $filter->clean($apiFilterInfo['state'], 'INT'));
		}

		if (array_key_exists('language', $apiFilterInfo))
		{
			$this->modelState->set('filter.language', $filter->clean($apiFilterInfo['language'], 'STRING'));
		}

		return parent::displayList();
	}

	/**
	 * Method to save a record.
	 *
	 * @param   integer  $recordKey  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	protected function save($recordKey = null)
	{
		$data = (array) json_decode($this->input->json->getRaw(), true);

		foreach (FieldsHelper::getFields('com_content.article') as $field)
		{
			if (isset($data[$field->name]))
			{
				!isset($data['com_fields']) && $data['com_fields'] = [];

				$data['com_fields'][$field->name] = $data[$field->name];
				unset($data[$field->name]);
			}
		}

		$this->input->set('data', $data);

		return parent::save($recordKey);
	}
}
