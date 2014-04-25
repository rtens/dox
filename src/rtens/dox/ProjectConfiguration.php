<?php
namespace rtens\dox;

class ProjectConfiguration {

    private $name;

    private $repositoryUrl;

    private $specFolder = 'spec';

    private $config;

    function __construct(Configuration $config, $name) {
        $this->config = $config;
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setRepositoryUrl($repositoryUrl) {
        $this->repositoryUrl = $repositoryUrl;
    }

    public function getRepositoryUrl() {
        return $this->repositoryUrl;
    }

    public function getClassSuffix() {
        return 'Test';
    }

    public function setSpecFolder($relativeToProjectRoot) {
        $this->specFolder = $relativeToProjectRoot;
    }

    public function getFullProjectFolder() {
        return $this->config->inProjectsFolder($this->name);
    }

    public function getFullSpecFolder() {
        $fullProjectFolder = $this->getFullProjectFolder();
        return $fullProjectFolder . DIRECTORY_SEPARATOR . $this->specFolder;
    }
}