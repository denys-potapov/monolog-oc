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

use Monolog\Formatter\FormatterInterface;
use Monolog\Collection\HandlerStack;

/**
 * Forwards records to multiple handlers
 *
 * @author Lenar Lõhmus <lenar@city.ee>
 */
class GroupHandler extends Handler implements ProcessableHandlerInterface
{
    use ProcessableHandlerTrait;

    protected $handlers;

    /**
     * @param array   $handlers Array of Handlers.
     * @param Boolean $bubble   Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(array $handlers, $bubble = true)
    {
        $this->handlers = new HandlerStack($handlers);
        $this->bubble = $bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record): bool
    {
        return $this->handlers->isHandling($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
    {
        if ($this->processors) {
            $record = $this->processRecord($record);
        }

        $this->handlers->handle($record);

        return false === $this->bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $this->handlers->handleBatch($records);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        $this->handlers->setFormatter($formatter);

        return $this;
    }
}
