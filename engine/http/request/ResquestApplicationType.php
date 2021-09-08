<?php
namespace engine\http\request;

enum ResquestApplicationType: string
{
    case WEB = "web";
    case ASSET = "asset";
    case API = "api";
}