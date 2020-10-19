<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\View\Remove;

defined('_JEXEC') or die;

use Joomla\CMS\Installation\View\DefaultView;
use Joomla\CMS\Version;

/**
 * The HTML Joomla Core Install Remove View
 *
 * @since  3.1
 */
class HtmlView extends DefaultView
{
	/**
	 * Is the Joomla Version a development version?
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $development;

	/**
	 * List of language choices to install
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $items;

	/**
	 * Full list of recommended PHP Settings
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $phpsettings;

	/**
	 * Array of PHP config options
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $phpoptions;

	/**
	 * Array of PHP config options
	 *
	 * @var    \stdClass
	 * @since  4.0.0
	 */
	protected $installed_languages;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string|null  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   4.0.0
	 */
	public function display($tpl = null)
	{
		$this->development = (new Version)->isInDevelopmentState();

		$this->items = $this->get('Items', 'Languages');

		$this->installed_languages = new \stdClass;
		$this->installed_languages->administrator = $this->get('InstalledlangsAdministrator', 'Languages');
		$this->installed_languages->frontend = $this->get('InstalledlangsFrontend', 'Languages');

		$this->phpoptions = $this->get('PhpOptions', 'Checks');
		$this->phpsettings = $this->get('PhpSettings', 'Checks');

		return parent::display($tpl);
	}
}
