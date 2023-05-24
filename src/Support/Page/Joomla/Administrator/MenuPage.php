<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Page\Joomla\Administrator;

class MenuPage
{
	public static $menuUrl                               = '/administrator/index.php?option=com_menus&view=items&filter[search]=';
	public static $menuPageTitle                         = 'Menus';
	public static $menuDefault                           = 'mainmenu';
	public static $menuItemNewPageTitle                  = 'Menus: New Item';
	public static $menuItemEditPageTitle                 = 'Menus: Edit Item';
	public static $menuItemFieldTitleName                = 'jform[title]';
	public static $menuItemFieldMenuDropdown             = ['xpath' => '//select[@id="jform_menutype"]'];
	public static $menuItemFieldMenuDropdownId           = 'jform_menutype';
	public static $menuItemFieldMenuDropdownSelectoption = 'Main Menu';
	public static $menuItemTypeModalText                 = 'Menu Item Type';
	public static $menuItemSuccessMessage                = 'Menu item saved';
}
