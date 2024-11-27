<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\Joomla;

trait MenuTrait
{
	/**
	 * Creates a menu item in the database and returns the menu item data
	 * as array including the id of the new menu item.
	 */
	public function createMenuItem(string $url, string $title, array $params = []): array
	{
		$I = $this;

		$parts = parse_url($url);
		parse_str($parts['query'], $query);

		$component = $query['component'] ?? 'content';

		$menuItem = [
			'title'        => $title,
			'alias'        => strtolower(str_replace(' ', '-', (string)$title)),
			'published'    => 1,
			'menutype'     => 'mainmenu',
			'type'         => 'component',
			'link'         => $url,
			'params'       => json_encode($params),
			'language'     => '*',
			'component_id' => $I->grabFromDatabase('extensions', 'extension_id', ['name' => 'com_' . $component]),
			'access'       => 1,
			'path'         => $component,
			'parent_id'    => 1,
			'level'        => 1,
			'img'          => ''
		];

		$menuItem['id'] = $I->haveInDatabase('menu', $menuItem);

		return $menuItem;
	}
}
