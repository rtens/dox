<?php
namespace spec\rtens\dox;

use rtens\dox\Report;
use rtens\mockster\Mock;
use rtens\mockster\MockFactory;
use spec\rtens\dox\fixtures\FileFixture;
use spec\rtens\dox\fixtures\WebFixture;
use watoki\scrut\Specification;

/**
 * The status (passing|failing|pending) of each scenario can be sent from a CI server
 * and is represented by color coding the scenario (green|red|orange).
 *
 * @property WebFixture web <-
 * @property FileFixture file <-
 */
class TestReportTest extends Specification {

    public function background() {
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

        $this->web->thenTheResponseShouldBe('OK - report saved for [myProject]');

        $this->thenScenario_Of_InProject_ShouldBeMarked('SomeScenario', 'some/folder/SomeSpecification', 'myProject', Report::STATUS_PASSING);
        $this->thenScenario_Of_InProject_ShouldBeMarked('OtherScenario', 'some/folder/SomeSpecification', 'myProject', Report::STATUS_PASSING);
        $this->thenScenario_Of_InProject_ShouldBeMarked('BadScenario', 'Failing', 'myProject', Report::STATUS_FAILING);
        $this->thenScenario_Of_InProject_ShouldBeMarked('GoodScenario', 'Failing', 'myProject', Report::STATUS_PASSING);
        $this->thenScenario_Of_InProject_ShouldBeMarked('PendingScenario', 'Failing', 'myProject', Report::STATUS_PENDING);
        $this->thenScenario_Of_InProject_ShouldBeMarked('notExisting', 'NoWhere', 'myProject', Report::STATUS_UNKNOWN);

        $this->web->then_ShouldBeLogged('Saved status of [5] scenarios in [myProject]');
    }

    public function testPushReportInInvalidFormat() {
        $this->web->givenTheRequestHasTheBody('Invalid format');
        $this->web->whenITryToSendA_RequestTo('post', 'projects/myProject/reports');
        $this->web->thenAnExceptionShouldBeThrownContaining('Format not recognized');
        $this->web->then_ShouldBeLogged('Failed saving status in [myProject]');
    }

    public function testReadStatusOfScenario() {
        $this->web->givenTheProject('project');
        $this->file->givenTheFile_WithContent('user/projects/project/spec/SpecificationTest.php', '
            <?php

            class SpecificationTest {
                public function testPassingScenario() {}
                public function testFailingScenario() {}
                public function testPendingScenario() {}
                public function testUnknownScenario() {}
            }'
        );

        $this->givenScenario_Of_In_HasTheStatus('PassingScenario', 'Specification', 'project', Report::STATUS_PASSING);
        $this->givenScenario_Of_In_HasTheStatus('FailingScenario', 'Specification', 'project', Report::STATUS_FAILING);
        $this->givenScenario_Of_In_HasTheStatus('PendingScenario', 'Specification', 'project', Report::STATUS_PENDING);
        $this->givenScenario_Of_In_HasTheStatus('UnknownScenario', 'Specification', 'project', Report::STATUS_UNKNOWN);

        $this->web->whenIRequestTheResourceAt('projects/project/specs/Specification');
        $this->web->then_ShouldBe('specification/scenario/0/status', 'passing');
        $this->web->then_ShouldBe('specification/scenario/1/status', 'failing');
        $this->web->then_ShouldBe('specification/scenario/2/status', 'pending');
        $this->web->then_ShouldBe('specification/scenario/3/status', 'unknown');
    }

    public function testNonExistentStatusFile() {
        $this->web->givenTheProject('project');
        $this->file->givenTheFile_WithContent('user/projects/project/spec/SpecificationTest.php', '
            <?php

            class SpecificationTest {
                public function testSomeScenario() {}
            }'
        );

        $this->web->whenIRequestTheResourceAt('projects/project/specs/Specification');
        $this->web->then_ShouldBe('specification/scenario/0/status', 'unknown');
    }

    ##################### SET-UP #######################

    /** @var Mock */
    private $report;

    private function thenScenario_Of_InProject_ShouldBeMarked($scenario, $spec, $project, $status) {
        /** @var Report $report */
        $report = $this->factory->getInstance(Report::$CLASS);
        $this->assertEquals($status, $report->getStatus($project, $spec, $scenario));
    }

    private function givenScenario_Of_In_HasTheStatus($scenario, $spec, $project, $status) {
        if (!$this->report) {
            $mf = new MockFactory();
            $this->report = $mf->getMock(Report::$CLASS);
            $this->factory->setSingleton(Report::$CLASS, $this->report);
        }

        $this->report->__mock()->method('getStatus')->willReturn($status)->withArguments($project, $spec, $scenario);
    }
}