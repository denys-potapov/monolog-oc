<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Collection\ProcessorStack;

/**
 * General Handler class replacer for AbstractHandler, ProcessableHandlerTrait 
 * and FormattableHandlerTrait
 */
abstract class GeneralHandler extends Handler
{
    /*
     * 
     * @var ProcessorStack
     */
    protected $processors;

    protected $bubble = true;

    /**
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($bubble = true)
    {
        $this->bubble = $bubble;
        $this->processors = new ProcessorStack();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function isHandling(array $record): bool;

    abstract public function postProcess(array $record);
    /**
     * Sets the bubbling behavior.
     *
     * @param  Boolean $bubble true means that this handler allows bubbling.
     *                         false means that bubbling is not permitted.
     * @return self
     */
    public function setBubble(bool $bubble): self
    {
        $this->bubble = $bubble;

        return $this;
    }

    /**
     * Gets the bubbling behavior.
     *
     * @return Boolean true means that this handler allows bubbling.
     *                 false means that bubbling is not permitted.
     */
    public function getBubble(): bool
    {
        return $this->bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function pushProcessor(callable $callback): HandlerInterface
    {
        $this->processors->push($callback);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function popProcessor(): callable
    {
        return $this->processors->pop();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processors->process($record);

        $this->postProcess($record);

        return false === $this->bubble;
    }
}
