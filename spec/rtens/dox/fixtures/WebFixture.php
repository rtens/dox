<?php
namespace spec\rtens\dox\fixtures;

use rtens\dox\Configuration;
use rtens\dox\Logger;
use rtens\dox\web\RootResource;
use rtens\mockster\Mock;
use rtens\mockster\MockFactory;
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

    private $body = null;

    /** @var Mock */
    private $logger;

    /** @var null|\Exception */
    private $caught;

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

    public function givenTheProject_IsIn($project, $folder) {
        $this->config()->getProject($project)->setFullProjectFolder($this->file->tmpDir() . '/' . $folder);
    }

    public function whenIRequestTheResourceAt($path) {
        $this->whenISendA_RequestTo(Request::METHOD_GET, $path);
    }

    public function givenTheRequestedFormatIs($format) {
        $this->format = $format;
    }

    public function whenISendA_RequestTo($method, $path) {
        $mf = new MockFactory();
        $this->logger = $mf->getMock(Logger::$CLASS);
        $this->spec->factory->setSingleton(Logger::$CLASS, $this->logger);

        /** @var RootResource $root */
        $root = $this->spec->factory->getInstance(RootResource::$CLASS, array(Url::parse('http://dox')));
        $this->response = $root->respond(new Request(Path::parse($path), array($this->format), $method,
            null, null, $this->body));
    }

    public function whenITryToSendA_RequestTo($method, $path) {
        try {
            $this->whenISendA_RequestTo($method, $path);
        } catch (\Exception $e) {
            $this->caught = $e;
        }
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
            $this->trimLines(trim(json_encode($model, JSON_PRETTY_PRINT), $charlist)),
            $this->trimLines(trim($this->response->getBody(), $charlist)));
    }

    public function thenTheResponseShouldContainTheText($string) {
        $this->spec->assertContains($this->trimLines($string), $this->trimLines($this->response->getBody()));
    }

    public function then_ShouldBe($fieldPath, $value) {
        $this->spec->assertEquals($this->trimLines($value), $this->trimLines($this->resolve($fieldPath)));
    }

    public function then_ShouldHaveTheSize($fieldPath, $count) {
        $this->spec->assertCount($count, $this->resolve($fieldPath));
    }

    private function resolve($fieldPath) {
        $model = json_decode($this->response->getBody(), true);
        foreach (explode('/', $fieldPath) as $field) {
            if (!array_key_exists($field, $model)) {
                $this->spec->fail("Could not find [$field] in " . json_encode($model));
            }
            $model = $model[$field];
        }
        return $model;
    }

    private function trimLines($string) {
        if (!is_string($string)) {
            return $string;
        }

        $string = implode("\n", array_map(function ($line) {
            return trim($line);
        }, explode("\n", $string)));
        return trim($string);
    }

    public function thenIShouldBeRedirectedTo($path) {
        $this->spec->assertEquals($path, $this->response->getHeaders()->get(Response::HEADER_LOCATION));
    }

    public function thenAnExceptionShouldBeThrownContaining($string) {
        $this->spec->assertNotNull($this->caught);
        $this->spec->assertContains($string, $this->caught->getMessage());
    }

    public function givenTheRequestHasTheBody($string) {
        $this->body = $string;
    }

    public function then_ShouldBeLogged($string) {
        $history = $this->logger->__mock()->method('log')->getHistory();
        $this->spec->assertTrue($history->wasCalledWith(array($string)),
            $history->toString());
    }

}