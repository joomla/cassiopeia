<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.Blog
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Database\ParameterType;

/**
 * Sampledata - Blog Plugin
 *
 * @since  3.8.0
 */
class PlgSampledataBlog extends CMSPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 *
	 * @since  3.8.0
	 */
	protected $db;

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 *
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 *
	 * @since  3.8.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Holds the menuitem model
	 *
	 * @var    MenusModelItem
	 *
	 * @since  3.8.0
	 */
	private $menuItemModel;

	/**
	 * Get an overview of the proposed sampledata.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  3.8.0
	 */
	public function onSampledataGetOverview()
	{
		if (!Factory::getUser()->authorise('core.create', 'com_content'))
		{
			return;
		}

		$data              = new stdClass;
		$data->name        = $this->_name;
		$data->title       = Text::_('PLG_SAMPLEDATA_BLOG_OVERVIEW_TITLE');
		$data->description = Text::_('PLG_SAMPLEDATA_BLOG_OVERVIEW_DESC');
		$data->icon        = 'wifi';
		$data->steps       = 4;

		return $data;
	}

	/**
	 * First step to enter the sampledata. Content.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 *
	 * @since  3.8.0
	 */
	public function onAjaxSampledataApplyStep1()
	{
		if (!Session::checkToken('get') || $this->app->input->get('type') != $this->_name)
		{
			return;
		}

		if (!ComponentHelper::isEnabled('com_content') || !Factory::getUser()->authorise('core.create', 'com_content')
			|| !ComponentHelper::isEnabled('com_workflow') || !Factory::getUser()->authorise('core.create', 'com_workflow'))
		{
			$response            = array();
			$response['success'] = true;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_SKIPPED', 1, 'com_content');

			return $response;
		}

		// Get some metadata.
		$access = (int) $this->app->get('access', 1);
		$user   = Factory::getUser();

		// Detect language to be used.
		$language   = Multilanguage::isEnabled() ? Factory::getLanguage()->getTag() : '*';
		$langSuffix = ($language !== '*') ? ' (' . $language . ')' : '';

		// Create workflow
		$workflowTable = new \Joomla\Component\Workflow\Administrator\Table\WorkflowTable($this->db);

		$workflowTable->default = 0;
		$workflowTable->title = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_SAMPLE_TITLE');
		$workflowTable->description = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_SAMPLE_DESCRIPTION');
		$workflowTable->published = 1;
		$workflowTable->access = $access;
		$workflowTable->created_user_id = $user->id;
		$workflowTable->extension = 'com_content.article';

		if (!$workflowTable->store())
		{
			Factory::getLanguage()->load('com_content');
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, Text::_($stageTable->getError()));

			return $response;
		}

		// Get ID from workflow we just added
		$workflowId = $workflowTable->id;

		// Create Stages.
		for ($i = 1; $i <= 9; $i++)
		{
			$stageTable = new \Joomla\Component\Workflow\Administrator\Table\StageTable($this->db);

			// Set values from language strings.
			$stageTable->title  = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE' . $i . '_TITLE');
			$stageTable->description = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE' . $i . '_DESCRIPTION');

			// Set values which are always the same.
			$stageTable->id = 0;
			$stageTable->published = 1;
			$stageTable->ordering = 0;
			$stageTable->default = $i == 1 ? 1 : 0;
			$stageTable->workflow_id = $workflowId;

			if (!$stageTable->store())
			{
				Factory::getLanguage()->load('com_content');
				$response            = array();
				$response['success'] = false;
				$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, Text::_($stageTable->getError()));

				return $response;
			}
		}

		// Get the stage Ids of the new stages
		$query = $this->db->getQuery(true);

		$query->select([$this->db->quoteName('title'), $this->db->quoteName('id')])
			->from($this->db->quoteName('#__workflow_stages'))
			->where($this->db->quoteName('workflow_id') . ' = :workflow_id')
			->bind(':workflow_id', $workflowId, ParameterType::INTEGER);

		$stages = $this->db->setQuery($query)->loadAssocList('title', 'id');

		// Prepare Transitions

		$defaultOptions = json_encode(
			[
				'publishing' => 0,
				'featuring' => 0,
				'notification_send_mail' => false,
			]
		);

		$fromTo = array(
			array(
				// Idea to Copywriting
				'from_stage_id' => $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE1_TITLE')],
				'to_stage_id' 	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE2_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// Copywriting to Graphic Design
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE2_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE3_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// Graphic Design to Fact Check
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE3_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE4_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// Fact Check to Review
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE4_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE5_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// Edit article - revision to copy writer
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE5_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE2_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// Revision to published and featured
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE5_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE6_TITLE')],
				'options' => json_encode(
					array(
						'publishing'  => 1,
						'featuring' => 1,
						'notification_send_mail' => true,
						'notification_text' => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE6_TEXT'),
						'notification_groups' => ["7"]
					)
				),
			),
			array(
				// All to on Hold
				'from_stage_id'	=> -1,
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE7_TITLE')],
				'options' => json_encode(
					array(
						'publishing'  => 2,
						'featuring' => 0,
						'notification_send_mail' => false,
					)
				),
			),
			array(
				// Idea to trash
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE1_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE8_TITLE')],
				'options' => json_encode(
					array(
						'publishing'  => -2,
						'featuring' => 0,
						'notification_send_mail' => false,
					)
				),
			),
			array(
				// On Hold to Idea (Re-activate an idea)
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE7_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE1_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// Unpublish a published article
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE6_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE9_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// Trash a published article
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE6_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE8_TITLE')],
				'options' => $defaultOptions,
			),
			array(
				// From unpublished back to published
				'from_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE9_TITLE')],
				'to_stage_id'	=> $stages[Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE6_TITLE')],
				'options' => json_encode(
					array(
						'publishing'  => 1,
						'featuring' => 0,
						'notification_send_mail' => true,
						'notification_text' => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_STAGE6_TEXT'),
						'notification_groups' => ["7"]
					)
				),
			),
		);

		// Create Transitions.
		for ($i = 0; $i < count($fromTo); $i++)
		{
			$trTable = new \Joomla\Component\Workflow\Administrator\Table\TransitionTable($this->db);

			$trTable->from_stage_id = $fromTo[$i]['from_stage_id'];
			$trTable->to_stage_id = $fromTo[$i]['to_stage_id'];
			$trTable->options = $fromTo[$i]['options'];

			// Set values from language strings.
			$trTable->title = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_TRANSITION' . ($i + 1) . '_TITLE');
			$trTable->description = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_WORKFLOW_TRANSITION' . ($i + 1) . '_DESCRIPTION');

			// Set values which are always the same.
			$trTable->id = 0;
			$trTable->published = 1;
			$trTable->ordering = 0;
			$trTable->workflow_id = $workflowId;

			if (!$trTable->store())
			{
				Factory::getLanguage()->load('com_content');
				$response            = array();
				$response['success'] = false;
				$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, Text::_($trTable->getError()));

				return $response;
			}
		}

		// Create "blog" category.
		$categoryModel = $this->app->bootComponent('com_categories')
			->getMVCFactory()->createModel('Category', 'Administrator');
		$catIds        = array();
		$categoryTitle = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_CATEGORY_0_TITLE');
		$alias         = ApplicationHelper::stringURLSafe($categoryTitle);

		// Set unicodeslugs if alias is empty
		if (trim(str_replace('-', '', $alias) == ''))
		{
			$unicode = $this->app->set('unicodeslugs', 1);
			$alias = ApplicationHelper::stringURLSafe($categoryTitle);
			$this->app->set('unicodeslugs', $unicode);
		}

		$category      = array(
			'title'           => $categoryTitle . $langSuffix,
			'parent_id'       => 1,
			'id'              => 0,
			'published'       => 1,
			'access'          => $access,
			'created_user_id' => $user->id,
			'extension'       => 'com_content',
			'level'           => 1,
			'alias'           => $alias . $langSuffix,
			'associations'    => array(),
			'description'     => '',
			'language'        => $language,
			'params'          => '{"workflow_id":"' . $workflowId . '"}',
		);

		try
		{
			if (!$categoryModel->save($category))
			{
				throw new Exception($categoryModel->getError());
			}
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, $e->getMessage());

			return $response;
		}

		// Get ID from category we just added
		$catIds[] = $categoryModel->getItem()->id;

		// Create "help" category.
		$categoryTitle = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_CATEGORY_1_TITLE');
		$alias         = ApplicationHelper::stringURLSafe($categoryTitle);

		// Set unicodeslugs if alias is empty
		if (trim(str_replace('-', '', $alias) == ''))
		{
			$unicode = $this->app->set('unicodeslugs', 1);
			$alias = ApplicationHelper::stringURLSafe($categoryTitle);
			$this->app->set('unicodeslugs', $unicode);
		}

		$category      = array(
			'title'           => $categoryTitle . $langSuffix,
			'parent_id'       => 1,
			'id'              => 0,
			'published'       => 1,
			'access'          => $access,
			'created_user_id' => $user->id,
			'extension'       => 'com_content',
			'level'           => 1,
			'alias'           => $alias . $langSuffix,
			'associations'    => array(),
			'description'     => '',
			'language'        => $language,
			'params'          => '{}',
		);

		try
		{
			if (!$categoryModel->save($category))
			{
				throw new Exception($categoryModel->getError());
			}
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, $e->getMessage());

			return $response;
		}

		// Get ID from category we just added
		$catIds[] = $categoryModel->getItem()->id;

		// Create "template" category.
		$categoryTitle = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_CATEGORY_2_TITLE');
		$alias         = ApplicationHelper::stringURLSafe($categoryTitle);

		// Set unicodeslugs if alias is empty
		if (trim(str_replace('-', '', $alias) == ''))
		{
			$unicode = $this->app->set('unicodeslugs', 1);
			$alias = ApplicationHelper::stringURLSafe($categoryTitle);
			$this->app->set('unicodeslugs', $unicode);
		}

		$category      = array(
			'title'           => $categoryTitle . $langSuffix,
			'parent_id'       => 1,
			'id'              => 0,
			'published'       => 1,
			'access'          => $access,
			'created_user_id' => $user->id,
			'extension'       => 'com_content',
			'level'           => 1,
			'alias'           => $alias . $langSuffix,
			'associations'    => array(),
			'description'     => '',
			'language'        => $language,
			'params'          => '{}',
		);

		try
		{
			if (!$categoryModel->save($category))
			{
				throw new Exception($categoryModel->getError());
			}
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, $e->getMessage());

			return $response;
		}

		// Get ID from category we just added
		$catIds[] = $categoryModel->getItem()->id;

		// Create Articles.
		$articles     = array(

			// Category 1 = Help
			array(
				// Article 0 - About
				'catid'    => $catIds[1],
				'ordering' => 2,
				'state'    => 1,
			),
			array(
				// Article 1 - Working on Your Site
				'catid'    => $catIds[1],
				'ordering' => 1,
				'access'   => 3,
				'state'    => 1,
			),

			// Category 0 = Blog
			array(
				// Article 2 - Welcome to your blog
				'catid'    => $catIds[0],
				'ordering' => 2,
				'state'    => 0,
				'images'   => array(
					'show_page_heading'      => '1',
					'image_intro'            => 'images/sampledata/cassiopeia/rocket-1000.jpg',
					'float_intro'            => 'left',
					'image_intro_alt'        => '',
					'image_intro_caption'    => '',
					'image_fulltext'         => '',
					'float_fulltext'         => '',
					'image_fulltext_alt'     => '',
					'image_fulltext_caption' => ''
				)
			),
			array(
				// Article 3 - About your home page
				'catid'    => $catIds[0],
				'ordering' => 1,
				'state'    => 0,
				'images'   => array(
					'show_page_heading'      => '1',
					'image_intro'            => 'images/sampledata/cassiopeia/rocket-1000.jpg',
					'float_intro'            => 'right',
					'image_intro_alt'        => '',
					'image_intro_caption'    => '',
					'image_fulltext'         => '',
					'float_fulltext'         => '',
					'image_fulltext_alt'     => '',
					'image_fulltext_caption' => ''
				)
			),
			array(
				// Article 4 - Your Modules
				'catid'    => $catIds[0],
				'ordering' => 0,
				'state'    => 0,
				'images'   => array(
					'show_page_heading'      => '1',
					'image_intro'            => 'images/sampledata/cassiopeia/rocket-1000.jpg',
					'float_intro'            => 'left',
					'image_intro_alt'        => '',
					'image_intro_caption'    => '',
					'image_fulltext'         => '',
					'float_fulltext'         => '',
					'image_fulltext_alt'     => '',
					'image_fulltext_caption' => ''
				)
			),
			array(
				// Article 5 - Your Template
				'catid'    => $catIds[0],
				'ordering' => 0,
				'state'    => 0,
			),

			// Category 2 = Joomla - marketing texts
			array(
				// Article 6 - Millions
				'catid'    => $catIds[2],
				'ordering' => 0,
				'state'    => 1,
				'images'   => array(
					'show_page_heading'      => '1',
					'image_intro'            => 'images/sampledata/cassiopeia/blue-rocket-400.jpg',
					'float_intro'            => '',
					'image_intro_alt'        => '',
					'image_intro_caption'    => '',
					'image_fulltext'         => '',
					'float_fulltext'         => '',
					'image_fulltext_alt'     => '',
					'image_fulltext_caption' => ''
				)
			),
			array(
				// Article 7 - Love
				'catid'    => $catIds[2],
				'ordering' => 0,
				'state'    => 1,
				'images'   => array(
					'show_page_heading'      => '1',
					'image_intro'            => 'images/sampledata/cassiopeia/rocket-400.jpg',
					'float_intro'            => '',
					'image_intro_alt'        => '',
					'image_intro_caption'    => '',
					'image_fulltext'         => '',
					'float_fulltext'         => '',
					'image_fulltext_alt'     => '',
					'image_fulltext_caption' => ''
				)
			),
			array(
				// Article 8 - Joomla
				'catid'    => $catIds[2],
				'ordering' => 0,
				'state'    => 1,
				'images'   => array(
					'show_page_heading'      => '1',
					'image_intro'            => 'images/sampledata/cassiopeia/blue-rocket-400.jpg',
					'float_intro'            => '',
					'image_intro_alt'        => '',
					'image_intro_caption'    => '',
					'image_fulltext'         => '',
					'float_fulltext'         => '',
					'image_fulltext_alt'     => '',
					'image_fulltext_caption' => ''
				)
			),
		);

		$mvcFactory = $this->app->bootComponent('com_content')->getMVCFactory();

		// Set com_workflow enabled for com_content
		$params = ComponentHelper::getParams('com_content');
		$params->set('workflow_enabled', '1');

		$query = $this->db->getQuery(true);

		$query->update($this->db->quoteName('#__extensions'))
			->set($this->db->quoteName('params') . '=' . $this->db->quote(json_encode($params)))
			->where($this->db->quoteName('name') . '=' . $this->db->quote('com_content'));

		$this->db->setQuery($query)->execute();

		// Store the articles
		foreach ($articles as $i => $article)
		{
			$articleModel = $mvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);

			// Set values from language strings.
			$title                = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_' . $i . '_TITLE');
			$alias                = ApplicationHelper::stringURLSafe($title);
			$article['title']     = $title . $langSuffix;
			$article['introtext'] = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_' . $i . '_INTROTEXT');
			$article['fulltext']  = Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_' . $i . '_FULLTEXT');

			// Set values which are always the same.
			$article['id']              = 0;
			$article['created_user_id'] = $user->id;
			$article['alias']           = ApplicationHelper::stringURLSafe($article['title']);

			// Set unicodeslugs if alias is empty
			if (trim(str_replace('-', '', $alias) == ''))
			{
				$unicode = $this->app->set('unicodeslugs', 1);
				$article['alias'] = ApplicationHelper::stringURLSafe($article['title']);
				$this->app->set('unicodeslugs', $unicode);
			}

			$article['language']        = $language;
			$article['associations']    = array();
			$article['metakey']         = '';
			$article['metadesc']        = '';

			if (!isset($article['state']))
			{
				$article['state']  = 1;
			}

			if (!isset($article['featured']))
			{
				$article['featured']  = 0;
			}

			if (!isset($article['images']))
			{
				$article['images']  = '';
			}

			if (!isset($article['access']))
			{
				$article['access'] = $access;
			}

			if (!$articleModel->save($article))
			{
				Factory::getLanguage()->load('com_content');
				$response            = array();
				$response['success'] = false;
				$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 1, Text::_($articleModel->getError()));

				return $response;
			}

			// Get ID from article we just added
			$ids[] = $articleModel->getItem()->id;
		}

		$this->app->setUserState('sampledata.blog.articles', $ids);
		$this->app->setUserState('sampledata.blog.articles.catids', $catIds);

		$response          = new stdClass;
		$response->success = true;
		$response->message = Text::_('PLG_SAMPLEDATA_BLOG_STEP1_SUCCESS');

		return $response;
	}

	/**
	 * Second step to enter the sampledata. Menus.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 *
	 * @since  3.8.0
	 */
	public function onAjaxSampledataApplyStep2()
	{
		if (!Session::checkToken('get') || $this->app->input->get('type') != $this->_name)
		{
			return;
		}

		if (!ComponentHelper::isEnabled('com_menus') || !Factory::getUser()->authorise('core.create', 'com_menus'))
		{
			$response            = array();
			$response['success'] = true;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_SKIPPED', 2, 'com_menus');

			return $response;
		}

		// Detect language to be used.
		$language   = Multilanguage::isEnabled() ? Factory::getLanguage()->getTag() : '*';
		$langSuffix = ($language !== '*') ? ' (' . $language . ')' : '';

		// Create the menu types.
		$menuTable = new \Joomla\Component\Menus\Administrator\Table\MenuTypeTable($this->db);
		$menuTypes = array();

		for ($i = 0; $i <= 2; $i++)
		{
			$menu = array(
				'id'          => 0,
				'title'       => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_MENU_' . $i . '_TITLE') . $langSuffix,
				'description' => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_MENU_' . $i . '_DESCRIPTION'),
			);

			// Calculate menutype. The number of characters allowed is 24.
			$type = HTMLHelper::_('string.truncate', $menu['title'], 23, true, false);

			$menu['menutype'] = $i . $type;

			try
			{
				$menuTable->load();
				$menuTable->bind($menu);

				if (!$menuTable->check())
				{
					throw new Exception($menuTable->getError());
				}

				$menuTable->store();
			}
			catch (Exception $e)
			{
				Factory::getLanguage()->load('com_menus');
				$response            = array();
				$response['success'] = false;
				$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

				return $response;
			}

			$menuTypes[] = $menuTable->menutype;
		}

		// Storing IDs in UserState for later usage.
		$this->app->setUserState('sampledata.blog.menutypes', $menuTypes);

		// Get previously entered Data from UserStates.
		$articleIds = $this->app->getUserState('sampledata.blog.articles');

		// Get MenuItemModel.
		$this->menuItemModel = new \Joomla\Component\Menus\Administrator\Model\ItemModel;

		// Get previously entered categories ids
		$catids = $this->app->getUserState('sampledata.blog.articles.catids');

		// Insert menuitems level 1.
		$menuItems = array(
			array(
				// Blog
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_0_TITLE'),
				'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $catids[0],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'layout_type'             => 'blog',
					'show-titles'             => 1,
					'link-titles'             => 0,
					'show_category_title'     => 0,
					'num_leading_articles'    => 4,
					'num_intro_articles'      => 0,
					'num_links'               => 2,
					'orderby_sec'             => 'rdate',
					'order_date'              => 'published',
					'blog_class_leading'      => 'boxed columns-1',
					'show_pagination'         => 2,
					'show_pagination_results' => 1,
					'show_category'           => 0,
					'info_bloc_position'      => 0,
					'show_publish_date'       => 0,
					'show_hits'               => 0,
					'show_feed_link'          => 1,
					'menu_text'               => 1,
					'show_page_heading'       => 1,
					'secure'                  => 0,
				),
			),
			array(
				// About
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_1_TITLE'),
				'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[0],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'show_title'          => 0,
					'info_block_position' => 0,
					'show_category'       => 0,
					'link_category'       => 0,
					'show_author'         => 0,
					'show_create_date'    => 0,
					'show_publish_date'   => 0,
					'show_hits'           => 0,
					'menu_text'           => 1,
					'show_page_heading'   => 1,
					'secure'              => 0,
				),
			),
			array(
				// Author Login
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_2_TITLE'),
				'link'         => 'index.php?option=com_users&view=login',
				'component_id' => ExtensionHelper::getExtensionRecord('com_users', 'component')->extension_id,
				'params'       => array(
					'show_title'             => 0,
					'logindescription_show'  => 1,
					'logoutdescription_show' => 1,
					'menu_text'              => 1,
					'show_page_heading'      => 1,
					'secure'                 => 0,
				),
			),
			array(
				// Sample metismenu (heading)
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_11_TITLE'),
				'type'         => 'heading',
				'link'         => '',
				'component_id' => 0,
				'params'       => array(
					'layout_type'             => 'heading',
					'menu_text'               => 1,
					'show_page_heading'       => 0,
					'secure'                  => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_3_TITLE'),
				'link'         => 'index.php?option=com_content&view=form&layout=edit',
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'access'       => 3,
				'params'       => array(
					'enable_category'   => 1,
					'catid'             => $catids[0],
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_4_TITLE'),
				'link'         => 'index.php?option=com_content&view=article&id=' . $articleIds[1],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_5_TITLE'),
				'link'         => 'administrator',
				'type'         => 'url',
				'component_id' => 0,
				'browserNav'   => 1,
				'access'       => 3,
				'params'       => array(
					'menu_text' => 1,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_6_TITLE'),
				'link'         => 'index.php?option=com_users&view=profile&layout=edit',
				'component_id' => ExtensionHelper::getExtensionRecord('com_users', 'component')->extension_id,
				'access'       => 2,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 1,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_7_TITLE'),
				'link'         => 'index.php?option=com_users&view=login',
				'component_id' => ExtensionHelper::getExtensionRecord('com_users', 'component')->extension_id,
				'params'       => array(
					'logindescription_show'  => 1,
					'logoutdescription_show' => 1,
					'menu_text'              => 1,
					'show_page_heading'      => 1,
					'secure'                 => 0,
				),
			),
		);

		try
		{
			$menuIdsLevel1 = $this->addMenuItems($menuItems, 1);
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

			return $response;
		}

		// Insert level 1 (Link in the footer as alias)
		$menuItems = array(
			array(
				'menutype'     => $menuTypes[2],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_8_TITLE'),
				'link'         => 'index.php?Itemid=',
				'type'         => 'alias',
				'params'       => array(
					'aliasoptions'      => $menuIdsLevel1[2],
					'alias_redirect'    => 0,
					'menu-anchor_title' => '',
					'menu-anchor_css'   => '',
					'menu_image'        => '',
					'menu_image_css'    => '',
					'menu_text'         => 1,
					'menu_show'         => 1,
					'secure'            => 0,
				),
			),
		);

		try
		{
			$menuIdsLevel1 = array_merge($menuIdsLevel1, $this->addMenuItems($menuItems, 1));
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

			return $response;
		}

		// Insert menuitems level 2.
		$menuItems = array(
			array(
				'menutype'     => $menuTypes[1],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_9_TITLE'),
				'link'         => 'index.php?option=com_config&view=config',
				'parent_id'    => $menuIdsLevel1[5],
				'component_id' => ExtensionHelper::getExtensionRecord('com_config', 'component')->extension_id,
				'access'       => 6,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[1],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_10_TITLE'),
				'link'         => 'index.php?option=com_config&view=templates',
				'parent_id'    => $menuIdsLevel1[5],
				'component_id' => ExtensionHelper::getExtensionRecord('com_config', 'component')->extension_id,
				'params'       => array(
					'menu_text'         => 1,
					'show_page_heading' => 0,
					'secure'            => 0,
				),
			),
			array(
				// Blog
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_0_TITLE'),
				'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $catids[0],
				'parent_id'    => $menuIdsLevel1[3],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'layout_type'             => 'blog',
					'link_titles'             => 0,
					'show_category_title'     => 0,
					'num_leading_articles'    => 1,
					'num_intro_articles'      => 2,
					'num_links'               => 2,
					'orderby_sec'             => 'front',
					'order_date'              => 'published',
					'blog_class_leading'      => 'boxed columns-1',
					'blog_class'              => 'columns-2',
					'show_pagination'         => 2,
					'show_pagination_results' => 1,
					'show_category'           => 0,
					'info_bloc_position'      => 0,
					'show_publish_date'       => 0,
					'show_hits'               => 0,
					'show_feed_link'          => 0,
					'menu_text'               => 1,
					'show_page_heading'       => 1,
					'secure'                  => 0,
				),
			),
			array(
				// Category List
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_12_TITLE'),
				'link'         => 'index.php?option=com_content&view=category&layout=list&id=' . $catids[0],
				'parent_id'    => $menuIdsLevel1[3],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'page_subheading'	=> 'Subheading of List',
					'menu_text'         => 1,
					'show_page_heading' => 1,
					'secure'            => 0,
				),
			),
			array(
				// Articles (menu header)
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MENUS_ITEM_13_TITLE'),
				'type'         => 'heading',
				'link'         => '',
				'component_id' => 0,
				'parent_id'    => $menuIdsLevel1[3],
				'params'       => array(
					'layout_type'             => 'heading',
					'menu_text'               => 1,
					'show_page_heading'       => 1,
					'secure'                  => 0,
				),
			)
		);

		try
		{
			$menuIdsLevel2 = $this->addMenuItems($menuItems, 2);
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

			return $response;
		}

		// Add a third level of menuItems - use article title also for menuItem title
		$menuItems = array(
			array(
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_6_TITLE'),
				'link'         => 'index.php?option=com_content&view=article&id=&id=' . (int) $articleIds[6],
				'parent_id'    => $menuIdsLevel2[4],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'menu_show' => 1,
					'secure'    => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_7_TITLE'),
				'link'         => 'index.php?option=com_content&view=article&id=&id=' . (int) $articleIds[7],
				'parent_id'    => $menuIdsLevel2[4],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'menu_show' => 1,
					'secure'    => 0,
				),
			),
			array(
				'menutype'     => $menuTypes[0],
				'title'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_CONTENT_ARTICLE_8_TITLE'),
				'link'         => 'index.php?option=com_content&view=article&id=' . (int) $articleIds[8],
				'parent_id'    => $menuIdsLevel2[4],
				'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
				'params'       => array(
					'menu_show' => 1,
					'secure'    => 0,
				),
			),
		);

		try
		{
			$this->addMenuItems($menuItems, 3);
		}
		catch (Exception $e)
		{
			$response            = array();
			$response['success'] = false;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 2, $e->getMessage());

			return $response;
		}
		$response            = array();
		$response['success'] = true;
		$response['message'] = Text::_('PLG_SAMPLEDATA_BLOG_STEP2_SUCCESS');

		return $response;
	}

	/**
	 * Third step to enter the sampledata. Modules.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 *
	 * @since  3.8.0
	 */
	public function onAjaxSampledataApplyStep3()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken('get') || $this->app->input->get('type') != $this->_name)
		{
			return;
		}

		if (!ComponentHelper::isEnabled('com_modules') || !Factory::getUser()->authorise('core.create', 'com_modules'))
		{
			$response            = array();
			$response['success'] = true;
			$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_SKIPPED', 3, 'com_modules');

			return $response;
		}

		// Detect language to be used.
		$language   = Multilanguage::isEnabled() ? Factory::getLanguage()->getTag() : '*';
		$langSuffix = ($language !== '*') ? ' (' . $language . ')' : '';

		// Add Include Paths.
		$model  = new \Joomla\Component\Modules\Administrator\Model\ModuleModel;
		$access = (int) $this->app->get('access', 1);

		// Get previously entered Data from UserStates
		$menuTypes = $this->app->getUserState('sampledata.blog.menutypes');

		$catids = $this->app->getUserState('sampledata.blog.articles.catids');

		$modules = array(
			array(
				// The main menu Blog
				'title'     => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_0_TITLE'),
				'ordering'  => 1,
				'position'  => 'menu',
				'module'    => 'mod_menu',
				'showtitle' => 0,
				'params'    => array(
					'menutype'        => $menuTypes[0],
					'layout'          => 'cassiopeia:metismenu',
					'startLevel'      => 1,
					'endLevel'        => 3,
					'showAllChildren' => 1,
					'class_sfx'       => '',
					'cache'           => 1,
					'cache_time'      => 900,
					'cachemode'       => 'itemid',
					'module_tag'      => 'nav',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				// The author Menu, for registered users
				'title'     => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_1_TITLE'),
				'ordering'  => 1,
				'position'  => 'sidebar-right',
				'module'    => 'mod_menu',
				'access'    => 3,
				'showtitle' => 0,
				'params'    => array(
					'menutype'        => $menuTypes[1],
					'startLevel'      => 1,
					'endLevel'        => 0,
					'showAllChildren' => 1,
					'class_sfx'       => '',
					'layout'          => '_:default',
					'cache'           => 1,
					'cache_time'      => 900,
					'cachemode'       => 'itemid',
					'module_tag'      => 'aside',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				// Syndication
				'title'     => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_2_TITLE'),
				'ordering'  => 6,
				'position'  => 'sidebar-right',
				'module'    => 'mod_syndicate',
				'showtitle' => 0,
				'params'    => array(
					'display_text' => 1,
					'text'         => 'My Blog',
					'format'       => 'rss',
					'layout'       => '_:default',
					'cache'        => 0,
					'module_tag'   => 'section',
				),
			),
			array(
				// Archived Articles
				'title'    => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_3_TITLE'),
				'ordering' => 4,
				'position' => 'sidebar-right',
				'module'   => 'mod_articles_archive',
				'params'   => array(
					'count'      => 10,
					'layout'     => '_:default',
					'cache'      => 1,
					'cache_time' => 900,
					'module_tag' => 'div',
					'cachemode'  => 'static',
				),
			),
			array(
				// Latest Posts
				'title'      => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_4_TITLE'),
				'ordering'   => 6,
				'position'   => 'top-a',
				'module'     => 'mod_articles_news',
				// Assignment 1 means here - only on the homepage
				'assignment' => 1,
				'showtitle'  => 0,
				'params'   => array(
					'catid'             => $catids[2],
					'image'             => 1,
					'img_intro_full'    => 'intro',
					'item_title'        => 0,
					'link_titles'       => '',
					'item_heading'      => 'h4',
					'triggerevents'     => 1,
					'showLastSeparator' => 1,
					'show_introtext'    => 1,
					'readmore'          => 1,
					'count'             => 3,
					'show_featured'     => '',
					'exclude_current'   => 0,
					'ordering'          => 'a.publish_up',
					'direction'         => 1,
					'layout'            => '_:horizontal',
					'moduleclass_sfx'   => '',
					'cache'             => 1,
					'cache_time'        => 900,
					'cachemode'         => 'itemid',
					'style'             => 'Cassiopeia-noCard',
					'module_tag'        => 'div',
					'bootstrap_size'    => '0',
					'header_tag'        => 'h3',
					'header_class'      => ''
				),
			),
			array(
				// Older Posts (from category 0 = blog)
				'title'    => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_5_TITLE'),
				'ordering' => 2,
				'position' => 'sidebar-right',
				'module'   => 'mod_articles_category',
				'params'   => array(
					'mode'                         => 'normal',
					'show_on_article_page'         => 0,
					'show_front'                   => 'show',
					'count'                        => 6,
					'category_filtering_type'      => 1,
					'catid'                        => $catids[0],
					'show_child_category_articles' => 0,
					'levels'                       => 1,
					'author_filtering_type'        => 1,
					'author_alias_filtering_type'  => 1,
					'date_filtering'               => 'off',
					'date_field'                   => 'a.created',
					'relative_date'                => 30,
					'article_ordering'             => 'a.created',
					'article_ordering_direction'   => 'DESC',
					'article_grouping'             => 'none',
					'article_grouping_direction'   => 'krsort',
					'month_year_format'            => 'F Y',
					'item_heading'                 => 5,
					'link_titles'                  => 1,
					'show_date'                    => 0,
					'show_date_field'              => 'created',
					'show_date_format'             => Text::_('DATE_FORMAT_LC5'),
					'show_category'                => 0,
					'show_hits'                    => 0,
					'show_author'                  => 0,
					'show_introtext'               => 0,
					'introtext_limit'              => 100,
					'show_readmore'                => 0,
					'show_readmore_title'          => 1,
					'readmore_limit'               => 15,
					'layout'                       => '_:default',
					'owncache'                     => 1,
					'cache_time'                   => 900,
					'module_tag'                   => 'div',
					'bootstrap_size'               => 0,
					'header_tag'                   => 'h3',
					'style'                        => 0,
				),
			),
			array(
				// Bottom Menu
				'title'     => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_6_TITLE'),
				'ordering'  => 1,
				'position'  => 'footer',
				'module'    => 'mod_menu',
				'showtitle' => 0,
				'params'    => array(
					'menutype'        => $menuTypes[2],
					'startLevel'      => 1,
					'endLevel'        => 0,
					'showAllChildren' => 0,
					'layout'          => '_:default',
					'cache'           => 1,
					'cache_time'      => 900,
					'cachemode'       => 'itemid',
					'module_tag'      => 'div',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				// Search
				'title'    => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_7_TITLE'),
				'ordering' => 1,
				'position' => 'search',
				'module'   => 'mod_finder',
				'params'   => array(
					'searchfilter'     => '',
					'show_autosuggest' => 1,
					'show_advanced'    => 0,
					'show_label'       => 0,
					'alt_label'        => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_7_TITLE'),
					'show_button'      => 1,
					'opensearch'       => 1,
					'opensearch_name'  => '',
					'set_itemid'       => 0,
					'layout'           => '_:default',
					'module_tag'       => 'search',
				),
			),
			array(
				// Header image
				'title'      => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_8_TITLE'),
				'content'    => '<p>' . Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_8_CONTENT') . '</p>',
				'ordering'   => 1,
				'position'   => 'banner',
				'module'     => 'mod_custom',
				// Assignment 1 means here - only on the homepage
				'assignment' => 1,
				'showtitle'  => 0,
				'params'     => array(
					'prepare_content' => 0,
					'backgroundimage' => 'images/banners/banner.jpg',
					'layout'          => 'cassiopeia:banner',
					'moduleclass_sfx' => '',
					'cache'           => 1,
					'cache_time'      => 900,
					'cachemode'       => 'static',
					'style'           => '0',
					'module_tag'      => 'div',
					'bootstrap_size'  => '0',
					'header_tag'      => 'h3',
					'header_class'    => ''
				),
			),
			array(
				// Popular Tags ( but there are no tags )
				'title'    => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_9_TITLE'),
				'ordering' => 1,
				'position' => 'sidebar-right',
				'module'   => 'mod_tags_popular',
				'params'   => array(
					'maximum'         => 8,
					'timeframe'       => 'alltime',
					'order_value'     => 'count',
					'order_direction' => 1,
					'display_count'   => 0,
					'no_results_text' => 0,
					'minsize'         => 1,
					'maxsize'         => 2,
					'layout'          => '_:default',
					'owncache'        => 1,
					'module_tag'      => 'aside',
					'bootstrap_size'  => 0,
					'header_tag'      => 'h3',
					'style'           => 0,
				),
			),
			array(
				// Similiar Items
				'title'    => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_10_TITLE'),
				'ordering' => 0,
				'position' => '',
				'module'   => 'mod_tags_similar',
				'params'   => array(
					'maximum'        => 5,
					'matchtype'      => 'any',
					'layout'         => '_:default',
					'owncache'       => 1,
					'module_tag'     => 'div',
					'bootstrap_size' => 0,
					'header_tag'     => 'h3',
					'style'          => 0,
				),
			),
			array(
				// Backend - Site Information
				'title'     => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_11_TITLE'),
				'ordering'  => 4,
				'position'  => 'cpanel',
				'module'    => 'mod_stats_admin',
				'access'    => 6,
				'client_id' => 1,
				'params'    => array(
					'serverinfo'     => 1,
					'siteinfo'       => 1,
					'counter'        => 0,
					'increase'       => 0,
					'layout'         => '_:default',
					'cache'          => 1,
					'cache_time'     => 900,
					'cachemode'      => 'static',
					'module_tag'     => 'div',
					'bootstrap_size' => 6,
					'header_tag'     => 'h3',
					'style'          => 0,
				),
			),
			array(
				// Backend - Release News
				'title'     => Text::_('PLG_SAMPLEDATA_BLOG_SAMPLEDATA_MODULES_MODULE_12_TITLE'),
				'ordering'  => 1,
				'position'  => 'postinstall',
				'module'    => 'mod_feed',
				'client_id' => 1,
				'params'    => array(
					'rssurl'         => 'https://www.joomla.org/announcements/release-news.feed',
					'rssrtl'         => 0,
					'rsstitle'       => 1,
					'rssdesc'        => 1,
					'rssimage'       => 1,
					'rssitems'       => 3,
					'rssitemdesc'    => 1,
					'word_count'     => 0,
					'layout'         => '_:default',
					'cache'          => 1,
					'cache_time'     => 900,
					'module_tag'     => 'div',
					'bootstrap_size' => 0,
					'header_tag'     => 'h3',
					'style'          => 0,
				),
			),
		);

		foreach ($modules as $module)
		{
			// Append language suffix to title.
			$module['title'] .= $langSuffix;

			// Set values which are always the same.
			$module['id']         = 0;
			$module['asset_id']   = 0;
			$module['language']   = $language;
			$module['note']       = '';
			$module['published']  = 1;

			if (!isset($module['assignment']))
			{
				$module['assignment'] = 0;
			}
			else
			{
				// Assignment means always "only on the homepage".
				if (Multilanguage::isEnabled())
				{
					$homes = Multilanguage::getSiteHomePages();

					if (isset($homes[$language]))
					{
						$home = $homes[$language]->id;
					}
				}

				if (!isset($home))
				{
					$home = $app->getMenu('site')->getDefault()->id;
				}

				$module['assigned'] = [$home];
			}

			if (!isset($module['content']))
			{
				$module['content'] = '';
			}

			if (!isset($module['access']))
			{
				$module['access'] = $access;
			}

			if (!isset($module['showtitle']))
			{
				$module['showtitle'] = 1;
			}

			if (!isset($module['client_id']))
			{
				$module['client_id'] = 0;
			}

			if (!$model->save($module))
			{
				Factory::getLanguage()->load('com_modules');
				$response            = array();
				$response['success'] = false;
				$response['message'] = Text::sprintf('PLG_SAMPLEDATA_BLOG_STEP_FAILED', 3, Text::_($model->getError()));

				return $response;
			}
		}

		$response            = array();
		$response['success'] = true;
		$response['message'] = Text::_('PLG_SAMPLEDATA_BLOG_STEP3_SUCCESS');

		return $response;
	}

	/**
	 * Final step to show completion of sampledata.
	 *
	 * @return  array or void  Will be converted into the JSON response to the module.
	 *
	 * @since  4.0.0
	 */
	public function onAjaxSampledataApplyStep4()
	{
		if ($this->app->input->get('type') != $this->_name)
		{
			return;
		}

		$response['success'] = true;
		$response['message'] = Text::_('PLG_SAMPLEDATA_BLOG_STEP4_SUCCESS');

		return $response;
	}

	/**
	 * Adds menuitems.
	 *
	 * @param   array    $menuItems  Array holding the menuitems arrays.
	 * @param   integer  $level      Level in the category tree.
	 *
	 * @return  array  IDs of the inserted menuitems.
	 *
	 * @since  3.8.0
	 *
	 * @throws  Exception
	 */
	private function addMenuItems(array $menuItems, $level)
	{
		$itemIds = array();
		$access  = (int) $this->app->get('access', 1);
		$user    = Factory::getUser();
		$app     = Factory::getApplication();

		// Detect language to be used.
		$language   = Multilanguage::isEnabled() ? Factory::getLanguage()->getTag() : '*';
		$langSuffix = ($language !== '*') ? ' (' . $language . ')' : '';

		foreach ($menuItems as $menuItem)
		{
			// Reset item.id in model state.
			$this->menuItemModel->setState('item.id', 0);

			// Set values which are always the same.
			$menuItem['id']              = 0;
			$menuItem['created_user_id'] = $user->id;
			$menuItem['alias']           = ApplicationHelper::stringURLSafe($menuItem['title']);

			// Set unicodeslugs if alias is empty
			if (trim(str_replace('-', '', $menuItem['alias']) == ''))
			{
				$unicode = $app->set('unicodeslugs', 1);
				$menuItem['alias'] = ApplicationHelper::stringURLSafe($menuItem['title']);
				$app->set('unicodeslugs', $unicode);
			}

			// Append language suffix to title.
			$menuItem['title'] .= $langSuffix;

			$menuItem['published']       = 1;
			$menuItem['language']        = $language;
			$menuItem['note']            = '';
			$menuItem['img']             = '';
			$menuItem['associations']    = array();
			$menuItem['client_id']       = 0;
			$menuItem['level']           = $level;
			$menuItem['home']            = 0;

			// Set pageheading = title to enforce h1 headings
			if (isset($menuItem['show_page_heading']) && $menuItem['show_page_heading'])
			{
				$menuItem['page_heading'] = $menuItem['title'];
			}

			// Set browserNav to default if not set
			if (!isset($menuItem['browserNav']))
			{
				$menuItem['browserNav'] = 0;
			}

			// Set access to default if not set
			if (!isset($menuItem['access']))
			{
				$menuItem['access'] = $access;
			}

			// Set type to 'component' if not set
			if (!isset($menuItem['type']))
			{
				$menuItem['type'] = 'component';
			}

			// Set template_style_id to global if not set
			if (!isset($menuItem['template_style_id']))
			{
				$menuItem['template_style_id'] = 0;
			}

			// Set parent_id to root (1) if not set
			if (!isset($menuItem['parent_id']))
			{
				$menuItem['parent_id'] = 1;
			}

			if (!$this->menuItemModel->save($menuItem))
			{
				// Try two times with another alias (-1 and -2).
				$menuItem['alias'] .= '-1';

				if (!$this->menuItemModel->save($menuItem))
				{
					$menuItem['alias'] = substr_replace($menuItem['alias'], '2', -1);

					if (!$this->menuItemModel->save($menuItem))
					{
						throw new Exception($menuItem['title'] . ' => ' . $menuItem['alias'] . ' : ' . $this->menuItemModel->getError());
					}
				}
			}

			// Get ID from menuitem we just added
			$itemIds[] = $this->menuItemModel->getstate('item.id');
		}

		return $itemIds;
	}
}
