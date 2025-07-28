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
}