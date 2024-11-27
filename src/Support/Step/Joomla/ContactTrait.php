<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\Joomla;

trait ContactTrait
{
	/**
	 * Creates a contact in the database and returns the contact data
	 * as array including the id of the new contact.
	 */
	public function createContact(?array $data = null): array
	{
		$contact = [
			'name'      => 'Test Manager',
			'alias'     => 'test-manager',
			'user_id'   => 43,
			'catid'     => '4',
			'published' => 1,
			'access'    => 1,
			'language'  => '*',
			'metakey'   => '',
			'metadesc'  => '',
			'metadata'  => '',
			'params'    => '',
			'created'   => (new \DateTime())->format('Y-m-d H:i:s'),
			'modified'  => (new \DateTime())->format('Y-m-d H:i:s')
		];

		if (\is_array($data)) {
			$contact = array_merge($contact, $data);
		}

		$contact['id'] = $this->haveInDatabase('contact_details', $contact);

		return $contact;
	}
}
