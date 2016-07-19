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
use Monolog\Handler\GroupHandler;
use Monolog\Collection\HandlerStack;
use Monolog\Collection\ProcessorStack;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerTrait;

/**
 * Monolog log channel ()
 *
 */
class NewLogger extends GroupHandler
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     * @param callable[]         $processors Optional array of processors
     * @param DateTimeZone       $timezone   Optional timezone, if not provided date_default_timezone_get() will be used
     */
    public function __construct(string $name, array $handlers = array(), array $processors = array())
    {
        $this->name = $name;
        $this->handlers = new HandlerStack($handlers);
        $this->processors = new ProcessorStack($processors);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return a new cloned instance with the name changed
     *
     * @return static
     */
    public function withName($name)
    {
        $new = clone $this;
        $new->name = $name;

        return $new;
    }

    /**
     * Adds a log record. TODO: the method called addRecord2 because
     * it accepts only LogLevel instance as level
     *
     * @param  LogLevel $level   The logging level
     * @param  string   $message The log message
     * @param  array    $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function addRecord2(LogLevel $level, string $message, array $context = array()): bool
    {
        $record = array(
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => (string) $level,
            'channel' => $this->name,
            'datetime' => new \DateTime(),
            'extra' => array(),
        );

        $this->handle($record);

        return true;
    }
    
    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  mixed   $level   The log level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean Whether the record has been processed
     */
    public function log($level, $message, array $context = array())
    {
        return $this->addRecord(new LogLevel($level), (string) $message, $context);
    }
}
