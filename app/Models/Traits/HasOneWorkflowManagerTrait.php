<?php namespace App\Models\Traits;

trait HasOneWorkflowManagerTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function workflowManager()
    {
        return $this->hasOne('App\Models\WorkflowManager');
    }
}
