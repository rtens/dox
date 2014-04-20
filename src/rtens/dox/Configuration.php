<?php
namespace rtens\dox;

class Configuration {

    public static $CLASS = __CLASS__;

    /** @var array|ProjectConfiguration[] */
    private $projects = array();

    public function addProject(ProjectConfiguration $project) {
        $this->projects[$project->getName()] = $project;
    }

    public function getProject($project) {
        return $this->projects[$project];
    }

    public function getProjects() {
        return array_keys($this->projects);
    }
}