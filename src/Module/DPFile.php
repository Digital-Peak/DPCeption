<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Codeception\Module;

class DPFile extends Module
{
	public function seeFileNotEmpty($fileName)
	{
		$this->seeFileExists($fileName);

		$this->assertGreaterThan(1, filesize($this->getFileDirectory($fileName)));
	}

	public function seeFileExists($fileName)
	{
		for ($i = 0; $i < 5; $i++) {
			if ($this->getFileDirectory($fileName)) {
				break;
			}
			sleep(1);
		}

		$this->assertNotEmpty($this->getFileDirectory($fileName));
	}

	public function hasInFile($fileName, $content)
	{
		$this->seeFileExists($fileName);

		$this->assertStringContainsString($content, file_get_contents($this->getFileDirectory($fileName)));
	}

	public function hasNotInFile($fileName, $content)
	{
		$this->seeFileExists($fileName);

		$this->assertStringNotContainsString($content, file_get_contents($this->getFileDirectory($fileName)));
	}

	private function getFileDirectory(string $fileName)
	{
		$files = glob($this->_getConfig('files_root') . '/' . $fileName);
		if(!$files) {
			return '';
		}

		return $files[0];
	}
}
