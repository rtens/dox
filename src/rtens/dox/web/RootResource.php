<?php
namespace rtens\dox\web;

use rtens\dox\Configuration;
use watoki\curir\resource\Container;

class RootResource extends Container {

    public static $CLASS = __CLASS__;

    /** @var Configuration <- */
    public $config;

    public function doGet() {
        return json_encode($this->assembleModel(), JSON_PRETTY_PRINT);
    }

    private function assembleModel() {
        return array(
            'project' => $this->assembleProjectList()
        );
    }

    private function assembleProjectList() {
        $projects = array();
        foreach ($this->config->getProjects() as $project) {
            $projects[] = array(
                'name' => $project,
                'href' => $this->getUrl($project)->toString()
            );
        }
        return $projects;
    }

} 