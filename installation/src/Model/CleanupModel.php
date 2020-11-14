<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * Cleanup model for the Joomla Core Installer.
 *
 * @since  4.0.0
 */
class CleanupModel extends BaseInstallationModel
{
	/**
	 * Deletes the installation folder. Returns true on success.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function deleteInstallationFolder()
	{
		$return = Folder::delete(JPATH_INSTALLATION) && (!file_exists(JPATH_ROOT . '/joomla.xml') || File::delete(JPATH_ROOT . '/joomla.xml'));

		// Rename the robots.txt.dist file if robots.txt doesn't exist
		if ($return && !file_exists(JPATH_ROOT . '/robots.txt') && file_exists(JPATH_ROOT . '/robots.txt.dist'))
		{
			$return = File::move(JPATH_ROOT . '/robots.txt.dist', JPATH_ROOT . '/robots.txt');
		}

		return $return;
	}
}
