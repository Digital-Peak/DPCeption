<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Extension;

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Lib\Interfaces\ConsolePrinter;
use Codeception\Subscriber\Console;
use Codeception\Test\Descriptor;
use PHPUnit\Framework\SelfDescribing;

class Reporter extends Console implements ConsolePrinter
{
	private int $counter = 0;
	private int $total   = 0;
	private int $memory  = 0;

	public function __construct()
	{
		parent::__construct(['steps' => false, 'ansi' => true]);
	}

	public function beforeSuite(SuiteEvent $event): void
	{
		$this->total = $event->getSuite()->getTestCount();

		$this->messageFactory->message('Memory info: (script/total/peak)')->writeln();

		parent::beforeSuite($event);
	}

	public function startTest(TestEvent $event): void
	{
		$this->counter++;
		$this->memory = memory_get_usage();

		if (function_exists('memory_reset_peak_usage')) {
			memory_reset_peak_usage();
		}

		parent::startTest($event);
	}

	protected function writeCurrentTest(SelfDescribing $test, bool $inProgress = true): void
	{
		$prefix = $this->output->isInteractive() && !$this->isDetailed($test) && $inProgress ? '- ' : '';

		$testString = '(' . $this->counter . '/' . $this->total . ') ' . Descriptor::getTestAsString($test);
		$testString = preg_replace('#^([^:]+):\s#', sprintf('<focus>$1%s</focus> ', $this->chars['of']), $testString);

		$this->messageFactory->message($testString)->prepend($prefix)->write();
	}

	protected function writeTimeInformation(TestEvent $event): void
	{
		parent::writeTimeInformation($event);

		$this->messageFactory->message('%s/%s/%s')
			->with($this->size(memory_get_usage() - $this->memory), $this->size(memory_get_usage()), $this->size(memory_get_peak_usage()))
			->prepend(' (')->append(')')
			->style('info')->write();
	}

	private function size(int $size): string
	{
		$unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
		return @round($size / 1024 ** $i = floor(log($size, 1024)), 2) . $unit[$i];
	}
}
