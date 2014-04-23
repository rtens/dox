<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\FileFixture;
use spec\rtens\dox\fixtures\WebFixture;
use watoki\scrut\Specification;

/**
 * @property WebFixture web <-
 * @property FileFixture file <-
 */
class NavigationTest extends Specification {

    public function testProjectList() {
        $this->web->givenTheProject('ProjectOne');
        $this->web->givenTheProject('ProjectTwo');
        $this->web->givenTheProject('ProjectThree');

        $this->web->whenIRequestTheResourceAt('');

        $this->web->thenTheResponseShouldContain('
            "project": [
                {
                    "name": "ProjectOne",
                    "href": "http://dox/ProjectOne"
                },
                {
                    "name": "ProjectTwo",
                    "href": "http://dox/ProjectTwo"
                },
                {
                    "name": "ProjectThree",
                    "href": "http://dox/ProjectThree"
                }
            ]
        ');
    }

    public function testNavigationInProjectResource() {
        $this->web->givenTheProject_WithTheSpecificationFolder('MyProject', 'spec');
        $this->file->givenTheFile('spec/OneTest.php');
        $this->file->givenTheFile('spec/TwoTest.php');
        $this->file->givenTheFile('spec/Three.php');
        $this->file->givenTheFile('spec/a/AOneTest.php');
        $this->file->givenTheFile('spec/a/ATwoTest.php');
        $this->file->givenTheFile('spec/a/b/ABTest.php');
        $this->file->givenTheFile('spec/c/COneTest.php');

        $this->web->whenIRequestTheResourceAt('MyProject');
        $this->web->thenTheResponseShouldContain('
            "navigation": {
                "name": "MyProject",
                "folder": [
                    {
                        "name": "a",
                        "specification": [
                            {
                                "name": "A one",
                                "href": "http://dox/MyProject/a/AOne"
                            },
                            {
                                "name": "A two",
                                "href": "http://dox/MyProject/a/ATwo"
                            }
                        ]
                    },
                    {
                        "name": "a/b",
                        "specification": [
                            {
                                "name": "A b",
                                "href": "http://dox/MyProject/a/b/AB"
                            }
                        ]
                    },
                    {
                        "name": "c",
                        "specification": [
                            {
                                "name": "C one",
                                "href": "http://dox/MyProject/c/COne"
                            }
                        ]
                    }
                ],
                "specification": [
                    {
                        "name": "One",
                        "href": "http://dox/MyProject/One"
                    },
                    {
                        "name": "Two",
                        "href": "http://dox/MyProject/Two"
                    }
                ]
            }
        ');
    }

    public function testNavigationInSpecificationResource() {
        $this->web->givenTheProject_WithTheSpecificationFolder('MyProject', 'spec');
        $this->file->givenTheFile_WithContent('spec/OneTest.php', '<?php class OneTest {}');
        $this->file->givenTheFile_WithContent('spec/TwoTest.php', '<?php class TwoTest {}');

        $this->web->whenIRequestTheResourceAt('MyProject/One');
        $this->web->thenTheResponseShouldContain('
            "navigation": {
                "name": "MyProject",
                "folder": [],
                "specification": [
                    {
                        "name": "One",
                        "href": "http://dox/MyProject/One"
                    },
                    {
                        "name": "Two",
                        "href": "http://dox/MyProject/Two"
                    }
                ]
            }
        ');
    }

    public function testSkipEmptyFolders() {
        $this->web->givenTheProject_WithTheSpecificationFolder('MyProject', 'spec');
        $this->file->givenTheFile('spec/OneTest.php');
        $this->file->givenTheFile('spec/a/Ignored.php');
        $this->file->givenTheFile('spec/a/b/IgnoredAsWell.php');

        $this->web->whenIRequestTheResourceAt('MyProject');
        $this->web->thenTheResponseShouldContain('
            "navigation": {
                "name": "MyProject",
                "folder": [],
                "specification": [
                    {
                        "name": "One",
                        "href": "http://dox/MyProject/One"
                    }
                ]
            }
        ');
    }

    public function testLinkBackInSpecification() {
        $this->web->givenTheProject_WithTheSpecificationFolder('project', 'spec');
        $this->file->givenTheFile_WithContent('spec/OneTest.php', '<?php class SomeSpecClass {}');
        $this->web->whenIRequestTheResourceAt('project/One');
        $this->web->thenTheResponseShouldContain('"back": {"href": "../project"}');
    }

    public function testLinkBackInProject() {
        $this->web->givenTheProject_WithTheSpecificationFolder('project', 'spec');
        $this->web->whenIRequestTheResourceAt('project');
        $this->web->thenTheResponseShouldContain('"back": {"href": "."}');
    }

}