<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2025 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\Joomla;

trait ModuleTrait
{
	/**
	 * Creates a module in the database and returns the module data
	 * as array including the id of the new module.
	 */
	public function createModule(?array $data = null): array
	{
		$I = $this;

		$module = [
			'title'     => 'Module',
			'client_id' => 0,
			'published' => 1,
			'language'  => '*',
			'position'  => 'sidebar-right',
			'access'    => 1,
			'params'    => []
		];

		if (\is_array($data)) {
			$module = array_merge($module, $data);
		}

		if (!empty($module['params']['layout']) && !str_contains((string)$module['params']['layout'], '_:')) {
			$module['params']['layout'] = '_:' . $module['params']['layout'];
		}

		$module['params'] = json_encode($module['params']);

		$module['id'] = $I->haveInDatabase('modules', $module);

		$I->haveInDatabase('modules_menu', ['moduleid' => $module['id'], 'menuid' => 0]);

		return $module;
	}
}
