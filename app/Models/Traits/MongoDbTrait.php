<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use DateTime;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDateTime;

trait MongoDbTrait
{
    /**
     * Convert a DateTime to a storable UTCDateTime object.
     *
     * @param DateTime|int $value
     *
     * @return UTCDateTime
     */
    public function fromDateTime($value)
    {
        // If the value is already a UTCDateTime instance, we don't need to parse it.
        if ($value instanceof UTCDateTime) {
            return $value;
        }
        // Let Eloquent convert the value to a DateTime instance.
        if (!$value instanceof DateTime) {
            $value = parent::asDateTime($value);
        }
        return new UTCDateTime($value->getTimestamp() * 1000);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     *
     * @return DateTime
     */
    protected function asDateTime($value)
    {
        // Convert UTCDateTime instances.
        if ($value instanceof UTCDateTime) {
            return Carbon::createFromTimestamp($value->toDateTime()->getTimestamp());
        }

        return parent::asDateTime($value);
    }

    /**
     * convert value into UTCDatetime.
     *
     * @param $value
     *
     * @return UTCDatetime
     */
    protected function asMongoDate($value)
    {
        if ($value instanceof UTCDatetime) {
            return $value;
        }

        return new UTCDatetime($this->asTimeStamp($value) * 1000);
    }

    /**
     * convert value into ObjectID if its possible.
     *
     * @param $value
     * @return ObjectID
     */
    protected function asMongoID($value)
    {
        if (is_string($value) and strlen($value) === 24 and ctype_xdigit($value)) {
            return new ObjectID($value);
        }

        return $value;
    }
}