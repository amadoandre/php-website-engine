<?php

namespace engine\http\response;

interface IResponse
{
    public function send(): void;
}
