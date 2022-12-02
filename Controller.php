<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for common app controller.
 */
abstract class Controller
{
    use UrlQueryManagerTrait;

    /**
     * View file manager.
     */
    protected ?ViewInterface $view = null;

    /**
     * Controller constructor.
     *
     * @param AbstractApp|null $app
     */
    public function __construct(AbstractApp $app = null)
    {
        $this->app = $app;
        $this->init();
    }

    /**
     * Inits methods of child classes.
     */
    protected function init(): void
    {

    }

    /**
     * Create View.
     * @deprecated ?
     *
     * @param string $absolutePathToTemplateDir
     * @param string|null $templateFileName
     *
     * @return View
     */
//    public function createView(string $absolutePathToTemplateDir, string $templateFileName = null): View
//    {
//        $this->view = new View();
//
//        $this->view->setAbsoluteTemplateDirName($absolutePathToTemplateDir);
//
//        $templateFileName = $templateFileName ?? $this->app->getParam('defaultTemplateFileName');
//        $this->view->setTemplateFileName($templateFileName);
//
//        return $this->view;
//    }

}