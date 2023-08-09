<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Codeception\Module;
use DigitalPeak\ThinHTTP;

class DPMail extends Module
{
	protected array $requiredFields = ['url'];

	public function clearEmails()
	{
		(new ThinHTTP())->delete($this->_getConfig('url') . '/messages');
	}

	public function seeNumberOfMails($count)
	{
		$mails = $this->getMails();

		$this->assertEquals($count, count($mails), print_r($mails, true));
	}

	public function seeInEmailSubjects($text)
	{
		$subjects = [];
		foreach ($this->getMails() as $email) {
			$subjects[] = $email->subject;
		}

		$this->assertContains($text, $subjects, print_r($subjects, true));
	}

	public function seeInEmails($text)
	{
		$mailContents = '';
		foreach ($this->getMails() as $email) {
			$mailContents .= (new ThinHTTP())->get($this->_getConfig('url') . '/messages/' . $email->id . '.html')->dp->body;
		}

		$this->assertStringContainsStringIgnoringCase($text, $mailContents, $mailContents);
	}

	public function dontSeeInEmails($text)
	{
		$mailContents = '';
		foreach ($this->getMails() as $email) {
			$mailContents .= (new ThinHTTP())->get($this->_getConfig('url') . '/messages/' . $email->id . '.html')->dp->body;
		}

		$this->assertStringNotContainsStringIgnoringCase($text, $mailContents, $mailContents);
	}

	public function seeSenderInMail($sender)
	{
		$senders = [];
		foreach ($this->getMails() as $email) {
			$senders[] = $email->sender;
		}

		$this->assertContains('<' . $sender . '>', $senders, print_r($senders, true));
	}

	public function dontSeeSenderInMail($sender)
	{
		$senders = [];
		foreach ($this->getMails() as $email) {
			$senders[] = $email->sender;
		}

		$this->assertNotContains('<' . $sender . '>', $senders, print_r($senders, true));
	}

	public function seeInRecipients($recipient)
	{
		$recipients = [];
		foreach ($this->getMails() as $email) {
			$recipients = array_merge($recipients, $email->recipients);
		}

		$this->assertContains('<' . $recipient . '>', $recipients, print_r($recipients, true));
	}

	public function dontSeeInRecipients($recipient)
	{
		$recipients = [];
		foreach ($this->getMails() as $email) {
			$recipients = array_merge($recipients, $email->recipients);
		}

		$this->assertNotContains('<' . $recipient . '>', $recipients, print_r($recipients, true));
	}

	public function hasAttachmentsInMails($fileName)
	{
		$mailContents = '';
		foreach ($this->getMails() as $email) {
			$mailContents .= (new ThinHTTP())->get($this->_getConfig('url') . '/messages/' . $email->id . '.source')->dp->body;
		}

		$this->assertStringContainsStringIgnoringCase('Content-Disposition: attachment; filename=' . $fileName, $mailContents, $mailContents);
	}

	public function hasNotAttachmentsInMails($fileName)
	{
		$mailContents = '';
		foreach ($this->getMails() as $email) {
			$mailContents .= (new ThinHTTP())->get($this->_getConfig('url') . '/messages/' . $email->id . '.source')->dp->body;
		}

		$this->assertStringNotContainsStringIgnoringCase('Content-Disposition: attachment; filename=' . $fileName, $mailContents);
	}

	private function getMails(): array
	{
		return (new ThinHTTP())->get($this->_getConfig('url') . '/messages')->data;
	}
}
