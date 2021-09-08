<?php

namespace engine\view;

use engine\http\response\IResponseContent;
use engine\view\TemplateView;

abstract class PageView extends TemplateView implements IResponseContent
{
    public final function getResponseContent(): string
    {
        return $this->render();
    }
}
