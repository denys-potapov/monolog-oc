<?php

declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Collection;

use Monolog\Handler\HandlerInterface;

/**
 * The handler stack
 *
 * It contains a stack of Handlers.
 *
 */
class HandlerStack
{
    /**
     * The handler stack
     *
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @param HandlerInterface[] $handlers   Optional stack of handlers, the first one in the array is called first, etc.
     */
    public function __construct(array $handlers = array())
    {
        $this->set($handlers);
    }

    /**
     * Pushes a handler on to the stack.
     *
     * @param  HandlerInterface $handler
     * @return $this
     */
    public function push(HandlerInterface $handler): self
    {
        array_unshift($this->handlers, $handler);

        return $this;
    }

    /**
     * Pops a handler from the stack
     *
     * @return HandlerInterface
     */
    public function pop(): HandlerInterface
    {
        if (!$this->handlers) {
            throw new \LogicException('You tried to pop from an empty handler stack.');
        }

        return array_shift($this->handlers);
    }

    /**
     * Set handlers, replacing all existing ones.
     *
     * If a map is passed, keys will be ignored.
     *
     * @param  HandlerInterface[] $handlers
     * @return $this
     */
    public function set(array $handlers): self
    {
        $this->handlers = array();
        foreach (array_reverse($handlers) as $handler) {
            $this->push($handler);
        }

        return $this;
    }

    /**
     * @return HandlerInterface[]
     */
    public function get(): array
    {
        return $this->handlers;
    }

    /**
     * Find first handler that listens on the given level
     * 
     * @param  int    $level [description]
     * @return int 
     */
    public function findHandlingKey(int $level)
    {
        $key = null;
        reset($this->handlers);
        while ($handler = current($this->handlers)) {
            if ($handler->isHandling(array('level' => $level))) {
                $key = key($this->handlers);
                break;
            }

            next($this->handlers);
        }

        return $key;
    }

    /**
     * Handle the record
     *
     * @param array $record   log record
     * @param mixed $startKey starrt from  paticular handler
     */
    public function handle($record, $reset = true)
    {
        if ($reset) {
            reset($this->handlers);
        };

        while ($handler = current($this->handlers)) {
            if (true === $handler->handle($record)) {
                break;
            }

            next($this->handlers);
        }
    }

    /**
     * Checks whether the Logger has a handler that listens on the given level
     *
     * @param  int     $level
     * @return Boolean
     */
    public function isHandling(int $level): bool
    {
        return ($this->findHandlingKey($level) !== null);
    }
}
