<?php
namespace app\library;

use app\library\helpers\Utils;

/**
 * Logger records logged messages in memory and sends them to DB
 *
 * When the application ends or [[flushInterval]] is reached, Logger will call [[flush()]]
 * to send logged messages to DB
 *
 * @author chen ming
 * @since 1.0
 */
class Logger extends Object
{
    /**
     * Error message level. An error message is one that indicates the abnormal termination of the
     * application and may require developer's handling.
     */
    const LEVEL_ERROR = 0x01;
    /**
     * Warning message level. A warning message is one that indicates some abnormal happens but
     * the application is able to continue to run. Developers should pay attention to this message.
     */
    const LEVEL_WARNING = 0x02;
    /**
     * Informational message level. An informational message is one that includes certain information
     * for developers to review.
     */
    const LEVEL_INFO = 0x04;

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * After the DbTarget object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'db';
    /**
     * @var string name of the DB table to store cache content. Defaults to "log".
     */
    public $logTable = 'log';
    
    /**
     * @var array logged messages. This property is managed by [[log()]] and [[flush()]].
     * Each log message is of the following structure:
     *
     * ```
     * [
     *   [0] => message (mixed, can be a string or some complex data, such as an exception object)
     *   [1] => level (integer)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true))
     *   [4] => traces (array, debug backtrace, contains the application code call stacks)
     * ]
     * ```
     */
    public $messages = [];
    
    /**
     * @var callable a PHP callable that returns a string to be prefixed to every exported message.
     *
     * If not set, [[getMessagePrefix()]] will be used, which prefixes the message with context information
     * such as user IP, user ID and session ID.
     *
     * The signature of the callable should be `function ($message)`.
     */
    public $prefix;
    
    public $flushInterval = 1000;
    

    /**
     * Initializes the logger by registering [[flush()]] as a shutdown function.
     */
    public function init()
    {
        parent::init();
        register_shutdown_function(function () {
            $this->flush();
            register_shutdown_function([$this, 'flush'], true);
        });
    }

    /**
     * Logs a message with the given type and category.
     * @param string $message the message to be logged.
     * @param integer $level the level of the message. This must be one of the following:
     * `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`,
     * `Logger::LEVEL_PROFILE_BEGIN`, `Logger::LEVEL_PROFILE_END`.
     * @param string $category the category of the message.
     */
    public function log($message, $level, $category = 'application')
    {
        $time = microtime(true);
        $this->messages[] = [$message, $level, $category, $time];
        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
        
        return true;
    }

    /**
     * Flushes log messages from memory to DB.
     * @param boolean $final whether this is a final call during a request.
     */
    public function flush($final = false)
    {
        $messages = $this->messages;
        $this->messages = [];
        
        $data = [];
        foreach ($messages as $item) {
            $data[] = [
                'level' => $item[1],
                'category' => $item[2],
                'log_time' => $item[3],
                'prefix' => $this->getMessagePrefix($item[0]),
                'message' => is_object($item[0]) ? $item[0].'' : $item[0]
            ];
        }
        
        $data && $this->db->insert($this->logTable, $data);
        
        return true;
    }

    /**
     * Returns the text display of the specified level.
     * @param integer $level the message level, e.g. [[LEVEL_ERROR]], [[LEVEL_WARNING]].
     * @return string the text display of the level
     */
    public static function getLevelName($level)
    {
        static $levels = [
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_INFO => 'info',
        ];

        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }
    
    /**
     * Returns a string to be prefixed to the given message.
     * If [[prefix]] is configured it will return the result of the callback.
     * The default implementation will return user IP, user ID and session ID as a prefix.
     * @param array $message the message being exported.
     * The message structure follows that in [[Logger::messages]].
     * @return string the prefix string
     */
    public function getMessagePrefix($message)
    {
        if ($this->prefix !== null) {
            return call_user_func($this->prefix, $message);
        }
    
        $ip = Utils::getUserIP() ?? '-';
    
        $user = \Yaf\Application::app()->user ?? null;
        if ($user) {
            $userID = $user->getId();
        } else {
            $userID = '-';
        }

        $sessionID = '-';
    
        return "[$ip][$userID][$sessionID]";
    }
}
