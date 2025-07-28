<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

/**
 * Responsible for console app controller.
 */
abstract class ConsoleController extends AbstractController
{
    /**
     * Console app object.
     */
    protected ConsoleApp $app;

    public function __construct(ConsoleApp $app)
    {
        $this->app = $app;
        $this->init();
    }
}