<?php namespace Biospex\Services\Import;

class NfnTranscriptionImport extends ImportServiceAbstract
{
    /**
     * Upload results for NfN.
     *
     * @param $id
     * @return string|void
     */
    public function import($id)
    {
        $validator = $this->validation->make(
            ['file' => $this->request->file('transcription')],
            ['file' => 'required|mimes:txt']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setDirectory('config.transcription_import_dir');

        $filename = $this->moveFile('transcription');
        $import = $this->importInsert($this->request->input('user_id'), $id, $filename);
        $this->setTube('config.beanstalkd.import');

        $this->queue->push('Biospex\Services\Queue\NfnTranscriptionQueue', ['id' => $import->id], $this->tube);

        return;
    }
}
