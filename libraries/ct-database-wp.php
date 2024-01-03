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

		$possibleOperators = ['=', '>', '<', '!=', '>=', '<=', 'LIKE', 'NULL', 'NOT NULL', 'INSTR'];

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

		$possibleOperators = ['=', '>', '<', '!=', '>=', '<=', 'LIKE', 'NULL', 'NOT NULL', 'INSTR'];

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

	public function getWhereClause(): string
	{
		$where = '';
		$count = count($this->conditions);
		$orCount = count($this->orConditions);
		$nestedCount = count($this->nestedConditions);
		$nestedOrCount = count($this->nestedOrConditions);

		if ($count > 0 || $orCount > 0 || $nestedCount > 0) {

			// Process regular conditions
			$where .= self::getWhereClauseMergeConditions($this->conditions, $count, $orCount, $nestedCount, $nestedOrCount);

			// Process OR conditions
			if ($orCount > 0) {

				$whereNew = self::getWhereClauseMergeConditions($this->orConditions, $count, $orCount, $nestedCount, $nestedOrCount, 'OR');

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

	protected function getWhereClauseMergeConditions($conditions, $count, $orCount, $nestedCount, $nestedOrCount, $logicalOperator = 'AND'): string
	{
		$where = '';

		foreach ($conditions as $index => $condition) {

			if ($where != '')
				$where .= ' ' . $logicalOperator . ' ';

			if ($condition['value'] === null) {
				$where .= $condition['field'];
			} elseif ($condition['operator'] == 'NULL') {
				$where .= $condition['field'] . ' IS NULL';
			} elseif ($condition['operator'] == 'NOT NULL') {
				$where .= $condition['field'] . ' IS NOT NULL';
			} elseif ($condition['operator'] == 'INSTR') {
				if ($condition['sanitized']) {
					$where .= 'INSTR(' . $condition['field'] . ',' . $condition['value'] . ')';
				} else {
					$where .= 'INSTR(' . $condition['field'] . ',' . $this->getPlaceholder($condition['value']) . ')';
					$this->placeholderValues[] = $condition['value'];
				}
			} elseif ($condition['operator'] == 'NOT INSTR') {
				if ($condition['sanitized']) {
					$where .= '!INSTR(' . $condition['field'] . ',' . $condition['value'] . ')';
				} else {
					$where .= '!INSTR(' . $condition['field'] . ',' . $this->getPlaceholder($condition['value']) . ')';
					$this->placeholderValues[] = $condition['value'];
				}
			} elseif ($condition['operator'] == 'REGEXP') {
				if ($condition['sanitized']) {
					$where .= $condition['field'] . ' REGEXP ' . $condition['value'];
				} else {
					$where .= $condition['field'] . ' REGEXP ' . $this->getPlaceholder($condition['value']);
					$this->placeholderValues[] = $condition['value'];
				}

			} else {
				if ($condition['sanitized']) {
					$where .= $condition['field'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
				} else {
					$where .= $condition['field'] . ' ' . $condition['operator'] . ' ' . $this->getPlaceholder($condition['value']);
					$this->placeholderValues[] = $condition['value'];
				}
			}
		}
		return $where;
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

	public static function getNumRowsOnly($query): int
	{
		global $wpdb;
		$wpdb->query(str_replace('#__', $wpdb->prefix, $query));
		return $wpdb->num_rows;
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
	                                     ?string $orderBy = null, ?int $limit = null, ?int $limitStart = null, string $groupBy = null): array
	{
		return self::loadObjectList($table, $selects, $whereClause, $order, $orderBy, $limit, $limitStart, 'ARRAY_A', $groupBy);
	}

	/**
	 * @throws Exception
	 * @since 3.2.2
	 */
	public static function loadObjectList(string  $table, array $selectsRaw, MySQLWhereClause $whereClause,
	                                      ?string $order = null, ?string $orderBy = null,
	                                      ?int    $limit = null, ?int $limitStart = null, string $output_type = 'OBJECT', string $groupBy = null)
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

		if (count($placeholders) == 0) {
			$results = $wpdb->get_results("SELECT " . implode(',', $selects) . " FROM " . $realTableName
				. ($whereString != '' ? ' WHERE ' . $whereString : '')
				. (!empty($groupBy) != '' ? ' GROUP BY ' . $groupBy : '')
				. (!empty($order) ? ' ORDER BY ' . $order . ($orderBy !== null and strtolower($orderBy) == 'desc' ? ' DESC' : '') : '')
				. (!empty($limit) ? ' LIMIT %d' : '')//Use of single explicit placeholder is needed for WPCS verification because it thinks that $placeholders is a single variable, but it's an array
				. (!empty($limitStart) ? ' OFFSET ' . $limitStart : ''),
				$output_type);// phpcs:ignore WordPress.DB.PreparedSQL -- Ignore Prepared SQL warnings
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare("SELECT " . implode(',', $selects) . " FROM " . $realTableName
					. ($whereString != '' ? ' WHERE ' . $whereString : '')
					. (!empty($groupBy) != '' ? ' GROUP BY ' . $groupBy : '')
					. (!empty($order) ? ' ORDER BY ' . $order . ($orderBy !== null and strtolower($orderBy) == 'desc' ? ' DESC' : '') : '')
					. (!empty($limit) ? ' LIMIT %d' : '')//Use of single explicit placeholder is needed for WPCS verification because it thinks that $placeholders is a single variable, but it's an array
					. (!empty($limitStart) ? ' OFFSET ' . $limitStart : ''), ...$placeholders),
				$output_type);// phpcs:ignore WordPress.DB.PreparedSQL -- Ignore Prepared SQL warnings
		}

		if ($wpdb->last_error !== '')
			throw new Exception($wpdb->last_error);

		return $results;
	}

	public static function loadRowList($query, $limitStart = null, $limit = null): ?array
	{
		global $wpdb;

		if ($limit !== null)
			$query .= ' LIMIT ' . $limit;

		if ($limitStart !== null)
			$query .= ' OFFSET ' . $limitStart;

		return $wpdb->get_results(str_replace('#__', $wpdb->prefix, $query), ARRAY_N);
	}

	public static function loadColumn($query, $limitStart = null, $limit = null): ?array
	{
		global $wpdb;

		if ($limit !== null)
			$query .= ' LIMIT ' . $limit;

		if ($limitStart !== null)
			$query .= ' OFFSET ' . $limitStart;

		return $wpdb->get_col(str_replace('#__', $wpdb->prefix, $query));
	}

	public static function getTableStatus(string $database, string $tablename, bool $addPrefix = true)
	{
		global $wpdb;
		$dbPrefix = $wpdb->prefix;

		if ($addPrefix)
			$realTableName = $dbPrefix . 'customtables_table_' . $tablename;
		else
			$realTableName = $tablename;

		return $wpdb->get_results('SHOW TABLE STATUS FROM "' . $database . '" LIKE "' . $realTableName . '"');
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

	public static function quote($value, bool $row = true): ?string
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