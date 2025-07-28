<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Exception;
use RuntimeException;
use function is_readable;
use const EXTR_OVERWRITE;

/**
 * Responsible for work with view files.
 */
class View
{
    use UrlQueryManagerTrait;

    /**
     * Absolute path to template dir name.
     */
    protected string $absoluteTemplateDirName;

    /**
     * Template file name.
     */
    protected string $templateFileName;

    /**
     * View title, is can be used for html title.
     */
    protected string $title = '';

    /**
     * Params are passed in template file name.
     */
    protected array $templateParams = [];

    /**
     * Main content of page, screen and so on.
     */
    protected string $content = '';

    /**
     * Router to generate (create) urls.
     */
    protected ?RouterInterface $router;

    /**
     * Return file content without template.
     *
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function renderPart(string $name, array $data = []): string
    {
        $viewFilePath = $this->absoluteTemplateDirName . $name . '.php';
        if (!is_readable($viewFilePath)) {
            throw new RuntimeException('View file "' . $viewFilePath. '" not found', 404);
        }

        ob_start();
        ob_implicit_flush(false);
        // Do not use extract() on untrusted data, like user input (e.g. $_GET, $_FILES).
        extract($data, EXTR_OVERWRITE);
        require $viewFilePath;

        return ob_get_clean();
    }

    /**
     * Return file content with template, set got file content in template content.
     *
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function render(string $name, array $data = []): string
    {
        $this->content = $this->renderPart($name, $data);

        // Warning: variables will be replaced in template from template part.
        $viewFilePath = $this->absoluteTemplateDirName . $this->templateFileName . '.php';

        // Filtering, delete vars like: '1invalid', 'GLOBALS', 'my-var'
        foreach ($data as $key => $value) {
            if (!preg_match('/^[a-zA-Z_]\w*$/', $key)) {
                unset($data[$key]);
            }
        }

        ob_start();
        ob_implicit_flush(false);
        // Do not use extract() on untrusted data, like user input (e.g. $_GET, $_FILES).
        extract($data, EXTR_OVERWRITE);
        require $viewFilePath;

        return ob_get_clean();
    }

    /**
     * Return file content.
     *
     * @param string $name
     * @param array $data
     *
     * @return string
     */
    public function renderFile(string $name, array $data = []): string
    {
        if (!is_readable($name)) {
            throw new RuntimeException('View file "' . $name. '" not found', 404);
        }

        ob_start();
        ob_implicit_flush(false);
        // Do not use extract() on untrusted data, like user input (e.g. $_GET, $_FILES).
        extract($data, EXTR_OVERWRITE);
        require $name;

        return ob_get_clean();
    }

    /**
     * Set router.
     *
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * Return generated url resource by router.
     *
     * @param string $routeName
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function createRoute(string $routeName, array $params = []): string
    {
        return $this->router->generate($routeName, $params);
    }

    /**
     * Set params in template file.
     * @param array $params
     */
    public function setTemplateParams(array $params): void
    {
        $this->templateParams = $params;
    }

    /**
     * Set a param in template file.
     *
     * @param string $key
     * @param string|float|int|array|object|null $value
     */
    public function setTemplateParam(string $key, string|float|int|array|object|null $value): void
    {
        $this->templateParams[$key] = $value;
    }

    /**
     * Return param was set in template file.
     *
     * @param string $key
     *
     * @return string|float|int|array|object|null
     */
    public function getTemplateParam(string $key): string|float|int|array|object|null
    {
        return $this->templateParams[$key] ?? null;
    }

    /**
     * Return params were set in template file.
     *
     * @return array
     */
    public function getTemplateParams(): array
    {
        return $this->templateParams;
    }

    /**
     * Set absolute path to template dir name.
     */
    public function setAbsoluteTemplateDirName(string $name): void
    {
        $this->absoluteTemplateDirName = $name;
    }

    /**
     * Return absolute path to template dir name.
     */
    public function getAbsoluteTemplateDirName(): string
    {
        return $this->absoluteTemplateDirName;
    }

    /**
     * Set template file name.
     */
    public function setTemplateFileName(string $name): void
    {
        $this->templateFileName = $name;
    }

    /**
     * Set view title.
     */
    public function setTitle(string $value): void
    {
        $this->title = $value;
    }

    /**
     * Return view title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Return page, screen... main content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

}