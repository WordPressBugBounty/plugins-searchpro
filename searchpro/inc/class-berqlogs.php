<?php
// class berqLogs {
//     private $logFile;

//     public function __construct() {
//         $this->logFile = optifer_cache . 'berqwp.log';
//     }

//     public function log($message, $status = 'INFO') {
//         $timestamp = date('Y-m-d H:i:s');
//         $logEntry = "[$timestamp][$status]: $message" . PHP_EOL;
//         file_put_contents($this->logFile, $logEntry, FILE_APPEND);
//     }

//     public function info($message) {
//         $this->log($message, 'INFO');
//     }

//     public function warning($message) {
//         $this->log($message, 'WARNING');
//     }

//     public function error($message) {
//         $this->log($message, 'ERROR');
//     }
// }

// global $berq_log;
// $berq_log = new berqLogs();

class berqLogs {
    private $logFile;
    private $logDir;
    private $maxSize = 1048576 * 20;

    public function __construct() {
        $this->logDir = rtrim(optifer_cache, '/') . '/logs/';
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        $this->logFile = $this->logDir . 'berqwp.log';
    }

    private function checkLogSize() {
        if (file_exists($this->logFile) && filesize($this->logFile) >= $this->maxSize) {
            $backup = $this->logDir . 'berqwp_' . date('Ymd_His') . '.log';
            rename($this->logFile, $backup);
        }
    }

    public function log($message, $status = 'INFO') {
        $this->checkLogSize();
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp][$status]: $message" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function info($message) {
        $this->log($message, 'INFO');
    }

    public function warning($message) {
        $this->log($message, 'WARNING');
    }

    public function error($message) {
        $this->log($message, 'ERROR');
    }
}

global $berq_log;
$berq_log = new berqLogs();
