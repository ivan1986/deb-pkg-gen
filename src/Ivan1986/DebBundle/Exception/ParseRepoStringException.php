<?php

namespace Ivan1986\DebBundle\Exception;

class ParseRepoStringException extends \Exception
{
    public $string;

    public function __construct($code, $message, $string)
    {
        $this->string = $string;
        parent::__construct($message, $code);
    }

}
