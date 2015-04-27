<?php

namespace Syno;

use Symfony\Component\Console\Application as BaseConsoleApplication;
use Symfony\Component\Console\Input\InputOption;

class ConsoleApplication extends BaseConsoleApplication
{
    private $app;

    public function __construct(Application $app)
    {
        parent::__construct('Syno Package', $app::VERSION);

        $this->app = $app;

        $this->getDefinition()->addOptions(array(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $app['env']),
            new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'),
        ));
    }

    public function getApplication()
    {
        return $this->app;
    }
}
