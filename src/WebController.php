<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for web app controller.
 */
abstract class WebController extends AbstractController
{
    /**
     * View object to work with view/template files.
     */
    protected ?View $view = null;

    /**
     * Web app object.
     */
    protected WebApp $app;

    public function __construct(WebApp $app)
    {
        $this->app = $app;
        $this->init();
    }

    /**
     * Redirect to concrete controller and action.
     */
    protected function redirect(string $controller, string $action): void
    {
        $url = $this->app->createUrl($controller, $action);
        $this->redirectTo($url);
    }

    /**
     * HTTP response redirect header.
     */
    protected function redirectTo(string $url): void
    {
        $this->app->getResponseHeader()->redirect($url);
    }

    /**
     * Set View object.
     */
    public function setView(View $view): void
    {
        $this->view = $view;
    }
}