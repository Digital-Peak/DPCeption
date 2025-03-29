<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\Joomla\Administrator;

class TasksPage
{
	public static string $url = '/administrator/index.php?option=com_scheduler&view=tasks';

	public static string $runButtonLabel   = 'Run Test';
	public static string $completedMessage = 'Status: Completed';
}
