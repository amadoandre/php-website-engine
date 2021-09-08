<?php

namespace engine\http\request;

interface IRequest
{
    public function getResquestApplicationType(): ?ResquestApplicationType;
    public function getMethod(): string;
    public function getDomain(): string;
    public function getAction(): string;
    public function getParameters(): object;
}
