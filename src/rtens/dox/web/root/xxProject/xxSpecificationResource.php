<?php
namespace rtens\dox\web\root\xxProject;

use rtens\dox\Configuration;
use rtens\dox\Reader;
use rtens\dox\Specification;
use rtens\dox\web\Presenter;
use rtens\dox\web\root\xxProjectResource;
use watoki\curir\http\Path;
use watoki\curir\http\Request;
use watoki\curir\resource\DynamicResource;
use watoki\curir\Responder;
use watoki\curir\responder\DefaultResponder;

class xxSpecificationResource extends DynamicResource {

    /** @var Configuration <- */
    public $config;

    /** @var \Parsedown <- */
    public $markdown;

    /** @var Path */
    private $path;

    public function respond(Request $request) {
        $this->path = $request->getTarget()->copy();
        $this->path->insert($this->getUrl()->getPath()->last(), 0);

        return parent::respond($request);
    }


    public function doGet() {
        $project = $this->getUrl()->getPath()->get(-2);
        $reader = new Reader($this->config->getProject($project));
        $specification = $reader->readSpecification($this->path);

        return new Presenter($this, $this->assembleModel($specification, $project));
    }

    private function assembleModel(Specification $specification, $project) {
        return array(
            'specification' => $this->assembleSpecification($specification),
            'navigation' => $this->getParent()->assembleNavigation($this->config->getProject($project))
        );
    }

    /**
     * @return xxProjectResource
     */
    public function getParent() {
        return parent::getParent();
    }

    private function assembleSpecification(Specification $specification) {
        return array(
            'name' => $specification->getName(),
            'description' => $this->asHtml($specification->getDescription()),
            'scenario' => $this->assembleScenarios($specification)
        );
    }

    private function assembleScenarios(Specification $specification) {
        $scenarios = array();
        foreach ($specification->getScenarios() as $scenario) {
            $scenarios[] = array(
                'name' => $scenario->getName(),
                'description' => $this->asHtml($scenario->getDescription()),
                'content' => $this->asHtml($scenario->getContent())
            );
        }
        return $scenarios;
    }

    private function asHtml($markdown) {
        if (!$markdown) {
            return null;
        }
        return $this->markdown->text($markdown);
    }

}