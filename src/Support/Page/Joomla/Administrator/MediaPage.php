<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\Joomla\Administrator;

class MediaPage
{
	public static $mediaUrl      = '/administrator/index.php?option=com_media&view=media';
	public static $imageEditFile = '/joomla_black.png';
	public static $imageEditUrl  = '/administrator/index.php?option=com_media&view=file&mediatypes=0,1,2,3&path=local-images:/joomla_black.png';
}
