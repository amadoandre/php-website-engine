<?php

namespace engine\http\response;

class PageRedirectResponse extends Response
{
    public function __construct(string $url)
    {
        parent::__construct(null, parent::HTTP_SEE_OTHER, array("Location: $url"));
    }

}
