<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use Exception;
use RuntimeException;
use Throwable;
use function is_readable;
use function is_string;
use function preg_match;
use function str_starts_with;
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
        return $this->requireFile($this->absoluteTemplateDirName . $name . '.php', $data);
    }

    /**
     * Return file content with template, set got file content in template content.
     */
    public function render(string $name, array $data = []): string
    {
        $this->content = $this->renderPart($name, $data);

        // Warning: variables will be replaced in template from template part.
        return $this->requireFile($this->absoluteTemplateDirName . $this->templateFileName . '.php', $data);
    }

    /**
     * Return file content.
     */
    public function renderFile(string $name, array $data = []): string
    {
        return $this->requireFile($name, $data);
    }

    /**
     * Include a view file and return what it printed.
     *
     * Locals here are __-prefixed and template data may not use that prefix (see
     * filterVariableNames()): the path being included is a local variable, and extract() with
     * EXTR_OVERWRITE replaces locals - so a data key named after one of them decided which file
     * got included. `renderFile($path, ['name' => 'Ivan'])` was enough to hit it.
     */
    protected function requireFile(string $__filePath, array $__data): string
    {
        if (!is_readable($__filePath)) {
            throw new RuntimeException('The view file "' . $__filePath . '" has not been found');
        }

        ob_start();
        ob_implicit_flush(false);

        try {
            // Do not use extract() on untrusted data, like user input (e.g. $_GET, $_FILES).
            extract(self::filterVariableNames($__data), EXTR_OVERWRITE);
            require $__filePath;

            return (string)ob_get_clean();
        } catch (Throwable $e) {
            // Leaving the buffer open would swallow whatever is printed after this.
            ob_end_clean();

            throw $e;
        }
    }

    /**
     * Drop keys that cannot become a variable, and keys reserved for this class's own locals.
     *
     * @param array<array-key, mixed> $data
     * @return array<string, mixed>
     */
    private static function filterVariableNames(array $data): array
    {
        foreach ($data as $key => $value) {
            // Filtering, delete vars like: '1invalid', 'GLOBALS', 'my-var'
            if (!is_string($key) || !preg_match('/^[a-zA-Z_]\w*$/', $key)) {
                unset($data[$key]);
                continue;
            }

            if (str_starts_with($key, '__')) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Set a router.
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * Return generated url resource by router.
     *
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
     */
    public function getTemplateParam(string $key): string|float|int|array|object|null|bool
    {
        return $this->templateParams[$key] ?? null;
    }

    /**
     * Return params were set in template file.
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