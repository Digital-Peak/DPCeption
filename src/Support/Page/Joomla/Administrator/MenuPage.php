<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\Joomla\Administrator;

class MenuPage
{
	public static string $menuUrl = '/administrator/index.php?option=com_menus&view=items&filter[search]=';

	public static string $menuPageTitle = 'Menus';

	public static string $menuDefault = 'mainmenu';

	public static string $menuItemNewPageTitle = 'Menus: New Item';

	public static string $menuItemEditPageTitle = 'Menus: Edit Item';

	public static string $menuItemFieldTitleName = 'jform[title]';

	public static array $menuItemFieldMenuDropdown = ['xpath' => '//select[@id="jform_menutype"]'];

	public static string $menuItemFieldMenuDropdownId = 'jform_menutype';

	public static string $menuItemFieldMenuDropdownSelectoption = 'Main Menu';

	public static string $menuItemTypeModalFrame = '#menuTypeModal iframe, .joomla-dialog-content-select-field iframe';

	public static string $menuItemSuccessMessage = 'Menu item saved';
}
