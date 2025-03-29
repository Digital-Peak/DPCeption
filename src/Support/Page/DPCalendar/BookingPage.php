<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\DPCalendar;

class BookingPage
{
	public static string $url                        = '/index.php?option=com_dpcalendar&view=booking';
	public static string $rootClass                  = '.com-dpcalendar-booking';
	public static string $confirmButtonClass         = '.com-dpcalendar-booking__actions .dp-button-confirm';
	public static string $downloadInvoiceButtonClass = '.com-dpcalendar-booking__actions .dp-button-download-invoice';
	public static string $titleText                  = 'Booking Details';
	public static string $confirmOrderText           = 'Thank you for booking';
	public static string $confirmPaidOrderText       = 'Confirm the booking';
	public static string $failedOrderText            = 'Your payment has been cancelled';
	public static string $abortOrderText             = 'You aborted the booking process';
	public static string $cancelText                 = 'Your booking has been cancelled';
	public static string $reviewTicketsHeader        = 'Review tickets';
	public static string $confirmHeader              = 'Confirm the booking';
	public static string $waitingListInfoText        = 'You are on the waiting list';

	public static function getDetailsUrl(string $bookingId): string
	{
		return self::$url . '&uid=' . $bookingId;
	}

	public static function getConfirmUrl(string $bookingId): string
	{
		return self::$url . '&layout=confirm&uid=' . $bookingId;
	}
}
