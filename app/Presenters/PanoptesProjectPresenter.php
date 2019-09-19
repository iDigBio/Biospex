<?php

namespace App\Presenters;

class PanoptesProjectPresenter extends Presenter
{
    /**
     * Return icon.
     *
     * @return string
     */
    public function url()
    {
        $url = $this->classifyReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard"></i></a>';
    }

    /**
     * Return icon.
     *
     * @return string
     */
    public function projectIcon()
    {
        $url = $this->projectReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard"></i></a>';
    }

    /**
     * Return icon.
     *
     * @return string
     */
    public function projectIconLrg()
    {
        $url = $this->projectReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" data-hover="tooltip" title="'.__('pages.participate').'" target="_blank">
                <i class="fas fa-keyboard fa-2x"></i></a>';
    }

    /**
     * Return icon.
     *
     * @return string
     */
    public function projectLink()
    {
        $url = $this->projectReplace();

        return $this->model->panoptes_workflow_id === null ? '#' :
            '<a href="'.$url.'" title="'.__('pages.participate').'" target="_blank">'.__('pages.event_participate').'</a>';
    }

    /**
     * Return lrg icon
     *
     * @return string
     */
    public function urlLrg()
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