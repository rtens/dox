<?php
namespace rtens\dox\web\root\xxProject;

use rtens\dox\Configuration;
use rtens\dox\Reader;
use rtens\dox\Specification;
use watoki\curir\http\Path;
use watoki\curir\http\Request;
use watoki\curir\resource\DynamicResource;
use watoki\curir\Responder;

class xxSpecificationResource extends DynamicResource {

    /** @var Configuration <- */
    public $config;

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

        return json_encode($this->assembleSpecification($specification), JSON_PRETTY_PRINT);
    }

    private function assembleSpecification(Specification $specification) {
        return array(
            'specification' => array(
                'name' => $specification->getName(),
                'description' => $specification->getDescription(),
                'scenarios' => $this->assembleScenarios($specification)
            )
        );
    }

    private function assembleScenarios(Specification $specification) {
        $scenarios = array();
        foreach ($specification->getScenarios() as $scenario) {
            $scenarios[] = array(
                'name' => $scenario->getName(),
                'description' => $scenario->getDescription(),
                'content' => $scenario->getContent()
            );
        }
        return $scenarios;
    }

}