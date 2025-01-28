<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\DPCalendar;

trait EventTrait
{
	/**
	 * Creates an event in the database and returns the event data
	 * as array including the id of the new event.
	 */
	public function createEvent(?array $data = null): array
	{
		$event = [
			'title'       => 'Test Event',
			'alias'       => 'test-event',
			'uid'         => 'test',
			'start_date'  => (new \DateTime('+2 hours'))->format('Y-m-d H:i:00'),
			'end_date'    => (new \DateTime('+4 hours'))->format('Y-m-d H:i:00'),
			'catid'       => 8,
			'original_id' => 0,
			'state'       => 1,
			'language'    => '*',
			'description' => '',
			'created'     => (new \DateTime())->format('Y-m-d H:i:s'),
			'created_by'  => $this->grabFromDatabase('users', 'id', ['username' => 'admin'])
		];

		if (\is_array($data)) {
			$event = array_merge($event, $data);
		}

		// Old price structure
		if (\array_key_exists('price', $event) && \is_array($event['price']) && \array_key_exists('value', $event['price'])) {
			$event['price']['label']       = $event['price']['label'] ?? [''];
			$event['price']['description'] = $event['price']['description'] ?? [''];
			$event['price']                = json_encode($event['price']);
		}

		// New price structure
		if (\array_key_exists('price', $event) && \is_array($event['price']) && !\array_key_exists('value', $event['price'])) {
			foreach ($event['price'] as $key => $price) {
				$price['label']                 = $price['label'] ?? '';
				$price['description']           = $price['description'] ?? '';
				$price['currency']              = $price['currency'] ?? 'EUR';
				$event['price']['price' . $key] = $price;
				unset($event['price'][$key]);
			}

			$event['price'] = json_encode($event['price']);
		}

		// Renamed price field with prices
		if (\array_key_exists('prices', $event) && \is_array($event['prices']) && !\array_key_exists('value', $event['prices'])) {
			foreach ($event['prices'] as $key => $price) {
				$price['label']                   = $price['label'] ?? '';
				$price['description']             = $price['description'] ?? '';
				$price['currency']                = $price['currency'] ?? 'EUR';
				$event['prices']['prices' . $key] = $price;
				unset($event['prices'][$key]);
			}

			$event['prices'] = json_encode($event['prices']);
		}

		if (isset($event['booking_options']) && \is_array($event['booking_options'])) {
			foreach ($event['booking_options'] as $key => $price) {
				$price['label']                                     = $price['label'] ?? '';
				$price['description']                               = $price['description'] ?? '';
				$price['currency']                                  = $price['currency'] ?? 'EUR';
				$price['amount']                                    = $price['amount'] ?? 2;
				$price['min_amount']                                = $price['min_amount'] ?? 0;
				$event['booking_options']['booking_options' . $key] = $price;
				unset($event['booking_options'][$key]);
			}

			$event['booking_options'] = json_encode($event['booking_options']);
		}

		if (isset($event['events_discount']) && \is_array($event['events_discount'])) {
			$event['events_discount'] = json_encode($event['events_discount']);
		}

		if (isset($event['tickets_discount']) && \is_array($event['tickets_discount'])) {
			$event['tickets_discount'] = json_encode($event['tickets_discount']);
		}

		if (isset($event['earlybird_discount']) && is_array($event['earlybird_discount'])) {
			$event['earlybird_discount'] = json_encode($event['earlybird_discount']);
		}

		if (isset($event['user_discount']) && is_array($event['user_discount'])) {
			$event['user_discount'] = json_encode($event['user_discount']);
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
