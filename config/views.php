<?php

declare(strict_types = 1);

return [
    
    'twig' => [
        /*
        |--------------------------------------------------------------------------
        | Twig Debug
        |--------------------------------------------------------------------------
        |
        | When set to true, the generated templates have a __toString() method that 
        | you can use to display the generated nodes (default to false).
        |
        */
        'debug' => true,

        /*
        |--------------------------------------------------------------------------
        | Twig Template Charset
        |--------------------------------------------------------------------------
        |
        | The charset used by the templates.
        |
        */
        'charset' => 'utf-8',

        /*
        |--------------------------------------------------------------------------
        | Twig Template Cache
        |--------------------------------------------------------------------------
        |
        | An absolute path where to store the compiled templates, or false to 
        | disable caching (which is the default).
        |
        */
        'cache' => '../storage/cache/twig',

        /*
        |--------------------------------------------------------------------------
        | Twig Template Auto Reload
        |--------------------------------------------------------------------------
        |
        | When developing with Twig, it's useful to recompile the template 
        | whenever the source code changes. 
        | If you don't provide a value for the auto_reload option, 
        | it will be determined automatically based on the debug value.
        |
        */
        'auto_reload' => true,

        /*
        |--------------------------------------------------------------------------
        | Twig Template Strict Variables
        |--------------------------------------------------------------------------
        |
        | If set to false, Twig will silently ignore invalid variables 
        | (variables and or attributes/methods that do not exist) and 
        | replace them with a null value. 
        | When set to true, Twig throws an exception instead (default to false).
        |
        */
        'strict_variables' => true,

        /*
        |--------------------------------------------------------------------------
        | Twig Auto Escape Strategy
        |--------------------------------------------------------------------------
        |
        | Sets the default auto-escaping strategy.
        | Set it to false to disable auto-escaping. 
        | The name escaping strategy determines the escaping strategy to use 
        | for a template based on the template filename extension 
        | (this strategy does not incur any overhead at runtime 
        | as auto-escaping is done at compilation time.)
        |
        | Supported: 'name', 'html', 'js', 'css', 'url', 'html_attr', 
        |            or a PHP callback that takes the template "filename" 
        |            and returns the escaping strategy to use 
        |            -- the callback cannot be a function name to avoid 
        |            collision with built-in escaping strategies);
        |
        */
        'autoescape' => 'name',

        /*
        |--------------------------------------------------------------------------
        | Twig Template Optimization
        |--------------------------------------------------------------------------
        |
        | A flag that indicates which optimizations to apply 
        | (default to -1 -- all optimizations are enabled; set it to 0 to disable).
        |
        */
        'optimizations' => -1,

        /*
        |--------------------------------------------------------------------------
        | Twig Template Extensions
        |--------------------------------------------------------------------------
        |
        | Supported template extensions
        |
        */
        'extensions' => [
            '.html',
            '.twig',
            '.html.twig',
        ],
    ],
];