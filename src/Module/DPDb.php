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

	private array $columnCache = [];

	public function deleteFromDatabase(string $table, array $criteria): void
	{
		$this->_getDriver()->deleteQueryByCriteria($this->_getConfig('prefix') . $table, $criteria);
	}

	public function updateInDatabase(string $table, array $data, array $criteria = []): void
	{
		parent::updateInDatabase($this->_getConfig('prefix') . $table, $data, $criteria);
	}

	public function haveInDatabase(string $table, array $data): int
	{
		return parent::haveInDatabase($this->_getConfig('prefix') . $table, $data);
	}

	public function seeInDatabase(string $table, array $criteria = []): void
	{
		parent::seeInDatabase($this->_getConfig('prefix') . $table, $criteria);
	}

	public function dontSeeInDatabase(string $table, array $criteria = []): void
	{
		parent::dontSeeInDatabase($this->_getConfig('prefix') . $table, $criteria);
	}

	public function grabEntryFromDatabase(string $table, array $criteria = []): array
	{
		return parent::grabEntryFromDatabase($this->_getConfig('prefix') . $table, $criteria);
	}

	public function grabFromDatabase(string $table, string $column, array $criteria = [])
	{
		return parent::grabFromDatabase($this->_getConfig('prefix') . $table, $column, $criteria);
	}

	public function grabColumnFromDatabase(string $table, string $column, array $criteria = []): array
	{
		return parent::grabColumnFromDatabase($this->_getConfig('prefix') . $table, $column, $criteria);
	}

	public function seeNumRecords(int $expectedNumber, string  $table, array $criteria = []): void
	{
		parent::seeNumRecords($expectedNumber, $this->_getConfig('prefix') . $table, $criteria);
	}

	public function grabNumRecords(string $table, array $criteria = []): int
	{
		return parent::grabNumRecords($this->_getConfig('prefix') . $table, $criteria);
	}

	/**
	 * Returns if the given table has the given column. Can be used for multiple versions compatibility.
	 */
	public function hasColumn(string $table, string $column): bool
	{
		if (!\array_key_exists($table . $column, $this->columnCache)) {
			codecept_debug('Grab columns for table ' . $table);
			$columns = $this->_getDriver()->executeQuery('DESCRIBE ' . $this->_getConfig('prefix') . $table, [])->fetchAll(\PDO::FETCH_COLUMN);

			$this->columnCache[$table . $column] = \in_array($column, $columns);
		}

		return $this->columnCache[$table . $column];
	}
}
