<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use RuntimeException;
/**
 * Class View
 * @package myth21\viewcontroller
 */
class View
{
    use UrlQueryManager;

    protected ?string $absoluteTemplateDirName = null;
    protected string $templateFileName = '';
    protected string $title = '';
    protected array $templateParams = [];
    protected array $metaTags = [];
    protected bool $isMinifyHtmlSpace = false;
    protected string $viewFileExtension = '.php';
    protected string $content = '';

    protected ?Router $router;

    public function renderPart(string $name, array $data = []): string
    {
        $viewFilePath = $this->absoluteTemplateDirName . $name . $this->viewFileExtension;
        if (!is_readable($viewFilePath)) {
            throw new RuntimeException('View file "' . $viewFilePath. '" not found');
        }

        ob_start();
        ob_implicit_flush(0); // PHP 8 requires bool?
        extract($data);
        require $viewFilePath;

        return ob_get_clean();
    }

    public function render(string $name, array $data = []): string
    {
        $this->content = $this->renderPart($name, $data);

        if ($this->isMinifyHtmlSpace) {
            $this->content = $this->minifyHtmlSpace();
        }

        // warning: variables will be replace in template from template part
        $viewFilePath = $this->absoluteTemplateDirName . $this->templateFileName . $this->viewFileExtension;

        ob_start();
        ob_implicit_flush(0); // PHP 8 requires bool?
        extract($data, EXTR_OVERWRITE);
        require $viewFilePath;

        return ob_get_clean();
    }

    public function setViewFileExtension(string $extention): void
    {
        $this->viewFileExtension = $extention;
    }

    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    public function createRoute(string $routeName, array $params = []): string
    {
        return $this->router->generate($routeName, $params);
    }

    public function setIsMinifyHtmlSpace(bool $value): void
    {
        $this->isMinifyHtmlSpace = $value;
    }

    public function minifyHtmlSpace(): string
    {
        return preg_replace('/\s+/', ' ', $this->content);
    }

    public function setTemplateParams(array $params): void
    {
        $this->templateParams = $params;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setTemplateParam(string $key, $value): void
    {
        $this->templateParams[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getTemplateParam(string $key)
    {
        if (array_key_exists($key, $this->templateParams)) {
            return $this->templateParams[$key];
        }

        return null;
    }

    public function getTemplateParams(): array
    {
        return $this->templateParams;
    }

    public function setAbsoluteTemplateDirName(string $name): void
    {
        $this->absoluteTemplateDirName = $name;
    }

    public function getAbsoluteTemplateDirName(): string
    {
        return $this->absoluteTemplateDirName;
    }

    public function setTemplateFileName(string $name): void
    {
        $this->templateFileName = $name;
    }

    public function addMetaTag(string $name, string $content): void
    {
        $this->metaTags[$name] = $content;
    }

    public function getMetaTags(): array
    {
        return $this->metaTags;
    }

    public function setTitle(string $value): void
    {
        $this->title = $value;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

}