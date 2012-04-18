<?php

namespace Ivan1986\DebBundle\Exception;

class GpgNotFound extends \Exception
{
    /** @var string ID ключа */
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}
