<?php

namespace Syno\Monolog\Processor;

use Symfony\Component\HttpFoundation\Request;

class SynologyProcessor
{
    protected $request;

    protected $postParams = array(
        'package_update_channel',
        'unique',
        'build',
        'language',
        'major',
        'minor',
        'arch',
        'timezone',
        'ds_sn',
    );

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
        if (null === $this->request) {
            return $record;
        }

        if ($this->request->isMethod('POST')) {
            foreach ($this->postParams as $param) {
                if ($this->request->request->has($param)) {
                    $record['extra']['syno_'.$param] = $this->request->request->get($param);
                }
            }

            if ($this->request->request->has('major') && $this->request->request->has('minor')) {
                $record['extra']['syno_dsm'] = $this->request->request->get('major').'.'.$this->request->request->get('minor');
            }
        }

        return $record;
    }
}
