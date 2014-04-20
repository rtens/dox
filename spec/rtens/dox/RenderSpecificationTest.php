<?php
namespace spec\rtens\dox;

use rtens\dox\Configuration;
use rtens\dox\ProjectConfiguration;
use rtens\dox\web\RootResource;
use watoki\curir\http\Path;
use watoki\curir\http\Request;
use watoki\curir\http\Response;
use watoki\curir\http\Url;
use watoki\curir\Resource;
use watoki\factory\Factory;
use watoki\scrut\Specification;

/**
 * @property FileFixture file <-
 * @property Configuration config
 * @property Response response
 * @property RootResource root
 */
class RenderSpecificationTest extends Specification {

    public function testEmptySpecification() {
        $this->givenTheProject_WithTheSpecificationFolder('MyProject', 'mySpec');
        $this->file->givenTheFile_WithContent('mySpec/SomeSpecificationTest.php',
            '<?php

            class SomeSpecificationTest {}'
        );
        $this->whenIRequestTheResourceAt('MyProject/SomeSpecification');
        $this->thenTheResponseShouldContain(
            '"specification": {
                "name": "Some specification",
                "description": null,
                "scenarios": [

                ]
            }'
        );
    }

    protected function setUp() {
        parent::setUp();
        $this->config = new Configuration();
        $this->root = new RootResource(Url::parse('http://dox'));
        $this->root->factory = new Factory();
        $this->root->factory->setSingleton(get_class($this->config), $this->config);
    }

    private function givenTheProject_WithTheSpecificationFolder($project, $folder) {
        $this->config->addProject(new ProjectConfiguration($project, $this->file->tmpDir() . '/'. $folder));
    }

    private function whenIRequestTheResourceAt($path) {
        $this->response = $this->root->respond(new Request(Path::parse($path), array('json')));
    }

    private function thenTheResponseShouldContain($json) {
        $this->assertContains($this->trimLines($json), $this->trimLines($this->response->getBody()));
    }

    private function trimLines($string) {
        $string = implode("\n", array_map(function ($line) {
            return trim($line);
        }, explode("\n", $string)));
        return $string;
    }

} 