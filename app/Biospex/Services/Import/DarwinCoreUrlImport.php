<?php

namespace Biospex\Services\Import;

class DarwinCoreUrlImport extends ImportServiceAbstract
{
    /**
     * Upload subjects for project.
     *
     * @param $id
     * @return \Illuminate\Validation\Validator|void
     */
    public function import($id)
    {
        $validator = \Validator::make(
            ['data-url' => \Input::input('data-url')],
            ['data-url' => 'required|url']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setQueue('config.beanstalkd.import');

        $data = [
            'id'      => $id,
            'user_id' => \Input::input('user_id'),
            'url'     => \Input::input('data-url'),
            'class'   => 'DarwinCoreUrlImportQueue'
        ];

        \Queue::push('Biospex\Services\Queue\QueueFactory', $data, $this->queue);

        return;
    }
}
