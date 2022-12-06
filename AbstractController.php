<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for common app controller.
 */
abstract class AbstractController
{
    use UrlQueryManagerTrait;

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