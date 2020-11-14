<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

$params  = $displayData->params;
$images  = json_decode($displayData->images);

?>
<?php if (!empty($images->image_intro)) : ?>
	<?php $imgclass = empty($images->float_intro) ? $params->get('float_intro') : $images->float_intro; ?>
	<?php $alt = empty($images->image_intro_alt) && empty($images->image_intro_alt_empty) ? '' : 'alt="'. htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8') .'"'; ?>
	<figure class="<?php echo htmlspecialchars($imgclass, ENT_COMPAT, 'UTF-8'); ?> item-image">
		<?php if ($params->get('link_intro_image') && ($params->get('access-view') || $params->get('show_noauth', '0') == '1')) : ?>
			<a href="<?php echo Route::_(
				RouteHelper::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)
				); ?>" itemprop="url" title="<?php echo $this->escape($displayData->title); ?>">
				<img loading="lazy" src="<?php echo htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8'); ?>"
					 <?php echo $alt; ?>
					 itemprop="thumbnailUrl"
				/>
			</a>
		<?php else : ?>
			<img loading="lazy" src="<?php echo htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8'); ?>"
				 <?php echo $alt; ?>
				 itemprop="thumbnailUrl"
			>
		<?php endif; ?>
		<?php if (isset($images->image_intro_caption) && $images->image_intro_caption !== '') : ?>
			<figcaption class="caption"><?php echo htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
		<?php endif; ?>
	</figure>
<?php endif; ?>
