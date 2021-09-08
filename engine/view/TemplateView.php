<?php

namespace engine\view;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

abstract class TemplateView implements IView
{
    private Environment $twig;

    public function __construct()
    {
        $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/../../resources/templates'));
        $this->twig->addFunction(new TwigFunction('asset', function ($asset) {
            // implement whatever logic you need to determine the asset path

            return sprintf('/assets/%s', ltrim($asset, '/'));
        }));
        $this->twig->addFunction(new TwigFunction('page', function ($page, $getParameters) {
            // implement whatever logic you need to determine the asset path

            return sprintf('/web/%s/?%s', $page);
        }));
        $this->twig->addFunction(new TwigFunction('image', function ($asset) {
            // implement whatever logic you need to determine the asset path

            return sprintf('/images/%s', ltrim($asset, '/'));
        }));
    }
    public final function render(): string
    {
        return $this->twig->render($this->getTemplate(), $this->getContext());
    }

    public abstract function getTemplate(): string;
    public abstract function getContext(): array;
}
