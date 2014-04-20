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

    public function testSpecificationWithDescriptionAndScenarios() {
        $this->givenTheProject_WithTheSpecificationFolder('YourProject', 'yourSpec');
        $this->file->givenTheFile_WithContent('yourSpec/some/SpecificationTest.php',
            '<?php

            /**
             * This is some *description*
             *
             * @property ignore this
             */
            class SpecificationTest {
                /**
                 * Description of *scenario*
                 */
                public function testSomeThings() {
                    // Just *some* **comment**
                    $andCode = 1 + 1;
                }
            }'
        );
        $this->whenIRequestTheResourceAt('YourProject/some/Specification');
        $this->thenTheResponseShouldContain(
            '"specification": {
                "name": "Specification",
                "description": "<p>This is some <em>description<\\/em><\\/p>",
                "scenarios": [
                    {
                        "name": "Some things",
                        "description": "<p>Description of <em>scenario<\\/em><\\/p>",
                        "content": "<p>Just <em>some<\\/em> <strong>comment<\\/strong><\\/p>\n<pre><code class=\"language-php\">$andCode = 1 + 1;<\\/code><\\/pre>"
                    }
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