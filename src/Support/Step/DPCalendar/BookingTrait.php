<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\DPCalendar;

trait BookingTrait
{
	/**
	 * Creates a booking in the database and returns the booking data
	 * as array including the id of the new booking.
	 */
	public function createBooking(?array $data = null): array
	{
		$booking = [
			'uid'       => 'BOOKING-UID',
			'prename'   => 'John',
			'name'      => 'Doo',
			'email'     => 'john@example.com',
			'country'   => 'US',
			'province'  => 'Test County',
			'city'      => 'Test City',
			'zip'       => 'Test Zip',
			'street'    => 'Test Street',
			'number'    => 'Test Number',
			'telephone' => '123',
			'latitude'  => 1,
			'longitude' => 1,
			'book_date' => (new \DateTime())->format('Y-m-d H:i:s'),
			'user_id'   => $this->grabFromDatabase('users', 'id', ['username' => 'admin'])
		];

		if (\is_array($data)) {
			$booking = array_merge($booking, $data);
		}

		$shortCode = $booking['country'];
		if ($booking['country']) {
			$booking['country'] = $this->grabFromDatabase('dpcalendar_countries', 'id', ['short_code' => $booking['country']]);
		}

		if (!empty($booking['price']) && empty($booking['processor'])) {
			$booking['processor'] = 'manual-1';
		}

		if (!empty($booking['price']) && empty($booking['currency'])) {
			$booking['currency'] = 'EUR';
		}

		if (!$this->hasColumn('dpcalendar_bookings', 'prename')) {
			$booking['name'] = $booking['prename'] . ' ' . $booking['name'];
			unset($booking['prename']);
		}

		$booking['id'] = $this->haveInDatabase('dpcalendar_bookings', $booking);

		if ($shortCode) {
			$booking['country_code'] = $shortCode;
		}

		if ($shortCode === 'US') {
			$booking['country_code_value'] = 'United States';
		}

		return $booking;
	}
}
