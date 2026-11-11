<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Codeception\Module;

class DPFile extends Module
{
	protected array $requiredFields = ['files_root'];

	public function seeFileNotEmpty(string $fileName): void
	{
		$this->seeFileExists($fileName);

		$this->assertGreaterThan(1, filesize($this->getFileDirectory($fileName)));
	}

	public function seeFileExists(string $fileName): void
	{
		for ($i = 0; $i < 5; $i++) {
			$dir = $this->getFileDirectory($fileName);
			if ($dir !== '' && $dir !== '0') {
				break;
			}
			sleep(1);
		}

		$this->assertNotEmpty($this->getFileDirectory($fileName));
	}

	public function hasInFile(string $fileName, string $content): void
	{
		$this->seeFileExists($fileName);

		$this->assertStringContainsString($content, file_get_contents($this->getFileDirectory($fileName)) ?: '');
	}

	public function hasNotInFile(string $fileName, string $content): void
	{
		$this->seeFileExists($fileName);

		$this->assertStringNotContainsString($content, file_get_contents($this->getFileDirectory($fileName)) ?: '');
	}

	public function hasInSpreadsheet(string $fileName, string $content): void
	{
		$this->seeFileExists($fileName);

		$this->assertStringContainsString($content, $this->getSpreadsheetContent($this->getFileDirectory($fileName)));
	}

	public function hasNotInSpreadsheet(string $fileName, string $content): void
	{
		$this->seeFileExists($fileName);

		$this->assertStringNotContainsString($content, $this->getSpreadsheetContent($this->getFileDirectory($fileName)));
	}

	/**
	 * Returns the text of all cells of an xlsx file, one cell per line.
	 */
	private function getSpreadsheetContent(string $path): string
	{
		$zip = new \ZipArchive();
		$this->assertTrue($zip->open($path) === true, 'Could not open spreadsheet file ' . $path);

		$texts = [];
		for ($i = 0; $i < $zip->numFiles; $i++) {
			$name = (string)$zip->getNameIndex($i);

			// Shared strings and worksheets (for inline strings and numbers)
			if ($name !== 'xl/sharedStrings.xml' && !str_starts_with($name, 'xl/worksheets/')) {
				continue;
			}

			preg_match_all('/<(?:t|v)(?:\s[^>]*)?>(.*?)<\/(?:t|v)>/s', (string)$zip->getFromIndex($i), $matches);
			foreach ($matches[1] as $text) {
				$texts[] = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
			}
		}

		$zip->close();

		return implode("\n", $texts);
	}

	private function getFileDirectory(string $fileName): string
	{
		$files = glob($this->_getConfig('files_root') . '/' . $fileName);
		if ($files === [] || $files === false) {
			return '';
		}

		return $files[0];
	}
}
