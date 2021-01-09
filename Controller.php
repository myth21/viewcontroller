<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Class Controller
 * is used to be common app controller
 * @package myth21\viewcontroller
 */
abstract class Controller
{
    use UrlQueryManager;

    protected ?View $view = null;

    public function __construct(App $app = null)
    {
        $this->app = $app;
        $this->init();
    }

    protected function init()
    {

    }

    public function createView(string $absolutePathToTemplateDir, string $templateFileName = null): View
    {
        $this->view = new View();

        $this->view->setAbsoluteTemplateDirName($absolutePathToTemplateDir);

        $templateFileName = $templateFileName ?? $this->app->getParam('defaultTemplateFileName');
        $this->view->setTemplateFileName($templateFileName);

        return $this->view;
    }

}