<?php

namespace Syno\Monolog\Processor;

use Monolog\Processor\WebProcessor as BaseWebProcessor;
use Symfony\Component\HttpFoundation\Request;

class WebProcessor extends BaseWebProcessor
{
    public function setRequest(Request $request)
    {
        $this->serverData = $request->server->all();
    }
}
