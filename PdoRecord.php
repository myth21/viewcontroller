<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use ReflectionException;
use RuntimeException;
use ReflectionClass;
use PDOException;
use PDOStatement;
use PDO;

/**
 * Class PdoRecord is PDO wrapper.
 * Note: class is tested for working with SQLite only.
 */
class PdoRecord implements TableRecord
{
    // todo what formats existing?
//    protected const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
//    protected const DATE_FORMAT = 'Y-m-d';

    protected static ?string $dsn = null;
    protected static PDO $pdo;
    protected ?PDOStatement $pdoStatement = null;
    protected static string $primaryKeyName = 'id';

    /**
     * Default value of primary field.
     *
     * @var null|int|float|string
     */
    protected $id = null;

    /**
     * @link https://www.php.net/manual/en/pdo.lastinsertid.php
     */
    protected static bool $isSequenceObjectId = true;

    /**
     * Init this wrapper. Create PDO, set attributes and save link to him.
     *
     * @param string $dsn
     * @param array $pdoAttributes
     * @param array|null $options
     */
    public static function initPdo(string $dsn, array $pdoAttributes = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION], array $options = null): void
    {
        self::$dsn = $dsn;
        self::$pdo = new PDO($dsn, $options);

        foreach ($pdoAttributes as $attr => $param) {
            self::$pdo->setAttribute($attr, $param);
        }
    }

    /**
     * Return PDO object.
     */
    public static function getPdo(): ?PDO
    {
        return self::$pdo;
    }

    /**
     * Return DSN string.
     */
    public static function getDsn(): ?string
    {
        return self::$dsn;
    }

    /**
     * Return primary key name of a table.
     */
    public static function getPrimaryKeyName(): string
    {
        return static::$primaryKeyName;
    }

    /**
     * Return value of primary table key.
     *
     * @return int|float|string (real for sqlite)
     */
    public function getPrimaryKey()
    {
        return $this->id;
    }

    /**
     * Call before insert record.
     */
    protected function beforeInsert(): void
    {

    }

    /**
     * Run before updating.
     */
    protected function beforeUpdate(): void
    {
        // Disabled foreign key default.
        static::$pdo->prepare('PRAGMA foreign_keys = OFF;')->execute();
    }

    /**
     * Run before deleting.
     */
    protected function beforeDelete(): void
    {
        // Disabled foreign key default.
        static::$pdo->prepare('PRAGMA foreign_keys = OFF;')->execute();
    }

    /**
     * Return subclass available attributes, associative array.
     */
    public static function getAvailableAttributes(): array
    {
        return [];
    }

    /**
     * Return attribute label.
     *
     * @param string $attr Attribute name.
     *
     * @return string
     */
    public static function getLabel(string $attr): string
    {
        $availableAttributes = static::getAvailableAttributes();
        return $availableAttributes[$attr] ?? $attr;
    }

    /**
     * Return subclass available attribute names.
     */
    protected function getAttributes(): array
    {
        return array_keys(static::getAvailableAttributes());
    }

    /**
     * Factory creates subclass object.
     */
    public static function getNew(): static
    {
        //$className = get_called_class();
        $className = static::class;
        return new $className();
    }

    /**
     * Trying to determine the table name through subclass name (syntactic sugar).
     *
     * @throws ReflectionException
     */
    public static function getTableName(): string
    {
        //$className = get_called_class();
        $className = static::class;
        $reflectionClass = new ReflectionClass($className);
        $shortName = $reflectionClass->getShortName();

        return mb_strtolower($shortName);
    }

    /**
     * Try to define existing record by primary table key.
     */
    public function isNew(): bool
    {
        return is_null($this->getPrimaryKey());
    }

    /**
     * Return subclass object by primary table key.
     *
     * @param string|int|float|null $primaryKey
     *
     * @return static|null
     * @throws ReflectionException
     */
    public static function getPrimary(float|int|string|null $primaryKey): static|null
    {
        if (is_null($primaryKey)) {
            return null;
        }

        $sql = 'SELECT * FROM `' . static::getTableName() . '` WHERE `' . static::$primaryKeyName . '`="' . $primaryKey . '"';
        $pdoStatement = self::$pdo->prepare($sql);
        $pdoStatement->setFetchMode(PDO::FETCH_CLASS, static::class);
        $pdoStatement->execute();

        $fetch = $pdoStatement->fetch();

        return $fetch ?: null;
    }

    /**
     * Return subclass objects (models) by params.
     * Method requires more conditions.
     *
     * @param array $params
     *
     * @return static[]
     * @throws ReflectionException
     */
    public static function getList(array $params = []): array
    {
        $where = isset($params['where']) ? ' WHERE ' . $params['where'] : '';
        $order = isset($params['order']) ? ' ORDER BY ' . $params['order'] : '';
        $group = isset($params['group']) ? ' GROUP BY ' . $params['group'] : '';
        $limit = isset($params['limit']) ? ' LIMIT ' . $params['limit'] : '';
        // TODO other operators...

        $sql = 'SELECT * FROM `' . static::getTableName() . '`' . $where . $group . $order . $limit;
        $pdoStatement = self::$pdo->prepare($sql);
        $pdoStatement->setFetchMode(PDO::FETCH_CLASS, static::class);
        $pdoStatement->execute();

        $models = [];
        while($model = $pdoStatement->fetch()) {
            $models[$model->getPrimaryKey()] = $model;
        }

        return $models;
    }

    /**
     * Return the first model of model list.
     *
     * @param array $params
     *
     * @return static|null
     * @throws ReflectionException
     */
    public static function getOne(array $params = []): ?static
    {
        $list = self::getList($params);
        if (!$list) {
            return null;
        }

        return $list[array_key_first($list)];
    }

    /**
     * Prepare sql query.
     *
     * @param string $sql
     *
     * @return bool|PDOStatement
     * @throws PDOException
     */
    public static function getPdoStatement(string $sql): bool|PDOStatement
    {
        return self::$pdo->prepare($sql);
    }

    /*
     * Return all records by clean sql.
     */
    public static function sqlFetchAll(string $sql, int $fetchMode = null, string $className = null): array
    {
        $pdoStatement = self::$pdo->prepare($sql);

        if (is_null($fetchMode)) {
            $fetchMode = PDO::FETCH_BOTH;
        }

        if ($fetchMode === PDO::FETCH_CLASS) {
            $pdoStatement->setFetchMode($fetchMode, $className);
        } else {
            $pdoStatement->setFetchMode($fetchMode);
        }

        $pdoStatement->execute();

        return $pdoStatement->fetchAll();
    }

    /*
     * Return a record by clean sql.
     */
    public static function sqlFetch(string $sql, int $fetchMode = null, string $className = null)
    {
        $pdoStatement = self::$pdo->prepare($sql);

        if (is_null($fetchMode)) {
            $fetchMode = PDO::FETCH_BOTH;
        }

        if ($fetchMode === PDO::FETCH_CLASS) {
            $pdoStatement->setFetchMode($fetchMode, $className);
        } else {
            $pdoStatement->setFetchMode($fetchMode);
        }

        $pdoStatement->execute();

        return $pdoStatement->fetch();
    }

    /**
     * Return records count by params.
     *
     * @param array $params
     *
     * @return int
     * @throws ReflectionException
     */
    public static function getCount(array $params = []): int
    {
        $where = isset($params['where']) ? 'WHERE ' . $params['where'] : '';
        $sql = 'SELECT COUNT(*) FROM `' . static::getTableName() . '` ' . $where . ';';
        return (int)self::$pdo->query($sql)->fetchColumn();
    }

    /**
     * Delete record is related with current object (model).
     *
     * @return bool
     * @throws ReflectionException
     */
    public function delete(): bool
    {
        $this->beforeDelete();

        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE `' . static::$primaryKeyName.'`=:' . static::$primaryKeyName;
        $pdoStatement = self::$pdo->prepare($sql);

        // Only variables should be passed by reference
        $primaryKey = $this->getPrimaryKey();
        $pdoStatement->bindParam(':' . static::$primaryKeyName, $primaryKey);

        return $pdoStatement->execute();
    }

    /**
     * Delete all records by scalar primary ids.
     *
     * @param array $ids
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function deleteAll(array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        $idsString = self::getDeletingIdsString($ids);

        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE `' . static::$primaryKeyName . '` in (' . $idsString . ');';
        $pdoStatement = self::$pdo->prepare($sql);

        return $pdoStatement->execute();
    }

    /**
     * Delete all records by field and scalar ids.
     *
     * @param string $field
     * @param array $ids
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function deleteAllByField(string $field, array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        $idsString = self::getDeletingIdsString($ids);

        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE `' . $field . '` in (' . $idsString . ');';
        $pdoStatement = self::$pdo->prepare($sql);

        return $pdoStatement->execute();
    }

    /**
     * Insert model record.
     *
     * @throws ReflectionException
     * @return bool
     */
    public function insert(): bool
    {
        $this->beforeInsert();

        if (empty($this->getInsertingAvailableAttributes())) {
            throw new RuntimeException('Inserting available attributes of '.static::class.' are not found');
        }

        $attributeNames = $this->getInsertingAvailableAttributes();
        $attributeBinds = $this->getInsertingAvailableAttributes(true);
        $sql = 'INSERT INTO `' . static::getTableName() . '` (' . $attributeNames . ') VALUES (' . $attributeBinds . ');';
        $pdoStatement = self::$pdo->prepare($sql);
        $execute = $pdoStatement->execute($this->getInsertingAvailableValues());

        if (static::$isSequenceObjectId) {
            // https://www.php.net/manual/ru/pdo.lastinsertid.php
            // Returns the ID of the last inserted row, or the last value from a sequence object,
            // depending on the underlying driver.
            $this->{static::$primaryKeyName} = self::$pdo->lastInsertId();
        }

        return $execute;
    }

    /**
     * Update model record.
     *
     * @return bool
     * @throws ReflectionException
     */
    public function update(): bool
    {
        $this->beforeUpdate();

        $updatingValues = $this->getUpdatingAvailableValues();
        $sql = 'UPDATE `' . static::getTableName() . '` SET ' . $updatingValues
            . ' WHERE `' . static::$primaryKeyName . '`="' . $this->getPrimaryKey() . '"';
        $pdoStatement = self::$pdo->prepare($sql);

        if ($pdoStatement instanceof PDOStatement) {
            $this->pdoStatement = $pdoStatement;
            $this->bindAvailableValues();
            return $this->pdoStatement->execute();
        }

        return false;
    }

    /**
     * Return formatted available attributes for inserting.
     *
     * @param bool $isAddBindSeparator
     *
     * @return string
     */
    private function getInsertingAvailableAttributes(bool $isAddBindSeparator = false): string
    {
        $out = '';
        foreach ($this->getAttributes() as $attr) {
            $out .= $isAddBindSeparator ? ':' . $attr . ',' : $attr . ',';
        }
        $out = substr($out, 0, -1);

        return $out;
    }

    /**
     * Return formatted available values for inserting.
     */
    private function getInsertingAvailableValues(): array
    {
        $out = [];
        foreach ($this->getAttributes() as $attr) {
            $value = $this->{$attr};
            if (is_string($value)) {
                $value = self::getEscapeString($value);
            }
            $out[$attr] = $value;
        }
        return $out;
    }

    /**
     * Return formatted available values for inserting, like title=:tile, ...
     */
    private function getUpdatingAvailableValues(): string
    {
        $out = '';
        foreach ($this->getAttributes() as $attr) {
            $out .= $attr . '=:' . $attr . ',';
        }

        return substr($out, 0, -1);
    }

    /**
     * Binds attributes and values.
     */
    private function bindAvailableValues(): void
    {
        foreach ($this->getAttributes() as $attr) {
            $value = $this->{$attr};
            if (is_string($value)) {
                $value = self::getEscapeString($value);
            }
            $this->pdoStatement->bindValue(':' . $attr, $value);
        }
    }

    /**
     * Return formatted values for deleting, like 1,2,...
     *
     * @param array $ids
     *
     * @return string
     */
    private static function getDeletingIdsString(array $ids): string
    {
        $idsString = '';
        foreach ($ids as $id) {
            // if $id is int then it will be converted to string, if you would like to us integers then us other method
            $idsString .= '"' . $id . '",';
        }

        return substr($idsString, 0, -1);
    }

    /**
     * Return escaped string for secure inserting.
     *
     * @param string $value
     *
     * @return string
     */
    private static function getEscapeString(string $value): string
    {
        // \SQLite3::escapeString($value); // hmm... it does not work as expect
        // TODO reliable escape method
        return str_replace('"',"'", $value);
    }

}