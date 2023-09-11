<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Closure;
use Codeception\Exception\ModuleException;
use Codeception\Module\WebDriver;
use Facebook\WebDriver\Exception\NoSuchAlertException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverKeys;

class DPBrowser extends WebDriver
{
	protected array $requiredFields = [
		'url',
		'browser',
		'username',
		'password',
		'admin email',
		'timeout',
		'downloads',
		'home_dir',
		'joomla_version'
	];

	public function getConfiguration($element = null, $moduleName = null)
	{
		$config = $moduleName ? $this->getModule($moduleName)->_getConfig() : $this->config;

		// When no module is given and the array key doesn't exist, fallback to the current module configuration
		if ($element && !$moduleName && !array_key_exists($element, $config)) {
			$moduleName = __CLASS__;
		}

		return !$element ? $config : $config[$element];
	}

	public function setJoomlaGlobalConfigurationOption($oldValue, $newValue)
	{
		$path = $this->getConfiguration('home_dir') . '/configuration.php';
		shell_exec('sudo chmod 777 ' . $path);
		$content = file_get_contents($path);
		$content = str_replace($oldValue, $newValue, $content);
		file_put_contents($path, $content);
	}

	public function createDPCategory($title, $component)
	{
		$this->amOnPage('/administrator/index.php?option=com_categories&extension=' . $component);
		$this->clickJoomlaToolbarButton('New');
		$this->fillField('#jform_title', $title);
		$this->clickJoomlaToolbarButton('Save & Close');

		/** @var DPDb $db */
		$db = $this->getModule(DPDb::class);

		return $db->grabFromDatabase('categories', 'id', ['title' => $title, 'extension' => $component]);
	}

	public function enablePlugin($pluginName, $enable = true)
	{
		/** @var DPDb $db */
		$db = $this->getModule(DPDb::class);

		$db->updateInDatabase('extensions', ['enabled' => $enable ? 1 : 0], ['name' => $pluginName]);
	}

	public function amOnPage($link, $checkForErrors = true): void
	{
		$this->executeJS('try { sessionStorage.clear();localStorage.clear(); } catch(error) {}');
		parent::amOnPage($link);
		$this->waitForJs('return document.readyState == "complete"', 10);

		if ($checkForErrors && strpos($link, 'com_dp')) {
			$this->checkForPhpNoticesOrWarnings();
			$this->checkForJsErrors();
		}
	}

	public function deleteAllCookies()
	{
		$this->executeInSelenium(fn (RemoteWebDriver $webdriver) => $webdriver->manage()->deleteAllCookies());
		$this->reloadPage();
	}

	public function makeVisible($selector)
	{
		$this->waitForElement($selector);
		$this->scrollTo($selector, null, -100);
		$this->wait(1);
	}

	public function closeSidebar()
	{
		if ($this->executeJS('return window.getComputedStyle(document.querySelector("#sidebarmenu .sidebar-item-title"), null).display !== "none"')) {
			$this->waitForElementClickable('#menu-collapse');
			$this->click('#menu-collapse');
			$this->waitForElementNotVisible('#sidebarmenu .sidebar-item-title');
			$this->wait(0.3);
		}
	}

	public function clickDPToolbarButton($button)
	{
		$this->waitForJs('return document.readyState == "complete"', 10);

		// Wait is needed here as on J4 buttons work after a certain time
		$this->scrollTo($button);
		$this->wait(0.7);
		$this->click($button);
		$this->waitForJs('return document.readyState == "complete"', 10);
		$this->wait(0.5);
	}

	public function clickJoomlaToolbarButton($button, $acceptPopup = false)
	{
		$this->waitForJs('return document.readyState == "complete"', 10);
		// Wait is needed here as on J4 buttons work after a certain time
		$this->wait(0.5);
		$this->click($button);

		if ($acceptPopup) {
			try {
				$this->acceptPopup();
			} catch (NoSuchAlertException $e) {
				// On Joomla 5 we have a normal dialog
				$this->click('Yes');
			}
		}

		$this->waitForJs('return document.readyState == "complete"', 10);
		$this->wait(0.5);
	}

	/**
	 * Driver implementation does clear the field which fires a JS change event. See
	 * http://phptest.club/t/fillfield-triggers-change-event-too-soon/126
	 *
	 * @param $field
	 * @param $value
	 *
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function fillFieldNoClear($field, $value)
	{
		$this->pressKey($field, ['ctrl', 'a'], $value, WebDriverKeys::TAB);
	}

	public function setExtensionParam($key, $value, $extension)
	{
		/** @var DPDb $db */
		$db     = $this->getModule(DPDb::class);
		$params = $db->grabFromDatabase('extensions', 'params', ['name' => $extension]);

		$params       = json_decode($params);
		$params->$key = $value;
		$db->updateInDatabase('extensions', ['params' => json_encode($params)], ['name' => $extension]);
	}

	public function doAdministratorLogin($user = null, $password = null, $useSnapshot = true)
	{
		if (is_null($user)) {
			$user = $this->config['username'];
		}

		if (is_null($password)) {
			$password = $this->config['password'];
		}

		if ($useSnapshot && $this->loadSessionSnapshot('back' . $user)) {
			return;
		}

		$this->amOnPage('/administrator/index.php');
		$this->waitForElement('#mod-login-username');
		$this->wait(1.5);
		$this->fillField('#mod-login-username', $user);
		$this->fillField('#mod-login-password', $password);
		$this->wait(1.5);
		$this->click('Log in');
		$this->waitForElement('.page-title');

		if ($useSnapshot) {
			$this->saveSessionSnapshot('back' . $user);
		}
	}

	public function doAdministratorLogout($user = null)
	{
		$this->click('User Menu');
		$this->click('Log in');
		$this->waitForElement('#mod-login-username');
		$this->waitForText('Log in');

		if (is_null($user)) {
			$user = $this->_getConfig('username');
		}

		$this->deleteSessionSnapshot('back' . $user);
	}

	public function doFrontEndLogin($user = null, $password = null, $useSnapshot = true)
	{
		if (is_null($user)) {
			$user = $this->config['username'];
		}

		if (is_null($password)) {
			$password = $this->config['password'];
		}

		if ($useSnapshot && $this->loadSessionSnapshot('front' . $user)) {
			return;
		}

		$this->amOnPage('/index.php?option=com_users&view=login');
		$this->fillField('#username', $user);
		$this->fillField('#password', $password);
		$this->click('#remember');
		$this->click('.com-users-login__submit button, #content .login button');

		$this->waitForElement('.profile');

		if ($useSnapshot) {
			$this->saveSessionSnapshot('front' . $user);
		}
	}

	public function doFrontendLogout($user = null)
	{
		$this->amOnPage('/index.php?option=com_users&view=login');
		$this->click('Log out');
		$this->amOnPage('/index.php?option=com_users&view=login');
		$this->waitForElement('.login');

		if (is_null($user)) {
			$user = $this->_getConfig('username');
		}

		$this->deleteSessionSnapshot('front' . $user);
	}

	public function checkForPhpNoticesOrWarnings($page = null)
	{
		if ($page) {
			$this->amOnPage($page);
		}

		try {
			$this->dontSeeInPageSource('Deprecated:');
			$this->dontSeeInPageSource('<b>Deprecated</b>:');
			$this->dontSeeInPageSource('Notice:');
			$this->dontSeeInPageSource('<b>Notice</b>:');

			// $this->dontSeeInPageSource('Warning:'); We have translation strings with this in the backend.
			$this->dontSeeInPageSource('<b>Warning</b>:');
			$this->dontSeeInPageSource('Strict standards:');
			$this->dontSeeInPageSource('<b>Strict standards</b>:');
			$this->dontSeeInPageSource('The requested page can\'t be found');
		} catch (ModuleException $e) {
			// Ignore as it happens when an error occurs before a page is opened
		}
	}

	public function searchForItem($name = null)
	{
		if ($name) {
			$this->fillField('#filter_search', $name);
			$this->click(['xpath' => "//button[@aria-label='Search']"]);

			return;
		}

		$this->click('Clear', ['xpath' => "//button[@type='button']"]);
	}

	public function checkForJsErrors()
	{
		try {
			$logs = $this->webDriver->manage()->getLog('browser');
		} catch (\Exception $e) {
			if (strpos($e->getMessage(), 'HTTP method not allowed') !== -1) {
				return;
			}

			throw $e;
		}

		if (!is_array($logs)) {
			return;
		}

		foreach ($logs as $log) {
			// Ugly hack for event creation JS error when save during a similar event ajax request
			if (strpos($log['message'], 'option=com_dpcalendar&view=form&id=0')) {
				continue;
			}

			// Only look for internal JS errors
			if (strpos($log['message'], $this->_getConfig()['url']) !== 0) {
				continue;
			}

			// J4 throws some CORS warnings
			if (strpos($log['message'], 'The Cross-Origin-Opener-Policy header has been ignored') !== 0) {
				continue;
			}

			$this->assertNotEquals('SEVERE', $log['level'], 'Some error in JavaScript: ' . json_encode($log));
		}
	}

	public function waitForElementChange($element, Closure $callback, int $timeout = -1): void
	{
		parent::waitForElementChange($element, $callback, $timeout === -1 ? $this->_getConfig('timeout') : $timeout);
	}

	public function waitForElement($element, int $timeout = -1): void
	{
		parent::waitForElement($element, $timeout === -1 ? $this->_getConfig('timeout') : $timeout);
	}

	public function waitForElementVisible($element, int $timeout = -1): void
	{
		parent::waitForElementVisible($element, $timeout === -1 ? $this->_getConfig('timeout') : $timeout);
	}

	public function waitForElementNotVisible($element, int $timeout = -1): void
	{
		parent::waitForElementNotVisible($element, $timeout === -1 ? $this->_getConfig('timeout') : $timeout);
	}

	public function waitForElementClickable($element, int $timeout = -1): void
	{
		parent::waitForElementClickable($element, $timeout === -1 ? $this->_getConfig('timeout') : $timeout);
	}

	public function waitForText(string $text, int $timeout = -1, $selector = null): void
	{
		parent::waitForText($text, $timeout === -1 ? $this->_getConfig('timeout') : $timeout, $selector);
	}
}
