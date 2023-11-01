<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\Joomla;

trait TaskTrait
{
	/**
	 * Creates a task in the database and returns the task data
	 * as array including the id of the new task.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function createTask($data = null)
	{
		$task = [
			'title'           => 'Test task',
			'execution_rules' => '{"rule-type":"manual"}',
			'cron_rules'      => '{"type":"manual"}',
			'type'            => '',
			'state'           => 1,
			'created'         => (new \DateTime())->format('Y-m-d H:i:s'),
			'created_by'      => $this->grabFromDatabase('users', 'id', ['username' => 'admin']),
			'params'          => ''
		];

		if (is_array($data)) {
			$task = array_merge($task, $data);
		}

		if (!empty($task['params'])) {
			$task['params'] = json_encode($task['params']);
		}

		$task['id'] = $this->haveInDatabase('scheduler_tasks', $task);

		return $task;
	}
}
