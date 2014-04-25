<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\FileFixture;
use spec\rtens\dox\fixtures\WebFixture;
use watoki\curir\Resource;
use watoki\scrut\Specification;

/**
 * All comments are interpreted as [markdown] and rendered as HTML.
 *
 * [markdown]: http://daringfireball.net/projects/markdown/
 *
 * @property WebFixture web <-
 * @property FileFixture file <-
 */
class RenderSpecificationTest extends Specification {

    public function testEmptySpecification() {
        $this->web->givenTheProject('MyProject');
        $this->web->givenTheProject_HasTheSpecFolder('MyProject', 'mySpec');
        $this->file->givenTheFile_WithContent('user/projects/MyProject/mySpec/SomeSpecificationTest.php', '
            <?php
            class SomeSpecificationTest {}'
        );
        $this->web->whenIRequestTheResourceAt('projects/MyProject/specs/SomeSpecification');
        $this->web->thenTheResponseShouldContain('
            "specification": {
                "name": "Some specification",
                "description": null,
                "scenario": []
            }
        ');
    }

    public function testSpecificationWithDescriptionAndMethods() {
        $this->web->givenTheProject('MyProject');
        $this->web->givenTheProject_HasTheSpecFolder('MyProject', 'mySpec');
        $this->file->givenTheFile_WithContent('user/projects/MyProject/mySpec/some/SpecificationTest.php', '
            <?php

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

                /**
                 * Method *description*
                 */
                public function someMethod() {
                    $code = 1;
                }
            }'
        );
        $this->web->whenIRequestTheResourceAt('projects/MyProject/specs/some/Specification');
        $this->web->thenTheResponseShouldContain('
            "specification": {
                "name": "Specification",
                "description": "<p>This is some <em>description</em></p>",
                "scenario": [
                    {
                        "name": "Some things",
                        "description": "<p>Description of <em>scenario</em></p>",
                        "content": "<p>Just <em>some</em> <strong>comment</strong></p>\n<pre><code>$andCode = 1 + 1;</code></pre>"
                    }
                ],
                "method": [
                    {
                        "name": "someMethod",
                        "description": "<p>Method <em>description</em></p>",
                        "content": "<pre><code>$code = 1;</code></pre>"
                    }
                ]
            }
        ');
    }

    public function testRenderSteps() {
        $this->web->givenTheRequestedFormatIs('html');
        $this->web->givenTheProject('YourProject');
        $this->web->givenTheProject_HasTheSpecFolder('YourProject', 'yourSpec');
        $this->file->givenTheFile_WithContent('user/projects/YourProject/yourSpec/some/SpecificationTest.php', '
            <?php

            class SpecificationTest {
                public function testSomeThings() {
                    $this->fix->given_Has_Cows("Bart", 2);
                    $this->whenSomethingHappens();
                    $this->thenItShouldBe("OK");
                }
            }'
        );
        $this->web->whenIRequestTheResourceAt('projects/YourProject/specs/some/Specification');
        $this->web->thenTheResponseShouldContainTheText('<div class="steps">
                <div class="step-group">
                    <div title="$this-&gt;fix-&gt;given_Has_Cows(\'Bart\', 2)" class="step">Given <span class="arg">\'Bart\'</span> has <span class="arg">2</span> cows</div>
                </div>
                <div class="step-group">
                    <div title="$this-&gt;whenSomethingHappens()" class="step">When something happens</div>
                </div>
                <div class="step-group">
                    <div title="$this-&gt;thenItShouldBe(\'OK\')" class="step">Then it should be <span class="arg">\'OK\'</span></div>
                </div>
            </div>'
        );
    }

    public function testHtmlEntitiesInCode() {
        $this->web->givenTheRequestedFormatIs('html');
        $this->web->givenTheProject('project');
        $this->file->givenTheFile_WithContent('user/projects/project/spec/SpecificationTest.php', '
            <?php

            class SpecificationTest {
                public function testSomeThings() {
                    $code = "<div>Some <em>HTML</em></div>";
                }
            }'
        );
        $this->web->whenIRequestTheResourceAt('projects/project/specs/Specification');
        $this->web->thenTheResponseShouldContainTheText('&lt;div&gt;Some &lt;em&gt;HTML&lt;/em&gt;&lt;/div&gt;');
    }

    public function testHtmlEntitiesInSteps() {
        $this->web->givenTheRequestedFormatIs('html');
        $this->web->givenTheProject('project');
        $this->file->givenTheFile_WithContent('user/projects/project/spec/SpecificationTest.php', '
            <?php

            class SpecificationTest {
                public function testSomeThings() {
                    $this->givenSomeHtml("<div>Some <em>HTML</em></div>");
                }
            }'
        );
        $this->web->whenIRequestTheResourceAt('projects/project/specs/Specification');
        $this->web->thenTheResponseShouldContainTheText('$this-&gt;givenSomeHtml(\'&lt;div&gt;Some &lt;em&gt;HTML&lt;/em&gt;&lt;/div&gt;\')');
        $this->web->thenTheResponseShouldContainTheText('<span class="arg">\'&lt;div&gt;Some &lt;em&gt;HTML&lt;/em&gt;&lt;/div&gt;\'</span>');
    }

} 