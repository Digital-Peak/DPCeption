<?php
/**
 * @package    DPCeption
 * @copyright  Copyright (C) 2023 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU/GP
 */

namespace DigitalPeak\Support\Step\Joomla;

trait CustomFieldTrait
{
	/**
	 * Creates a custom field in the database and returns the custom field data
	 * as array including the id of the new custom field.
	 */
	public function createCustomField(?array $data = null, ?int $itemId = null, ?string $value = null): array
	{
		$I = $this;

		$field = [
			'title'           => 'Test Custom Field',
			'label'           => 'Test Custom Field',
			'name'            => 'test-custom-field',
			'type'            => 'text',
			'context'         => '',
			'state'           => 1,
			'access'          => 1,
			'language'        => '*',
			'created_time'    => (new \DateTime())->format('Y-m-d H:i:s'),
			'created_user_id' => $I->grabFromDatabase('users', 'id', ['username' => 'admin']),
			'params'          => '',
			'fieldparams'     => '',
			'default_value'   => '',
			'required'        => 0,
			'description'     => '',
			'modified_time'   => (new \DateTime())->format('Y-m-d H:i:s')
		];

		if (\is_array($data)) {
			$field = array_merge($field, $data);
		}

		if (\is_array($field['params'])) {
			$field['params'] = json_encode($field['params']);
		}
		if (\is_array($field['fieldparams'])) {
			$field['fieldparams'] = json_encode($field['fieldparams']);
		}

		$cats = [];
		if (!empty($field['assigned_cat_ids'])) {
			$cats = $field['assigned_cat_ids'];
			unset($field['assigned_cat_ids']);
		}

		$field['id'] = $I->haveInDatabase('fields', $field);

		if ($itemId !== null && $value !== null) {
			$I->haveInDatabase('fields_values', ['field_id' => $field['id'], 'item_id' => $itemId, 'value' => $value]);
		}

		if ($cats) {
			foreach ($cats as $cat) {
				$I->haveInDatabase('fields_categories', ['field_id' => $field['id'], 'category_id' => $cat]);
			}
		}

		return $field;
	}
}
