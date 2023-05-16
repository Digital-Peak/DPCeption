<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\DPCalendar;

class BookingPage
{
	public static $url                  = '/index.php?option=com_dpcalendar&view=booking';
	public static $rootClass            = '.com-dpcalendar-booking';
	public static $confirmButtonClass   = '.com-dpcalendar-booking__actions .dp-button-confirm';
	public static $titleText            = 'Booking Details';
	public static $confirmOrderText     = 'Thank you for booking';
	public static $confirmPaidOrderText = 'Confirm the booking';
	public static $failedOrderText      = 'Your payment has been cancelled';
	public static $abortOrderText       = 'You aborted the booking process';
	public static $cancelText           = 'Your booking has been cancelled';
	public static $reviewTicketsHeader  = 'Review tickets';
	public static $confirmHeader        = 'Confirm the booking';
	public static $waitingListInfoText  = 'You are on the waiting list';
}
