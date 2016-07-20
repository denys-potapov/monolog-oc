<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog;

use DateTimeZone;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\DateTimeProcessor;
use Monolog\Collection\HandlerStack;
use Monolog\Collection\ProcessorStack;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;

/**
 * Monolog log channel
 *
 * It contains a stack of Handlers and a stack of Processors,
 * and uses them to store records that are added to it.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Logger extends NewLogger implements LoggerInterface
{
    /**
     * Detailed debug information
     */
    const DEBUG = 100;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 200;

    /**
     * Uncommon events
     */
    const NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 300;

    /**
     * Runtime errors
     */
    const ERROR = 400;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 500;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 550;

    /**
     * Urgent alert.
     */
    const EMERGENCY = 600;

    /**
     * Monolog API version
     *
     * This is only bumped when API breaks are done and should
     * follow the major version of the library
     *
     * @var int
     */
    const API = 2;

    /**
     * @var bool
     */
    protected $dateTimeProcessor;

    /**
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     * @param DateTimeZone       $timezone   Optional timezone, if not provided date_default_timezone_get() will be used
     */
    public function __construct(string $name, array $handlers = array(), array $processors = array(), DateTimeZone $timezone = null)
    {
        parent::__construct($name, $handlers, $processors);
        // BC compatible date time
        $this->dateTimeProcessor = new DateTimeProcessor($timezone);
        $this->pushProcessor($this->dateTimeProcessor);
    }

    /**
     * Pushes a handler on to the stack.
     *
     * @param  HandlerInterface $handler
     * @return $this
     */
    public function pushHandler(HandlerInterface $handler): self
    {
        $this->handlers->push($handler);

        return $this;
    }

    /**
     * Pops a handler from the stack
     *
     * @return HandlerInterface
     */
    public function popHandler(): HandlerInterface
    {
        return $this->handlers->pop();
    }

    /**
     * Set handlers, replacing all existing ones.
     *
     * If a map is passed, keys will be ignored.
     *
     * @param  HandlerInterface[] $handlers
     * @return $this
     */
    public function setHandlers(array $handlers): self
    {
        $this->handlers->set($handlers);

        return $this;
    }

    /**
     * @return HandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers->get();
    }

    /**
     * Adds a processor on to the stack.
     *
     * @param  callable $callback
     * @return $this
     */
    public function pushProcessor(callable $callback): HandlerInterface
    {
        $this->processors->push($callback);

        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popProcessor(): callable
    {
        return $this->processors->pop();
    }

    /**
     * @return callable[]
     */
    public function getProcessors(): array
    {
        return $this->processors->get();
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
        $this->dateTimeProcessor->useMicrosecondTimestamps($micro);
    }

    /**
     * Adds a log record.
     *
     * @param  LogLevel $level   The logging level
     * @param  string   $message The log message
     * @param  array    $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord($level, string $message, array $context = array()): bool
    {
        $this->addRecord2(LogLevel::fromLevel($level), $message, $context);

        return true;
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getLevels(): array
    {
        return LogLevel::getLevels();
    }

    /**
     * Gets the name of the logging level.
     *
     * @param  int    $level
     * @return string
     */
    public static function getLevelName(int $level): string
    {
        $name = (string) (new LogLevel($level));

        if ($name == 'undefined') {
            throw new InvalidArgumentException('Level "'.$level.'" is not defined');
        }

        return $name;
    }

    /**
     * Converts PSR-3 levels to Monolog ones if necessary
     *
     * @param string|int Level number (monolog) or name (PSR-3)
     * @return LogLevel
     */
    public static function toMonologLevel($level): LogLevel
    {
        return new LogLevel($level);
    }

    /**
     * Set the timezone to be used for the timestamp of log records.
     *
     * @param DateTimeZone $tz Timezone object
     */
    public function setTimezone(DateTimeZone $tz)
    {
        $this->dateTimeProcessor->setTimezone($tz);
    }

    /**
     * Set the timezone to be used for the timestamp of log records.
     *
     * @return DateTimeZone
     */
    public function getTimezone(): DateTimeZone
    {
        return $this->dateTimeProcessor->getTimezone();
    }
}
