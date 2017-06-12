<?php

namespace App\Services\Import;

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
        $validator = $this->validation->make(
            ['data-url' => request()->input('data-url')],
            ['data-url' => 'required|url']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setTube('config.beanstalkd.import');

        $data = [
            'id'      => $id,
            'user_id' => request()->input('user_id'),
            'url'     => request()->input('data-url')
        ];

        $this->queue->push('App\Services\Queue\DarwinCoreUrlImportQueue', $data, $this->tube);

    }
}
