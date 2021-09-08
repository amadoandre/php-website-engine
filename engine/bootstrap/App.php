<?php

namespace engine\bootstrap;

use engine\config\ConfigBoot;
use engine\http\request\Request;
use engine\module\ModuleBoot;
use engine\route\Route;

class App
{
    public static function get(): App
    {
        return new App();
    }

    public function boot(ConfigBoot $configBoot, ModuleBoot $module)
    {
        ob_start();
        $configBoot::boot();
        $module::boot();

        $request = Request::capture();
        $response = Route::process($request);
        $buffer_logs = ob_get_clean();
        $response->send();
        echo $buffer_logs;
    }
}
