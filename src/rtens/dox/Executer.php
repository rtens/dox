<?php
namespace rtens\dox;

class Executer {

    public static $CLASS = __CLASS__;

    public function execute($command) {
        $return = 0;
        $output = array();
        exec($command . " 2>&1", $output, $return);
        $this->log($command, $output, $return);
        return $return;
    }

    private function log($command, $output, $return) {
        $logDir = ROOT . '/log';
        if (!file_exists($logDir)) {
            mkdir($logDir);
        }

        $output = $output ? "\n" . implode("\n", $output) : "";

            $data = date('Y-m-d H:i:s') . "\n$ $command [$return] $output\n\n";
        file_put_contents($logDir . '/' . date('Y-m-d') . '.txt', $data, FILE_APPEND);
    }

} 