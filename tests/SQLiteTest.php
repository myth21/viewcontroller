<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use myth21\viewcontroller\PdoRecord;
use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * Require SQLite driver
 */
class SQLiteTest extends TestCase
{
    public const ID = 123;
    public const TABLE_NAME = 'projects';
    public const TABLE_FIELD_NAME = 'project_name';
    public const PROJECT_NAME = 'a project name';

    public static function setUpBeforeClass(): void
    {
        PdoRecord::initPdo( 'sqlite::memory:');
        $pdo = PdoRecord::getPdo();

        $pdo->exec('CREATE TABLE IF NOT EXISTS projects (
                              id INTEGER PRIMARY KEY,
                              project_name TEXT NOT NULL
        );');

        $pdo->exec('INSERT INTO ' . SQLiteTest::TABLE_NAME . ' (id, ' . SQLiteTest::TABLE_FIELD_NAME . ') VALUES (null, "' . SQLiteTest::PROJECT_NAME .'");');
        $pdo->exec('INSERT INTO ' . SQLiteTest::TABLE_NAME . ' (id, ' . SQLiteTest::TABLE_FIELD_NAME . ') VALUES (' . SQLiteTest::ID . ', "my project name");');
    }

    protected function getModel()
    {
        $model = new class extends PdoRecord {
            protected $id = null;
            protected ?string $project_name = null;
            public static function getAvailableAttributes(): array
            {
                return [
                    SQLiteTest::TABLE_FIELD_NAME => 'Title',
                ];
            }
            public static function getTableName(): string
            {
                return SQLiteTest::TABLE_NAME;
            }
            public function setName(string $value): void
            {
                $this->project_name = $value;
            }
            public function getName(): ?string
            {
                return $this->project_name;
            }
        };

        return $model;
    }

    public function testCreatePdo(): void
    {
        $this->assertInstanceOf(PDO::class, PdoRecord::getPdo());
        $this->assertEquals('id', PdoRecord::getPrimaryKeyName());
    }

    public function testMetaData(): void
    {
        $model = $this->getModel();

        $this->assertNull($model->getPrimaryKey());
        $this->assertArrayHasKey(SQLiteTest::TABLE_FIELD_NAME, $model::getAvailableAttributes());

        $this->assertEquals('Title', $model::getLabel(SQLiteTest::TABLE_FIELD_NAME));
        $this->assertEquals(SQLiteTest::TABLE_NAME, $model::getTableName());
    }

    public function testIsNew(): void
    {
        $model = $this->getModel();

        $this->assertTrue($model->isNew());

        $reflectionProperty = new ReflectionProperty($model::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($model, SQLiteTest::ID);

        $this->assertFalse($model->isNew());
    }

    public function testGetCountList(): void
    {
        $model = $this->getModel();
        $this->assertCount(2, $model::getList());
    }

    public function testGetPrimary(): void
    {
        $model = $this->getModel();

        $reflectionProperty = new ReflectionProperty($model::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($model, SQLiteTest::ID);

        $this->assertNotNull($model::getPrimary(SQLiteTest::ID));
    }

    public function testGetOne(): void
    {
        $model = $this->getModel();

        $this->assertNotNull($model->getOne(['where' => SQLiteTest::TABLE_FIELD_NAME . ' = "' . SQLiteTest::PROJECT_NAME . '"']));
    }

    public function testSqlFetchAll(): void
    {
        $records = PdoRecord::sqlFetchAll('SELECT * FROM ' . SQLiteTest::TABLE_NAME);
        $this->assertCount(2, $records);
    }

    public function testSqlFetch(): void
    {
        $record = PdoRecord::sqlFetch('SELECT * FROM ' . SQLiteTest::TABLE_NAME . ' WHERE id = "' . SQLiteTest::ID . '"');
        $this->assertEquals(SQLiteTest::ID, $record['id']);
    }

    public function testInsert(): void
    {
        $model = $this->getModel();
        $model->setName(SQLiteTest::PROJECT_NAME);

        $this->assertTrue($model->insert());

        $nextPrimaryKey = $model->getPrimaryKey();
        $this->assertEquals(SQLiteTest::ID + 1, $nextPrimaryKey);

        $model->delete();
    }

    public function testUpdate(): void
    {
        $updateName = 'UpdatedName';

        $model = $this->getModel();
        $dbModel = $model::getPrimary(SQLiteTest::ID);
        $dbModel->setName($updateName);

        $this->assertTrue($dbModel->update());

        $updatedModel = $dbModel::getPrimary(SQLiteTest::ID);

        $this->assertEquals($updateName, $updatedModel->getName());
    }

    public function testDelete(): void
    {
        $model = $this->getModel();

        $this->assertTrue($model->delete());
    }

    public function testDeleteAll(): void
    {
        $model = $this->getModel();
        $this->assertTrue($model::deleteAll([1, SQLiteTest::ID]));

        $this->assertCount(0, $model::getList());
    }
}