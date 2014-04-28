<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\FileFixture;
use spec\rtens\dox\fixtures\WebFixture;
use watoki\scrut\Specification;

/**
 * dox is supposed to be a browser so we need some links forth and back
 *
 * @property WebFixture web <-
 * @property FileFixture file <-
 */
class NavigationTest extends Specification {

    public function testRootRedirectsToHome() {
        $this->web->whenIRequestTheResourceAt('');
        $this->web->thenIShouldBeRedirectedTo('http://dox/home');
    }

    public function testProjectListOnHomePage() {
        $this->web->givenTheProject('ProjectOne');
        $this->web->givenTheProject('ProjectTwo');
        $this->web->givenTheProject('ProjectThree');

        $this->web->whenIRequestTheResourceAt('home');

        $this->web->then_ShouldHaveTheSize('project', 3);
        $this->web->then_ShouldBe('project/0/name', 'ProjectOne');
        $this->web->then_ShouldBe('project/0/href', 'http://dox/projects/ProjectOne');
        $this->web->then_ShouldBe('project/1/name', 'ProjectTwo');
        $this->web->then_ShouldBe('project/1/href', 'http://dox/projects/ProjectTwo');
        $this->web->then_ShouldBe('project/2/name', 'ProjectThree');
        $this->web->then_ShouldBe('project/2/href', 'http://dox/projects/ProjectThree');
    }

    public function testShowProjectDescriptionInProjectList() {
        $this->web->givenTheProject('SomeProject');
        $this->file->givenTheFile_WithContent('user/projects/SomeProject/composer.json', '{
            "name": "Some cool project",
            "description": "A short description of this project"
        }');

        $this->web->whenIRequestTheResourceAt('home');

        $this->web->then_ShouldBe('project/0/description', 'A short description of this project');
    }

    public function testNavigationInProjectResource() {
        $this->web->givenTheProject('MyProject');
        $this->file->givenTheFile('user/projects/MyProject/spec/OneTest.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/TwoTest.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/Three.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/a/AOneTest.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/a/ATwoTest.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/a/b/ABTest.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/c/COneTest.php');

        $this->web->whenIRequestTheResourceAt('projects/MyProject');

        $this->web->then_ShouldBe('navigation/name', "MyProject");
        $this->web->then_ShouldHaveTheSize('navigation/folder', 3);

        $this->web->then_ShouldBe('navigation/folder/0/name', 'a');
        $this->web->then_ShouldHaveTheSize('navigation/folder/0/specification', 2);
        $this->web->then_ShouldBe('navigation/folder/0/specification/0/name', 'A one');
        $this->web->then_ShouldBe('navigation/folder/0/specification/0/href', 'http://dox/projects/MyProject/specs/a__AOne');

        $this->web->then_ShouldBe('navigation/folder/1/name', 'a/b');
        $this->web->then_ShouldBe('navigation/folder/1/specification/0/href', 'http://dox/projects/MyProject/specs/a__b__AB');

        $this->web->then_ShouldBe('navigation/folder/2/name', 'c');

        $this->web->then_ShouldHaveTheSize('navigation/specification', 2);
        $this->web->then_ShouldBe('navigation/specification/0/name', 'One');
        $this->web->then_ShouldBe('navigation/specification/0/href', 'http://dox/projects/MyProject/specs/One');
        $this->web->then_ShouldBe('navigation/specification/1/name', 'Two');
    }

    public function testRenderReadmeInProjectHome() {
        $this->web->givenTheProject('MyProject');
        $this->file->givenTheFile('user/projects/MyProject/spec/OneTest.php');

        $this->file->givenTheFile_WithContent('user/projects/MyProject/readme.md', 'Some *markdown* text.');
        $this->web->whenIRequestTheResourceAt('projects/MyProject');
        $this->web->then_ShouldBe('readme/text', '<p>Some <em>markdown</em> text.</p>');

        // Uppercase
        $this->web->givenTheProject('Uppercase');
        $this->file->givenTheFile('user/projects/Uppercase/spec/OneTest.php');

        $this->file->givenTheFile_WithContent('user/projects/Uppercase/README.md', 'Found');
        $this->web->whenIRequestTheResourceAt('projects/Uppercase');
        $this->web->then_ShouldBe('readme/text', '<p>Found</p>');

        // Mixed case and .markdown extension
        $this->web->givenTheProject('MixedCase');
        $this->file->givenTheFile('user/projects/MixedCase/spec/OneTest.php');

        $this->file->givenTheFile_WithContent('user/projects/MixedCase/Readme.markdown', 'Found');
        $this->web->whenIRequestTheResourceAt('projects/MixedCase');
        $this->web->then_ShouldBe('readme/text', '<p>Found</p>');

        // Not markdown format
        $this->web->givenTheProject('NoReadme');
        $this->file->givenTheFile('user/projects/NoReadme/spec/OneTest.php');

        $this->file->givenTheFile_WithContent('user/projects/NoReadmeCase/Readme', 'Found');
        $this->web->whenIRequestTheResourceAt('projects/NoReadme');
        $this->web->then_ShouldBe('readme', null);
    }

    public function testDeepSpecificationUrl() {
        $this->web->givenTheProject('MyProject');
        $this->file->givenTheFile_WithContent('user/projects/MyProject/spec/a/b/c/SomeTest.php', '
            <?php class SomeTest {}
        ');
        $this->web->whenIRequestTheResourceAt('projects/MyProject/specs/a__b__c__Some');
        $this->web->thenTheResponseShouldContainTheText('
            "specification": {
                "name": "Some"');
    }

    public function testFileNameConsistingOfOnlySuffix() {
        $this->web->givenTheProject('MyProject');
        $this->file->givenTheFile_WithContent('user/projects/MyProject/spec/Test.php', '
            <?php class SomeTest {}
        ');
        $this->web->whenIRequestTheResourceAt('projects/MyProject');
        $this->web->thenTheResponseShouldContain('"specification": []');
    }

    public function testProjectInNonDefaultFolder() {
        $this->web->givenTheProject('SomeProject');
        $this->web->givenTheProject_IsIn('SomeProject', 'some/other/folder');
        $this->file->givenTheFile('some/other/folder/spec/SomeSpecificationTest.php');
        $this->web->whenIRequestTheResourceAt('projects/SomeProject');
        $this->web->thenTheResponseShouldContainTheText('"name": "Some specification"');
    }

    public function testNavigationInSpecificationResource() {
        $this->web->givenTheProject('MyProject');
        $this->file->givenTheFile_WithContent('user/projects/MyProject/spec/OneTest.php', '<?php class OneTest {}');
        $this->file->givenTheFile_WithContent('user/projects/MyProject/spec/TwoTest.php', '<?php class TwoTest {}');

        $this->web->whenIRequestTheResourceAt('projects/MyProject/specs/One');
        $this->web->then_ShouldHaveTheSize('navigation/folder', 0);
        $this->web->then_ShouldHaveTheSize('navigation/specification', 2);
    }

    public function testSkipEmptyFolders() {
        $this->web->givenTheProject('MyProject');
        $this->file->givenTheFile('user/projects/MyProject/spec/a/Ignored.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/a/b/IgnoredAsWell.php');
        $this->file->givenTheFile('user/projects/MyProject/spec/c/OneTest.php');

        $this->web->whenIRequestTheResourceAt('projects/MyProject');
        $this->web->then_ShouldHaveTheSize('navigation/folder', 1);
        $this->web->then_ShouldBe('navigation/folder/0/name', 'c');
    }

    public function testLinkBackInSpecification() {
        $this->web->givenTheProject('project');
        $this->file->givenTheFile_WithContent('user/projects/project/spec/OneTest.php', '<?php class SomeSpecClass {}');
        $this->web->whenIRequestTheResourceAt('projects/project/specs/One');
        $this->web->then_ShouldBe('back/href', "http://dox/projects/project");
    }

    public function testLinkBackInProject() {
        $this->web->givenTheProject('project');
        $this->file->givenTheFolder('user/projects/project/spec');
        $this->web->whenIRequestTheResourceAt('projects/project');
        $this->web->then_ShouldBe('back/href', "http://dox/home");
    }

}