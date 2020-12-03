<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

/**
 * Report table
 *
 * @since  4.0.0
 */
class ReportTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseInterface  $db  Database driver object.
	 *
	 * @since   4.0.0
	 */
	public function __construct(DatabaseInterface $db)
	{
		parent::__construct('#__csp', 'id', $db);
	}
}
