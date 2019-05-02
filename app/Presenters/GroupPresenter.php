<?php

namespace App\Presenters;

use Carbon\Carbon;
use DateTimeZone;

class GroupPresenter extends Presenter
{
    /**
     * Return show icon.
     *
     * @return string
     */
    public function groupShowIcon()
    {
        return '<a href="'.route('admin.groups.show', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.view').' '.__('pages.group').'">
            <i class="fas fa-eye"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupEditIcon()
    {
        return '<a href="'.route('admin.groups.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' '.__('pages.group').'">
            <i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupEditIconLrg()
    {
        return '<a href="'.route('admin.groups.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' '.__('pages.group').'">
            <i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function groupDeleteIcon()
    {
        return '<a href="'.route('admin.groups.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('pages.delete').' '.__('pages.group').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' '.__('pages.group').'?" data-content="'.__('messages.record_delete').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function groupDeleteIconLrg()
    {
        return '<a href="'.route('admin.groups.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('pages.delete').' '.__('pages.group').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' '.__('pages.group').'?" data-content="'.__('messages.record_delete').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Return return invite icon.
     *
     * @return string
     */
    public function groupInviteIcon()
    {
        $route = route('admin.invites.index', [$this->model->id]);

        return '<a href="#" class="preventDefault" data-toggle="modal" data-remote="'.$route.'" 
                    data-target="#invite-modal" 
                    data-hover="tooltip" title="'.__('pages.invite_group').'">
                    <i class="fas fa-user-plus"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupInviteIconLrg()
    {
        $route = route('admin.invites.index', [$this->model->id]);

        return '<a href="#" class="preventDefault" data-toggle="modal" data-remote="'.$route.'" 
                    data-target="#invite-modal" 
                    data-hover="tooltip" title="'.__('pages.invite_group').'">
                    <i class="fas fa-user-plus fa-2x"></i></a>';
    }
}