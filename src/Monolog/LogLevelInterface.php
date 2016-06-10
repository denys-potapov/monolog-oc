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

namespace Monolog;


/**
 * Interface describing comparable Log level
 *
 *
 */
interface LogLevelInterface
{

    /**
     * Returns if level includes other level
     * 
     * @param mixed Level number (monolog) or name (PSR-3)
     */
    public function includes($level): bool;
}
