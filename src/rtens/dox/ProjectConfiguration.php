<?php
namespace rtens\dox;

class ProjectConfiguration {

    private $name;

    private $folder;

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
}