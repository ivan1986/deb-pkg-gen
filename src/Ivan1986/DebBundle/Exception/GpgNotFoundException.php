<?php

namespace Ivan1986\DebBundle\Exception;

/**
 * На сервере не найден ключ
 */
class GpgNotFoundException extends \Exception
{
    /** @var string ID ключа */
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}
