<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Class WebController
 * @package myth21\viewcontroller
 */
abstract class WebController extends Controller
{
    protected AppWeb $app;

    protected ResponseHeader $response;

    public function createView(string $dir = '', string $templateFileName = ''): View
    {
        $this->view = new View();

        $dir = $dir ?: $this->app->getParam('defaultViewDirName') . $this->app->getParam('defaultTemplateDirName');
        $this->view->setTemplateDir($dir);

        $templateFileName = $templateFileName ?: $this->app->getParam('defaultTemplateFileName');
        $this->view->setTemplateFileName($templateFileName);

        return $this->view;
    }

    protected function redirect(string $controller, string $action): void
    {
        $url = $this->createUrl($controller, $action);
        $this->redirectTo($url);
    }

    protected function redirectTo(string $url = null): void
    {
        $this->app->getResponseHeader()->redirect($url);
    }

    protected static function getCsrfTokenName(): string
    {
        return 'csrfToken';
    }

}