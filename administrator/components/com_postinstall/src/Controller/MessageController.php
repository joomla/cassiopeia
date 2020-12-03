<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Postinstall\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Postinstall\Administrator\Helper\PostinstallHelper;
use Joomla\Component\Postinstall\Administrator\Model\MessagesModel;

/**
 * Postinstall message controller.
 *
 * @since  3.2
 */
class MessageController extends BaseController
{
	/**
	 * Resets all post-installation messages of the specified extension.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function reset()
	{
		$this->checkToken();

		/** @var MessagesModel $model */
		$model = $this->getModel('Messages', '', array('ignore_request' => true));

		$eid = (int) $model->getState('eid', $model->getJoomlaFilesExtensionId());

		if (empty($eid))
		{
			$eid = $model->getJoomlaFilesExtensionId();
		}

		$model->resetMessages($eid);

		$this->setRedirect('index.php?option=com_postinstall&eid=' . $eid);
	}

	/**
	 * Unpublishes post-installation message of the specified extension.
	 *
	 * @return   void
	 *
	 * @since   3.2
	 */
	public function unpublish()
	{
		$model = $this->getModel('Messages', '', array('ignore_request' => true));

		$id = $this->input->get('id');

		$eid = (int) $model->getState('eid', $model->getJoomlaFilesExtensionId());

		if (empty($eid))
		{
			$eid = $model->getJoomlaFilesExtensionId();
		}

		$model->setState('published', 0);
		$model->unpublishMessage($id);

		$this->setRedirect('index.php?option=com_postinstall&eid=' . $eid);
	}

	/**
	 * Executes the action associated with an item.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function action()
	{
		$this->checkToken();

		$model = $this->getModel('Messages', '', array('ignore_request' => true));

		$id = $this->input->get('id');

		$item = $model->getItem($id);

		switch ($item->type)
		{
			case 'link':
				$this->setRedirect($item->action);

				return;

				break;

			case 'action':
				$helper = new PostinstallHelper;
				$file = $helper->parsePath($item->action_file);

				if (File::exists($file))
				{
					require_once $file;

					call_user_func($item->action);
				}
				break;

			case 'message':
			default:
				break;
		}

		$this->setRedirect('index.php?option=com_postinstall');
	}

	/**
	 * Hides all post-installation messages of the specified extension.
	 *
	 * @return  void
	 *
	 * @since   3.8.7
	 */
	public function hideAll()
	{
		$this->checkToken();

		/** @var MessagesModel $model */
		$model = $this->getModel('Messages', '', array('ignore_request' => true));
		$eid = (int) $model->getState('eid', $model->getJoomlaFilesExtensionId());

		if (empty($eid))
		{
			$eid = $model->getJoomlaFilesExtensionId();
		}

		$model->hideMessages($eid);
		$this->setRedirect('index.php?option=com_postinstall&eid=' . $eid);
	}
}
