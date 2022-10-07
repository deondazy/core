<?php 

namespace Deondazy\Core\Helpers;

class Helpers
{
    public static function dd($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }

    public static function publicPath($path)
    {
        return CORE_ROOT . '/public/' . $path;
    }
}