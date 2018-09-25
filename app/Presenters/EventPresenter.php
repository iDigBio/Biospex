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
}