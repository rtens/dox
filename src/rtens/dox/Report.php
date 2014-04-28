<?php
namespace rtens\dox;

class Report {

    public static $CLASS = __CLASS__;

    const TAP_VERSION = 'TAP version 13';

    const STATUS_PASSING = 'passing';
    const STATUS_FAILING = 'failing';
    const STATUS_PENDING = 'pending';
    const STATUS_UNKNOWN = 'unknown';

    /** @var Configuration <- */
    public $config;

    /** @var Logger <- */
    public $logger;

    private $reportCache;

    public function getStatus($project, $specification, $scenario) {
        $reportFile = $this->reportFile($project);
        if (!file_exists($reportFile)) {
            return self::STATUS_UNKNOWN;
        }

        if ($this->reportCache === null) {
            $this->reportCache = json_decode(file_get_contents($reportFile), true);
        }
        $key = $specification . '::' . $scenario;
        if (!array_key_exists($key, $this->reportCache)) {
            return self::STATUS_UNKNOWN;
        }
        return $this->reportCache[$key];
    }

    public function save($report, $projectName) {
        if (substr($report, 0, strlen(self::TAP_VERSION)) == self::TAP_VERSION) {
            $this->saveTap($report, $projectName);
        } else {
            $this->logger->log('Failed saving status in [' .  $projectName. ']');
            throw new \Exception('Format not recognized');
        }
    }

    private function saveTap($report, $projectName) {
        $project = $this->config->getProject($projectName);

        $lines = explode("\n", trim($report));

        $out = array();
        foreach ($lines as $line) {
            $matches = array();
            if (!preg_match('/^(.+) \d+ - (.* )?(\S+)::(\S+)( .+)?$/', $line, $matches)) {
                continue;
            }

            if ($matches[1] == 'ok') {
                $status = self::STATUS_PASSING;
            } else if (isset($matches[5]) && strpos(strtolower($matches[5]), 'incomplete test')) {
                $status = self::STATUS_PENDING;
            } else {
                $status = self::STATUS_FAILING;
            }

            $specification = str_replace('\\', '/', $matches[3]);
            $scenario = $matches[4];

            $specification = substr($specification, strlen($project->getSpecFolder()) + 1);

            $suffixLength = strlen($project->getClassSuffix());
            if (substr($specification, -$suffixLength) == $project->getClassSuffix()) {
                $specification = substr($specification, 0, -$suffixLength);
            }

            $prefixLength = strlen($project->getScenarioPrefix());
            if (substr($scenario, 0, $prefixLength) == $project->getScenarioPrefix()) {
                $scenario = substr($scenario, $prefixLength);
            }

            $out[$specification . '::' . $scenario] = $status;
        }

        file_put_contents($this->reportFile($projectName), json_encode($out, JSON_PRETTY_PRINT));

        $this->logger->log('Saved status of [' . count($out) . '] scenarios in [' . $projectName . ']');
    }

    private function reportFile($project) {
        $fileName = $this->config->inUserFolder('reports/' . $project . '.json');
        $dir = dirname($fileName);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        return $fileName;
    }
}