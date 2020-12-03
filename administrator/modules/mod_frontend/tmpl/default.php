<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_frontend
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>
<div class="header-item-content">
	<a class="d-flex" href="<?php echo Uri::root(); ?>"
		title="<?php echo Text::sprintf('MOD_FRONTEND_PREVIEW', $sitename); ?>"
		target="_blank">
		<div class="d-flex align-items-end mx-auto">
			<span class="icon-external-link-alt" aria-hidden="true"></span>
		</div>
		<div class="align-items-center tiny">
			<?php echo HTMLHelper::_('string.truncate', $sitename, 28, false, false); ?>
		</div>
	</a>
</div>
