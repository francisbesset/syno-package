<?php

namespace Syno\Monolog\Processor;

use Symfony\Component\HttpFoundation\Request;
use Syno\Application;

class ApplicationProcessor
{
    private $app;

    private $request;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['app_env'] = $this->app['env'];
        $record['extra']['app_debug'] = $this->app['debug'];

        $app = $this->app;
        $record['extra']['app_version'] = $app::VERSION;

        if (null !== $this->request) {
            $record['extra']['route'] = $this->request->attributes->get('_route');
            $record['extra']['user_agent'] = $this->request->server->get('HTTP_USER_AGENT');
        }

        return $record;
    }
}
