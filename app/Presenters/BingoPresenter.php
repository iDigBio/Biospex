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
    public function adminShowIcon()
    {
        return '<a href="'.route('admin.bingos.show', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.view').' '.__('pages.bingo').'">
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
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.view').' '.__('pages.bingo').'">
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
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' '.__('pages.bingo').'">
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
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' '.__('pages.bingo').'">
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
            title="'.__('pages.delete').' '.__('pages.bingo').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' '.__('pages.bingo').'?" data-content="'.__('messages.record_delete').'">
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
            title="'.__('pages.delete').' '.__('pages.bingo').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' '.__('pages.bingo').'?" data-content="'.__('messages.record_delete').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Create Twitter icon.
     *
     * <a href="https://twitter.com/intent/tweet?url=https%3A%2F%2Fbiospex.org%2Fevents%2F13&text=Event%20to%20show&hashtags=biospex%2Ceventname" target="_blank">
     * <i class="fab fa-twitter"></i> <span class="d-none text d-sm-inline"></span>
     * </a>
     * @return string
     */
    public function twitterIcon()
    {
        $id = $this->model->id;
        $title = $this->model->title;
        $hashtag = $this->model->hashtag;
        $url = config('app.url') . '/bingos/' . $id . '&text=' . $title . '&hashtags=' . $hashtag;

        return '<a href="https://twitter.com/intent/tweet?url='.$url.'" 
            target="_blank" 
            data-hover="tooltip" 
            title="'.__('pages.twitter_share').'">
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
        $url = urlencode(config('app.url') . '/bingos/' . $this->model->id);
        $title = urlencode($this->model->title);

        return '<a href="http://www.facebook.com/share.php?u='.$url.'&title='.$title.'" 
            target="_blank" 
            data-hover="tooltip" 
            title="'.__('pages.facebook_share').'">
            <i class="fab fa-facebook"></i> <span class="d-none text d-sm-inline"></span></a>';
    }
}