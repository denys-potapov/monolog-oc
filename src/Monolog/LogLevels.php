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
class LogLevels implements LogLevelInterface
{
    /**
     * Minimum level for logs that are passed to handler
     *
     * @var int[]
     */
    protected $acceptedLevels;


    /**
     * @param int|string|array $minLevelOrList A list of levels to accept or a minimum level or level name if maxLevel is provided
     * @param int|string       $maxLevel       Maximum level or level name to accept, only used if $minLevelOrList is not an array
     */
    public function __construct($minLevelOrList, $maxLevel)
    {
        if (is_array($minLevelOrList)) {
            $acceptedLevels = array_map(function ($level) {
                return (new LogLevel($level))->getLevel();
            }, $minLevelOrList);
        } else {
            $minLevelOrList = Logger::toMonologLevel($minLevelOrList)->getLevel();
            $maxLevel = Logger::toMonologLevel($maxLevel)->getLevel();
            $acceptedLevels = array_values(array_filter(Logger::getLevels(), function ($level) use ($minLevelOrList, $maxLevel) {
                return $level >= $minLevelOrList && $level <= $maxLevel;
            }));
        }
        $this->levels = array_flip($acceptedLevels);
    }

    /**
     * @inheritdocs
     */
    public function includes($level): bool
    {
        if ($level instanceof LogLevel) {

            return isset($this->levels[$level->getLevel()]);
        }

        return isset($this->levels[$level]);
    }

    /**
     * @return array
     */
    public function getLevels(): array
    {
        return array_flip($this->levels);
    }
}
