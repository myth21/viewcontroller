<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for web app controller.
 */
abstract class WebController extends AbstractController
{
    /**
     * View file manager.
     */
    protected ?ViewInterface $view = null;

    /**
     * Web app object.
     */
    protected WebApp $app;

    /**
     * Response header object.
     */
    protected ResponseHeader $response;

    /**
     * Redirect to concrete controller and action.
     *
     * @param string $controller
     * @param string $action
     */
    protected function redirect(string $controller, string $action): void
    {
        $url = $this->createUrl($controller, $action);
        $this->redirectTo($url);
    }

    /**
     * HTTP response redirect header.
     *
     * @param string|null $url
     */
    protected function redirectTo(string $url = null): void
    {
        $this->app->getResponseHeader()->redirect($url);
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
    public function createView(string $absolutePathToTemplateDir, string $templateFileName = null): View
    {
        $this->view = new View();

        $this->view->setAbsoluteTemplateDirName($absolutePathToTemplateDir);

        $templateFileName = $templateFileName ?? $this->app->getParam('defaultTemplateFileName');
        $this->view->setTemplateFileName($templateFileName);

        return $this->view;
    }

    /**
     * Set View.
     *
     * @param ViewInterface $view
     */
    public function setView(ViewInterface $view): void
    {
        $this->view = $view;
    }
}