<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Module;

use Codeception\Module\Db;

class DPDb extends Db
{
	protected array $requiredFields = ['prefix'];

	public function deleteFromDatabase(string $table, array $criteria): void
	{
		$this->_getDriver()->deleteQueryByCriteria($this->_getConfig('prefix') . $table, $criteria);
	}

	public function updateInDatabase($table, array $data, array $criteria = []): void
	{
		parent::updateInDatabase($this->_getConfig('prefix') . $table, $data, $criteria);
	}

	public function haveInDatabase($table, array $data): int
	{
		return parent::haveInDatabase($this->_getConfig('prefix') . $table, $data);
	}

	public function seeInDatabase($table, array $criteria = []): void
	{
		parent::seeInDatabase($this->_getConfig('prefix') . $table, $criteria);
	}

	public function dontSeeInDatabase($table, array $criteria = []): void
	{
		parent::dontSeeInDatabase($this->_getConfig('prefix') . $table, $criteria);
	}

	public function grabEntryFromDatabase(string $table, array $criteria = []): array
	{
		return parent::grabEntryFromDatabase($this->_getConfig('prefix') . $table, $criteria);
	}

	public function grabFromDatabase($table, string $column, array $criteria = [])
	{
		return parent::grabFromDatabase($this->_getConfig('prefix') . $table, $column, $criteria);
	}

	public function grabColumnFromDatabase($table, string $column, array $criteria = []): array
	{
		return parent::grabColumnFromDatabase($this->_getConfig('prefix') . $table, $column, $criteria);
	}

	public function seeNumRecords(int $expectedNumber, $table, array $criteria = []): void
	{
		parent::seeNumRecords($expectedNumber, $this->_getConfig('prefix') . $table, $criteria);
	}

	public function grabNumRecords($table, array $criteria = []): int
	{
		return parent::grabNumRecords($this->_getConfig('prefix') . $table, $criteria);
	}
}
