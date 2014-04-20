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
        $this->markTestIncomplete();
    }

    public function testSpecificationList() {
        $this->markTestIncomplete();
    }

} 