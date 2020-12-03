<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Banners\Administrator\View\Download\HtmlView $this */

HTMLHelper::_('behavior.formvalidator');

?>
<div class="container-popup">
	<form
		class="form-horizontal form-validate"
		id="download-form"
		name="adminForm"
		action="<?php echo Route::_('index.php?option=com_banners&task=tracks.display&format=raw'); ?>"
		method="post">

		<?php foreach ($this->form->getFieldset() as $field) : ?>
			<?php echo $this->form->renderField($field->fieldname); ?>
		<?php endforeach; ?>

		<button class="sr-only"
			id="exportBtn"
			type="button"
			onclick="this.form.submit();window.top.setTimeout('window.parent.Joomla.Modal.getCurrent().close()', 700);">
		</button>
	</form>
</div>
