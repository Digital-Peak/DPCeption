<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\Joomla\Site;

class ArticlePage
{
	public static $url = '/index.php?option=com_content&view=article';

	public static function getDetailsUrl(string $articleId): string
	{
		return self::$url . '&id=' . $articleId;
	}
}
