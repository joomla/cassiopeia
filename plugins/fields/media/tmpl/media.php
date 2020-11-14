<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

if ($field->value == '')
{
	return;
}

$class = $fieldParams->get('image_class');

if ($class)
{
	$class = ' class="' . htmlentities($class, ENT_COMPAT, 'UTF-8', true) . '"';
}

$value  = $field->value;

$buffer = '';

if ($value)
{
	$path = $value['imagefile'];
	$alt = empty($value['alt_text']) && empty($value['alt_empty']) ? '' : ' alt="' . htmlspecialchars($value['alt_text'], ENT_COMPAT, 'UTF-8') . '"';

	if (file_exists($path))
	{
		$buffer .= sprintf('<img loading="lazy" src="%s"%s%s>',
			htmlentities($path, ENT_COMPAT, 'UTF-8', true),
			$class,
			$alt
		);
	}
}

echo $buffer;
