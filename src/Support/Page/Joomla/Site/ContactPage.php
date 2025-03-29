<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\Joomla\Site;

class ContactPage
{
	public static string $url = '/index.php?option=com_contact&view=contact';

	public static function getDetailsUrl(string $contactId): string
	{
		return self::$url . '&id=' . $contactId;
	}
}
