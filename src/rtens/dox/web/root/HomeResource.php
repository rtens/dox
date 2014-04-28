<?php
namespace rtens\dox\web\root;

use rtens\dox\Configuration;
use rtens\dox\web\Presenter;
use watoki\curir\resource\Container;

class HomeResource extends Container {

    /** @var Configuration <- */
    public $config;

    public function doGet() {
        return new Presenter($this, $this->assembleModel());
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
                'name' => $project->getName(),
                'href' => $this->getParent()->getUrl('projects/' . $project->getName())->toString(),
                'description' => $project->getDescription()
            );
        }
        return $projects;
    }

}