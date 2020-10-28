<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Help;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Admin\Administrator\Model\HelpModel;

/**
 * HTML View class for the Admin component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The search string
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $helpSearch = null;

	/**
	 * The page to be viewed
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $page = null;

	/**
	 * The iso language tag
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $languageTag = null;

	/**
	 * Table of contents
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $toc = [];

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null): void
	{
		/** @var HelpModel $model */
		$model                    = $this->getModel();
		$this->helpSearch         = $model->getHelpSearch();
		$this->page               = $model->getPage();
		$this->toc                = $model->getToc();
		$this->languageTag        = $model->getLangTag();

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		ToolbarHelper::title(Text::_('COM_ADMIN_HELP'), 'support help_header');
	}
}
