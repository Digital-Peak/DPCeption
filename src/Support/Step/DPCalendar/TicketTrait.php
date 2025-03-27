<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\DPCalendar;

trait TicketTrait
{
	/**
	 * Creates a ticket in the database and returns the ticket data
	 * as array including the id of the new ticket.
	 */
	public function createTicket(?array $data = null): array
	{
		$ticket = [
			'uid'        => 'TICKET-UID',
			'first_name' => 'John',
			'name'       => 'Doo',
			'email'      => 'john@example.com',
			'country'    => 'US',
			'province'   => 'Test County',
			'city'       => 'Test City',
			'zip'        => 'Test Zip',
			'street'     => 'Test Street',
			'number'     => 'Test Number',
			'telephone'  => '123',
			'latitude'   => 1,
			'longitude'  => 1,
			'created'    => (new \DateTime())->format('Y-m-d H:i:s'),
			'user_id'    => $this->grabFromDatabase('users', 'id', ['username' => 'admin'])
		];

		if (\is_array($data)) {
			$ticket = array_merge($ticket, $data);
		}

		$shortCode = $ticket['country'];
		if ($ticket['country']) {
			$ticket['country'] = $this->grabFromDatabase('dpcalendar_countries', 'id', ['short_code' => $ticket['country']]);
		}

		$ticket['id'] = $this->haveInDatabase('dpcalendar_tickets', $ticket);

		if ($shortCode) {
			$ticket['country_code'] = $shortCode;
		}

		if ($shortCode == 'US') {
			$ticket['country_code_value'] = 'United States';
		}

		return $ticket;
	}
}
