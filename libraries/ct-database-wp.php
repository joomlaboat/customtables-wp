<?php
/**
 * CustomTables WordPress 6.x Plugin
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2023. Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

// no direct access
if (!defined('WPINC')) {
	die('Restricted access');
}

use Exception;

class MySQLWhereClause
{
	public array $placeholderValues = [];
	public array $conditions = [];
	private array $orConditions = [];
	private array $nestedConditions = [];
	private array $nestedOrConditions = [];

	function __construct()
	{
		// Placeholder values array
		$this->placeholderValues = [];
	}

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

		$possibleOperators = ['=', '>', '<', '!=', '>=', '<=', 'LIKE', 'NULL', 'NOT NULL', 'INSTR','NOT INSTR'];

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

		$possibleOperators = ['=', '>', '<', '!=', '>=', '<=', 'LIKE', 'NULL', 'NOT NULL', 'INSTR','NOT INSTR'];

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

		return $wpdb->prepare($whereString, $placeholders);
	}

	public function getWhereClause(string $logicalOperator = 'AND'): string
	{
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
		if (count($this->nestedOrConditions) > 0) {
			foreach ($this->nestedOrConditions as $nestedOrCondition) {
				$where [] = '(' . $nestedOrCondition->getWhereClause('OR') . ')';
				$nestedValues = $nestedOrCondition->getWhereClausePlaceholderValues();
				$this->placeholderValues = array_merge($this->placeholderValues, $nestedValues);
			}
		}
		return implode(' ' . $logicalOperator . ' ', $where);
	}

	/*
	public function getWhereClause(): string
	{
		$where = '';
		$count = count($this->conditions);
		$orCount = count($this->orConditions);
		$nestedCount = count($this->nestedConditions);
		$nestedOrCount = count($this->nestedOrConditions);

		if ($count > 0 || $orCount > 0 || $nestedCount > 0) {

			// Process regular conditions
			$where .= self::getWhereClauseMergeConditions($this->conditions);

			// Process OR conditions
			if ($orCount > 0) {

				$whereNew = self::getWhereClauseMergeConditions($this->orConditions);

				if ($whereNew != '')
					$where .= '(' . $whereNew . ')';
			}

			// Process nested conditions
			if ($nestedCount > 0) {
				foreach ($this->nestedConditions as $nestedCondition) { //foreach ($this->nestedConditions as $index => $nestedCondition) {
					$nestedWhere = $nestedCondition->getWhereClause();
					$nestedValues = $nestedCondition->getWhereClausePlaceholderValues();

					if (!empty($nestedWhere)) {
						if ($count > 0 || $orCount > 0) {
							$where .= ' AND ';
						}
						$where .= '(' . $nestedWhere . ')';
						$this->placeholderValues = array_merge($this->placeholderValues, $nestedValues);
					}
				}
			}

			// Process nested OR conditions
			if ($nestedOrCount > 0) {
				foreach ($this->nestedOrConditions as $nestedOrCondition) { //foreach ($this->nestedConditions as $index => $nestedCondition) {
					$nestedWhere = $nestedOrCondition->getWhereClause();
					$nestedValues = $nestedOrCondition->getWhereClausePlaceholderValues();

					if (!empty($nestedWhere)) {
						if ($count > 0 || $orCount > 0) {
							$where .= ' OR ';
						}
						$where .= '(' . $nestedWhere . ')';
						$this->placeholderValues = array_merge($this->placeholderValues, $nestedValues);
					}
				}
			}
		}
		echo '$where=' . $where . '<br/>';
		return $where;
	}
*/
	protected function getWhereClauseMergeConditions($conditions,  $logicalOperator = 'AND'): string
	{
		$where = [];

		foreach ($conditions as $condition) {

			if ($condition['value'] === null) {
				$where []= $condition['field'];
			} elseif ($condition['operator'] == 'NULL') {
				$where []= $condition['field'] . ' IS NULL';
			} elseif ($condition['operator'] == 'NOT NULL') {
				$where []= $condition['field'] . ' IS NOT NULL';
			} elseif ($condition['operator'] == 'INSTR') {
				if ($condition['sanitized']) {
					$where []= 'INSTR(' . $condition['field'] . ',' . $condition['value'] . ')';
				} else {
					$where []= 'INSTR(' . $condition['field'] . ',' . $this->getPlaceholder($condition['value']) . ')';
					$this->placeholderValues[] = $condition['value'];
				}
			} elseif ($condition['operator'] == 'NOT INSTR') {
				if ($condition['sanitized']) {
					$where []= '!INSTR(' . $condition['field'] . ',' . $condition['value'] . ')';
				} else {
					$where []= '!INSTR(' . $condition['field'] . ',' . $this->getPlaceholder($condition['value']) . ')';
					$this->placeholderValues[] = $condition['value'];
				}
			} elseif ($condition['operator'] == 'REGEXP') {
				if ($condition['sanitized']) {
					$where []= $condition['field'] . ' REGEXP ' . $condition['value'];
				} else {
					$where []= $condition['field'] . ' REGEXP ' . $this->getPlaceholder($condition['value']);
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
					$where [] = $condition['field'] . ' ' . $condition['operator'] . ' '.$this->getPlaceholder($condition['value']);
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
}

class database
{
	public static function getDBPrefix(): ?string
	{
		global $wpdb;
		return $wpdb->prefix;
	}

	public static function getDataBaseName(): ?string
	{
		return DB_NAME;
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function setQuery($query): void
	{
		global $wpdb;
		$wpdb->query(str_replace('#__', $wpdb->prefix, $query));
		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);
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
		$wpdb->insert(str_replace('#__', $wpdb->prefix, $tableName), $data);

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		$id = $wpdb->insert_id;
		if (!$id)
			return null;

		return $id;
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
	public static function update(string $tableName, array $data, array $where): bool
	{
		if (count($data) == 0)
			return true;


		global $wpdb;
		$wpdb->update(str_replace('#__', $wpdb->prefix, $tableName), $data, $where);
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
	public static function loadAssocList(string  $table, array $selects, MySQLWhereClause $whereClause, ?string $order = null,
	                                     ?string $orderBy = null, ?int $limit = null,
	                                     ?int $limitStart = null, string $groupBy = null)
	{
		return self::loadObjectList($table, $selects, $whereClause, $order, $orderBy, $limit, $limitStart, 'ARRAY_A', $groupBy);
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function loadObjectList(string  $table, array $selectsRaw, MySQLWhereClause $whereClause,
	                                      ?string $order = null, ?string $orderBy = null,
	                                      ?int    $limit = null, ?int $limitStart = null, string $output_type = 'OBJECT',
	                                      string $groupBy = null)
	{
		global $wpdb;

		$selects = [];
		foreach ($selectsRaw as $s) {
			$selects[] = str_replace('#__', $wpdb->prefix, $s);
		}

		//Tables name may have Joomla prefix as this Library is cross-platform (Joomla and WordPress)
		$realTableName = str_replace('#__', $wpdb->prefix, $table);
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

		$query = "SELECT " . implode(',', $selects) . " FROM " . $realTableName
			. ($whereString != '' ? ' WHERE ' . $whereString : '')
			. (!empty($groupBy) != '' ? ' GROUP BY ' . $groupBy : '')
			. (!empty($order) ? ' ORDER BY ' . $order . ($orderBy !== null and strtolower($orderBy) == 'desc' ? ' DESC' : '') : '')
			. (!empty($limit) ? ' LIMIT %d' : '')//Use of single explicit placeholder is needed for WPCS verification because it thinks that $placeholders is a single variable, but it's an array
			. (!empty($limitStart) ? ' OFFSET ' . $limitStart : '');

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

		if ($output_type == 'COLUMN')
		{
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

	public static function loadRowList(string  $table, array $selects, MySQLWhereClause $whereClause,
	                                   ?string $order = null, ?string $orderBy = null,
	                                   ?int    $limit = null, ?int $limitStart = null,
	                                   string  $groupBy = null, bool $returnQueryString = false)
	{
		return self::loadObjectList($table, $selects, $whereClause, $order, $orderBy, $limit, $limitStart, 'ROW_LIST', $groupBy, $returnQueryString);
	}

	public static function loadColumn(string  $table, array $selects, MySQLWhereClause $whereClause,
	                                  ?string $order = null, ?string $orderBy = null,
	                                  ?int    $limit = null, ?int $limitStart = null,
	                                  string  $groupBy = null, bool $returnQueryString = false)
	{
		return self::loadObjectList($table, $selects, $whereClause, $order, $orderBy, $limit, $limitStart, 'COLUMN', $groupBy, $returnQueryString);
	}

	public static function getTableStatus(string $database, string $tablename, bool $addPrefix = true)
	{
		global $wpdb;
		$dbPrefix = $wpdb->prefix;

		if ($addPrefix)
			$realTableName = $dbPrefix . 'customtables_table_' . $tablename;
		else
			$realTableName = $tablename;

		return $wpdb->get_results('SHOW TABLE STATUS FROM ' . $database . ' LIKE "' . $realTableName . '"');
	}

	public static function getTableIndex(string $tableName, string $fieldName)
	{
		global $wpdb;
		return $wpdb->get_results('SHOW INDEX FROM ' . $tableName . ' WHERE Key_name = "' . $fieldName . '"');
	}

	public static function showTables()
	{
		global $wpdb;
		return $wpdb->get_results('SHOW TABLES', 'ARRAY_A');
	}

	public static function showCreateTable($tableName)
	{
		global $wpdb;
		return $wpdb->get_results('SHOW CREATE TABLE ' . $tableName, 'ARRAY_A');
	}

	public static function getExistingFields($tableName)
	{
		global $wpdb;
		return $wpdb->get_results('SHOW COLUMNS FROM ' . $tableName, 'ARRAY_A');
	}

	public static function getFieldType(string $realtablename, $realfieldname)
	{
		global $wpdb;

		$realtablename = self::realTableName($realtablename);
		$serverType = self::getServerType();

		if ($serverType == 'postgresql')
			$query = 'SELECT data_type FROM information_schema.columns WHERE table_name = ' . database::quote($realtablename) . ' AND column_name=' . database::quote($realfieldname);
		else
			$query = 'SHOW COLUMNS FROM ' . $realtablename . ' WHERE ' . database::quoteName('field') . '=' . database::quote($realfieldname);

		$rows = $wpdb->get_results($query, 'ARRAY_A');

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

	public static function quoteName($value)
	{
		return $value;
	}
}