<?php

namespace Localdisk\Request;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait CastAttribute
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        foreach ($this->getCasts() as $key => $value) {
            if (! $this->has($key)) {
                continue;
            }

            $this->merge([$key => $this->castAttribute($key, $this->input($key))]);
        }
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string $key
     * @param  mixed $value
     *
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if ($value === null) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
                if (is_array($value)) {
                    return $value;
                }

                return [$value];
            case 'json':
                return $this->fromJson($value);
            case 'collection':
                return new Collection($value);
            case 'date':
                return $this->asDate($value);
            case 'datetime':
            case 'custom_datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimestamp($value);
            default:
                return $value;
        }
    }

    /**
     * Get the type of cast for a model attribute.
     *
     * @param  string $key
     *
     * @return string
     */
    protected function getCastType($key): string
    {
        if ($this->isCustomDateTimeCast($this->getCasts()[$key])) {
            return 'custom_datetime';
        }

        return strtolower(trim($this->getCasts()[$key]));
    }

    /**
     * @return array
     */
    protected function getCasts(): array
    {
        if (property_exists($this, 'casts')) {
            return $this->casts;
        }

        return [];
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param  string $value
     * @param  bool $asObject
     *
     * @return mixed
     */
    public function fromJson($value, $asObject = false)
    {
        return json_decode($value, ! $asObject);
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param  mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asDate($value): Carbon
    {
        return $this->asDateTime($value)->startOfDay();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asDateTime($value): Carbon
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon) {
            return $value;
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if ($this->isStandardDateFormat($value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Carbon::createFromFormat(
            str_replace('.v', '.u', $this->getDateFormat()), $value
        );
    }

    /**
     * Determine if the given value is a standard date format.
     *
     * @param  string  $value
     * @return bool
     */
    protected function isStandardDateFormat($value): bool
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    public function getDateFormat(): string
    {
        return $this->dateFormat ?: 'U';
    }

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed $value
     *
     * @return int
     */
    protected function asTimestamp($value): int
    {
        return $this->asDateTime($value)->getTimestamp();
    }

    /**
     * Determine if the cast type is a custom date time cast.
     *
     * @param  string $cast
     *
     * @return bool
     */
    protected function isCustomDateTimeCast($cast): bool
    {
        return strncmp($cast, 'date:', 5) === 0 ||
            strncmp($cast, 'datetime:', 9) === 0;
    }
}
