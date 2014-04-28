<?php
namespace rtens\dox;

class Project {

    private $name;

    private $repositoryUrl;

    private $specFolder = 'spec';

    private $config;

    private $projectFolder;

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
}