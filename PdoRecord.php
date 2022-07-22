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
 * Class PdoRecord is PDO wrapper
 *
 * @property string $dsn
 * @property PDO $pdo
 */
class PdoRecord implements TableRecord
{
    protected const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    protected const DATE_FORMAT = 'Y-m-d';

    protected static ?string $dsn = null;
    protected static ?PDO $pdo = null;
    protected ?PDOStatement $pdoStatement = null;
    protected static string $primaryKeyName = 'id';

    /**
     * @var string|int|float|null
     */
    protected $id = null;

    /**
     * @link https://www.php.net/manual/en/pdo.lastinsertid.php
     */
    protected static bool $isSequenceObjectId = true;

    public static function initPdo(string $dsn, array $pdoAttributes = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION], array $options = null): void
    {
        self::$dsn = $dsn;
        self::$pdo = new PDO($dsn, $options);

        foreach ($pdoAttributes as $attr => $param) {
            self::$pdo->setAttribute($attr, $param);
        }
    }

    public static function getPdo(): ?PDO
    {
        return self::$pdo;
    }

    public static function getDsn(): ?string
    {
        return self::$dsn;
    }

    public static function primaryKeyName(): string
    {
        return static::$primaryKeyName;
    }

    /**
     * @return string|int|float|null
     */
    public function getPrimaryKey()
    {
        return $this->id;
    }

    public function init(array $data = []): void
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->getAttributes())) {
                // it should remain NULL if class attribute value by default null
                // It means that attributes in not initialed and input is empty
                // TODO via isset($data[$key])?
                if (empty($data[$key]) && is_null($this->{$key})) {
                    continue;
                }
                $this->{$key} = $value;
            }
        }
    }

    protected function beforeInsert(){}
    protected function beforeUpdate(){}
    protected function beforeDelete(){}

    /**
     * Return child available attributes
     */
    public static function availableAttributes(): array
    {
        return [];
    }

    public static function getLabel(string $attr): string
    {
        $availableAttributes = static::availableAttributes();
        return $availableAttributes[$attr] ?? $attr;
    }

    /**
     * Return child available attributes
     */
    protected function getAttributes(): array
    {
        return array_keys(static::availableAttributes());
    }

    public static function getNew(): static
    {
        $className = get_called_class();
        return new $className();
    }

    /**
     * Trying to determine the table name through child class name (syntactic sugar)
     * @throws ReflectionException
     */
    public static function tableName(): string
    {
        $className = get_called_class();
        $reflectionClass = new ReflectionClass($className);
        $shortName = $reflectionClass->getShortName();

        return mb_strtolower($shortName);
    }

    public function isNew(): bool
    {
        return is_null($this->getPrimaryKey());
    }

    public function isOld(): bool
    {
        return !$this->isNew();
    }

    /**
     * @param string|int|float|null $primaryKey
     * @throws ReflectionException
     * @return self|null
     */
    public static function getPrimary($primaryKey)
    {
        if (is_null($primaryKey)) {
            return null;
        }

        $sql = 'SELECT * FROM `'. static::tableName().'` WHERE `'.static::$primaryKeyName.'`="'.$primaryKey.'"';
        $pdoStatement = self::$pdo->prepare($sql);
        $pdoStatement->setFetchMode(PDO::FETCH_CLASS, static::class);
        $pdoStatement->execute();

        $fetch = $pdoStatement->fetch();

        return $fetch ?: null;
    }

    /**
     * @param array $params
     * @return PdoRecord[]
     * @throws ReflectionException
     */
    public static function getList(array $params = []): array
    {
        $where = isset($params['where']) ? ' WHERE ' . $params['where'] : '';
        $order = isset($params['order']) ? ' ORDER BY ' . $params['order'] : '';
        $group = isset($params['group']) ? ' GROUP BY ' . $params['group'] : '';
        $limit = isset($params['limit']) ? ' LIMIT ' . $params['limit'] : '';
        // TODO other operators...

        $sql = 'SELECT * FROM `' . static::tableName() . '`' . $where . $group . $order . $limit;
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
     * @param array $params
     * @throws ReflectionException
     * @return PdoRecord|null
     */
    public static function getOne(array $params = []): ?PdoRecord
    {
        $list = self::getList($params);
        if (!$list) {
            return null;
        }

        return $list[array_key_first($list)];
    }

    /**
     * @param string $sql
     * @throws PDOException
     * @return bool|PDOStatement
     */
    public static function getPdoStatement(string $sql)
    {
        return self::$pdo->prepare($sql);
    }

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
     * @param array $params
     * @return int
     * @throws ReflectionException
     */
    public static function getCount(array $params = []): int
    {
        $where = isset($params['where']) ? 'WHERE ' . $params['where'] : '';
        $sql = 'SELECT COUNT(*) FROM `' . static::tableName() . '` ' . $where . ';';
        return (int)self::$pdo->query($sql)->fetchColumn();
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function delete(): bool
    {
        $this->beforeDelete();

        $sql = 'DELETE FROM `'.static::tableName().'` WHERE `'.static::$primaryKeyName.'`=:'.static::$primaryKeyName;
        $pdoStatement = self::$pdo->prepare($sql);

        // Only variables should be passed by reference
        $primaryKey = $this->getPrimaryKey();
        $pdoStatement->bindParam(':'.static::$primaryKeyName, $primaryKey);

        return $pdoStatement->execute();
    }

    /**
     * @param array $ids
     * @return bool
     * @throws ReflectionException
     */
    public static function deleteAll(array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        $idsString = self::getDeletingIdsString($ids);

        $sql = 'DELETE FROM `' . static::tableName() . '` WHERE `' . static::$primaryKeyName . '` in (' . $idsString . ');';
        $pdoStatement = self::$pdo->prepare($sql);

        return $pdoStatement->execute();
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function insert(): bool
    {
        $this->beforeInsert();

        if (empty($this->getInsertingAvailableAttributes())) {
            throw new RuntimeException('Inserting available attributes of '.static::class.' are not found');
        }

        $sql = 'INSERT INTO `'.static::tableName().'` ('.$this->getInsertingAvailableAttributes().') VALUES ('.$this->getInsertingAvailableAttributes(true).');';
        $pdoStatement = self::$pdo->prepare($sql);
        $execute = $pdoStatement->execute($this->getInsertingAvailableValues());

        if (static::$isSequenceObjectId) {
            $this->{static::$primaryKeyName} = self::$pdo->lastInsertId();
        }

        return $execute;
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function update(): bool
    {
        $this->beforeUpdate();

        $sql = 'UPDATE `'.static::tableName().'` SET '.$this->getUpdatingAvailableValues().' WHERE `'.static::$primaryKeyName.'`="'.$this->getPrimaryKey().'"';
        $pdoStatement = self::$pdo->prepare($sql);

        if ($pdoStatement instanceof PDOStatement) {
            $this->pdoStatement = $pdoStatement;
            $this->bindAvailableValues();
            return $this->pdoStatement->execute();
        }

        return false;
    }

    private function getInsertingAvailableAttributes(bool $isAddBindSeparator = false): string
    {
        $out = '';
        foreach ($this->getAttributes() as $attr) {
            $out .= $isAddBindSeparator ? ':' . $attr . ',' : $attr . ',';
        }
        $out = substr($out, 0, -1);

        return $out;
    }

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

    private function getUpdatingAvailableValues(): string
    {
        $out = '';
        foreach ($this->getAttributes() as $attr) {
            $out .= $attr . '=:' . $attr . ','; // string like title=:tile, ...
        }
        return substr($out, 0, -1);
    }

    private function bindAvailableValues(): void
    {
        foreach ($this->getAttributes() as $attr) {
            $value = $this->{$attr};
            if (is_string($value)) {
                $value = self::getEscapeString($value);
            }
            $this->pdoStatement->bindValue(':'.$attr, $value);
        }
    }

    private static function getDeletingIdsString(array $ids): string
    {
        $idsString = '';
        foreach ($ids as $id) {
            // if $id is int then it will be converted to string, if you would like to us integers then us other method
            $idsString .= '"'.$id.'",';
        }

        return substr($idsString, 0, -1);
    }

    private static function getEscapeString(string $value): string
    {
        // \SQLite3::escapeString($value); // hmm... it does not work as I expect
        // TODO reliable escape method
        return str_replace('"',"'", $value);
    }

}