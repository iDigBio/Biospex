<?php namespace Biospex\Services\Import;


class DarwinCoreFileImport extends ImportServiceAbstract
{
    /**
     * Upload darwin core file
     * @param $id
     * @return \Illuminate\Validation\Validator|void
     */
    public function import($id)
    {
        $validator = $this->validation->make(
            ['dwc' => $this->request->file('dwc')],
            ['dwc' => 'required|mimes:zip']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setDirectory('config.subject_import_dir');

        $filename = $this->moveFile('dwc');
        $import = $this->importInsert($this->request->input('user_id'), $id, $filename);
        $this->setTube('config.beanstalkd.import');

        $this->queue->push('Biospex\Services\Queue\DarwinCoreFileImportQueue', ['id' => $import->id], $this->tube);

        return;
    }
}
