<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\ParserFixture;
use watoki\scrut\Specification;

/**
 * A *step* is meant in the [SbE] sense being a sentence starting with *given*, *when* or *then*.
 * In code, steps are methods that use a special syntax for in-lining arguments.
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

    public function testStepWithArguments() {
        $this->parser->whenIParseTheMethodBody(
            '$this->givenSomething(2);'
        );
        $this->parser->thenTheScenarioShouldContain('{
            "code": "$this->givenSomething(2)",
            "step": [
                "Given something",
                {"value":"2"}
            ]
        }');
    }

    public function testMethodInComposedObject() {
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
        // Underscores are used as placeholders for arguments (trailing underscores can be omitted)
        $this->parser->whenIParseTheMethodBody('
            $this->given_Has_Cows("Bart", 2);
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
        $this->parser->whenIParseTheMethodBody('
            $this->givenSomething();
            $this->givenSomethingElse();'
        );
        $this->parser->thenTheScenarioShouldContain('[
            {
                "code": "$this->givenSomething()",
                "step": ["Given something"]
            },
            {
                "code": "$this->givenSomethingElse()",
                "step": ["Given something else"]
            }
        ]');
    }

    public function testDifferentGroups() {
        $this->parser->whenIParseTheMethodBody('
            $this->givenSomething();
            $this->givenSomethingElse();
            $this->whenSomethingHappens();
            $this->thenItShouldBeOk();
            $this->whenThis();'
        );
        $this->parser->thenTheScenarioShouldContain('[
            [
                {
                    "code": "$this->givenSomething()",
                    "step": ["Given something"]
                },
                {
                    "code": "$this->givenSomethingElse()",
                    "step": ["Given something else"]
                }
            ],
            [
                {
                    "code": "$this->whenSomethingHappens()",
                    "step": ["When something happens"]
                }
            ],
            [
                {
                    "code": "$this->thenItShouldBeOk()",
                    "step": ["Then it should be ok"]
                }
            ],
            [
                {
                    "code": "$this->whenThis()",
                    "step": ["When this"]
                }
            ]
        ]');
    }

    /**
     * Some words should not be lower-case
     */
    public function testCamelCaseExceptions() {
        $this->parser->whenIParseTheMethodBody(
            '$this->whenIDoSomething();'
        );
        $this->parser->thenTheScenarioShouldContain(
            '"When I do something"'
        );
    }

    public function testNonScalarArguments() {
        $this->parser->whenIParseTheMethodBody(
            '$this->given(array(), null);'
        );
        $this->parser->thenTheScenarioShouldContain('[
            "Given",
            {"value": "array()"},
            {"value": "null"}
        ]');
    }

    public function testIndentedString() {
        // For some reason, the parser adds a token when printing indented strings
        $this->parser->whenIParseTheMethodBody('
            $this->given("
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