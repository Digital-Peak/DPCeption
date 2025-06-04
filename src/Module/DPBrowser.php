<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

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

	private static array $processedLogs = [];

	public function getConfiguration(?string $element = null, ?string $moduleName = null): string
	{
		$config = $moduleName !== null && $moduleName !== '' && $moduleName !== '0' ? $this->getModule($moduleName)->_getConfig() : $this->config;

		// When no module is given and the array key doesn't exist, fallback to the current module configuration
		if ($element && ($moduleName === null || $moduleName === '' || $moduleName === '0') && !\array_key_exists($element, $config)) {
			$moduleName = self::class;
		}

		return $element !== null && $element !== '' && $element !== '0' ? ($config[$element] ?? '') : $config;
	}

	public function setJoomlaGlobalConfigurationOption(string $oldValue, string $newValue): void
	{
		$path = $this->getConfiguration('home_dir') . '/configuration.php';
		shell_exec('sudo chmod 777 ' . $path);
		$content = file_get_contents($path) ?: '';
		$content = str_replace($oldValue, $newValue, $content);
		file_put_contents($path, $content);
	}

	public function createDPCategory(string $title, string $component, array $permissions = [], int $parentId = 0): int
	{
		/** @var DPDb $db */
		$db = $this->getModule(DPDb::class);

		$this->amOnPage('/administrator/index.php?option=com_categories&extension=' . $component);
		$this->clickJoomlaToolbarButton('New');
		$this->fillField('#jform_title', $title);

		if ($parentId !== 0) {
			$this->executeJS('document.querySelector("joomla-field-fancy-select[placeholder=\"Type or select a Category\"]").choicesInstance.setChoiceByValue("' . $parentId . '")');
		}

		if ($permissions !== []) {
			$this->clickJoomlaToolbarButton('Save');
			$this->click('Permissions');
			foreach ($permissions as $permission) {
				$groupId = $db->grabColumnFromDatabase('usergroups', 'id', ['title' => $permission['group']])[0];

				// Public is preselected and can't be selected by the web driver
				if ($permission['group'] !== 'Public') {
					$this->click($permission['group']);
				}

				$this->makeVisible('#jform_rules_' . $permission['action'] . '_' . $groupId);
				$this->selectOption('#jform_rules_' . $permission['action'] . '_' . $groupId, $permission['allowed'] ? 1 : 0);
			}
		}

		$this->clickJoomlaToolbarButton('Save & Close');

		return (int)$db->grabFromDatabase('categories', 'id', ['title' => $title, 'extension' => $component]);
	}

	public function enablePlugin(string $pluginName, ?bool $enable = true): void
	{
		/** @var DPDb $db */
		$db = $this->getModule(DPDb::class);

		$db->updateInDatabase('extensions', ['enabled' => $enable === true ? 1 : 0], ['name' => $pluginName]);
	}

	public function amOnPage(mixed $link, ?bool $checkForErrors = true): void
	{
		$this->executeJS('try { sessionStorage.clear();localStorage.clear(); } catch(error) {}');
		parent::amOnPage($link);
		$this->waitForJs('return document.readyState == "complete"', 10);

		if ($checkForErrors && strpos((string)$link, 'com_dp')) {
			$this->checkForPhpNoticesOrWarnings();
			$this->checkForJsErrors();
		}
	}

	public function deleteAllCookies(): void
	{
		$this->executeInSelenium(fn (RemoteWebDriver $webdriver) => $webdriver->manage()->deleteAllCookies());
		$this->reloadPage();
	}

	public function makeVisible(string $selector): void
	{
		$this->waitForElement($selector);
		$this->scrollTo($selector, null, -100);
		$this->wait(1);
	}

	public function closeSidebar(): void
	{
		if ($this->executeJS('return window.getComputedStyle(document.querySelector("#sidebarmenu .sidebar-item-title"), null).display !== "none"')) {
			$this->waitForElementClickable('#menu-collapse');
			$this->click('#menu-collapse');
			$this->waitForElementNotVisible('#sidebarmenu .sidebar-item-title');
			$this->wait(0.3);
		}
	}

	public function clickDPToolbarButton(string $button): void
	{
		$this->waitForJs('return document.readyState == "complete"', 10);

		// Wait is needed here as on J4 buttons work after a certain time
		$this->scrollTo($button);
		$this->wait(0.7);
		$this->click($button);
		$this->waitForJs('return document.readyState == "complete"', 10);
		$this->wait(0.5);
	}

	public function clickJoomlaToolbarButton(string $button, ?bool $acceptPopup = false): void
	{
		$this->waitForJs('return document.readyState == "complete"', 10);
		// Wait is needed here as on J4 buttons work after a certain time
		$this->wait(0.5);
		$this->click($button);

		if ($acceptPopup === true) {
			try {
				$this->acceptPopup();
			} catch (NoSuchAlertException) {
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
	 */
	public function fillFieldNoClear(string|array $field, string $value): void
	{
		// @phpstan-ignore-next-line
		$this->pressKey($field, ['ctrl', 'a'], $value, WebDriverKeys::TAB);
	}

	public function setExtensionParam(string $key, mixed $value, string  $extension): void
	{
		/** @var DPDb $db */
		$db     = $this->getModule(DPDb::class);
		$params = $db->grabFromDatabase('extensions', 'params', ['name' => $extension]);

		$params       = json_decode((string)$params);
		$params->$key = $value;

		$db->updateInDatabase('extensions', ['params' => json_encode($params)], ['name' => $extension]);
	}

	public function doAdministratorLogin(?string $user = null, ?string  $password = null, ?bool $useSnapshot = true): void
	{
		if (\is_null($user)) {
			$user = $this->config['username'];
		}

		if (\is_null($password)) {
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

		if ($useSnapshot === true) {
			$this->saveSessionSnapshot('back' . $user);
		}
	}

	public function doAdministratorLogout(?string $user = null): void
	{
		$this->click('User Menu');
		$this->click('Log out');
		$this->waitForElement('#mod-login-username');
		$this->waitForText('Log in');

		if (\is_null($user)) {
			$user = $this->_getConfig('username');
		}

		$this->deleteSessionSnapshot('back' . $user);
	}

	public function doFrontEndLogin(?string $user = null, ?string  $password = null, ?bool $useSnapshot = true): void
	{
		if (\is_null($user)) {
			$user = $this->config['username'];
		}

		if (\is_null($password)) {
			$password = $this->config['password'];
		}

		if ($useSnapshot && $this->loadSessionSnapshot('front' . $user)) {
			return;
		}

		$this->amOnPage('/index.php?option=com_users&view=login');
		$this->fillField('#username', $user);
		$this->fillField('#password', $password);
		$this->click('#remember');
		$this->click('.com-users-login__submit button[type="submit"], #content .login button[type="submit"]');

		$this->waitForElement('.profile');

		if ($useSnapshot === true) {
			$this->saveSessionSnapshot('front' . $user);
		}
	}

	public function doFrontendLogout(?string $user = null): void
	{
		$this->amOnPage('/index.php?option=com_users&view=login');
		$this->click('Log out');
		$this->amOnPage('/index.php?option=com_users&view=login');
		$this->waitForElement('.login');

		if (\is_null($user)) {
			$user = $this->_getConfig('username');
		}

		$this->deleteSessionSnapshot('front' . $user);
	}

	public function checkForPhpNoticesOrWarnings(?string $page = null): void
	{
		if ($page !== null && $page !== '' && $page !== '0') {
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
			$this->dontSeeInPageSource("The requested page can't be found");
		} catch (ModuleException) {
			// Ignore as it happens when an error occurs before a page is opened
		}

		$webLogsFile = $this->getConfiguration('web_logs_error_file');
		if ($webLogsFile === '' || $webLogsFile === '0' || !file_exists($webLogsFile)) {
			return;
		}

		$extensionDir = $this->getConfiguration('extension_dir');
		if ($extensionDir === '' || $extensionDir === '0') {
			return;
		}

		$logs = file_get_contents($webLogsFile);
		if ($logs === '' || $logs === '0' || $logs === false) {
			return;
		}

		file_put_contents($webLogsFile, '');

		$logs = array_filter(explode("\n", $logs));
		foreach ($logs as $log) {
			if (\array_key_exists($log, self::$processedLogs)) {
				continue;
			}

			// Do not process errors outside of the extension
			if (!str_contains($log, '/' . basename($extensionDir) . '/')) {
				self::$processedLogs[$log] = $log;
				continue;
			}

			$this->assertEmpty($log, $log);
			self::$processedLogs[$log] = $log;
		}
	}

	public function searchForItem(?string $name = null): void
	{
		if ($name !== null && $name !== '' && $name !== '0') {
			$this->fillField('#filter_search', $name);
			$this->click(['xpath' => "//button[@aria-label='Search']"]);
			$this->waitForElement('.row0');

			return;
		}

		$this->click('Clear', ['xpath' => "//button[@type='button']"]);
	}

	public function checkForJsErrors(): void
	{
		if (!$this->webDriver instanceof RemoteWebDriver) {
			return;
		}

		try {
			$logs = $this->webDriver->manage()->getLog('browser');
		} catch (\Exception $exception) {
			if (str_contains($exception->getMessage(), 'HTTP method not allowed')) {
				return;
			}

			throw $exception;
		}

		foreach ($logs as $log) {
			// Ugly hack for event creation JS error when save during a similar event ajax request
			if (strpos((string)$log['message'], 'option=com_dpcalendar&view=form&id=0')) {
				continue;
			}

			// Only look for internal JS errors
			if (!str_starts_with((string)$log['message'], (string)$this->_getConfig()['url'])) {
				continue;
			}

			// J4 throws some CORS warnings
			if (!str_starts_with((string)$log['message'], 'The Cross-Origin-Opener-Policy header has been ignored')) {
				continue;
			}

			$this->assertNotEquals('SEVERE', $log['level'], 'Some error in JavaScript: ' . json_encode($log));
		}
	}

	public function waitForElementChange($element, \Closure $callback, int $timeout = -1): void
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
