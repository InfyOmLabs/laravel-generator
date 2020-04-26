<?php

namespace App\Helpers;

use Carbon\Carbon;
use function Safe\strtotime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DateHelper
{
    /**
     * Creates a Carbon object from DateTime format.
     * If timezone is given, it parse the date with this timezone.
     * Always return a date with default timezone (UTC).
     *
     * @param \DateTime|Carbon|string|null $date
     * @param string $timezone
     * @return Carbon|null
     */
    public static function parseDateTime($date, $timezone = null)
    {
        if (is_null($date)) {
            return;
        }
        if ($date instanceof Carbon) {
            // ok
        } elseif ($date instanceof \DateTimeInterface) {
            $date = Carbon::instance($date);
        } else {
            try {
                $date = Carbon::parse($date, $timezone);
            } catch (\Exception $e) {
                // Parse error
                return;
            }
        }

        $appTimezone = config('app.timezone');
        if ($date->timezone !== $appTimezone) {
            $date->setTimezone($appTimezone);
        }

        return $date;
    }

    /**
     * Creates a Carbon object from Date format.
     * If timezone is given, it parse the date with this timezone.
     * Always return a date with default timezone (UTC).
     *
     * @param Carbon|string $date
     * @param string $timezone
     * @return Carbon|null
     */
    public static function parseDate($date, $timezone = null)
    {
        if (! $date instanceof Carbon) {
            try {
                $date = Carbon::parse($date);
            } catch (\Exception $e) {
                // Parse error
                return;
            }
        }

        $date = Carbon::create($date->year, $date->month, $date->day, 0, 0, 0, $timezone ?? $date->timezone);
        $f = $date->getLocale();

        $appTimezone = config('app.timezone');
        if ($date->timezone !== $appTimezone) {
            $date->setTimezone($appTimezone);
        }

        return $date;
    }

    /**
     * Return timestamp date format.
     *
     * @param Carbon|\App\Models\Instance\SpecialDate|string|null $date
     * @return string|null
     */
    public static function getTimestamp($date)
    {
        if (is_null($date)) {
            return;
        }
        if ($date instanceof \App\Models\Instance\SpecialDate) {
            $date = $date->date;
        }
        if (! $date instanceof Carbon) {
            $date = Carbon::create($date);
        }

        return $date->translatedFormat(config('api.timestamp_format'));
    }

    /**
     * Return date timestamp format.
     *
     * @param Carbon|\App\Models\Instance\SpecialDate|string|null $date
     * @return string|null
     */
    public static function getDate($date)
    {
        if (is_null($date)) {
            return;
        }
        if ($date instanceof \App\Models\Instance\SpecialDate) {
            $date = $date->date;
        }
        if (! $date instanceof Carbon) {
            $date = Carbon::create($date);
        }

        return $date->translatedFormat('Y-m-d');
    }

    /**
     * Get timezone of the current user, or null.
     *
     * @return string|null
     */
    public static function getTimezone()
    {
        if (Auth::check()) {
            return Auth::user()->timezone;
        }
    }

    /**
     * Return a date in a short format like "Oct 29, 1981".
     *
     * @param string $date
     * @return string
     */
    public static function getShortDate($date): string
    {
        return self::formatDate($date, 'format.short_date_year');
    }

    /**
     * Return a date in a full format like "October 29, 1981".
     *
     * @param string $date
     * @return string
     */
    public static function getFullDate($date): string
    {
        return self::formatDate($date, 'format.full_date_year');
    }

    /**
     * Return the month of the date according to the timezone of the user
     * like "Oct", or "Dec".
     *
     * @param string $date
     * @return string
     */
    public static function getShortMonth($date): string
    {
        return self::formatDate($date, 'format.short_month');
    }

    /**
     * Return the month and year of the date according to the timezone of the user
     * like "October 2010", or "March 2032".
     *
     * @param string $date
     * @return string
     */
    public static function getFullMonthAndDate($date): string
    {
        return self::formatDate($date, 'format.full_month_year');
    }

    /**
     * Return the day of the date according to the timezone of the user
     * like "Mon", or "Wed".
     *
     * @param Carbon $date
     * @return string
     */
    public static function getShortDay($date): string
    {
        return self::formatDate($date, 'format.short_day');
    }

    /**
     * Return a date according to the timezone of the user, in a short format
     * like "Oct 29".
     *
     * @param Carbon $date
     * @return string
     */
    public static function getShortDateWithoutYear($date): string
    {
        return self::formatDate($date, 'format.short_date');
    }

    /**
     * Return a date and the time according to the timezone of the user, in a short format
     * like "Oct 29, 1981 19:32".
     *
     * @param Carbon $date
     * @return string
     */
    public static function getShortDateWithTime($date): string
    {
        return self::formatDate($date, 'format.short_date_year_time');
    }

    /**
     * Return a date in a given format.
     *
     * @param string $date
     * @return string
     */
    private static function formatDate($date, $format): string
    {
        $date = Carbon::parse($date)->setTimezone(static::getTimezone());
        $format = trans($format, [], Carbon::getLocale());

        return $date->translatedFormat($format) ?: '';
    }

    /**
     * Add a given number of week/month/year to a date.
     * @param Carbon $date      the start date
     * @param string $frequency week/month/year
     * @param int $number    the number of week/month/year to increment to
     * @return Carbon
     */
    public static function addTimeAccordingToFrequencyType(Carbon $date, string $frequency, int $number): Carbon
    {
        switch ($frequency) {
            case 'week':
                $date->addWeeks($number);
                break;
            case 'month':
                $date->addMonths($number);
                break;
            default:
                $date->addYears($number);
                break;
        }

        return $date;
    }

    /**
     * Get the name of the month and year of a given date with a given number
     * of months more.
     * @param  int    $month
     * @return string
     */
    public static function getMonthAndYear(int $month): string
    {
        $date = Carbon::now(static::getTimezone())->addMonthsNoOverflow($month);
        $format = trans('format.short_month_year', [], Carbon::getLocale());

        return $date->translatedFormat($format) ?: '';
    }

    /**
     * Gets the next theoritical billing date.
     * This is used on the Upgrade page to tell the user when the next billing
     * date would be if he subscribed.
     *
     * @param  string $interval
     * @return Carbon
     */
    public static function getNextTheoriticalBillingDate(string $interval): Carbon
    {
        if ($interval == 'monthly') {
            return now()->addMonth();
        }

        return now()->addYear();
    }

    /**
     * Gets a list of all the year from min to max (0 is the current year).
     *
     * @param int $max
     * @param int $min
     * @return Collection
     */
    public static function getListOfYears($max = 120, $min = 0): Collection
    {
        $years = collect([]);
        $maxYear = now(static::getTimezone())->subYears($min)->year;
        $minYear = now(static::getTimezone())->subYears($max)->year;

        for ($year = $maxYear; $year >= $minYear; $year--) {
            $years->push([
                'id' => $year,
                'name' => $year,
            ]);
        }

        return $years;
    }

    /**
     * Gets a list of all the months in a year.
     *
     * @return Collection
     */
    public static function getListOfMonths()
    {
        $months = collect([]);
        $currentDate = Carbon::parse('2000-01-01');
        $format = trans('format.full_month', [], Carbon::getLocale());

        for ($month = 1; $month <= 12; $month++) {
            $currentDate->month = $month;
            $months->push([
                'id' => $month,
                'name' => mb_convert_case($currentDate->translatedFormat($format), MB_CASE_TITLE, 'UTF-8'),
            ]);
        }

        return $months;
    }

    /**
     * Gets a list of all the days in a month.
     *
     * @return Collection
     */
    public static function getListOfDays()
    {
        $days = collect([]);
        for ($day = 1; $day <= 31; $day++) {
            $days->push(['id' => $day, 'name' => $day]);
        }

        return $days;
    }

    /**
     * Gets a list of all the hours in a day.
     *
     * @return Collection
     */
    public static function getListOfHours()
    {
        $currentDate = Carbon::parse('2000-01-01 00:00:00');
        $format = trans('format.full_hour', [], Carbon::getLocale());

        $hours = collect([]);
        for ($hour = 1; $hour <= 24; $hour++) {
            $currentDate->hour = $hour;
            $hours->push([
                'id' => date('H:i', strtotime("$hour:00")),
                'name' => $currentDate->translatedFormat($format),
            ]);
        }

        return $hours;
    }
}
