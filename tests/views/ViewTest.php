<?php

declare(strict_types=1);

namespace myth21\viewcontroller\tests\views;

use myth21\viewcontroller\View;
use PHPUnit\Framework\TestCase;

use const DIRECTORY_SEPARATOR;

class ViewTest extends TestCase
{
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

    public function testOther()
    {
        $view = new View();

        $view->setTitle('Title');
        $this->assertEquals('Title', $view->getTitle());

        $view->setTemplateParam('key', 'value');
        $this->assertEquals('value', $view->getTemplateParam('key'));
    }
}