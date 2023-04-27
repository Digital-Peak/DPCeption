<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Codeception\Module;
use GuzzleHttp\Client;

class DPMail extends Module
{
	public function clearEmails()
	{
		(new Client())->delete('http://mailcatcher-test:1080/messages');
	}

	public function seeNumberOfMails($count)
	{
		$mailcatcher = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$mails       = json_decode($mailcatcher->get('/messages')->getBody());
		$this->assertEquals($count, count($mails), print_r($mails, true));
	}

	public function seeInEmailSubjects($text)
	{
		$mailcatcher = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$subjects    = [];
		$mails       = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$subjects[] = $email->subject;
		}
		$this->assertContains($text, $subjects, print_r($subjects, true));
	}

	public function seeInEmails($text)
	{
		$mailcatcher  = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$mailContents = '';
		$mails        = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$mailContents .= $mailcatcher->get('/messages/' . $email->id . '.html')->getBody();
		}
		$this->assertStringContainsStringIgnoringCase($text, $mailContents, $mailContents);
	}

	public function dontSeeInEmails($text)
	{
		$mailcatcher  = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$mailContents = '';
		$mails        = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$mailContents .= $mailcatcher->get('/messages/' . $email->id . '.html')->getBody();
		}
		$this->assertStringNotContainsStringIgnoringCase($text, $mailContents, $mailContents);
	}

	public function seeSenderInMail($sender)
	{
		$mailcatcher = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$senders     = [];
		$mails       = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$senders[] = $email->sender;
		}
		$this->assertContains('<' . $sender . '>', $senders, print_r($senders, true));
	}

	public function dontSeeSenderInMail($sender)
	{
		$mailcatcher = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$senders     = [];
		$mails       = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$senders[] = $email->sender;
		}
		$this->assertNotContains('<' . $sender . '>', $senders, print_r($senders, true));
	}

	public function seeInRecipients($recipient)
	{
		$mailcatcher = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$recipients  = [];
		$mails       = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$recipients = array_merge($recipients, $email->recipients);
		}
		$this->assertContains('<' . $recipient . '>', $recipients, print_r($recipients, true));
	}

	public function dontSeeInRecipients($recipient)
	{
		$mailcatcher = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$recipients  = [];
		$mails       = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$recipients = array_merge($recipients, $email->recipients);
		}
		$this->assertNotContains('<' . $recipient . '>', $recipients, print_r($recipients, true));
	}

	public function hasAttachmentsInMails($fileName)
	{
		$mailcatcher  = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$mailContents = '';
		$mails        = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$mailContents .= $mailcatcher->get('/messages/' . $email->id . '.source')->getBody();
		}
		$this->assertStringContainsStringIgnoringCase('Content-Disposition: attachment; filename=' . $fileName, $mailContents, $mailContents);
	}

	public function hasNotAttachmentsInMails($fileName)
	{
		$mailcatcher  = new Client(['base_uri' => 'http://mailcatcher-test:1080']);
		$mailContents = '';
		$mails        = json_decode($mailcatcher->get('/messages')->getBody());
		foreach ($mails as $email) {
			$mailContents .= $mailcatcher->get('/messages/' . $email->id . '.source')->getBody();
		}
		$this->assertStringNotContainsStringIgnoringCase(
			'Content-Disposition: attachment; filename=' . $fileName,
			$mailContents,
			print_r($mails, true)
		);
	}
}
