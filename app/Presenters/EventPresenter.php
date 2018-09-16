<?php

namespace App\Presenters;

use Carbon\Carbon;
use DateTimeZone;

class EventPresenter extends Presenter
{
    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function startDateTimezone()
    {
        return $this->model->start_date->setTimezone($this->model->timezone)->toDayDateTimeString();
    }

    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function startDateUtcTimezone()
    {
        return $this->model->start_date->setTimezone('UTC')->toDayDateTimeString();
    }

    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function endDateTimezone()
    {
        return $this->model->end_date->setTimezone($this->model->timezone)->toDayDateTimeString();
    }

    /**
     * Returns event date as string.
     *
     * @return string
     */
    public function endDateUtcTimezone()
    {
        return $this->model->end_date->setTimezone('UTC')->toDayDateTimeString();
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
        $now = Carbon::now(new DateTimeZone('UTC'));
        $start_date = $this->model->start_date->setTimezone('UTC');
        $end_date = $this->model->end_date->setTimezone('UTC');
        if ($now->gt($end_date)) {
            return 'Completed';
        }

        return $end_date->gt($start_date) ? $end_date->toDayDateTimeString() : $start_date->toDayDateTimeString();
    }
}