<?php
namespace rtens\dox;

class Executer {

    public static $CLASS = __CLASS__;

    /** @var Logger <- */
    public $logger;

    public function execute($command) {
        $return = 0;
        $output = array();
        exec($command . " 2>&1", $output, $return);
        $this->log($command, $output, $return);
        return $return;
    }

    private function log($command, $output, $return) {

        $output = $output ? "\n" . implode("\n", $output) : "";
        $message = "$ $command [$return] $output";

        $this->logger->log($message);
    }

} 