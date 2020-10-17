<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log\Logger;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger;

/**
 * Logger class that keeps all entries in memory
 *
 * @since  4.0.0
 */
class InMemoryLogger extends Logger
{
	/**
	 * List of collected log entries, grouped by $group
	 *
	 * @var array
	 * @since  4.0.0
	 */
	protected static $logEntries = [];

	/**
	 * Group name to store the entries
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $group = 'default';

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   4.0.0
	 */
	public function __construct(array &$options)
	{
		parent::__construct($options);

		if (!empty($this->options['group']))
		{
			$this->group = $this->options['group'];
		}
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   LogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function addEntry(LogEntry $entry)
	{
		static::$logEntries[$this->group][] = $entry;
	}

	/**
	 * Returns a list of collected entries.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getCollectedEntries()
	{
		if (empty(static::$logEntries[$this->group]))
		{
			return [];
		}

		return static::$logEntries[$this->group];
	}
}
