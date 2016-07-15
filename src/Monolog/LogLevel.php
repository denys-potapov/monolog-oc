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


use Psr\Log\LogLevel as PsrLevel;

/**
 * Monolog log channel
 *
 * It contains a stack of Handlers and a stack of Processors,
 * and uses them to store records that are added to it.
 *
 */
class LogLevel implements LogLevelInterface
{
    /*
     * Monolog log level
     * @var int
     */
    protected $level;

    /*
     * @var string[] $levels Logging levels with the levels as key
     */
    protected static $levels = [
        PsrLevel::DEBUG     => 100,
        PsrLevel::INFO      => 200,
        PsrLevel::NOTICE    => 250,
        PsrLevel::WARNING   => 300,
        PsrLevel::ERROR     => 400,
        PsrLevel::CRITICAL  => 500,
        PsrLevel::ALERT     => 550,
        PsrLevel::EMERGENCY => 600,
    ];

    /**
     * @param string|int Level number (monolog) or name (PSR-3)
     */
    public function __construct($level)
    {
        if (is_int($level)) {
            $this->level = $level;

            return;
        }

        // str to lower for bc dompatibility
        if (isset(self::$levels[strtolower($level)])) {
            $this->level = self::$levels[strtolower($level)];

            return;
        }

        throw new \Psr\Log\InvalidArgumentException('Level "'.$level.'" is not defined, use one of: '.implode(', ', array_keys(static::$levels)));
    }


    public function __toString()
    {
        $name = array_search($this->level, self::$levels);
        if ($name !== false) {

            return strtoupper($name);
        }

        return 'undefined';
    }

    /**
     * @inheritdocs
     */
    public function includes($level): bool
    {
        if ($level instanceof LogLevel) {

            return $level->getLevel() >= $this->level;
        }

        return $level >= $this->level;
    }

    /**
     * Get Monolog int level
     * 
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Gets all supported logging levels.
     *
     * @return array Assoc array with human-readable level names => level codes.
     */
    public static function getLevels(): array
    {
        return static::$levels;
    }

    /**
     * Backward compatibility
     * @deprecated
     * @param  [type] $level [description]
     * @return [type]        [description]
     */
    public static function fromLevel($level): LogLevel
    {
        if ($level instanceof LogLevel) {

            return $level;
        }

        return new LogLevel($level);
    }
}
