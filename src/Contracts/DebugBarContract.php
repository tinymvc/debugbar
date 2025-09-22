<?php

namespace DebugBar\Contracts;

/**
 * Interface DebugBarContract
 *
 * This interface defines the contract for a DebugBar implementation.
 * It includes methods for registering the debug bar and logging messages
 * with various levels and contexts.
 *  
 * @package DebugBar\Contracts
 * @author Shahin Moyshan <shahin.moyshan2@gmail.com>
 */
interface DebugBarContract
{
    /**
     * Registers the DebugBar instance.
     *
     * @return self
     */
    public static function register(): self;

    /**
     * Logs a message with a given context, level, and optional group.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     * @param string $level The log level (e.g., 'info', 'error').
     * @param string|null $group Optional group name for categorizing logs.
     * 
     * @return void
     */
    public function log(string $message, array $context = [], string $level = 'info', ?string $group = null): void;
}