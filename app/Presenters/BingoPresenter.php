<?php

namespace App\Presenters;

use Carbon\Carbon;
use DateTimeZone;

class BingoPresenter extends Presenter
{
    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function createDateToString()
    {
        return $this->model->created_at->toDayDateTimeString();
    }

    /**
 * Return show icon.
 *
 * @return string
 */
    public function readIcon()
    {
        return '<a href="'.route('admin.bingos.read', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.view').' ' . __('pages.bingo').'">
                <i class="fas fa-eye"></i></a>';
    }

    /**
     * Return show icon.
     *
     * @return string
     */
    public function showIcon()
    {
        return '<a href="'.route('admin.bingos.read', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.view').' ' . __('pages.bingo').'">
                <i class="fas fa-eye"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function editIcon()
    {
        return '<a href="'.route('admin.bingos.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' ' .__('pages.bingo').'">
                <i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function editIconLrg()
    {
        return '<a href="'.route('admin.bingos.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' ' .__('pages.bingo').'">
                <i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function deleteIcon()
    {
        return '<a href="'.route('admin.bingos.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('pages.delete').' ' .__('pages.bingo').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' ' .__('pages.bingo').'?" data-content="'.__('messages.record_delete').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function deleteIconLrg()
    {
        return '<a href="'.route('admin.bingos.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('pages.delete').' ' .__('pages.bingo').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' ' .__('pages.bingo').'?" data-content="'.__('messages.record_delete').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }
}