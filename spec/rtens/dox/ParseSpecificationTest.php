<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\ParserFixture;
use watoki\scrut\Specification;

/**
 * @property ParserFixture parser <-
 */
class ParseSpecificationTest extends Specification {

    public function testSpecificationClass() {
        $this->parser->whenIParse('
            namespace some\name\space;
            class SomeSpecificationTest {}'
        );
        $this->parser->thenThereShouldBeASpecificationWithTheName('Some specification');
    }

    public function testSpecificationWithDescription() {
        $this->parser->whenIParse('
            /**
              * This is my
              * *DocComment* text.
              *
              * @var not this
              */
            class SomeTest {}'
        );
        $this->parser->thenTheSpecificationShouldHaveTheDescription(
            'This is my
            *DocComment* text.'
        );
    }

    public function testScenarios() {
        $this->parser->whenIParse('
            class SomeTest {
                function testSomeStuff() {}
                function testSomeMoreStuff() {}
            }'
        );
        $this->parser->thenThereShouldBe_Scenarios(2);
        $this->parser->thenScenario_ShouldHaveTheName(1, 'Some stuff');
        $this->parser->thenScenario_ShouldHaveTheName(2, 'Some more stuff');
    }

    public function testScenarioWithDescription() {
        $this->parser->whenIParse('
            class SomeTest {
                /**
                 * This is the description
                 * of this scenario.
                 */
                function testSomeStuff() {}
            }'
        );
        $this->parser->thenScenario_ShouldHaveTheDescription(1,
            'This is the description
            of this scenario.'
        );
    }

    public function testScenarioWithCode() {
        $this->parser->whenIParse('
            class SomeTest {
                public function testSomeStuff() {
                    $code = 1 + 1;
                }
            }'
        );
        $this->parser->thenScenario_ShouldHaveTheContent(1, '[
            {
                "content": "$code = 1 + 1;",
                "type": "code"
            }
        ]');
    }

    public function testIgnorePrivateAndProtectedMethods() {
        $this->parser->whenIParse('
            class SomeTest {
                public function testSomeStuff() {}
                private function testMoreStuff() {}
                protected function testEvenMoreStuff() {}
            }'
        );
        $this->parser->thenThereShouldBe_Scenarios(1);
    }

    public function testIgnoreMethodsWithoutPrefix() {
        $this->parser->givenTheMethodPrefixIs('my');
        $this->parser->whenIParse('
            class SomeTest {
                public function myMethod() {}
                public function yourMethod() {}
            }'
        );
        $this->parser->thenThereShouldBe_Scenarios(1);
    }

}