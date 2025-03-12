<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Codeception\Module;

class DPScreenshot extends Module
{
	protected array $requiredFields = ['screenshot_dir'];

	public function makeScreenshot($fileName, $selector, ?array $pictureSize, $windowSize = false)
	{
		/** @var DPBrowser $browser */
		$browser = $this->getModule(DPBrowser::class);

		if ($windowSize) {
			$browser->resizeWindow($windowSize[0], $windowSize[1]);
		}

		$browser->waitForElement($selector);

		return $this->takeScreenshot($fileName, $selector, $pictureSize);
	}

	/**
	 * Coords are:
	 * 0: Upper left x coordinate.
	 * 1: Upper left y coordinate 0, 0 is the top left corner of the image.
	 * 2: Bottom right x coordinate.
	 * 3: Bottom right y coordinate.
	 *
	 * @param $file
	 * @param $coords
	 */
	public function drawRectangle($file, $coords): void
	{
		// Load image
		$img = imagecreatefrompng($file);
		// Transparent red
		$red = imagecolorallocatealpha($img, 255, 0, 0, 50);

		imagesetthickness($img, 5);

		// Draw a white rectangle
		imagerectangle($img, $coords[0], $coords[1], $coords[2], $coords[3], $red);
		// Save the image (overwrite)
		imagepng($img, $file);
		imagedestroy($img);
	}

	private function takeScreenshot(string $fileName, $selector, ?array $dimensions = null): string
	{
		$root = $this->_getConfig('screenshot_dir') . '/';
		if (!is_dir(dirname($root . $fileName))) {
			mkdir(dirname($root . $fileName), 0777, true);
		}

		/** @var DPBrowser $driver */
		$driver = $this->getModule(DPBrowser::class);

		$driver->executeJS('const head=document.querySelector("#subhead-container"); if(head)head.style.position = "inherit";');

		$elements = $driver->_findElements($selector);
		$element  = reset($elements);

		// Change the Path to your own settings
		$screenshot = $root . $fileName . '.png';

		// Change the driver instance
		$this->takeFullScreenshot($driver, $screenshot);

		if (!file_exists($screenshot)) {
			throw new \Exception('Could not save screenshot');
		}

		if (!(bool)$element) {
			return $screenshot;
		}

		$element_screenshot = $root . $fileName . '.png'; // Change the path here as well

		$element_width  = $dimensions !== null && $dimensions !== [] ? $dimensions[0] : $element->getSize()->getWidth();
		$element_height = $dimensions !== null && $dimensions !== [] ? $dimensions[1] : $element->getSize()->getHeight();


		$element_src_x = $element->getLocation()->getX();
		$element_src_y = $element->getLocation()->getY();

		if ($dimensions && count($dimensions) > 2) {
			$element_src_x += $dimensions[2];
			$element_src_y += $dimensions[3];
		}

		// Create image instances
		$src  = imagecreatefrompng($screenshot);
		$dest = imagecreatetruecolor($element_width, $element_height);

		// Copy
		imagecopy($dest, $src, 0, 0, $element_src_x, $element_src_y, $element_width, $element_height);

		imagepng($dest, $element_screenshot);

		// unlink($screenshot); // unlink function might be restricted in mac os x.

		if (!file_exists($element_screenshot)) {
			throw new \Exception('Could not save element screenshot');
		}

		return $element_screenshot;
	}

	private function takeFullScreenshot(DPBrowser $driver, string $screenshot_name): void
	{
		$total_width     = $driver->executeJS('return Math.max.apply(null, [document.body.clientWidth, document.body.scrollWidth, document.documentElement.scrollWidth, document.documentElement.clientWidth])');
		$total_height    = $driver->executeJS('return Math.max.apply(null, [document.body.clientHeight, document.body.scrollHeight, document.documentElement.scrollHeight, document.documentElement.clientHeight])');
		$viewport_width  = $driver->executeJS('return document.documentElement.clientWidth');
		$viewport_height = $driver->executeJS('return document.documentElement.clientHeight');
		$driver->executeJS('window.scrollTo(0, 0)');
		$driver->wait(0.8);

		$full_capture = imagecreatetruecolor($total_width, $total_height);
		$repeat_x     = ceil($total_width / $viewport_width);
		$repeat_y     = ceil($total_height / $viewport_height);
		for ($x = 0; $x < $repeat_x; $x++) {
			$x_pos      = $x * $viewport_width;
			$before_top = -1;
			for ($y = 0; $y < $repeat_y; $y++) {
				$y_pos = $y * $viewport_height;
				$driver->executeJS(sprintf('window.scrollTo(%s, %s)', $x_pos, $y_pos));
				$driver->wait(0.8);
				$scroll_left = $driver->executeJS("return window.pageXOffset");
				$scroll_top  = $driver->executeJS("return window.pageYOffset");
				if ($before_top == $scroll_top) {
					break;
				}
				$tmp_name = $screenshot_name . '.tmp';
				$driver->_saveScreenshot($tmp_name);
				if (!file_exists($tmp_name)) {
					throw new \Exception('Could not save screenshot');
				}
				$tmp_image = imagecreatefrompng($tmp_name);
				imagecopy($full_capture, $tmp_image, $scroll_left, $scroll_top, 0, 0, $viewport_width, $viewport_height);
				imagedestroy($tmp_image);
				unlink($tmp_name);
				$before_top = $scroll_top;
			}
		}
		imagepng($full_capture, $screenshot_name);
		imagedestroy($full_capture);

		$driver->executeJS('window.scrollTo(0, 0)');
	}
}
