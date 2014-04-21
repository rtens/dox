<?php
namespace spec\rtens\dox;

use rtens\dox\Executer;
use rtens\mockster\Mock;
use rtens\mockster\MockFactory;
use spec\rtens\dox\fixtures\FileFixture;
use spec\rtens\dox\fixtures\WebFixture;
use watoki\curir\http\Request;
use watoki\scrut\Specification;

/**
 * Projects can be updated by triggering a web hook
 *
 * @property WebFixture web <-
 * @property FileFixture file <-
 * @property Mock executer
 */
class WebHookTest extends Specification {

    public function testReceivePushEvent() {
        $this->web->givenTheProject_WithTheSpecificationFolder('Project', 'spec');
        $this->web->whenISendA_RequestTo(Request::METHOD_POST, 'Project');
        $this->thenExecutedCommand_ShouldBe(1, 'cd ' . $this->file->pathTo('spec'));
        $this->thenExecutedCommand_ShouldBe(2, 'git pull origin master');
        $this->web->thenTheResponseShouldBe('OK - Updated Project');
    }

    protected function setUp() {
        parent::setUp();
        $mf = new MockFactory();
        $this->executer = $mf->getMock(Executer::CLASS);
        $this->factory->setSingleton(Executer::CLASS, $this->executer);
    }

    private function thenExecutedCommand_ShouldBe($pos, $command) {
        $history = $this->executer->__mock()->method('execute')->getHistory();
        $this->assertEquals($command, $history->getCalledArgumentAt($pos - 1, 0));
    }

} 