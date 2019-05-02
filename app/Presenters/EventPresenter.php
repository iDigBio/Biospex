<?php

namespace App\Presenters;

use Carbon\Carbon;
use DateTimeZone;

class EventPresenter extends Presenter
{
    /**
     * Returns start date according to timezone.
     *
     * @return mixed
     */
    public function startDateTimezone()
    {
        return $this->model->start_date->setTimezone($this->model->timezone);
    }

    /**
     * Return start date formatted for calender picker.
     *
     * @return mixed
     */
    public function startDateCalendar()
    {
        return $this->startDateTimezone()->format('Y-m-d H:i');
    }

    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function startDateToString()
    {
        return $this->startDateTimezone()->toDayDateTimeString();
    }

    /**
     * Returns end date according to timezone.
     *
     * @return mixed
     */
    public function endDateTimezone()
    {
        return $this->model->end_date->setTimezone($this->model->timezone);
    }

    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function endDateCalendar()
    {
        return $this->endDateTimezone()->format('Y-m-d H:i');
    }

    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function endDateToString()
    {
        return $this->endDateTimezone()->toDayDateTimeString();
    }

    /**
     * Return date for scoreboard.
     *
     * start_date count down
     * event end date count down
     * after end date completed
     *
     * @return string
     */
    public function scoreboardDate()
    {
        $now = Carbon::now(new \DateTimeZone('UTC'));
        $start_date = $this->model->start_date->setTimezone('UTC');
        $end_date = $this->model->end_date->setTimeZone('UTC');

        if ($now->gt($end_date)) {
            return 'Completed';
        }

        return $end_date->gt($start_date) ? $end_date->toIso8601ZuluString() : $start_date->toIso8601ZuluString();
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
        $url = config('app.url') . '/events/' . $id . '&text=' . $title . '&hashtags=' . $hashtag;

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
        $url = urlencode(config('app.url') . '/events/' . $this->model->id);
        $title = urlencode($this->model->title);

        return '<a href="http://www.facebook.com/share.php?u='.$url.'&title='.$title.'" 
            target="_blank" 
            data-hover="tooltip" 
            title="'.__('pages.facebook_share').'">
            <i class="fab fa-facebook"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return contact with Icon awesome button
     *
     * @return string
     */
    public function contactEmailIcon()
    {
        return $this->model->contact_email === null ? '' :
            '<a href="mailto:'.$this->model->contact_email.'" 
            data-hover="tooltip" 
            title="'.__('pages.contact').'">
            <i class="far fa-envelope"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return show icon.
     *
     * @return string
     */
    public function eventShowIcon()
    {
        return '<a href="'.route('admin.events.show', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.view').' ' . __('pages.event').'">
                <i class="fas fa-eye"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function eventEditIcon()
    {
        return '<a href="'.route('admin.events.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' ' .__('pages.event').'">
                <i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function eventEditIconLrg()
    {
        return '<a href="'.route('admin.events.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'.__('pages.edit').' '.__('pages.event').'"><i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function eventDeleteIcon()
    {
        return '<a href="'.route('admin.events.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('pages.delete').' ' .__('pages.event').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' ' .__('pages.event').'?" data-content="'.__('messages.record_delete').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function eventDeleteIconLrg()
    {
        return '<a href="'.route('admin.events.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('pages.delete').' ' .__('pages.event').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('pages.delete').' ' .__('pages.event').'?" data-content="'.__('messages.record_delete').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Return return download icon lrg.
     *
     * @return string
     */
    public function eventDownloadUsersIconLrg()
    {
        $route = route('admin.events.exportUsers', [
            $this->model->id,
        ]);

        return '<a href="#" class="preventDefault event-export"
        data-href="'.$route.'"
        data-success="An email with attached export will be sent."
        data-error="There was an error while exporting. Please inform the Administration"
        data-hover="tooltip" title="'.__('pages.event_download_users').'"><i class="fas fa-users fa-2x"></i></a>';
    }

    /**
     * Return return download icon lrg.
     *
     * @return string
     */
    public function eventDownloadTranscriptsIconLrg()
    {
        $route = route('admin.events.exportTranscriptions', [
            $this->model->id,
        ]);

        return '<a href="#" class="preventDefault event-export"
        data-href="'.$route.'"
        data-success="An email with attached export will be sent."
        data-error="There was an error while exporting. Please inform the Administration"
        data-hover="tooltip" title="'.__('pages.event_download_transcripts').'">
        <i class="fas fa-file-download fa-2x"></i></a>';
    }
}