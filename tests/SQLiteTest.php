<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests;

use InvalidArgumentException;
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
            protected string|int|null|float $id = null;
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

    public function testGetListWithBoundParams(): void
    {
        $model = $this->getModel();

        $models = $model::getList([
            'where' => '`' . SQLiteTest::TABLE_FIELD_NAME . '` = :name',
            'params' => ['name' => SQLiteTest::PROJECT_NAME],
        ]);

        $this->assertCount(1, $models);
        $this->assertNotNull($model::getOne([
            'where' => '`id` = :id',
            'params' => ['id' => SQLiteTest::ID],
        ]));
    }

    public function testGetCountWithBoundParams(): void
    {
        $model = $this->getModel();

        $this->assertEquals(1, $model::getCount([
            'where' => '`id` = :id',
            'params' => ['id' => SQLiteTest::ID],
        ]));
    }

    /**
     * A bound value is data, never SQL - concatenated into the `where` string the same input is
     * executed, which is how a request parameter reaching a repository became an injection.
     */
    public function testBoundValueIsNotExecutedAsSql(): void
    {
        $model = $this->getModel();
        $injection = 'x" OR 1=1 --';

        $this->assertNull($model::getOne([
            'where' => '`' . SQLiteTest::TABLE_FIELD_NAME . '` = :name',
            'params' => ['name' => $injection],
        ]));
        $this->assertEquals(0, $model::getCount([
            'where' => '`' . SQLiteTest::TABLE_FIELD_NAME . '` = :name',
            'params' => ['name' => $injection],
        ]));
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

        // Warning, primary key can be not integer
        $nextPrimaryKey = (int)$model->getPrimaryKey();
        $this->assertGreaterThan(SQLiteTest::ID, $nextPrimaryKey);

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

    /**
     * The key used to be quoted into the statement. Interpolated, the value below reads as
     * `WHERE id="1" OR "1"="1"` - true for every row - so one model rewrote the whole table.
     */
    public function testUpdateBindsPrimaryKey(): void
    {
        $model = $this->getModel();
        $originalName = $model::getPrimary(SQLiteTest::ID)->getName();

        $rogueModel = $this->getModel();
        $reflectionProperty = new ReflectionProperty($rogueModel::class, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($rogueModel, '1" OR "1"="1');
        $rogueModel->setName('overwritten');
        $rogueModel->update();

        $this->assertEquals($originalName, $model::getPrimary(SQLiteTest::ID)->getName());
    }

    public function testUpdateFields(): void
    {
        $model = $this->getModel();
        $model->setName('Temp Name');
        $model->insert();

        $this->assertTrue($model->updateFields([SQLiteTest::TABLE_FIELD_NAME => 'Updated By Fields']));
        $this->assertEquals('Updated By Fields', $model::getPrimary($model->getPrimaryKey())->getName());

        $model->delete();
    }

    /**
     * Column names cannot be bound, they are inlined - so a key that is not a plain name is refused
     * instead of becoming SQL.
     */
    public function testUpdateFieldsRejectsInvalidFieldName(): void
    {
        $model = $this->getModel();
        $dbModel = $model::getPrimary(SQLiteTest::ID);

        $this->expectException(InvalidArgumentException::class);

        $dbModel->updateFields([SQLiteTest::TABLE_FIELD_NAME . '` = "injected", `id' => 'x']);
    }

    /**
     * lastInsertId() answers with a string. With a declared type the property keeps it (handled
     * below by reflection), but an untyped `$id` used to hold that string, so a model fresh out of
     * insert() differed from the same row read back - and `getId(): ?int` accessors raised a
     * TypeError on it.
     */
    public function testInsertCastsNumericPrimaryKeyOfUntypedProperty(): void
    {
        $model = new class extends PdoRecord {
            // Untyped on purpose - the case this test is about.
            protected $id;
            protected ?string $project_name = null;
            public static function getAvailableAttributes(): array
            {
                return [SQLiteTest::TABLE_FIELD_NAME => 'Title'];
            }
            public static function getTableName(): string
            {
                return SQLiteTest::TABLE_NAME;
            }
            public function setName(string $value): void
            {
                $this->project_name = $value;
            }
        };
        $model->setName('Untyped id');

        $this->assertTrue($model->insert());
        $this->assertIsInt($model->getPrimaryKey());

        $model->delete();
    }

    public function testDelete(): void
    {
        $model = $this->getModel();
        $model->setName('Temp Name');
        $model->insert();
        $this->assertTrue($model->delete());
    }

    public function testDeleteAll(): void
    {
        $model1 = $this->getModel();
        $model1->setName('Temp 1');
        $model1->insert();

        $model2 = $this->getModel();
        $model2->setName('Temp 2');
        $model2->insert();

        $primaryKeys = [$model1->getPrimaryKey(), $model2->getPrimaryKey()];
        $this->assertTrue($model1::deleteAll($primaryKeys));

        foreach ($primaryKeys as $id) {
            $this->assertNull($model1::getPrimary($id));
        }
    }
}