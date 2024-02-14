<?php
/**
 * CustomTables WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2024. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

// no direct access
if (!defined('WPINC')) {
	die('Restricted access');
}

use Exception;
use WP_User_Query;

class MySQLWhereClause
{
	public array $placeholderValues = [];
	public array $conditions = [];
	private array $orConditions = [];
	private array $nestedConditions = [];
	private array $nestedOrConditions = [];

	public function hasConditions(): bool
	{
		if (count($this->conditions) > 0)
			return true;

		if (count($this->orConditions) > 0)
			return true;

		if (count($this->nestedConditions) > 0)
			return true;

		if (count($this->nestedOrConditions) > 0)
			return true;

		return false;
	}

	public function addConditionsFromArray(array $conditions): void
	{
		foreach ($conditions as $fieldName => $fieldValue) {
			// Assuming default operator is '=' if not specified
			$this->addCondition($fieldName, $fieldValue);
		}
	}

	public function addCondition($fieldName, $fieldValue, $operator = '=', $sanitized = false): void
	{
		$operator = strtoupper($operator);

		$possibleOperators = ['=', '>', '<', '!=', '>=', '<=', 'LIKE', 'NULL', 'NOT NULL', 'INSTR', 'NOT INSTR', 'IN'];

		if (!in_array($operator, $possibleOperators)) {
			throw new \mysql_xdevapi\Exception('SQL Where Clause operator "' . common::ctStripTags($operator) . '" not recognized.');
		}

		$this->conditions[] = [
			'field' => $fieldName,
			'value' => $fieldValue,
			'operator' => $operator,
			'sanitized' => $sanitized
		];
	}

	public function addOrCondition($fieldName, $fieldValue, $operator = '=', $sanitized = false): void
	{
		$operator = strtoupper($operator);

		$possibleOperators = ['=', '>', '<', '!=', '>=', '<=', 'LIKE', 'NULL', 'NOT NULL', 'INSTR', 'NOT INSTR', 'IN'];

		if (!in_array($operator, $possibleOperators)) {
			throw new \mysql_xdevapi\Exception('SQL Where Clause operator "' . common::ctStripTags($operator) . '" not recognized.');
		}

		$this->orConditions[] = [
			'field' => $fieldName,
			'value' => $fieldValue,
			'operator' => $operator,
			'sanitized' => $sanitized
		];

	}

	public function addNestedCondition(MySQLWhereClause $condition): void
	{
		$this->nestedConditions[] = $condition;
	}

	public function addNestedOrCondition(MySQLWhereClause $orCondition): void
	{
		$this->nestedOrConditions[] = $orCondition;
	}

	public function __toString(): string
	{
		global $wpdb;
		$whereString = $this->getWhereClause();// Returns the "where" clause with %d,%f,%s placeholders
		$placeholders = $this->getWhereClausePlaceholderValues();//Values
		if (count($placeholders) == 0)
			return $whereString;

		//$whereString already contains the placeholders like (%s, %d, %f),and the number is matching with replacement variables;
		return $wpdb->prepare($whereString, $placeholders);
	}

	public function getWhereClause(string $logicalOperator = 'AND'): string
	{
		$this->placeholderValues = [];
		$where = [];

		// Process regular conditions
		if (count($this->conditions))
			$where [] = self::getWhereClauseMergeConditions($this->conditions);

		// Process OR conditions
		if (count($this->orConditions) > 0)
			$where [] = '(' . self::getWhereClauseMergeConditions($this->orConditions, 'OR') . ')';

		// Process nested conditions
		if (count($this->nestedConditions) > 0) {

			foreach ($this->nestedConditions as $nestedCondition) {
				$where [] = $nestedCondition->getWhereClause();
				$nestedValues = $nestedCondition->getWhereClausePlaceholderValues();
				$this->placeholderValues = array_merge($this->placeholderValues, $nestedValues);
			}
		}

		// Process nested OR conditions
		$orWhere = [];
		foreach ($this->nestedOrConditions as $nestedOrCondition) {
			if ($nestedOrCondition->countConditions() == 1)
				$orWhere [] = $nestedOrCondition->getWhereClause('OR');
			else
				$orWhere [] = '(' . $nestedOrCondition->getWhereClause('OR') . ')';

			$nestedValues = $nestedOrCondition->getWhereClausePlaceholderValues();
			$this->placeholderValues = array_merge($this->placeholderValues, $nestedValues);
		}

		if (count($orWhere) > 0)
			$where [] = implode(' OR ', $orWhere);

		return implode(' ' . $logicalOperator . ' ', $where);
	}

	protected function getWhereClauseMergeConditions($conditions, $logicalOperator = 'AND'): string
	{
		$where = [];

		foreach ($conditions as $condition) {

			if ($condition['value'] === null) {
				$where [] = $condition['field'];
			} elseif ($condition['operator'] == 'NULL') {
				$where [] = $condition['field'] . ' IS NULL';
			} elseif ($condition['operator'] == 'NOT NULL') {
				$where [] = $condition['field'] . ' IS NOT NULL';
			} elseif ($condition['operator'] == 'LIKE') {
				$where [] = $condition['field'] . ' LIKE ' . $this->getPlaceholder($condition['value']);
				$this->placeholderValues[] = $condition['value'];
			} elseif ($condition['operator'] == 'INSTR') {
				if ($condition['sanitized']) {
					$where [] = 'INSTR(' . $condition['field'] . ',' . $condition['value'] . ')';
				} else {
					$where [] = 'INSTR(' . $condition['field'] . ',' . $this->getPlaceholder($condition['value']) . ')';
					$this->placeholderValues[] = $condition['value'];
				}
			} elseif ($condition['operator'] == 'NOT INSTR') {
				if ($condition['sanitized']) {
					$where [] = '!INSTR(' . $condition['field'] . ',' . $condition['value'] . ')';
				} else {
					$where [] = '!INSTR(' . $condition['field'] . ',' . $this->getPlaceholder($condition['value']) . ')';
					$this->placeholderValues[] = $condition['value'];
				}
			} elseif ($condition['operator'] == 'REGEXP') {
				if ($condition['sanitized']) {
					$where [] = $condition['field'] . ' REGEXP ' . $condition['value'];
				} else {
					$where [] = $condition['field'] . ' REGEXP ' . $this->getPlaceholder($condition['value']);
					$this->placeholderValues[] = $condition['value'];
				}
			} elseif ($condition['operator'] == 'IN') {
				if ($condition['sanitized']) {
					$where [] = $condition['field'] . ' IN ' . $condition['value'];
				} else {
					$where [] = $this->getPlaceholder($condition['field']) . ' IN ' . $condition['value'];
					$this->placeholderValues[] = $condition['field'];
				}
			} else {
				if ($condition['sanitized']) {
					$where [] = $condition['field'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
				} else {
					$where [] = $condition['field'] . ' ' . $condition['operator'] . ' ' . $this->getPlaceholder($condition['value']);
					$this->placeholderValues[] = $condition['value'];
				}
			}
		}
		return implode(' ' . $logicalOperator . ' ', $where);
	}

	private function getPlaceholder($value): string
	{
		// Check the value type and return the appropriate placeholder
		if (is_int($value)) {
			return '%d';
		} elseif (is_float($value)) {
			return '%f';
		} else {
			return '%s';
		}
	}

	public function getWhereClausePlaceholderValues(): array
	{
		return $this->placeholderValues ?? [];
	}

	public function countConditions(): int
	{
		return count($this->conditions) + count($this->orConditions) + count($this->nestedConditions) + count($this->nestedOrConditions);
	}
}

class database
{
	public static function getDBPrefix(): ?string
	{
		global $wpdb;
		return $wpdb->prefix;
	}

	/**
	 * Inserts data into a database table in a cross-platform manner (Joomla and WordPress).
	 *
	 * @param string $tableName The name of the table to insert data into.
	 * @param array $data An associative array of data to insert. Keys represent column names, values represent values to be inserted.
	 *
	 * @return int|null The ID of the last inserted record, or null if the insert operation failed.
	 * @throws Exception If an error occurs during the insert operation.
	 *
	 * @since 3.1.8
	 */
	public static function insert(string $tableName, array $data): ?int
	{
		global $wpdb;

		$realTableName = str_replace('#__', $wpdb->prefix, $tableName);
		$dataHolders = (object)self::prepareFields($data);

		$columnPlaceHolder = [];

		for ($i = 0; $i < count($dataHolders->columns); $i++)
			$columnPlaceHolder[] = '%i';

		$values = [];
		foreach ($dataHolders->values as $value) {
			if ($value !== null)
				$values[] = $value;
		}

		$placeHolders = array_merge($dataHolders->columns, $values);

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $realTableName (" . implode(',', $columnPlaceHolder) . ")"
				. " VALUES (" . implode(",", $dataHolders->placeHolders) . ")", ...$placeHolders)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		$id = $wpdb->insert_id;
		if (!$id)
			return null;

		return $id;
	}

	protected static function prepareFields($data): array
	{
		// Construct the update statement
		$columns = [];
		$values = [];
		$placeHolders = [];
		foreach ($data as $key => $value) {

			$columns[] = $key;

			if (is_array($value) and count($value) == 2 and $value[1] == 'sanitized') {
				$values[] = null;
				$placeHolders[] = $value[0];
			} else {
				if ($value === null) {
					$values[] = null;
					$placeHolders[] = 'NULL';
				} elseif (is_bool($value)) {
					$values[] = null;
					$placeHolders[] = $value ? 'TRUE' : 'FALSE';
				} elseif (is_int($value)) {
					$values[] = $value;
					$placeHolders[] = '%d';
				} elseif (is_float($value)) {
					$values[] = $value;
					$placeHolders[] = '%f';
				} else {
					$values[] = $value;
					$placeHolders[] = '%s';
				}
			}
		}
		return ['columns' => $columns, 'values' => $values, 'placeHolders' => $placeHolders];
	}

	/**
	 * Updates data in a database table in a cross-platform manner (Joomla and WordPress).
	 *
	 * @param string $tableName The name of the table to update.
	 * @param array $data An associative array of data to update. Keys represent column names, values represent new values.
	 * @param array $where An associative array specifying which rows to update. Keys represent column names, values represent conditions for the update.
	 *
	 * @return bool True if the update operation is successful, otherwise false.
	 * @throws Exception If an error occurs during the update operation.
	 *
	 * @since 3.1.8
	 */
	public static function update(string $tableName, array $data, MySQLWhereClause $whereClause): bool
	{
		global $wpdb;

		if (!$whereClause->hasConditions()) {
			throw new Exception('Update database table records without WHERE clause is prohibited.');
		}

		$realTableName = str_replace('#__', $wpdb->prefix, $tableName);

		if (count($data) == 0)
			return true;

		$dataHolders = (object)self::prepareFields($data);
		$columnPlaceHolder = [];
		$placeHolders = [];
		$index = 0;

		foreach ($dataHolders->columns as $column) {
			$placeHolders[] = $column;//$dataHolders->columns[$index];
			if ($dataHolders->values[$index] !== null)
				$placeHolders[] = $dataHolders->values[$index];

			$columnPlaceHolder[] = '%i=' . $dataHolders->placeHolders[$index];

			$index += 1;
		}

		$whereString = $whereClause->getWhereClause();// Returns the "where" clause with %d,%f,%s placeholders
		$wherePlaceHolders = $whereClause->getWhereClausePlaceholderValues();//Values

		$placeHolders = array_merge($placeHolders, $wherePlaceHolders);

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $realTableName SET " . implode(',', $columnPlaceHolder) . " WHERE " . $whereString, ...$placeHolders)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		return true;
	}

	public static function getVersion(): ?float
	{
		global $wpdb;
		$result = $wpdb->get_results('select @@version', 'ARRAY_A');
		return floatval($result[0]['@@version']);
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function loadRowList(string  $table, array $selects, MySQLWhereClause $whereClause,
	                                   ?string $order = null, ?string $orderBy = null,
	                                   ?int    $limit = null, ?int $limitStart = null,
	                                   string  $groupBy = null, bool $returnQueryString = false): array
	{
		return self::loadObjectList($table, $selects, $whereClause, $order, $orderBy, $limit, $limitStart, 'ROW_LIST', $groupBy, $returnQueryString);
	}

	protected static function sanitizeSelects(array $selectsRaw, string $realTableName): string
	{
		global $wpdb;
		$serverType = database::getServerType();

		$selects = [];

		foreach ($selectsRaw as $select) {

			if ($select == '*') {
				$selects[] = '*';
			} elseif ($select == 'LISTING_PUBLISHED') {
				$selects[] = '`' . $realTableName . '`.`published` AS listing_published';
			} elseif ($select == 'LISTING_PUBLISHED_1') {
				$selects[] = '1 AS listing_published';
			} elseif ($select == 'COUNT_ROWS') {
				$selects[] = 'COUNT(*) AS record_count';
			} elseif ($select == 'MODIFIED_BY') {
				$selects[] = '(SELECT display_name FROM ' . $wpdb->prefix . 'users AS u WHERE u.ID=a.modified_by LIMIT 1) AS modifiedby';
			} elseif ($select == 'LAYOUT_SIZE') {
				$selects[] = 'LENGTH(layoutcode) AS layout_size';
			} elseif ($select == 'GROUP_TITLE') {
				$selects[] = '(SELECT `title` FROM `' . $wpdb->prefix . 'usergroups` AS g WHERE g.id = m.group_id LIMIT 1) AS group_title';
			} elseif ($select == 'TABLE_TITLE') {
				$selects[] = '(SELECT tabletitle FROM `' . $wpdb->prefix . 'customtables_tables` AS tables WHERE tables.id=a.tableid) AS tabletitle';
			} elseif ($select == 'TABLE_NAME') {
				$selects[] = '(SELECT tablename FROM `' . $wpdb->prefix . 'customtables_tables` AS tables WHERE tables.id=a.tableid) AS TABLE_NAME';
			} elseif ($select == 'CATEGORY_NAME') {
				$selects[] = '(SELECT `categoryname` FROM `' . $wpdb->prefix . 'customtables_categories` AS categories WHERE categories.id=tablecategory LIMIT 1) AS categoryname';
			} elseif ($select == 'FIELD_COUNT') {
				$selects[] = '(SELECT COUNT(fields.id) FROM ' . $wpdb->prefix . 'customtables_fields AS fields WHERE fields.tableid=a.id AND fields.published=1 LIMIT 1) AS fieldcount';
			} elseif ($select == 'MODIFIED_TIMESTAMP') {
				if ($serverType == 'postgresql')
					$selects [] = 'CASE WHEN modified IS NULL THEN extract(epoch FROM created) ELSE extract(epoch FROM modified) AS modified_timestamp';
				else
					$selects [] = 'IF(modified IS NULL,UNIX_TIMESTAMP(created),UNIX_TIMESTAMP(modified)) AS modified_timestamp';
			} elseif ($select == 'REAL_FIELD_NAME') {
				if ($serverType == 'postgresql')
					$selects[] = 'CASE WHEN customfieldname!="" THEN customfieldname ELSE CONCAT("es_",fieldname) END AS realfieldname';
				else
					$selects[] = 'IF(customfieldname!="", customfieldname, CONCAT("es_",fieldname)) AS realfieldname';
			} elseif ($select == 'REAL_TABLE_NAME') {
				if ($serverType == 'postgresql') {
					$selects[] = 'CASE WHEN customtablename!="" THEN customtablename ELSE CONCAT("' . $wpdb->prefix . 'customtables_table_", tablename) END AS realtablename';
				} else {
					$selects[] = 'IF((customtablename IS NOT NULL AND customtablename!=""), customtablename, CONCAT("' . $wpdb->prefix . 'customtables_table_", tablename)) AS realtablename';
				}
			} elseif ($select == 'REAL_ID_FIELD_NAME') {
				if ($serverType == 'postgresql') {
					$selects[] = 'CASE WHEN customidfield!="" THEN customidfield ELSE "id" END AS realidfieldname';
				} else {
					$selects[] = 'IF(customidfield!="", customidfield, "id") AS realidfieldname';
				}
			} elseif ($select == 'PUBLISHED_FIELD_FOUND') {
				$selects[] = '1 AS published_field_found';
			} elseif (str_contains($select, 'CUSTOM_VALUE')) {
				$parts = explode('_', $select);
				if (count($parts) == 5) {

					$selectTable = str_replace('#__', $wpdb->prefix, $parts[3]);//Joomla way
					$selectTable = preg_replace('/[^a-zA-Z0-9_]/', '', $selectTable);
					$selectField = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[4]);

					if ($parts[2] == 'COUNT')
						$selects[] = 'COUNT(`' . $selectTable . '`.`' . $selectField . '`) AS vlu';
					if ($parts[2] == 'SUM')
						$selects[] = 'SUM(`' . $selectTable . '`.`' . $selectField . '`) AS vlu';
					if ($parts[2] == 'AVG')
						$selects[] = 'AVG(`' . $selectTable . '`.`' . $selectField . '`) AS vlu';
					if ($parts[2] == 'MIN')
						$selects[] = 'MIN(`' . $selectTable . '`.`' . $selectField . '`) AS vlu';
					if ($parts[2] == 'MAX')
						$selects[] = 'MAX(`' . $selectTable . '`.`' . $selectField . '`) AS vlu';
					if ($parts[2] == 'VALUE')
						$selects[] = '`' . $selectTable . '`.`' . $selectField . '` AS vlu';
				}
			} else {
				$parts = explode('.', $select);
				if (count($parts) == 2) {
					$selectTable = str_replace('#__', $wpdb->prefix, $parts[0]);//Joomla way
					$selectTable = preg_replace('/[^a-zA-Z0-9_]/', '', $selectTable);

					if($parts[1]=='*')
						$selects[] = "" . $selectTable . ".*";
					else {
						$column_name = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[1]);
						$selects[] = "`" . $selectTable . "`.`" . $column_name . "`";
					}
				} else {
					$column_name = preg_replace('/[^a-zA-Z0-9_]/', '', $select);
					$selects[] = "`" . $column_name . "`";
				}
			}
		}
		return implode(',', $selects);
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function loadObjectList(string  $table, array $selectsRaw, MySQLWhereClause $whereClause,
	                                      ?string $order = null, ?string $orderBy = null,
	                                      ?int    $limit = null, ?int $limitStart = null, string $output_type = 'OBJECT',
	                                      string  $groupBy = null)
	{
		global $wpdb;

		//Tables name may have Joomla prefix as this Library is cross-platform (Joomla and WordPress)

		$realTableName = str_replace('#__', $wpdb->prefix, $table);

		if($realTableName != 'information_schema.columns' and $realTableName != 'information_schema.tables')
			$realTableName = preg_replace('/[^a-zA-Z0-9_ ]/', '', $realTableName);

		//Select columns sanitation
		$selects_sanitized = self::sanitizeSelects($selectsRaw, $realTableName);

		$whereString = $whereClause->getWhereClause();// Returns the "where" clause with %d,%f,%s placeholders
		$placeholders = $whereClause->getWhereClausePlaceholderValues();//Values

		if (!empty($limit))
			$placeholders[] = $limit;

		// https://core.trac.wordpress.org/ticket/54042
		// phpcs:ignore WordPress.DB.PreparedSQL -- Ignore Prepared SQL warnings
		// WPCS does not recognize splat operators

		//$selects are internal and not provided by the user, so cannot be manipulated.
		//$realTableName is internal and cannot be manipulated
		//Where values sanitized properly in MySQLWhereClause class

		$query = "SELECT $selects_sanitized FROM " . $realTableName
			. ($whereString != '' ? ' WHERE ' . $whereString : '')
			. (!empty($groupBy) != '' ? ' GROUP BY ' . $groupBy : '')
			. (!empty($order) ? ' ORDER BY ' . $order . ($orderBy !== null and strtolower($orderBy) == 'desc' ? ' DESC' : '') : '')
			. (!empty($limit) ? ' LIMIT %d' : '')//Use of single explicit placeholder is needed for WPCS verification because it thinks that $placeholders is a single variable, but it's an array
			. (!empty($limitStart) ? ' OFFSET ' . (int)$limitStart : '');

		if (count($placeholders) > 0)
			$query = $wpdb->prepare($query, ...$placeholders);

		$output_type_temp = $output_type;

		if ($output_type == 'ROW_LIST' or $output_type == 'COLUMN')
			$output_type_temp = 'ARRAY_A';

		$results = $wpdb->get_results($query, $output_type_temp);// phpcs:ignore WordPress.DB.PreparedSQL -- Ignore Prepared SQL warnings

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		if ($output_type == 'OBJECT' or $output_type == 'ARRAY_A')
			return $results;

		if ($output_type == 'ROW_LIST') {
			// Convert associative array of associative arrays to a numerical array of associative arrays
			return array_values($results);
		}

		if ($output_type == 'COLUMN') {
			// Assuming $results is an array of associative arrays fetched from the database
			$firstRow = reset($results); // Get the first row from the result set

			if ($firstRow !== false) { // Check if there's at least one row
				$columnNames = array_keys($firstRow); // Get the keys (column names) of the associative array (first row)

				// The column name corresponding to the key of the first column
				$firstColumnName = $columnNames[0];

				// Extract a single column from the associative array of associative arrays{
				return array_column($results, $firstColumnName);
			} else {
				return null;
			}
		}
		return null;
	}

	public static function getServerType(): ?string
	{
		if (str_contains(DB_HOST, 'mysql')) {
			return 'mysql';
		} elseif (str_contains(DB_HOST, 'pgsql')) {
			return 'postgresql';
		} else {
			return 'Unknown';
		}
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function loadColumn(string  $table, array $selects, MySQLWhereClause $whereClause,
	                                  ?string $order = null, ?string $orderBy = null,
	                                  ?int    $limit = null, ?int $limitStart = null,
	                                  string  $groupBy = null, bool $returnQueryString = false): array
	{
		return self::loadObjectList($table, $selects, $whereClause, $order, $orderBy, $limit, $limitStart, 'COLUMN', $groupBy, $returnQueryString);
	}

	public static function getTableStatus(string $tableName, string $type = 'table'): array
	{
		global $wpdb;

		if ($type == 'gallery')
			$realTableName = $wpdb->prefix . 'customtables_gallery_' . $tableName;
		elseif ($type == 'filebox')
			$realTableName = $wpdb->prefix . 'customtables_filebox_' . $tableName;
		elseif ($type == 'native')
			$realTableName = $tableName;
		else
			$realTableName = $wpdb->prefix . 'customtables_' . $tableName;

		return $wpdb->get_results($wpdb->prepare("SHOW TABLE STATUS FROM " . DB_NAME . " LIKE %s", $realTableName));
	}

	public static function getTableIndex(string $tableName, string $fieldName): array
	{
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM %i WHERE Key_name = %s", $tableName, $fieldName));
	}

	public static function showTables(): array
	{
		global $wpdb;
		return $wpdb->get_results('SHOW TABLES', 'ARRAY_A');
	}

	public static function showCreateTable($tableName): array
	{
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare("SHOW CREATE TABLE %i", $tableName), 'ARRAY_A');
	}

	public static function getFieldType(string $realtablename, $realfieldname)
	{
		global $wpdb;

		$realtablename = self::realTableName($realtablename);
		$serverType = self::getServerType();

		if ($serverType == 'postgresql') {
			$query = 'SELECT `data_type` FROM `information_schema`.`columns` WHERE `table_name` = %s AND `column_name` = %s';
			$rows = $wpdb->get_results($wpdb->prepare($query, $realtablename, $realfieldname), 'ARRAY_A');
		} else {
			$query = 'SHOW COLUMNS FROM ' . $realtablename . ' WHERE `field` = %s';
			$rows = $wpdb->get_results($wpdb->prepare($query, $realfieldname), 'ARRAY_A');
		}

		if (count($rows) == 0)
			return '';

		$row = $rows[0];

		if ($serverType == 'postgresql')
			return $row['data_type'];
		else
			return $row['Type'];
	}

	public static function realTableName($tableName): ?string
	{
		global $wpdb;
		return str_replace('#__', $wpdb->prefix, $tableName);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function deleteRecord(string $tableName, string $realIdFieldName, $id): void
	{
		global $wpdb;

		if (is_int($id)) {
			$wpdb->query(
				$wpdb->prepare('DELETE FROM %i WHERE %i=%d', $tableName, $realIdFieldName, $id)
			);
		} else {
			$wpdb->query(
				$wpdb->prepare('DELETE FROM %i WHERE %i=%s', $tableName, $realIdFieldName, $id)
			);
		}

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	public static function deleteTableLessFields(): void
	{
		global $wpdb;

		$wpdb->query('DELETE FROM #__customtables_fields AS f WHERE (SELECT id FROM #__customtables_tables AS t WHERE t.id = f.tableid) IS NULL');

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	/**
	 * @throws Exception
	 * @1.1.2
	 */
	public static function dropTableIfExists(string $tableName, string $type = 'table'): void
	{
		global $wpdb;

		if ($type == 'gallery')
			$realTableName = $wpdb->prefix . 'customtables_gallery_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $tableName)));
		elseif ($type == 'filebox')
			$realTableName = $wpdb->prefix . 'customtables_filebox_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $tableName)));
		else
			$realTableName = $wpdb->prefix . 'customtables_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $tableName)));

		$serverType = self::getServerType();

		$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->_escape($realTableName));

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		if ($serverType == 'postgresql') {

			$wpdb->query('DROP SEQUENCE IF EXISTS `' . $wpdb->_escape($realTableName) . '._seq` CASCADE');

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);
		}
	}

	/**
	 * @throws Exception
	 * @1.1.2
	 */
	public static function dropColumn(string $realTableName, string $columnName): void
	{
		global $wpdb;

		$wpdb->query('SET foreign_key_checks = 0');

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i DROP COLUMN %i', $realTableName, $columnName)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		$wpdb->query('SET foreign_key_checks = 1');

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function addForeignKey(string $realTableName, string $columnName, string $join_with_table_name, string $join_with_table_field): void
	{
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i ADD FOREIGN KEY (%i) REFERENCES %i (%i) ON DELETE RESTRICT ON UPDATE RESTRICT',
				$realTableName, $columnName, self::getDataBaseName() . '.' . $join_with_table_name, $join_with_table_field)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	public static function getDataBaseName(): ?string
	{
		return DB_NAME;
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function dropForeignKey(string $realTableName, string $constrance): void
	{
		global $wpdb;

		$wpdb->query('SET foreign_key_checks = 0');

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i DROP FOREIGN KEY %i', $realTableName, $constrance)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		$wpdb->query('SET foreign_key_checks = 1');

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function setTableInnoDBEngine(string $realTableName, string $comment): void
	{
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i ENGINE = InnoDB', $realTableName)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function changeTableComment(string $realTableName, string $comment): void
	{
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i COMMENT %s', $realTableName, $comment)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function addIndex(string $realTableName, string $columnName): void
	{
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i ADD INDEX (%i)', $realTableName, $columnName)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function addColumn(string $realTableName, string $columnName, string $type, ?bool $nullable, ?string $extra = null, ?string $comment = null): void
	{
		global $wpdb;

		if ($comment === null)
			$comment = $columnName;

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i ADD COLUMN %i ' . $type
				. ($nullable !== null ? ($nullable ? ' NULL' : ' NOT NULL') : '')
				. ($extra !== null ? ' ' . $extra : '')
				. ' COMMENT %s'
				, $realTableName, $columnName, $comment)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function createTable(string $realTableName, string $privateKey, array $columns, string $comment, array $keys = null, string $primaryKeyType = 'int'): void
	{
		global $wpdb;

		if (self::getServerType() == 'postgresql') {

			$wpdb->query(
				$wpdb->prepare('CREATE SEQUENCE IF NOT EXISTS %i', $realTableName . '_seq')
			);
			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);

			$allColumns = array_merge([$privateKey . ' ' . $primaryKeyType . ' NOT NULL DEFAULT nextval (\'' . $realTableName . '_seq\')'], $columns);

			$wpdb->query(
				$wpdb->prepare('CREATE TABLE IF NOT EXISTS %i(' . implode(',', $allColumns) . ')', $realTableName)
			);

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);

			$wpdb->query(
				$wpdb->prepare('ALTER SEQUENCE %i RESTART WITH 1', $realTableName . '_seq')
			);
			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);

		} else {

			$primaryKeyTypeString = 'INT';//(11)
			if ($primaryKeyType !== 'int')
				$primaryKeyTypeString = $primaryKeyType;

			$allColumns = array_merge(['`' . $privateKey . '` ' . $primaryKeyTypeString . ' NOT NULL AUTO_INCREMENT'], $columns, ['PRIMARY KEY  (`id`)']);

			if ($keys !== null)
				$allColumns = array_merge($allColumns, $keys);

			$wpdb->query(
				$wpdb->prepare('CREATE TABLE IF NOT EXISTS %i(' . implode(',', $allColumns) . ')'
					. ' ENGINE=InnoDB COMMENT=%s'
					. ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1;', $realTableName, $comment)
			);
			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);
		}
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function copyCTTable(string $newTableName, string $oldTableName): void
	{
		global $wpdb;

		$realNewTableName = '#__customtables_table_' . $newTableName;

		if (self::getServerType() == 'postgresql') {

			$wpdb->query(
				$wpdb->prepare('CREATE SEQUENCE IF NOT EXISTS %i', $realNewTableName . '_seq')
			);

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);

			$wpdb->query(
				$wpdb->prepare('CREATE TABLE %i AS TABLE %i', $realNewTableName, '#__customtables_table_' . $oldTableName)
			);

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);


			$wpdb->query(
				$wpdb->prepare('ALTER SEQUENCE %i RESTART WITH 1', $realNewTableName . '_seq')
			);

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);

		} else {

			$wpdb->query(
				$wpdb->prepare('CREATE TABLE %i AS SELECT * FROM %i', '#__customtables_table_' . $newTableName, '#__customtables_table_' . $oldTableName)
			);

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);
		}

		$wpdb->query(
			$wpdb->prepare('ALTER TABLE %i ADD PRIMARY KEY (id)', $realNewTableName)
		);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		database::changeColumn($realNewTableName, 'id', 'id', 'INT UNSIGNED', false, null, 'AUTO_INCREMENT', 'Primary Key');
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function changeColumn(string $realTableName, string $oldColumnName, string $newColumnName, $PureFieldType, ?string $comment = null): void
	{
		global $wpdb;

		$possibleTypes = ['varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'tinyblob', 'blob', 'mediumblob',
			'longblob', 'char', 'int', 'bigint', 'numeric', 'decimal', 'smallint', 'tinyint', 'date', 'TIMESTAMP', 'datetime'];

		if (!in_array($PureFieldType['data_type'], $possibleTypes))
			throw new Exception('Change Column type: unsupported column type "' . $PureFieldType['data_type'] . '"');

		if ($comment === null)
			$comment = $newColumnName;

		if (self::getServerType() == 'postgresql') {
			if ($oldColumnName != $newColumnName)
				$wpdb->query("ALTER TABLE `{$realTableName}` RENAME COLUMN `{$oldColumnName}` TO `{$newColumnName}`");

			$wpdb->query("ALTER TABLE `{$realTableName}` ALTER COLUMN `{$newColumnName}` " . $PureFieldType['data_type']);

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);

		} else {
			$attributes = [];
			$type = $PureFieldType['data_type'];

			if (($PureFieldType['length'] ?? '') != '') {
				if (str_contains($PureFieldType['length'], ',')) {
					$parts = explode(',', $PureFieldType['length']);
					$placeholders = [];

					foreach ($parts as $part) {
						$placeholders [] = '%d';
						$attributes [] = (int)$part;
					}
					$type .= '(' . implode(',', $placeholders) . ')';
				} else {
					$type .= '(%d)';
					$attributes [] = (int)$PureFieldType['length'];
				}
			}

			if ($PureFieldType['is_unsigned'] ?? false)
				$type .= ' UNSIGNED';

			if (($PureFieldType['default'] ?? '') != '')
				$attributes [] = $PureFieldType['default'];

			$attributes [] = $comment;

			$wpdb->query($wpdb->prepare("ALTER TABLE `{$realTableName}` CHANGE `{$oldColumnName}` `{$newColumnName}`"
				. ' ' . $type
				. (($PureFieldType['is_nullable'] ?? false) ? ' NULL' : ' NOT NULL')
				. (($PureFieldType['default'] ?? '') != "" ? ' DEFAULT %s' : '')
				. (($PureFieldType['autoincrement'] ?? false) ? ' AUTO_INCREMENT' : '')
				. ' COMMENT %s', ...$attributes));

			if ($wpdb->last_error !== '')
				throw new Exception($wpdb->last_error);
		}
	}

	public static function quote($value, bool $row = false): ?string
	{
		global $wpdb;

		if ($row)
			return '\'' . esc_sql($value) . '\'';
		else
			return $wpdb->prepare('%s', $value);

		//    %d for integers
		//    %f for floating-point numbers
	}

	/**
	 * @throws Exception
	 * @since 1.1.2
	 */
	public static function renameTable(string $oldCTTableName, string $newCTTableName, string $type = 'table'): void
	{
		global $wpdb;

		//Validation
		if ($type == 'gallery') {
			$oldTableName = $wpdb->prefix . 'customtables_gallery_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $oldCTTableName)));
			$newTableName = $wpdb->prefix . 'customtables_gallery_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $newCTTableName)));
		} elseif ($type == 'filebox') {
			$oldTableName = $wpdb->prefix . 'customtables_filebox_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $oldCTTableName)));
			$newTableName = $wpdb->prefix . 'customtables_filebox_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $newCTTableName)));
		} else {
			$oldTableName = $wpdb->prefix . 'customtables_table_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $oldCTTableName)));
			$newTableName = $wpdb->prefix . 'customtables_table_' . strtolower(trim(preg_replace("/[^a-zA-Z_\d]/", "", $newCTTableName)));
		}
		$dbName = DB_NAME;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query("RENAME TABLE `$dbName`.`$oldTableName` TO `$dbName`.`$newTableName`");

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
	}

	public static function getExistingFields(string $tablename, $add_table_prefix = true): array
	{
		global $wpdb;

		$tablename = preg_replace('/[^a-zA-Z0-9_]/', '', $tablename);

		if ($add_table_prefix)
			$realtablename = $wpdb->prefix . 'customtables_table_' . $tablename;
		else
			$realtablename = $tablename;

		$serverType = database::getServerType();

		if ($serverType == 'postgresql') {
			$results = $wpdb->get_results($wpdb->prepare('SELECT
				`column_name`,`data_type`,`is_nullable`,`column_default` FROM `information_schema.columns` WHERE `table_name`=%s', $realtablename)
			);

		} else {
			$results = $wpdb->get_results($wpdb->prepare('SELECT
				`COLUMN_NAME` AS column_name,
				`DATA_TYPE` AS data_type,
				`COLUMN_TYPE` AS column_type,
				IF(`COLUMN_TYPE` LIKE "%unsigned", "YES", "NO") AS is_unsigned,
				`IS_NULLABLE` AS is_nullable,
				`COLUMN_DEFAULT` AS column_default,
				`EXTRA` AS extra FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`=%s AND `TABLE_NAME`=%s', DB_NAME, $realtablename), 'ARRAY_A');
		}

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		return $results;
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function loadAssocList(string  $table, array $selects, MySQLWhereClause $whereClause, ?string $order = null,
	                                     ?string $orderBy = null, ?int $limit = null,
	                                     ?int    $limitStart = null, string $groupBy = null)
	{
		return self::loadObjectList($table, $selects, $whereClause, $order, $orderBy, $limit, $limitStart, 'ARRAY_A', $groupBy);
	}
}