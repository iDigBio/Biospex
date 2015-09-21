<?php

namespace Biospex\Services\Import;

class DarwinCoreFileImport extends ImportServiceAbstract
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
            ['file' => \Input::file('file')],
            ['file' => 'required|mimes:zip']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setDirectory('config.subjectImportDir');

        $filename = $this->moveFile();
        $import = $this->importInsert(\Input::get('user_id'), $id, $filename);
        $this->setQueue('config.beanstalkd.import');

        \Queue::push('Biospex\Services\Queue\QueueFactory', ['id' => $import->id, 'class' => 'DarwinCoreFileImportQueue'], $this->queue);

        return;
    }
}
