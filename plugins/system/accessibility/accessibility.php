<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.accessibility
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * System plugin to add additional accessibility features to the administrator interface.
 *
 * @since  4.0.0
 */
class PlgSystemAccessibility extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Add the javascript for the accessibility menu
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onBeforeCompileHead()
	{
		$section = $this->params->get('section', 'administrator');

		if ($section !== 'both' && $this->app->isClient($section) !== true)
		{
			return;
		}

		// Get the document object.
		$document = $this->app->getDocument();

		if ($document->getType() !== 'html')
		{
			return;
		}

		// Load language file.
		$this->loadLanguage();

		// Determine if it is an LTR or RTL language
		$direction = Factory::getLanguage()->isRTL() ? 'right' : 'left';

		/**
		* Add strings for translations in Javascript.
		* Reference  https://ranbuch.github.io/accessibility/
		*/
		$document->addScriptOptions(
			'accessibility-options',
			[
				'labels' => [
					'menuTitle'           => Text::_('PLG_SYSTEM_ACCESSIBILITY_MENU_TITLE'),
					'increaseText'        => Text::_('PLG_SYSTEM_ACCESSIBILITY_INCREASE_TEXT'),
					'decreaseText'        => Text::_('PLG_SYSTEM_ACCESSIBILITY_DECREASE_TEXT'),
					'increaseTextSpacing' => Text::_('PLG_SYSTEM_ACCESSIBILITY_INCREASE_SPACING'),
					'decreaseTextSpacing' => Text::_('PLG_SYSTEM_ACCESSIBILITY_DECREASE_SPACING'),
					'invertColors'        => Text::_('PLG_SYSTEM_ACCESSIBILITY_INVERT_COLORS'),
					'grayHues'            => Text::_('PLG_SYSTEM_ACCESSIBILITY_GREY'),
					'underlineLinks'      => Text::_('PLG_SYSTEM_ACCESSIBILITY_UNDERLINE'),
					'bigCursor'           => Text::_('PLG_SYSTEM_ACCESSIBILITY_CURSOR'),
					'readingGuide'        => Text::_('PLG_SYSTEM_ACCESSIBILITY_READING'),
					'textToSpeech'        => Text::_('PLG_SYSTEM_ACCESSIBILITY_TTS'),
					'speechToText'        => Text::_('PLG_SYSTEM_ACCESSIBILITY_STT'),
				],
				'icon' => [
					'position' => [
						$direction => [
							'size' => '0',
							'units' => 'px',
						]
					]
				]
			]
		);

		$document->getWebAssetManager()
			->useScript('accessibility')
			->addInlineScript(
				'window.addEventListener("load", function() {'
				. 'new Accessibility(Joomla.getOptions("accessibility-options") || {});'
				. '});',
				['name' => 'inline.plg.system.accessibility'],
				['type' => 'module'],
				['accessibility']
			);
	}
}
