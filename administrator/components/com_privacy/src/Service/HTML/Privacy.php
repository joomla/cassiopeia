<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Privacy component HTML helper.
 *
 * @since  3.9.0
 */
class Privacy
{
	/**
	 * Render a status badge
	 *
	 * @param   integer  $status  The item status
	 *
	 * @return  string
	 *
	 * @since   3.9.0
	 */
	public function statusLabel($status)
	{
		switch ($status)
		{
			case 2:
				return '<span class="badge badge-success">' . Text::_('COM_PRIVACY_STATUS_COMPLETED') . '</span>';

			case 1:
				return '<span class="badge badge-info">' . Text::_('COM_PRIVACY_STATUS_CONFIRMED') . '</span>';

			case -1:
				return '<span class="badge badge-danger">' . Text::_('COM_PRIVACY_STATUS_INVALID') . '</span>';

			default:
			case 0:
				return '<span class="badge badge-warning">' . Text::_('COM_PRIVACY_STATUS_PENDING') . '</span>';
		}
	}
}
