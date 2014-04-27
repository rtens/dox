<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\WebFixture;
use watoki\scrut\Specification;

/**
 * @property WebFixture web <-
 */
class TestReportTest extends Specification {

    protected function background() {
        $this->web->givenTheProject('myProject');
        $this->web->givenTheProject_HasTheSpecFolder('myProject', 'spec/folder');
    }

    public function testPushReportInTapFormat() {
        $this->web->givenTheRequestHasTheBody('
TAP version 13
ok 1 - spec/folder/some/folder/SomeSpecificationTest::testSomeScenario
ok 2 - spec\folder\some\folder\SomeSpecificationTest::testOtherScenario
not ok 3 - Failure: spec/folder/FailingTest::testBadScenario
  ---
  message: \'Some message\'
  severity: fail
  ...
ok 4 - spec/folder/FailingTest::testGoodScenario
not ok 5 - spec/folder/FailingTest::testPendingScenario # TODO Incomplete Test
1..5');
        $this->web->whenISendA_RequestTo('post', 'projects/myProject/reports');

        $this->thenScenario_Of_InProject_ShouldBeMarkedPassing('SomeScenario', 'some/folder/SomeSpecification', 'myProject');
        $this->thenScenario_Of_InProject_ShouldBeMarkedPassing('testOtherScenario', 'some/folder/SomeSpecification', 'myProject');
        $this->thenScenario_Of_InProject_ShouldBeMarkedFailing('testBadScenario', 'FailingTest', 'myProject');
        $this->thenScenario_Of_InProject_ShouldBeMarkedPassing('testGoodScenario', 'FailingTest', 'myProject');
        $this->thenScenario_Of_InProject_ShouldBeMarkedPending('testPendingScenario', 'FailingTest', 'myProject');
    }

    public function testPushReportInInvalidFormat() {
        $this->web->givenTheRequestHasTheBody('Invalid format');
        $this->web->whenITryToSendA_RequestTo('post', 'projects/myProject/reports');
        $this->web->thenAnExceptionShouldBeThrownContaining('Format not recognized');
    }

    private function thenScenario_Of_InProject_ShouldBeMarkedPassing($scenario, $spec, $project) {
        $this->markTestIncomplete();
    }

    private function thenScenario_Of_InProject_ShouldBeMarkedFailing($scenario, $spec, $project) {
        $this->markTestIncomplete();
    }

    private function thenScenario_Of_InProject_ShouldBeMarkedPending($scenario, $spec, $project) {
        $this->markTestIncomplete();
    }
} 