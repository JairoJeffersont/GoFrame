<?php

namespace GoFrame\Core\Helpers;

/**
 * Logger Class
 * 
 * The `Logger` class is responsible for recording log messages in files.
 * The main goal is to provide an easy way to log relevant information in the system,
 * whether for debugging, auditing, or monitoring, with the ability to categorize logs by levels (INFO, ERROR, DEBUG, etc).
 * 
 * Logs are stored in specific files within a log directory, with the date in the file name,
 * and each log message is formatted with a timestamp and level.
 * 
 * @package GoFrame\Core\Helpers
 */
class Logger {

    /**
     * Logs a message to a specific file.
     *
     * This method writes a log message to a file inside the 'logs' directory. The file name is generated
     * from the current date and the provided base name. If the 'logs' directory does not exist, it will be created automatically.
     * 
     * The log message includes a timestamp and the specified log level (INFO, ERROR, DEBUG, etc.).
     * The default level is 'INFO'. The file name is also sanitized to ensure there are no invalid characters.
     * 
     * @param string $filename Base name of the log file (without extension). It will be sanitized to avoid invalid characters.
     *                         The file name will consist of the date and the provided name.
     * 
     * @param string $message Message to be logged. It can be any text or variable that needs to be recorded.
     * 
     * @param string $level Log level (INFO, ERROR, DEBUG, etc.). The level helps categorize messages. The default value is 'INFO'.
     *                      Examples of levels include:
     *                      - 'INFO': informational messages, usually routine operations.
     *                      - 'ERROR': error or failure messages.
     *                      - 'DEBUG': detailed information for debugging.
     * 
     * @return void This method does not return any value. The record is written directly to the log file.
     * 
     * @throws \Exception If there is any problem creating the log directory or writing to the log file, an exception may be thrown.
     */
    public static function newLog(string $filename, string $message, string $level = 'INFO'): void {
        // Path to the directory where logs will be stored
        $logDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'logs';

        // Check if the log directory exists. If not, create the directory with appropriate permissions
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Sanitize the file name to ensure it only contains valid characters
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);

        // Generate the full log file name, with the current date and the sanitized file name
        $logFile = sprintf('%s/%s_%s.log', $logDir, date('Y-m-d'), $safeFilename);

        // Format the log message, including the timestamp and log level
        $formattedMessage = sprintf("[%s] [%s] %s%s", date('Y-m-d H:i:s'), strtoupper($level), $message, PHP_EOL);

        // Write the formatted message to the log file, appending it without overwriting
        file_put_contents($logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
    }
}