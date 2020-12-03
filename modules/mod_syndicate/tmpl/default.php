<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_syndicate
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<a href="<?php echo $link; ?>" class="mod-syndicate syndicate-module">
	<?php echo HTMLHelper::_('image', 'system/livemarks.png', '', null, true); ?>
	<?php $class = $params->get('display_text', 1) ? '' : 'class="sr-only"'; ?>
	<span <?php echo $class; ?>>
		<?php if (str_replace(' ', '', $text) !== '') : ?>
			<?php echo $text; ?>
		<?php else : ?>
			<?php echo Text::_('MOD_SYNDICATE_DEFAULT_FEED_ENTRIES'); ?>
		<?php endif; ?>
	</span>
</a>
