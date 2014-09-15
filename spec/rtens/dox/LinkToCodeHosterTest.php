<?php
namespace spec\rtens\dox;
use spec\rtens\dox\fixtures\FileFixture;
use spec\rtens\dox\fixtures\WebFixture;
use watoki\scrut\Specification;

/**
 * If a code hosting website is provided, a link for the project and each specification is provided.
 *
 * @property WebFixture web <-
 * @property FileFixture file <-
 */
class LinkToCodeHosterTest extends Specification {

    public function background() {
        $this->web->givenTheProject('MyProject');
        $this->file->givenTheFile_WithContent('user/projects/MyProject/spec/some/SpecificationTest.php', '<?php
            class SpecificationTest {
                public function testSomeThings() {}
            }'
        );
    }

    function testNoLinkForProjectIfHosterNotSet() {
        $this->web->whenIRequestTheResourceAt('projects/MyProject');
        $this->web->then_ShouldBe('codeLink', null);
    }

    function testShowLinkForProject() {
        $this->web->givenTheProject_HasTheHoster('MyProject', 'http://github.com/some/project');
        $this->web->whenIRequestTheResourceAt('projects/MyProject');
        $this->web->then_ShouldBe('codeLink/href', 'http://github.com/some/project');
    }

    function testNoLinkForSpecificationWhenHosterNotSet() {
        $this->web->whenIRequestTheResourceAt('projects/MyProject/specs/some__Specification');
        $this->web->then_ShouldBe('specification/codeLink', null);
    }

    function testShowLinkForSpecification() {
        $this->web->givenTheProject_HasTheHoster('MyProject', 'http://github.com/some/project');

        $this->web->whenIRequestTheResourceAt('projects/MyProject/specs/some__Specification');
        $this->web->then_ShouldBe('specification/codeLink/href',
            'http://github.com/some/project/blob/master/spec/some/SpecificationTest.php');
    }

} 