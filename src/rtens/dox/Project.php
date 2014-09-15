<?php
namespace rtens\dox;

use rtens\dox\model\Specification;

class Project {

    private $name;

    private $repositoryUrl;

    private $specFolder = 'spec';

    private $config;

    private $projectFolder;

    private $description;

    private $hoster;

    function __construct(Configuration $config, $name) {
        $this->config = $config;
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setRepositoryUrl($repositoryUrl) {
        $this->repositoryUrl = $repositoryUrl;
        return $this;
    }

    public function getRepositoryUrl() {
        return $this->repositoryUrl;
    }

    public function getClassSuffix() {
        return 'Test';
    }

    public function getScenarioPrefix() {
        return 'test';
    }

    public function setSpecFolder($relativeToProjectRoot) {
        $this->specFolder = $relativeToProjectRoot;
        return $this;
    }

    public function getSpecFolder() {
        return $this->specFolder;
    }

    public function getFullProjectFolder() {
        return $this->projectFolder ? : $this->config->inProjectsFolder($this->name);
    }

    public function setFullProjectFolder($absolutePath) {
        $this->projectFolder = $absolutePath;
        return $this;
    }

    public function getFullSpecFolder() {
        return $this->getFullProjectFolder() . DIRECTORY_SEPARATOR . $this->specFolder;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getDescription() {
        $composerFile = $this->getFullProjectFolder() . DIRECTORY_SEPARATOR . 'composer.json';
        if (file_exists($composerFile)) {
            $meta = json_decode(file_get_contents($composerFile), true);
            if (isset($meta['description'])) {
                return $meta['description'];
            }
        }
        return $this->description;
    }

    public function getReadmeText() {
        foreach (glob($this->getFullProjectFolder() . '/*') as $file) {
            if (in_array(strtolower(basename($file)), array('readme.md', 'readme.markdown'))) {
                return file_get_contents($file);
            }
        }
        return null;
    }

    public function setHoster($hosterUrl) {
        $this->hoster = $hosterUrl;
        return $this;
    }

    public function getHosterProjectUrl() {
        return $this->hoster;
    }

    public function getHosterSpecificationUrl(Specification $specification) {
        if (!$this->hoster) {
            return null;
        }
        return $this->hoster . '/blob/master/' . $this->specFolder . '/'
        . $specification->path . $this->getClassSuffix() . '.php';
    }

}