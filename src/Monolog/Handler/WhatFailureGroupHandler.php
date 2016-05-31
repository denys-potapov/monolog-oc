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

use Monolog\Collection\WhatFailureHandlerStack;

/**
 * Forwards records to multiple handlers suppressing failures of each handler
 * and continuing through to give every handler a chance to succeed.
 *
 * @author Craig D'Amelio <craig@damelio.ca>
 */
class WhatFailureGroupHandler extends GroupHandler
{
    /**
     * @param array   $handlers Array of Handlers.
     * @param Boolean $bubble   Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(array $handlers, $bubble = true)
    {
        $this->handlers = new WhatFailureHandlerStack($handlers);
        $this->bubble = $bubble;
    }
}
