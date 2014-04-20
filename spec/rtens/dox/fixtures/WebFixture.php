<?php
namespace spec\rtens\dox\fixtures;

use watoki\scrut\Fixture;
use rtens\dox\Configuration;
use rtens\dox\ProjectConfiguration;
use rtens\dox\web\RootResource;
use watoki\curir\http\Path;
use watoki\curir\http\Request;
use watoki\curir\http\Response;
use watoki\curir\http\Url;
use watoki\factory\Factory;

/**
 * @property FileFixture file <-
 *
 * @property Configuration config
 * @property Response response
 * @property RootResource root
 */
class WebFixture extends Fixture {

    protected function setUp() {
        parent::setUp();
        $this->config = new Configuration();
        $factory = new Factory();
        $factory->setSingleton(get_class($this->config), $this->config);

        $this->root = $factory->getInstance(RootResource::$CLASS, array(Url::parse('http://dox')));
    }

    public function givenTheProject($project) {
        $this->config->addProject(new ProjectConfiguration($project, $this->file->tmpDir() . '/spec'));
    }

    public function givenTheProject_WithTheSpecificationFolder($project, $folder) {
        $this->config->addProject(new ProjectConfiguration($project, $this->file->tmpDir() . '/' . $folder));
    }

    public function whenIRequestTheResourceAt($path) {
        $this->response = $this->root->respond(new Request(Path::parse($path), array('json')));
    }

    public function thenTheResponseShouldContain($model) {
        $this->spec->assertContains(json_encode($model, JSON_PRETTY_PRINT), $this->response->getBody());
    }

} 