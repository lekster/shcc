<?php

namespace Tonic;

class BadInputDataException extends Exception
{
    protected $code = 400;
    protected $message = 'Input data are not allowed';
}
