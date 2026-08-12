<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use ReflectionProperty;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * Class PdoRecord is a base Active Record wrapper for PDO results.
 * Note: class is tested for working with SQLite only.
 *
 * This class is intended to be extended. Child classes should define
 * an `id` property with the appropriate scalar type to represent the
 * model's primary key. This base class provides the `getPrimaryKey()`
 * method, which relies on that property being present.
 *
 * @property float|int|string|null $id Primary key of the record.
 */
class PdoRecord implements PdoRecordInterface
{
    protected static ?string $dsn = null;
    protected static string $primaryKeyName = 'id';

    /**
     * Placeholder name the primary key is bound under. Prefixed so that it cannot collide with a
     * column of the same name, since attributes are bound as `:attributeName`.
     */
    private const PRIMARY_KEY_BIND_NAME = '__primaryKey';

    /**
     * @link https://www.php.net/manual/en/pdo.lastinsertid.php
     */
    protected static bool $isSequenceObjectId = true;

    /**
     * Init this wrapper. Create PDO, set attributes and save link to him.
     */
    public static function initPdo(string $dsn, array $pdoAttributes = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION], array $options = null): void
    {
        self::$dsn = $dsn;
        $pdo = new PDO($dsn, $options);

        foreach ($pdoAttributes as $attr => $param) {
            $pdo->setAttribute($attr, $param);
        }

        PdoRegistry::set($pdo);
    }

    /**
     * Return PDO object.
     */
    public static function getPdo(): ?PDO
    {
        return PdoRegistry::get();
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
     * Returns the primary key value of the current record.
     *
     * This method relies on the child class to define a property named `$id`.
     * It supports null, int, float, or string values to accommodate various primary key types.
     *
     * @return float|int|string|null The primary key of the record, or null if undefined.
     */
    public function getPrimaryKey(): float|int|string|null
    {
        return $this->id ?? null;
    }

    /**
     * Call before insert record.
     */
    protected function beforeInsert(): void
    {
        // Overridable by child classes
    }

    /**
     * Hook method called before update.
     *
     * Note for SQLite users:
     * SQLite supports foreign key constraints, but they are **disabled by default**
     * in versions prior to 3.6.19 (released in October 2009). Even in newer versions,
     * enforcement must be **explicitly enabled per connection** using:
     *
     *     PRAGMA foreign_keys = ON;
     *
     * If your application relies on foreign key constraints, make sure to enable them
     * manually after opening the connection. This library does not manage this behavior.
     *
     * @see https://www.sqlite.org/foreignkeys.html
     */
    protected function beforeUpdate(): void
    {
        // Overridable by child classes
    }

    /**
     * Hook method called before delete.
     *
     * Note for SQLite users:
     * SQLite supports foreign key constraints, but they are **disabled by default**
     * in versions prior to 3.6.19 (released in October 2009). Even in newer versions,
     * enforcement must be **explicitly enabled per connection** using:
     *
     *     PRAGMA foreign_keys = ON;
     *
     * If your application relies on foreign key constraints, make sure to enable them
     * manually after opening the connection. This library does not manage this behavior.
     *
     * @see https://www.sqlite.org/foreignkeys.html
     */
    protected function beforeDelete(): void
    {
        // Overridable by child classes
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
        $className = static::class;
        return new $className();
    }

    /**
     * Trying to determine the table name through subclass name (syntactic sugar).
     */
    public static function getTableName(): string
    {
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
     */
    public static function getPrimary(float|int|string $primaryKey): static|null
    {
        $sql = 'SELECT * FROM `' . static::getTableName() . '` WHERE `' . static::$primaryKeyName . '` = :primaryKey';
        $pdoStatement = static::getPdo()->prepare($sql);
        $pdoStatement->bindValue(':primaryKey', $primaryKey);
        $pdoStatement->setFetchMode(PDO::FETCH_CLASS, static::class);
        $pdoStatement->execute();

        $fetch = $pdoStatement->fetch();

        return $fetch ?: null;
    }

    /**
     * Return subclass objects (models) by params.
     * Method requires more conditions.
     *
     * Values belong in `params`, never in the `where` string:
     *
     *     getList(['where' => '`email` = :email', 'params' => ['email' => $email]])
     *
     * Every clause below is inlined into the statement as written, so a value concatenated into
     * `where` by a caller is executed as SQL - that is how a request parameter reaching a
     * repository turns into an injection. `params` is handed to PDOStatement::execute(), which
     * binds instead of interpolating. The clause keys themselves (fields, join, order, group,
     * having, limit) are still raw SQL and must never be built from request data.
     *
     * @param array $params
     *
     * @return static[]
     * @throws ReflectionException
     */
    public static function getList(array $params = []): array
    {
        $fields = $params['fields'] ?? '*';

        $join   = isset($params['join']) ? ' ' . $params['join'] : '';
        $where  = isset($params['where']) ? ' WHERE ' . $params['where'] : '';
        $order  = isset($params['order']) ? ' ORDER BY ' . $params['order'] : '';
        $group  = isset($params['group']) ? ' GROUP BY ' . $params['group'] : '';
        $having = isset($params['having']) ? ' HAVING ' . $params['having'] : '';
        $limit  = isset($params['limit']) ? ' LIMIT ' . $params['limit'] : '';
        if ($limit) {
            $limit .= isset($params['offset']) ? ' OFFSET ' . $params['offset'] : '';
        }
        // To build a complex sql query then use other method, e.g. sqlFetch()

        $sql = 'SELECT ' . $fields . ' FROM `' . static::getTableName() . '`' . $join . $where . $group . $having . $order . $limit;
        $pdoStatement = static::getPdo()->prepare($sql);
        $pdoStatement->setFetchMode(PDO::FETCH_CLASS, static::class);
        $pdoStatement->execute($params['params'] ?? null);

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
        // static, not self: a subclass overriding getList() must not be bypassed here.
        $list = static::getList($params);
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
        $prepared = static::getPdo()->prepare($sql);
        return $prepared ?: throw new RuntimeException('PDO statement could not be executed');
    }

    /*
     * Return all records by clean sql.
     * Pass values through $params (['id' => 1] for ':id'), not by concatenating them into $sql.
     */
    public static function sqlFetchAll(string $sql, int $fetchMode = null, string $className = null, array $params = null): array
    {
        $pdoStatement = static::getPdo()->prepare($sql);

        if (is_null($fetchMode)) {
            $fetchMode = PDO::FETCH_BOTH;
        }

        if ($fetchMode === PDO::FETCH_CLASS) {
            $pdoStatement->setFetchMode($fetchMode, $className);
        } else {
            $pdoStatement->setFetchMode($fetchMode);
        }

        $pdoStatement->execute($params);

        return $pdoStatement->fetchAll();
    }

    /*
     * Return a record by clean sql.
     * Pass values through $params (['id' => 1] for ':id'), not by concatenating them into $sql.
     */
    public static function sqlFetch(string $sql, int $fetchMode = null, string $className = null, array $params = null)
    {
        $pdoStatement = static::getPdo()->prepare($sql);

        if (is_null($fetchMode)) {
            $fetchMode = PDO::FETCH_BOTH;
        }

        if ($fetchMode === PDO::FETCH_CLASS) {
            $pdoStatement->setFetchMode($fetchMode, $className);
        } else {
            $pdoStatement->setFetchMode($fetchMode);
        }

        $pdoStatement->execute($params);

        return $pdoStatement->fetch();
    }

    /**
     * Return records count by params.
     *
     * Takes `where` and `params` the same way getList() does - see the note there on why values
     * belong in `params`.
     */
    public static function getCount(array $params = []): int
    {
        $where = isset($params['where']) ? 'WHERE ' . $params['where'] : '';
        $sql = 'SELECT COUNT(*) FROM `' . static::getTableName() . '` ' . $where . ';';
        $pdoStatement = static::getPdo()->prepare($sql);
        $pdoStatement->execute($params['params'] ?? null);

        return (int)$pdoStatement->fetchColumn();
    }

    /**
     * Delete record is related with current object (model).
     */
    public function delete(): bool
    {
        $this->beforeDelete();

        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE `' . static::$primaryKeyName.'`=:' . static::$primaryKeyName;
        $pdoStatement = static::getPdo()->prepare($sql);

        // Only variables should be passed by reference
        $primaryKey = $this->getPrimaryKey();
        $pdoStatement->bindParam(':' . static::$primaryKeyName, $primaryKey);

        return $pdoStatement->execute();
    }

    /**
     * Delete all records by scalar primary ids.
     */
    public static function deleteAll(array $ids): bool
    {
        if ($ids === []) {
            return false;
        }

        // Generate placeholders :id0, :id1, ...
        $placeholders = [];
        foreach ($ids as $index => $_) {
            $placeholders[] = ':id' . $index;
        }

        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE `' . static::$primaryKeyName . '` IN (' . implode(', ', $placeholders) . ')';
        $pdoStatement = static::getPdo()->prepare($sql);

        // Binding values
        foreach ($ids as $index => $id) {
            $pdoStatement->bindValue(':id' . $index, $id, is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $pdoStatement->execute();
    }

    /**
     * Fail on anything that is not a plain column name.
     *
     * Column names cannot be bound, they are inlined into the statement - so wherever one comes
     * from a caller it has to be checked here first.
     *
     * @throws InvalidArgumentException
     */
    protected static function assertFieldName(string $field): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
            throw new InvalidArgumentException('Invalid field name: ' . $field);
        }
    }

    /**
     * Delete all records by field and scalar ids.
     */
    public static function deleteAllWhereField(string $field, array $ids): bool
    {
        if ($ids === []) {
            return false;
        }

        static::assertFieldName($field);

        $placeholders = [];
        foreach ($ids as $index => $_) {
            $placeholders[] = ':id' . $index;
        }

        $sql = 'DELETE FROM `' . static::getTableName() . '` WHERE `' . $field . '` IN (' . implode(', ', $placeholders) . ')';
        $stmt = static::getPdo()->prepare($sql);

        foreach ($ids as $index => $id) {
            $stmt->bindValue(':id' . $index, $id, is_int($id) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        return $stmt->execute();
    }

    /**
     * Insert model record.
     *
     * @throws ReflectionException
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
        $pdoStatement = static::getPdo()->prepare($sql);
        $execute = $pdoStatement->execute($this->getInsertingAvailableValues());

        // Nothing was written, so there is no key to read - and lastInsertId() would answer with
        // whatever the previous statement on this connection produced.
        if ($execute && static::$isSequenceObjectId) {
            // https://www.php.net/manual/ru/pdo.lastinsertid.php
            // Returns the ID of the last inserted row, or the last value from a sequence object, depending on the underlying driver.
            $id = static::getPdo()->lastInsertId();

            if ($id !== false) {
                $property = new ReflectionProperty(static::class, static::$primaryKeyName);
                $type = $property->getType();

                if ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();

                    if ($typeName === 'int') {
                        $id = (int) $id;
                    } elseif ($typeName === 'float') {
                        $id = (float) $id;
                    }
                } elseif ($type instanceof ReflectionUnionType) {
                    // For union types, prefer int if it is one of the types
                    foreach ($type->getTypes() as $namedType) {
                        if ($namedType->getName() === 'int') {
                            $id = (int) $id;
                            break;
                        } elseif ($namedType->getName() === 'float') {
                            $id = (float) $id;
                            break;
                        }
                    }
                } elseif ($type === null && is_numeric($id)) {
                    // An untyped `$id` used to keep the string lastInsertId() returns, so a model
                    // straight out of insert() differed from the same row read back from the
                    // database - and every `getId(): ?int` accessor on it raised a TypeError. The
                    // declared type is honoured above wherever there is one; this is the fallback.
                    $id = (int) $id;
                }

                $this->{static::$primaryKeyName} = $id;
            }
        }

        return $execute;
    }

    /**
     * Update model record.
     */
    public function update(): bool
    {
        $this->beforeUpdate();

        $updatingValues = $this->getUpdatingAvailableValues();
        // The primary key is bound like every other value. Quoting it into the statement was safe
        // only as long as it came from the database; a key taken from a request (a route parameter
        // assigned to the model) was executed as SQL.
        $sql = 'UPDATE `' . static::getTableName() . '` SET ' . $updatingValues
            . ' WHERE `' . static::$primaryKeyName . '` = :' . self::PRIMARY_KEY_BIND_NAME;
        $pdoStatement = static::getPdo()->prepare($sql);

        if ($pdoStatement instanceof PDOStatement) {
            $this->bindAvailableValues($pdoStatement);
            $pdoStatement->bindValue(':' . self::PRIMARY_KEY_BIND_NAME, $this->getPrimaryKey());

            return $pdoStatement->execute();
        }

        return false;
    }

    public function updateFields(array $data, bool $isBeforeUpdate = true): bool
    {
        // Call hook before update if requested
        if ($isBeforeUpdate) {
            $this->beforeUpdate();
        }

        // Prepare the SET part of the SQL query with named parameters
        $setParts = [];
        foreach ($data as $attr => $value) {
            // Values are bound below, but the column names are inlined - so they have to be
            // names and nothing else. Without this a $data key was free SQL.
            static::assertFieldName((string)$attr);
            $setParts[] = '`' . $attr . '`=:' . $attr;
        }
        $setClause = implode(', ', $setParts);

        // Build the full SQL update statement
        $sql = 'UPDATE `' . static::getTableName() . '` SET ' . $setClause . ' WHERE `' . static::$primaryKeyName . '` = :primaryKey';

        // Prepare the PDO statement
        $pdoStatement = static::getPdo()->prepare($sql);
        if (!$pdoStatement) {
            return false;
        }

        // Bind values for all attributes
        foreach ($data as $attr => $value) {
            $pdoStatement->bindValue(':' . $attr, $value);
        }

        // Bind primary key value for WHERE condition
        $pdoStatement->bindValue(':primaryKey', $this->getPrimaryKey());

        // Execute the statement and return result
        return $pdoStatement->execute();
    }


    /**
     * Insert or update model record on depended on primary key of model.
     *
     * @throws ReflectionException
     */
    public function save(): bool
    {
        if ($this->getPrimaryKey()) {
            return $this->update();
        }

        return $this->insert();
    }

    /**
     * Return formatted available attributes for inserting.
     */
    protected function getInsertingAvailableAttributes(bool $isAddBindSeparator = false): string
    {
        $out = '';
        foreach ($this->getAttributes() as $attr) {
            $out .= $isAddBindSeparator ? ':' . $attr . ',' : '`' . $attr . '`,';
        }
        $out = mb_substr($out, 0, -1);

        return $out;
    }

    /**
     * Return formatted available values for inserting.
     */
    protected function getInsertingAvailableValues(): array
    {
        $out = [];
        foreach ($this->getAttributes() as $attr) {
            $value = $this->{$attr};
            // Probably if is_string($value) then make sense to call $value = self::getEscapeString($value) here
            $out[$attr] = $value;
        }
        return $out;
    }

    /**
     * Return formatted available values for inserting, like title=:tile, ...
     */
    protected function getUpdatingAvailableValues(): string
    {
        $out = '';
        foreach ($this->getAttributes() as $attr) {
            $out .= '`' . $attr . '`' . '=:' . $attr . ',';
        }

        return mb_substr($out, 0, -1);
    }

    /**
     * Binds attributes and values.
     */
    protected function bindAvailableValues(PDOStatement $pdoStatement): void
    {
        foreach ($this->getAttributes() as $attr) {
            $value = $this->{$attr};
            // Probably if is_string($value) then make sense to call $value = self::getEscapeString($value) here
            $pdoStatement->bindValue(':' . $attr, $value);
        }
    }

    /**
     * Helper method.
     * Return escaped string for secure inserting.
     * Ideally client must care about quotes self.
     */
    protected static function getEscapeString(string $value): string
    {
        return str_replace('"',"'", $value);
    }
}