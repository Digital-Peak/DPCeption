<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\DPCalendar;

use DateTime;

trait EventTrait
{
	/**
	 * Creates an event in the database and returns the event data
	 * as array including the id of the new event.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function createEvent($data = null)
	{
		$event = [
			'title'       => 'Test Event',
			'alias'       => 'test-event',
			'uid'         => 'test',
			'start_date'  => (new DateTime('+2 hours'))->format('Y-m-d H:i:00'),
			'end_date'    => (new DateTime('+4 hours'))->format('Y-m-d H:i:00'),
			'catid'       => 8,
			'original_id' => 0,
			'state'       => 1,
			'language'    => '*',
			'price'       => '',
			'description' => '',
			'created'     => (new DateTime())->format('Y-m-d H:i:s'),
			'created_by'  => $this->grabFromDatabase('users', 'id', ['username' => 'admin'])
		];

		if (is_array($data)) {
			$event = array_merge($event, $data);
		}

		if (is_array($event['price'])) {
			$event['price']['label']       = $event['price']['label'] ?? [''];
			$event['price']['description'] = $event['price']['description'] ?? [''];
			$event['price']                = json_encode($event['price']);
		}

		if (isset($event['booking_options']) && is_array($event['booking_options'])) {
			$event['booking_options'] = json_encode($event['booking_options']);
		}

		$locationId = 0;
		if (!empty($event['location_id'])) {
			$locationId = $event['location_id'];
			unset($event['location_id']);
		}

		$hostIds = [];
		if (!empty($event['host_ids'])) {
			$hostIds = $event['host_ids'];
			unset($event['host_ids']);
		}

		$event['id'] = $this->haveInDatabase('dpcalendar_events', $event);

		if ($locationId) {
			$this->haveInDatabase('dpcalendar_events_location', ['event_id' => $event['id'], 'location_id' => $locationId]);
		}

		if ($hostIds) {
			foreach ($hostIds as $host) {
				$this->haveInDatabase('dpcalendar_events_hosts', ['event_id' => $event['id'], 'user_id' => $host]);
			}
		}

		return $event;
	}
}
