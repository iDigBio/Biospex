<?php

namespace App\Presenters;

class NfnWorkflowPresenter extends Presenter
{
    /**
     * Return nfn icon.
     *
     * @return string
     */
    public function nfnUrl()
    {
        $url = $this->replace();

        return $this->model->workflow === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard"></i></a>';
    }

    /**
     * Return nfn lrg icon
     *
     * @return string
     */
    public function nfnUrlLrg()
    {
        $url = $this->replace();

        return $this->model->workflow === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard fa-2x"></i></a>';
    }

    /**
     * Return participation url.
     *
     * @return mixed
     */
    private function replace()
    {
        $urlString = str_replace('PROJECT_SLUG', $this->model->slug, config('config.nfn_participate_url'));
        $url = str_replace('WORKFLOW_ID', $this->model->workflow, $urlString);

        return $url;
    }
}