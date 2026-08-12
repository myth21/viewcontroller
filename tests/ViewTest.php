<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use myth21\viewcontroller\View;

class ViewTest extends TestCase
{
    public function testSetAndGetParam(): void
    {
        $view = new View();
        $view->setTemplateParam('title', 'Test');
        $this->assertEquals('Test', $view->getTemplateParam('title'));
    }

    public function testRendering()
    {
        $view = new View();

        $pathToDir = __DIR__ . DIRECTORY_SEPARATOR;

        $view->setAbsoluteTemplateDirName($pathToDir);
        $view->setTemplateFileName('template');

        $render = $view->renderPart('view', ['key' => 'value']);
        $this->assertEquals('value', $render);

        $render = $view->render('view', ['key' => 'value']);
        $this->assertEquals('value', $render);

        $view->render('view', ['key' => 'content']);
        $this->assertEquals('content', $view->getContent());

        $render = $view->renderFile($pathToDir . 'view.php', ['key' => 'value']);
        $this->assertEquals('value', $render);
    }

    /**
     * The path being included is a local variable, and extract() with EXTR_OVERWRITE replaces
     * locals - so template data holding a key named after one of them decided which file got
     * included. A `name` key is the plausible one: renderFile($path, ['name' => 'Ivan']).
     */
    public function testTemplateDataCannotChangeIncludedFile(): void
    {
        $view = new View();

        $pathToDir = __DIR__ . DIRECTORY_SEPARATOR;
        $view->setAbsoluteTemplateDirName($pathToDir);
        $view->setTemplateFileName('template');

        $data = [
            'key' => 'value',
            'name' => '/etc/passwd',
            'viewFilePath' => '/etc/passwd',
            '__filePath' => '/etc/passwd',
        ];

        $this->assertEquals('value', $view->renderFile($pathToDir . 'view.php', $data));
        $this->assertEquals('value', $view->renderPart('view', $data));
        $this->assertEquals('value', $view->render('view', $data));
    }

    public function testMissingViewFileThrows(): void
    {
        $view = new View();
        $view->setAbsoluteTemplateDirName(__DIR__ . DIRECTORY_SEPARATOR);

        $this->expectException(RuntimeException::class);

        $view->renderPart('no-such-view');
    }
}