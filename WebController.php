<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for web app controller.
 */
abstract class WebController extends Controller
{
    /**
     * Web app object.
     */
    protected AppWeb $app;

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
}