<?php namespace Biospex\Services\Import;

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
        $recordset = strstr($this->request->input('recordset'), '/') ?
            trim(strrchr($this->request->input('recordset'), "/"), "/") : trim($this->request->input('recordset'));

        $validator = $this->validation->make(
            ['recordset' => $recordset],
            ['recordset' => 'required|alpha_dash']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setTube('config.beanstalkd.import');

        $data = [
            'id'         => $recordset,
            'user_id'    => $this->request->input('user_id'),
            'project_id' => $id
        ];
        $this->queue->push('Biospex\Services\Queue\RecordSetImportQueue', $data, $this->tube);

        return;
    }
}
