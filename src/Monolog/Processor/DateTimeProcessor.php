<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Processor;

use DateTimeZone;

/**
 * Processes a record's message according to PSR-3 rules
 *
 * It replaces {foo} with the value from $context['foo']
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class DateTimeProcessor
{
    /**
     * @var bool
     */
    protected $microsecondTimestamps = false;

    /**
     * @var DateTimeZone
     */
    protected $timezone;

    /**
     * @param DateTimeZone       $timezone   Optional timezone, if not provided date_default_timezone_get() will be used
     */
    public function __construct(DateTimeZone $timezone = null)
    {
        $this->timezone = new DateTimeZone($timezone ?: date_default_timezone_get() ?: 'UTC');
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        if ($this->microsecondTimestamps) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), $this->timezone);
        } else {
            $ts = new \DateTime('', $this->timezone);
        }
        $ts->setTimezone($this->timezone);

        $record['datetime'] = $ts;
        return $record;
    }

    /**
     * Control the use of microsecond resolution timestamps in the 'datetime'
     * member of new records.
     *
     * Generating microsecond resolution timestamps by calling
     * microtime(true), formatting the result via sprintf() and then parsing
     * the resulting string via \DateTime::createFromFormat() can incur
     * a measurable runtime overhead vs simple usage of DateTime to capture
     * a second resolution timestamp in systems which generate a large number
     * of log events.
     *
     * @param bool $micro True to use microtime() to create timestamps
     */
    public function useMicrosecondTimestamps(bool $micro)
    {
        $this->microsecondTimestamps = $micro;
    }

    /**
     * Set the timezone to be used for the timestamp of log records.
     *
     * @param DateTimeZone $tz Timezone object
     */
    public function setTimezone(DateTimeZone $tz)
    {
        $this->timezone = $tz;
    }

    /**
     * Set the timezone to be used for the timestamp of log records.
     *
     * @return DateTimeZone
     */
    public function getTimezone(): DateTimeZone
    {
        return $this->timezone;
    }
}
