<?php
namespace spec\rtens\dox\fixtures;

use rtens\dox\Configuration;
use rtens\dox\web\RootResource;
use watoki\curir\http\Path;
use watoki\curir\http\Request;
use watoki\curir\http\Response;
use watoki\curir\http\Url;
use watoki\scrut\Fixture;

/**
 * @property Response response
 * @property FileFixture file <-
 */
class WebFixture extends Fixture {

    private $format = 'json';

    protected function config() {
        try {
            return $this->spec->factory->getSingleton(Configuration::$CLASS);
        } catch (\Exception $e) {
            $config = new Configuration($this->file->tmpDir());
            $this->spec->factory->setSingleton(Configuration::$CLASS, $config);
            return $config;
        }
    }

    public function givenTheProject($project) {
        $this->config()->addProject($project);
    }

    public function givenTheProject_HasTheSpecFolder($project, $path) {
        $this->config()->getProject($project)->setSpecFolder($path);
    }

    public function givenTheProject_HasTheRepositoryUrl($project, $url) {
        $this->config()->getProject($project)->setRepositoryUrl($url);
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