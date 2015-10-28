<?php

namespace Biospex\Services\Import;

class RecordSetImport extends ImportServiceAbstract
{
    /**
     * Upload using record set id or url. Project Id passed.
     *
     * @param $id
     * @return \Illuminate\Validation\Validator|void
     */
    public function import($id)
    {
        $recordset = strstr(\Input::get('recordset'), '/') ?
            trim(strrchr(\Input::get('recordset'), "/"), "/") : trim(\Input::get('recordset'));

        $validator = \Validator::make(
            ['recordset' => $recordset],
            ['recordset' => 'required|alpha_dash']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setQueue('config.beanstalkd.import');

        $data = [
            'id'         => $recordset,
            'user_id'    => \Input::get('user_id'),
            'project_id' => $id
        ];
        \Queue::push('Biospex\Services\Queue\RecordSetImportQueue', $data, $this->queue);

        return;
    }
}
