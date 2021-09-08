<?php

namespace engine\http\response;

class NotFoundResponse extends PageRedirectResponse
{
    public function __construct()
    {
        parent::__construct("http://localhost:8000/web/main/notFound/");
    }

}
