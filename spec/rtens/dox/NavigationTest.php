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

        $this->web->thenTheResponseShouldContain([
            'project' => [
                [
                    "name" => "ProjectOne",
                    "href" => "http://dox/ProjectOne"
                ],
                [
                    "name" => "ProjectTwo",
                    "href" => "http://dox/ProjectTwo"
                ],
                [
                    "name" => "ProjectThree",
                    "href" => "http://dox/ProjectThree"
                ]
            ]
        ]);
    }

    public function testProjectStartSpecification() {
        $this->web->givenTheProject('MyProject');
        $this->web->givenTheStartSpecificationOf_Is('MyProject', 'my/Introduction');
        $this->web->whenIRequestTheResourceAt('MyProject');
        $this->web->thenIShouldBeRedirectedTo('http://dox/MyProject/my/Introduction');
    }

    public function testSpecificationTree() {
        $this->web->givenTheProject_WithTheSpecificationFolder('MyProject', 'spec');
        $this->file->givenTheFile('spec/OneTest.php');
        $this->file->givenTheFile('spec/TwoTest.php');
        $this->file->givenTheFile('spec/Three.php');
        $this->file->givenTheFile('spec/a/AOneTest.php');
        $this->file->givenTheFile('spec/a/ATwoTest.php');
        $this->file->givenTheFile('spec/a/b/ABTest.php');
        $this->file->givenTheFile('spec/c/COneTest.php');

        $this->web->whenIRequestTheResourceAt('MyProject');
        $this->web->thenTheResponseShouldContain([
            "navigation" => [
                "name" => "MyProject",
                "folder" => [
                    [
                        "name" => "a",
                        "folder" => [
                            [
                                "name" => "b",
                                "folder" => [],
                                "specification" => [
                                    [
                                        "name" => "A b",
                                        "href" => "http://dox/MyProject/a/b/AB"
                                    ]
                                ]
                            ]
                        ],
                        "specification" => [
                            [
                                "name" => "A one",
                                "href" => "http://dox/MyProject/a/AOne"
                            ],
                            [
                                "name" => "A two",
                                "href" => "http://dox/MyProject/a/ATwo"
                            ]
                        ]
                    ],
                    [
                        "name" => "c",
                        "folder" => [],
                        "specification" => [
                            [
                                "name" => "C one",
                                "href" => "http://dox/MyProject/c/COne"
                            ]
                        ]
                    ]
                ],
                "specification" => [
                    [
                        "name" => "One",
                        "href" => "http://dox/MyProject/One"
                    ],
                    [
                        "name" => "Two",
                        "href" => "http://dox/MyProject/Two"
                    ]
                ]
            ]
        ]);
    }

} 