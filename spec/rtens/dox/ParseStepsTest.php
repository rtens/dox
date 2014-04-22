<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\ParserFixture;
use watoki\scrut\Specification;

/**
 * A *step* is meant in the [SbE] sense being a sentence starting with *given*, *when* or *then*.
 * In code, steps are method calls that use a special syntax for in-lining arguments.
 *
 * [SbE]: http://specificationbyexample.com
 *
 * @property ParserFixture parser <-
 */
class ParseStepsTest extends Specification {

    public function testStepWithoutArguments() {
        $this->parser->whenIParseTheMethodBody(
            '$this->givenSomething();'
        );
        $this->parser->thenTheScenarioShouldContain('{
            "code": "$this->givenSomething()",
            "step": [
                "Given something"
            ]
        }');
    }

    public function testCallOnProperty() {
        $this->parser->whenIParseTheMethodBody(
            '$this->property->givenSomething();'
        );
        $this->parser->thenTheScenarioShouldContain('{
            "code": "$this->property->givenSomething()",
            "step": [
                "Given something"
            ]
        }');
    }

    public function testInlineArguments() {
        $this->parser->whenIParseTheMethodBody(
            '$this->given_Has_Cows("Bart", 2);
            $this->given_Is("Bart", 10);'
        );
        $this->parser->thenTheScenarioShouldContain('[
            "Given",
            {"value": "\'Bart\'"},
            "has",
            {"value": "2"},
            "cows"
        ]');
        $this->parser->thenTheScenarioShouldContain('[
            "Given",
            {"value": "\'Bart\'"},
            "is",
            {"value": "10"}
        ]');
    }

    public function testTwoSteps() {
        $this->parser->whenIParseTheMethodBody(
            '$this->givenSomething();
            $this->givenSomethingElse();'
        );
        $this->parser->thenTheScenarioShouldContain('{
            "context": [
                {
                    "code": "$this->givenSomething()",
                    "step": ["Given something"]
                },
                {
                    "code": "$this->givenSomethingElse()",
                    "step": ["Given something else"]
                }
            ]
        }');
    }

    public function testDifferentGroups() {
        $this->parser->whenIParseTheMethodBody(
            '$this->givenSomething();
            $this->givenSomethingElse();
            $this->whenSomethingHappens();
            $this->thenItShouldBeOk();'
        );
        $this->parser->thenTheScenarioShouldContain('{
            "context": [
                {
                    "code": "$this->givenSomething()",
                    "step": ["Given something"]
                },
                {
                    "code": "$this->givenSomethingElse()",
                    "step": ["Given something else"]
                }
            ],
            "action": [
                {
                    "code": "$this->whenSomethingHappens()",
                    "step": ["When something happens"]
                }
            ],
            "assertion": [
                {
                    "code": "$this->thenItShouldBeOk()",
                    "step": ["Then it should be ok"]
                }
            ]
        }');
    }

    public function testCamelCaseExceptions() {
        $this->parser->whenIParseTheMethodBody(
            '$this->whenIDoSomething();'
        );
        $this->parser->thenTheScenarioShouldContain(
            '"When I do something"'
        );
    }

    public function testArrayArguments() {
        $this->parser->whenIParseTheMethodBody(
            '$this->given(array());'
        );
        $this->parser->thenTheScenarioShouldContain('[
            "Given",
            {"value": "array()"}
        ]');
    }

    public function testIndentedString() {
        $this->parser->whenIParseTheMethodBody(
            '$this->given("
                Something
                indented
            ");'
        );
        $this->parser->thenTheScenarioShouldContain('[
            "Given",
            {"value": "\'\nSomething\nindented\n\'"}
        ]');
    }

} 