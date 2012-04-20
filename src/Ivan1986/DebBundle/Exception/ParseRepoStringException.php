<?php

namespace Ivan1986\DebBundle\Exception;

class ParseRepoStringException extends \Exception
{
    public $string;

    /**
     * @param string $string
     * @param int $message
     * @param \Exception|null $code
     */
    public function __construct($string, $message, $code)
    {
        $this->string = $string;
        parent::__construct($message, $code);
    }

}
