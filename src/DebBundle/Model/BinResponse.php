<?php

namespace Ivan1986\DebBundle\Model;

use Symfony\Component\HttpFoundation\Response;

class BinResponse extends Response
{
    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, $status, $headers);
        $this->headers->set('Content-Type', 'application/octet-stream');
    }
}
