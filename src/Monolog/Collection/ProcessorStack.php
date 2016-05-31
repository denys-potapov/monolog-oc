<?php

/*
 * This file is part of the Monolog package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Collection;

/**
 * Processors stack
 *
 * It contains a stack of processors
 *
 */
class ProcessorStack
{
   
    /**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var callable[]
     */
    protected $processors;

    /**
     * @param callable[] $processors Array of processors
     */
    public function __construct(array $processors = array())
    {
        $this->processors = $processors;
    }

    /**
     * Adds a processor on to the stack.
     *
     * @param  callable $callback
     * @return $this
     */
    public function push(callable $callback): self
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
        }

        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function pop(): callable
    {
        if (!$this->processors) {
            throw new \LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * @return callable[]
     */
    public function get(): array
    {
        return $this->processors;
    }

    /**
     * Process record
     * 
     * @param array $record   log record
     * @return array          processed log record
     */
    public function process(array $record): array
    {
        foreach ($this->processors as $processor) {
            $record = call_user_func($processor, $record);
        }

        return $record;
    }
}
