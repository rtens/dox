<?php
namespace rtens\dox;

class Configuration {

    /** @var array|ProjectConfiguration[] */
    private $projects = array();

    public function addProject(ProjectConfiguration $project) {
        $this->projects[$project->getName()] = $project;
    }

    public function getProject($project) {
        return $this->projects[$project];
    }
}