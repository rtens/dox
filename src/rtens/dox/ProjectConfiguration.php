<?php
namespace rtens\dox;

class ProjectConfiguration {

    private $name;

    private $folder;

    private $startSpecificationPath;

    function __construct($name, $folder) {
        $this->name = $name;
        $this->folder = $folder;
    }

    public function getName() {
        return $this->name;
    }

    public function getFolder() {
        return $this->folder;
    }

    public function setStartSpecification($path) {
        $this->startSpecificationPath = $path;
        return $this;
    }

    public function getStartSpecification() {
        return $this->startSpecificationPath;
    }

    public function getClassSuffix() {
        return 'Test';
    }
}