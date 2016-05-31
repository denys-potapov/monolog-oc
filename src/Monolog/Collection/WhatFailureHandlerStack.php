<?php

declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Collection;

use Monolog\Handler\HandlerInterface;

/**
 * The handler stack  suppressing failures of each handler
 * and continuing through to give every handler a chance to succeed.
 *
 * It contains a stack of Handlers.
 *
 */
class WhatFailureHandlerStack extends HandlerStack
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $record, $reset = true): bool
    {
        foreach ($this->handlers as $handler) {
            try {
                $handler->handle($record);
            } catch (\Throwable $e) {
                // What failure?
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        foreach ($this->handlers as $handler) {
            try {
                $handler->handleBatch($records);
            } catch (\Throwable $e) {
                // What failure?
            }
        }
    }
}
