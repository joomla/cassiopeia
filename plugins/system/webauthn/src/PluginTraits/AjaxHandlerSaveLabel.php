<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

// Protect from unauthorized access
\defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Plugin\System\Webauthn\CredentialRepository;

/**
 * Ajax handler for akaction=savelabel
 *
 * Stores a new label for a security key
 *
 * @since   4.0.0
 */
trait AjaxHandlerSaveLabel
{
	/**
	 * Handle the callback to rename an authenticator
	 *
	 * @return  boolean
	 *
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	public function onAjaxWebauthnSavelabel(): bool
	{
		// Initialize objects
		/** @var CMSApplication $app */
		$app        = Factory::getApplication();
		$input      = $app->input;
		$repository = new CredentialRepository;

		// Retrieve data from the request
		$credentialId = $input->getBase64('credential_id', '');
		$newLabel     = $input->getString('new_label', '');

		// Is this a valid credential?
		if (empty($credentialId))
		{
			return false;
		}

		$credentialId = base64_decode($credentialId);

		if (empty($credentialId) || !$repository->has($credentialId))
		{
			return false;
		}

		// Make sure I am editing my own key
		try
		{
			$credentialHandle = $repository->getUserHandleFor($credentialId);
			$myHandle         = $repository->getHandleFromUserId($app->getIdentity()->id);
		}
		catch (Exception $e)
		{
			return false;
		}

		if ($credentialHandle !== $myHandle)
		{
			return false;
		}

		// Make sure the new label is not empty
		if (empty($newLabel))
		{
			return false;
		}

		// Save the new label
		try
		{
			$repository->setLabel($credentialId, $newLabel);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
}
