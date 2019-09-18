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
        $url = $this->classifyReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard"></i></a>';
    }

    /**
     * Return nfn icon.
     *
     * @return string
     */
    public function nfnProjectIcon()
    {
        $url = $this->projectReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard"></i></a>';
    }

    /**
     * Return nfn icon.
     *
     * @return string
     */
    public function nfnProjectIconLrg()
    {
        $url = $this->projectReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard fa-2x"></i></a>';
    }

    /**
     * Return nfn icon.
     *
     * @return string
     */
    public function nfnProjectLink()
    {
        $url = $this->projectReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" title="'.__('pages.participate').'" target="_blank">'.__('pages.event_participate').'</a>';
    }

    /**
     * Return nfn lrg icon
     *
     * @return string
     */
    public function nfnUrlLrg()
    {
        $url = $this->classifyReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard fa-2x"></i></a>';
    }

    /**
     * Return participation url.
     *
     * @return mixed
     */
    private function classifyReplace()
    {
        $urlString = str_replace('PROJECT_SLUG', $this->model->slug, config('config.nfn_participate_url'));
        $url = str_replace('WORKFLOW_ID', $this->model->panoptes_workflow_id, $urlString);

        return $url;
    }

    /**
     * Return project url.
     *
     * @return mixed
     */
    private function projectReplace()
    {
        $url = str_replace('PROJECT_SLUG', $this->model->slug, config('config.nfn_project_url'));

        return $url;
    }
}