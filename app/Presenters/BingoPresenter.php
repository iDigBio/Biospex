<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Presenters;

/**
 * Class BingoPresenter
 */
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
    public function adminShowIcon()
    {
        return '<a href="'.route('admin.bingos.show', [
            $this->model->id,
        ]).'" data-hover="tooltip" title="'.t('View Bingo').'">
                <i class="fas fa-eye"></i></a>';
    }

    /**
     * Return show icon.
     *
     * @return string
     */
    public function showIcon()
    {
        return '<a href="'.route('front.bingos.show', [
            $this->model,
        ]).'" data-hover="tooltip" title="'.t('View Bingo').'">
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
        ]).'" data-hover="tooltip" title="'.t('Edit Bingo').'">
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
        ]).'" data-hover="tooltip" title="'.t('Edit Bingo').'">
                <i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function deleteIcon()
    {
        return '<a href="'.route('admin.bingos.destroy', [
            $this->model->id,
        ]).'" class="prevent-default"
            title="'.t('Delete Bingo').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.t('Delete Bingo').'?" data-content="'.t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function deleteIconLrg()
    {
        return '<a href="'.route('admin.bingos.destroy', [
            $this->model->id,
        ]).'" class="prevent-default"
            title="'.t('Delete Bingo').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.t('Delete Bingo').'?" data-content="'.t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Create Twitter icon.
     *
     * <a href="https://twitter.com/intent/tweet?url=https%3A%2F%2Fbiospex.org%2Fevents%2F13&text=Event%20to%20show&hashtags=biospex%2Ceventname" target="_blank">
     * <i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span>
     * </a>
     *
     * @return string
     */
    public function twitterIcon()
    {
        $id = $this->model->id;
        $title = $this->model->title;
        $url = config('app.url').'/bingos/'.$id.'&text='.$title;

        return '<a href="https://twitter.com/intent/tweet?url='.$url.'" 
            target="_blank" 
            data-hover="tooltip" 
            title="'.t('Share on Twitter').'">
            <i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return facebook with Icon awesome button
     *
     * http://www.facebook.com/share.php?u=$url&title=$title
     *
     * @return string
     */
    public function facebookIcon()
    {
        $url = urlencode(config('app.url').'/bingos/'.$this->model->id);
        $title = urlencode($this->model->title);

        return '<a href="http://www.facebook.com/share.php?u='.$url.'&title='.$title.'" 
            target="_blank" 
            data-hover="tooltip" 
            title="'.t('Share on Facebook').'">
            <i class="fab fa-facebook"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return contact small icon
     *
     * @return string
     */
    public function contactIcon()
    {
        return '<a href="mailto:'.$this->model->contact.'" data-hover="tooltip" title="'.t('Contact').'">
                <i class="fas fa-envelope"></i></a>';
    }
}
