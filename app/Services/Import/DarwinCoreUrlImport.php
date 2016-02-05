<?php namespace Biospex\Services\Import;

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
            ['data-url' => $this->request->input('data-url')],
            ['data-url' => 'required|url']
        );

        if ($validator->fails()) {
            return $validator;
        }

        $this->setTube('config.beanstalkd.import');

        $data = [
            'id'      => $id,
            'user_id' => $this->request->input('user_id'),
            'url'     => $this->request->input('data-url')
        ];

        $this->queue->push('Biospex\Services\Queue\DarwinCoreUrlImportQueue', $data, $this->tube);

        return;
    }
}
