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
    protected ?View $view = null;

    protected null|WebApp|ConsoleApp $app;

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
}