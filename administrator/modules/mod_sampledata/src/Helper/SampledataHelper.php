<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Sampledata\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Helper for mod_sampledata
 *
 * @since  3.8.0
 */
abstract class SampledataHelper
{
	/**
	 * Get a list of sampledata.
	 *
	 * @return  mixed  An array of sampledata, or false on error.
	 *
	 * @since  3.8.0
	 */
	public static function getList()
	{
		PluginHelper::importPlugin('sampledata');

		return Factory::getApplication()->triggerEvent('onSampledataGetOverview', array('test', 'foo'));
	}
}
