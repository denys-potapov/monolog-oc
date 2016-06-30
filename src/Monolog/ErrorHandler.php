<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel as PsrLevel;

/**
 * Monolog error handler
 *
 * A facility to enable logging of runtime errors, exceptions and fatal errors.
 *
 * Quick setup: <code>ErrorHandler::register($logger);</code>
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ErrorHandler
{
    private $logger;

    private $previousExceptionHandler;
    private $uncaughtExceptionLevelMap;

    private $previousErrorHandler;
    private $errorLevelMap;

    private $hasFatalErrorHandler;
    private $fatalLevel;
    private $reservedMemory;
    private static $fatalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Registers a new ErrorHandler for a given Logger
     *
     * By default it will handle errors, exceptions and fatal errors
     *
     * @param  LoggerInterface $logger
     * @param  array|false     $errorLevelMap     an array of E_* constant to PsrLevel::* constant mapping, or false to disable error handling
     * @param  array|false     $exceptionLevelMap an array of class name to PsrLevel::* constant mapping, or false to disable exception handling
     * @param  int|false       $fatalLevel        a PsrLevel::* constant, or false to disable fatal error handling
     * @return ErrorHandler
     */
    public static function register(LoggerInterface $logger, $errorLevelMap = array(), $exceptionLevelMap = array(), $fatalLevel = null)
    {
        $handler = new static($logger);
        if ($errorLevelMap !== false) {
            $handler->registerErrorHandler($errorLevelMap);
        }
        if ($exceptionLevelMap !== false) {
            $handler->registerExceptionHandler($exceptionLevelMap);
        }
        if ($fatalLevel !== false) {
            $handler->registerFatalHandler($fatalLevel);
        }

        return $handler;
    }

    public function registerExceptionHandler($levelMap = array(), $callPrevious = true)
    {
        $prev = set_exception_handler(array($this, 'handleException'));
        $this->uncaughtExceptionLevelMap = array_replace($this->defaultExceptionLevelMap(), $levelMap);
        if ($callPrevious && $prev) {
            $this->previousExceptionHandler = $prev;
        }
    }

    public function registerErrorHandler(array $levelMap = array(), $callPrevious = true, $errorTypes = -1)
    {
        $prev = set_error_handler(array($this, 'handleError'), $errorTypes);
        $this->errorLevelMap = array_replace($this->defaultErrorLevelMap(), $levelMap);
        if ($callPrevious) {
            $this->previousErrorHandler = $prev ?: true;
        }
    }

    public function registerFatalHandler($level = null, $reservedMemorySize = 20)
    {
        register_shutdown_function(array($this, 'handleFatalError'));

        $this->reservedMemory = str_repeat(' ', 1024 * $reservedMemorySize);
        $this->fatalLevel = $level;
        $this->hasFatalErrorHandler = true;
    }

    protected function defaultExceptionLevelMap()
    {
        return array(
            'ParseError' => PsrLevel::CRITICAL,
            'Throwable' => PsrLevel::ERROR,
        );
    }

    protected function defaultErrorLevelMap()
    {
        return array(
            E_ERROR             => PsrLevel::CRITICAL,
            E_WARNING           => PsrLevel::WARNING,
            E_PARSE             => PsrLevel::ALERT,
            E_NOTICE            => PsrLevel::NOTICE,
            E_CORE_ERROR        => PsrLevel::CRITICAL,
            E_CORE_WARNING      => PsrLevel::WARNING,
            E_COMPILE_ERROR     => PsrLevel::ALERT,
            E_COMPILE_WARNING   => PsrLevel::WARNING,
            E_USER_ERROR        => PsrLevel::ERROR,
            E_USER_WARNING      => PsrLevel::WARNING,
            E_USER_NOTICE       => PsrLevel::NOTICE,
            E_STRICT            => PsrLevel::NOTICE,
            E_RECOVERABLE_ERROR => PsrLevel::ERROR,
            E_DEPRECATED        => PsrLevel::NOTICE,
            E_USER_DEPRECATED   => PsrLevel::NOTICE,
        );
    }

    /**
     * @private
     */
    public function handleException($e)
    {
        $level = PsrLevel::ERROR;
        foreach ($this->uncaughtExceptionLevelMap as $class => $candidate) {
            if ($e instanceof $class) {
                $level = $candidate;
                break;
            }
        }

        $this->logger->log(
            $level,
            sprintf('Uncaught Exception %s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
            array('exception' => $e)
        );

        if ($this->previousExceptionHandler) {
            call_user_func($this->previousExceptionHandler, $e);
        }

        exit(255);
    }

    /**
     * @private
     */
    public function handleError($code, $message, $file = '', $line = 0, $context = array())
    {
        if (!(error_reporting() & $code)) {
            return;
        }

        // fatal error codes are ignored if a fatal error handler is present as well to avoid duplicate log entries
        if (!$this->hasFatalErrorHandler || !in_array($code, self::$fatalErrors, true)) {
            $level = isset($this->errorLevelMap[$code]) ? $this->errorLevelMap[$code] : PsrLevel::CRITICAL;
            $this->logger->log($level, self::codeToString($code).': '.$message, array('code' => $code, 'message' => $message, 'file' => $file, 'line' => $line));
        }

        if ($this->previousErrorHandler === true) {
            return false;
        } elseif ($this->previousErrorHandler) {
            return call_user_func($this->previousErrorHandler, $code, $message, $file, $line, $context);
        }
    }

    /**
     * @private
     */
    public function handleFatalError()
    {
        $this->reservedMemory = null;

        $lastError = error_get_last();
        if ($lastError && in_array($lastError['type'], self::$fatalErrors, true)) {
            $this->logger->log(
                $this->fatalLevel === null ? PsrLevel::ALERT : $this->fatalLevel,
                'Fatal Error ('.self::codeToString($lastError['type']).'): '.$lastError['message'],
                array('code' => $lastError['type'], 'message' => $lastError['message'], 'file' => $lastError['file'], 'line' => $lastError['line'])
            );

            if ($this->logger instanceof Logger) {
                foreach ($this->logger->getHandlers() as $handler) {
                    $handler->close();
                }
            }
        }
    }

    private static function codeToString($code)
    {
        switch ($code) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
        }

        return 'Unknown PHP error';
    }
}
