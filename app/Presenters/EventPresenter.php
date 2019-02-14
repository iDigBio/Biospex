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
            title="'.__('Share on Twitter').'">
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
            title="'.__('Share on Facebook').'">
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
            title="'.__('Contact').'">
            <i class="far fa-envelope"></i> <span class="d-none text d-sm-inline"></span></a>';
    }
}