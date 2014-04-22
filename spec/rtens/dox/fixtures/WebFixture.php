<?php
namespace spec\rtens\dox\fixtures;

use rtens\dox\Configuration;
use rtens\dox\ProjectConfiguration;
use rtens\dox\web\RootResource;
use watoki\curir\http\Path;
use watoki\curir\http\Request;
use watoki\curir\http\Response;
use watoki\curir\http\Url;
use watoki\scrut\Fixture;

/**
 * @property FileFixture file <-
 *
 * @property Configuration config
 * @property Response response
 */
class WebFixture extends Fixture {

    private $format = 'json';

    protected function setUp() {
        parent::setUp();
        $this->config = new Configuration();
        $this->spec->factory->setSingleton(get_class($this->config), $this->config);
    }

    public function givenTheProject($project) {
        $this->config->addProject(new ProjectConfiguration($project, $this->file->tmpDir() . '/spec'));
    }

    public function givenTheStartSpecificationOf_Is($project, $specification) {
        $this->config->getProject($project)->setStartSpecification($specification);
    }

    public function givenTheProject_WithTheSpecificationFolder($project, $folder) {
        $this->config->addProject(new ProjectConfiguration($project, $this->file->tmpDir() . '/' . $folder));
    }

    public function whenIRequestTheResourceAt($path) {
        $this->whenISendA_RequestTo(Request::METHOD_GET, $path);
    }

    public function givenTheRequestedFormatIs($format) {
        $this->format = $format;
    }

    public function whenISendA_RequestTo($method, $path) {
        /** @var RootResource $root */
        $root = $this->spec->factory->getInstance(RootResource::$CLASS, array(Url::parse('http://dox')));
        $this->response = $root->respond(new Request(Path::parse($path), array($this->format), $method));
    }

    public function thenTheResponseShouldBe($string) {
        $this->spec->assertEquals($string, $this->response->getBody());
    }

    public function thenTheResponseShouldContain($json) {
        $model = json_decode('{' . $json . '}');
        if (!$model) {
            throw new \Exception('Invalid JSON: ' . $json);
        }
        $charlist = "{}, \n";
        $this->spec->assertContains(
            trim(json_encode($model, JSON_PRETTY_PRINT), $charlist),
            trim($this->response->getBody(), $charlist));
    }

    public function thenTheResponseShouldContainTheText($string) {
        $this->spec->assertContains($this->trimLines($string), $this->trimLines($this->response->getBody()));
    }

    private function trimLines($string) {
        $string = implode("\n", array_map(function ($line) {
            return trim($line);
        }, explode("\n", $string)));
        return $string;
    }

    public function thenIShouldBeRedirectedTo($path) {
        $this->spec->assertEquals($path, $this->response->getHeaders()->get(Response::HEADER_LOCATION));
    }

} 