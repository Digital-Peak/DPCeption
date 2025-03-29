<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use DigitalPeak\ThinHTTP\ClientFactoryAwareTrait;
use DigitalPeak\ThinHTTP\CurlClientFactory;

class DPMail extends Module
{
	use ClientFactoryAwareTrait;

	protected array $requiredFields = ['url'];

	public function __construct(protected ModuleContainer $moduleContainer, ?array $config = null)
	{
		parent::__construct($moduleContainer, $config);

		$this->setClientFactory(new CurlClientFactory());
	}

	public function clearEmails(): void
	{
		$this->getClientFactory()->create()->delete($this->_getConfig('url') . '/messages');
	}

	public function seeNumberOfMails(int $count): void
	{
		$mails = $this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data;

		$this->assertEquals($count, \count($mails), print_r($mails, true));
	}

	public function seeInEmailSubjects(string $text): void
	{
		$subjects = [];
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$subjects[] = $email->subject;
		}

		$this->assertContains($text, $subjects, print_r($subjects, true));
	}

	public function seeInEmails(string $text): void
	{
		$mailContents = '';
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$mailContents .= $this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages/' . $email->id . '.html')->dp->body;
		}

		$this->assertStringContainsStringIgnoringCase($text, $mailContents, $mailContents);
	}

	public function dontSeeInEmails(string $text): void
	{
		$mailContents = '';
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$mailContents .= $this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages/' . $email->id . '.html')->dp->body;
		}

		$this->assertStringNotContainsStringIgnoringCase($text, $mailContents, $mailContents);
	}

	public function seeSenderInMail(string $sender): void
	{
		$senders = [];
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$senders[] = $email->sender;
		}

		$this->assertContains('<' . $sender . '>', $senders, print_r($senders, true));
	}

	public function dontSeeSenderInMail(string $sender): void
	{
		$senders = [];
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$senders[] = $email->sender;
		}

		$this->assertNotContains('<' . $sender . '>', $senders, print_r($senders, true));
	}

	public function seeInRecipients(string $recipient): void
	{
		$recipients = [];
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$recipients = array_merge($recipients, $email->recipients);
		}

		$this->assertContains('<' . $recipient . '>', $recipients, print_r($recipients, true));
	}

	public function dontSeeInRecipients(string $recipient): void
	{
		$recipients = [];
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$recipients = array_merge($recipients, $email->recipients);
		}

		$this->assertNotContains('<' . $recipient . '>', $recipients, print_r($recipients, true));
	}

	public function hasAttachmentsInMails(string $fileName): void
	{
		$mailContents = '';
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$mailContents .= $this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages/' . $email->id . '.source')->dp->body;
		}

		$this->assertStringContainsStringIgnoringCase('Content-Disposition: attachment; filename=' . $fileName, $mailContents, $mailContents);
	}

	public function hasNotAttachmentsInMails(string $fileName): void
	{
		$mailContents = '';
		foreach ($this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages')->data as $email) {
			$mailContents .= $this->getClientFactory()->create()->get($this->_getConfig('url') . '/messages/' . $email->id . '.source')->dp->body;
		}

		$this->assertStringNotContainsStringIgnoringCase('Content-Disposition: attachment; filename=' . $fileName, $mailContents);
	}
}
