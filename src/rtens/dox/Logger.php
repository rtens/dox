<?php
namespace rtens\dox;

class Logger {

    public static $CLASS = __CLASS__;

    private $logDir;

    public function __construct(Configuration $config) {
        $this->logDir = $config->inUserFolder('log');
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir);
        }
    }

    public function log($message) {
        $data = date('Y-m-d H:i:s') . "\n$message\n\n";
        file_put_contents($this->logDir . '/' . date('Y-m-d') . '.txt', $data, FILE_APPEND);
    }

} 