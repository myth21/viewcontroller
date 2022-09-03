<?php

declare(strict_types=1);

namespace myth21\viewcontroller;

use RuntimeException;

use const EXTR_OVERWRITE;

class View
{
    use UrlQueryManager;

    protected ?string $absoluteTemplateDirName = null;
    protected string $templateFileName = '';
    protected string $title = ''; // TODO deleting, it's not his responsibility
    protected array $templateParams = [];
    protected array $metaTags = []; // TODO deleting, it's not his responsibility
    protected string $content = '';

    protected ?Router $router;
    protected ?PresenterInterface $presenter;

    public function renderPart(string $name, array $data = []): string
    {
        $viewFilePath = $this->absoluteTemplateDirName . $name . '.php';
        if (!is_readable($viewFilePath)) {
            throw new RuntimeException('View file "' . $viewFilePath. '" not found');
        }

        ob_start();
        ob_implicit_flush(false);
        extract($data, EXTR_OVERWRITE);
        require $viewFilePath;

        return ob_get_clean();
    }

    public function render(string $name, array $data = []): string
    {
        $this->content = $this->renderPart($name, $data);

        // Warning: variables will be replace in template from template part
        $viewFilePath = $this->absoluteTemplateDirName . $this->templateFileName . '.php';

        ob_start();
        ob_implicit_flush(false);
        extract($data, EXTR_OVERWRITE);
        require $viewFilePath;

        return ob_get_clean();
    }

    public function setPresenter(PresenterInterface $viewModel): void
    {
        $this->presenter = $viewModel;
    }

    public function getPresenter(): PresenterInterface
    {
        return $this->presenter;
    }

    public function setRouter(Router $router): void
    {
        $this->router = $router;
    }

    public function createRoute(string $routeName, array $params = []): string
    {
        return $this->router->generate($routeName, $params);
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