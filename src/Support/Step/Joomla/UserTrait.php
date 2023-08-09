<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\Joomla;

use DateTime;

trait UserTrait
{
	/**
	 * Creates a user in the database and returns the user data
	 * as array including the id of the new user.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function createUser($data = null)
	{
		$user = [
			'name'         => 'Test Manager',
			'username'     => 'test',
			'registerDate' => (new DateTime())->format('Y-m-d H:i:s'),
			'params'       => ''
		];

		if (is_array($data)) {
			$user = array_merge($user, $data);
		}

		$user['id'] = $this->haveInDatabase('users', $user);

		return $user;
	}
}
