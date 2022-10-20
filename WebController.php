<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for web app controller.
 */
abstract class WebController extends Controller
{
    protected AppWeb $app;

    protected ResponseHeader $response;

    protected function redirect(string $controller, string $action): void
    {
        $url = $this->createUrl($controller, $action);
        $this->redirectTo($url);
    }

    protected function redirectTo(string $url = null): void
    {
        $this->app->getResponseHeader()->redirect($url);
    }
}