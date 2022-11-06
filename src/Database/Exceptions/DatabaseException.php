<?php

namespace Deondazy\Core\Database\Exceptions;

use Deondazy\Core\Exceptions\DeondazyCoreException;

class DatabaseException extends DeondazyCoreException
{
    public function __construct($message = null, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
