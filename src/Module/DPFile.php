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

	public function seeFileNotEmpty($fileName): void
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

	public function hasInFile($fileName, string $content): void
	{
		$this->seeFileExists($fileName);

		$this->assertStringContainsString($content, file_get_contents($this->getFileDirectory($fileName)));
	}

	public function hasNotInFile($fileName, string $content): void
	{
		$this->seeFileExists($fileName);

		$this->assertStringNotContainsString($content, file_get_contents($this->getFileDirectory($fileName)));
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
