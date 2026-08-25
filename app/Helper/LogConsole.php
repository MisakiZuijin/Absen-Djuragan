<?php

namespace App\Helper;

use Throwable;

/**
 * Class LogConsole
 *
 * A helper class for logging various types of data to the PHP error log.
 * It adjusts to the type of the first argument provided and applies it
 * to all subsequent arguments, formatting them uniformly before logging.
 *
 * @package App\Helper
 */
class LogConsole {
    /**
     * Logs information about the provided data to the PHP error log.
     * All data are formatted according to the type of the first argument.
     *
     * @param mixed ...$args The data to be logged. The first argument determines
     *                       the type and formatting for all following arguments.
     *
     * @return void
     */
    public static function info(...$args): void {
        $messages = [];

        if (!empty($args)) {
            $firstDataType = gettype($args[0]);
            $isThrowable = $args[0] instanceof Throwable;

            foreach ($args as $data) {
                if ($isThrowable && $data instanceof Throwable) {
                    $messages[] = '[Exception] ' . $data->getMessage() . " | " . $data->getFile() . " | " . $data->getLine();
                } elseif ($firstDataType == 'array') {
                    $messages[] = '[Array] ' . json_encode($data);
                } elseif ($firstDataType == 'object') {
                    $messages[] = '[Object] ' . json_encode($data);
                } elseif ($firstDataType == 'string' || $firstDataType == 'integer' || $firstDataType == 'double') {
                    $messages[] = '[Value] ' . $data;
                }
            }
        }

        $combinedMessage = implode(" ", $messages);
        error_log($combinedMessage);
    }

    /**
     * Retrieves a formatted status message based on the provided data.
     * All statuses are formatted according to the type of the first argument.
     *
     * @param mixed ...$args The data for which the status message is to be generated.
     *                       The first argument determines the type and formatting
     *                       for all following arguments.
     *
     * @return string A concatenated string representing the status of the provided data.
     */
    public static function status(...$args): string {
        $statuses = [];

        if (!empty($args)) {
            $firstDataType = gettype($args[0]);
            $isThrowable = $args[0] instanceof Throwable;

            foreach ($args as $data) {
                if ($isThrowable && $data instanceof Throwable) {
                    $statuses[] = '[Exception] ' . $data->getMessage() . " | " . $data->getFile() . " | " . $data->getLine();
                } elseif ($firstDataType == 'array') {
                    $statuses[] = '[Array] ' . json_encode($data);
                } elseif ($firstDataType == 'object') {
                    $statuses[] = '[Object] ' . json_encode($data);
                } elseif ($firstDataType == 'string' || $firstDataType == 'integer' || $firstDataType == 'double') {
                    $statuses[] = '[Value] ' . $data;
                }
            }
        }

        return implode(" ", $statuses);
    }
}
