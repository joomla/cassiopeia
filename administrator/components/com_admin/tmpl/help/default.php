<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Help\Help;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Admin\Administrator\View\Help\HtmlView $this */

?>
<form action="<?php echo Route::_('index.php?option=com_admin&amp;view=help'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row mt-sm-3">
		<div id="sidebar" class="col-md-3">
			<button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-toggle="collapse" data-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false">
				 <span class="icon-align-justify" aria-hidden="true"></span>
				 <?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
			</button>
			<div class="sidebar-nav" id="help-index">
				<ul class="nav flex-column">
					<li><?php echo HTMLHelper::_('link', Help::createUrl('JHELP_START_HERE'), Text::_('COM_ADMIN_START_HERE'), ['target' => 'helpFrame']); ?></li>
					<li><?php echo HTMLHelper::_('link', 'https://www.gnu.org/licenses/gpl-2.0.html', Text::_('COM_ADMIN_LICENSE'), ['target' => 'helpFrame']); ?></li>
					<li><?php echo HTMLHelper::_('link', Help::createUrl('JHELP_GLOSSARY'), Text::_('COM_ADMIN_GLOSSARY'), ['target' => 'helpFrame']); ?></li>
					<li class="divider"></li>
					<li class="nav-header"><?php echo Text::_('COM_ADMIN_ALPHABETICAL_INDEX'); ?></li>
					<?php foreach ($this->toc as $k => $v) : ?>
						<li>
							<?php $url = Help::createUrl('JHELP_' . strtoupper($k)); ?>
							<?php echo HTMLHelper::_('link', $url, $v, ['target' => 'helpFrame']); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="col-md-9">
			<iframe onLoad="var x = getElementById('help-index'); x.classList.remove('show');" name="helpFrame" title="helpFrame" height="2100px" src="<?php echo $this->page; ?>" class="helpFrame table table-bordered"></iframe>
		</div>
	</div>
	<input class="textarea" type="hidden" name="option" value="com_admin">
</form>
