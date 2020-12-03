<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Component\Installer\Administrator\Model\ManageModel;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Installer Manage Controller
 *
 * @since  1.6
 */
class ManageController extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since  1.6
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$this->registerTask('unpublish', 'publish');
		$this->registerTask('publish',   'publish');
	}

	/**
	 * Enable/Disable an extension (if supported).
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6
	 */
	public function publish()
	{
		// Check for request forgeries.
		$this->checkToken();

		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('publish' => 1, 'unpublish' => 0);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			$this->setMessage(Text::_('COM_INSTALLER_ERROR_NO_EXTENSIONS_SELECTED'), 'warning');
		}
		else
		{
			/** @var ManageModel $model */
			$model = $this->getModel('manage');

			// Change the state of the records.
			if (!$model->publish($ids, $value))
			{
				$this->setMessage(implode('<br>', $model->getErrors()), 'warning');
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_PUBLISHED';
				}
				else
				{
					$ntext = 'COM_INSTALLER_N_EXTENSIONS_UNPUBLISHED';
				}

				$this->setMessage(Text::plural($ntext, count($ids)));
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Remove an extension (Uninstall).
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5
	 */
	public function remove()
	{
		// Check for request forgeries.
		$this->checkToken();

		/** @var ManageModel $model */
		$model = $this->getModel('manage');

		$eid = $this->input->get('cid', array(), 'array');
		$eid = ArrayHelper::toInteger($eid, array());
		$model->remove($eid);
		$this->setRedirect(Route::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Refreshes the cached metadata about an extension.
	 *
	 * Useful for debugging and testing purposes when the XML file might change.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function refresh()
	{
		// Check for request forgeries.
		$this->checkToken();

		/** @var ManageModel $model */
		$model = $this->getModel('manage');

		$uid = $this->input->get('cid', array(), 'array');
		$uid = ArrayHelper::toInteger($uid, array());
		$model->refresh($uid);
		$this->setRedirect(Route::_('index.php?option=com_installer&view=manage', false));
	}

	/**
	 * Load the changelog for a given extension.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function loadChangelog()
	{
		/** @var ManageModel $model */
		$model = $this->getModel('manage');

		$eid    = $this->input->get('eid', 0, 'int');
		$source = $this->input->get('source', 'manage', 'string');

		if (!$eid)
		{
			return;
		}

		$output = $model->loadChangelog($eid, $source);

		echo (new JsonResponse($output));
	}
}
