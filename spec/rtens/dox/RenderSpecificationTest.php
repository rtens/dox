<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\FileFixture;
use spec\rtens\dox\fixtures\WebFixture;
use watoki\curir\Resource;
use watoki\scrut\Specification;

/**
 * @property WebFixture web <-
 * @property FileFixture file <-
 */
class RenderSpecificationTest extends Specification {

    public function testEmptySpecification() {
        $this->web->givenTheProject_WithTheSpecificationFolder('MyProject', 'mySpec');
        $this->file->givenTheFile_WithContent('mySpec/SomeSpecificationTest.php',
            '<?php
            class SomeSpecificationTest {}'
        );
        $this->web->whenIRequestTheResourceAt('MyProject/SomeSpecification');
        $this->web->thenTheResponseShouldContain([
            "specification" => [
                "name" => "Some specification",
                "description" => null,
                "scenario" => []
            ]
        ]);
    }

    public function testSpecificationWithDescriptionAndScenarios() {
        $this->web->givenTheProject_WithTheSpecificationFolder('YourProject', 'yourSpec');
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
        $this->web->whenIRequestTheResourceAt('YourProject/some/Specification');
        $this->web->thenTheResponseShouldContain([
            "specification" => [
                "name" => "Specification",
                "description" => "<p>This is some <em>description</em></p>",
                "scenario" => [
                    [
                        "name" => "Some things",
                        "description" => "<p>Description of <em>scenario</em></p>",
                        "content" => '<p>Just <em>some</em> <strong>comment</strong></p>' . "\n" .
                            '<pre><code class="language-php">$andCode = 1 + 1;</code></pre>'
                    ]
                ]
            ]
        ]);
    }

} 