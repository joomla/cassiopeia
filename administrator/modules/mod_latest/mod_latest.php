<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\Latest\Administrator\Helper\LatestHelper;

$model = $app->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Administrator', ['ignore_request' => true]);
$list = LatestHelper::getList($params, $model);

if ($params->get('automatic_title', 0))
{
	$module->title = LatestHelper::getTitle($params);
}

require ModuleHelper::getLayoutPath('mod_latest', $params->get('layout', 'default'));
