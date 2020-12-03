<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

\defined('JPATH_PLATFORM') or die;

use Joomla\Event\DispatcherAwareInterface;

/**
 * Access to plugin specific services.
 *
 * @since  4.0.0
 */
interface PluginInterface extends DispatcherAwareInterface
{
	/**
	 * Registers its listeners.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function registerListeners();
}
