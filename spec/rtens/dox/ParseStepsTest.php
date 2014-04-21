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
        $this->parser->thenTheScenarioShouldBe(
            '<div class="steps">
                <div class="step-group context">
                    <div class="step" title="$this->givenSomething()">Given something.</div>
                </div>
            </div>'
        );
    }

    public function testTwoSteps() {
        $this->parser->whenIParseTheMethodBody(
            '$this->givenSomething();
            $this->givenSomethingElse();'
        );
        $this->parser->thenTheScenarioShouldBe(
            '<div class="steps">
                <div class="step-group context">
                    <div class="step" title="$this->givenSomething()">Given something.</div>
                    <div class="step" title="$this->givenSomethingElse()">Given something else.</div>
                </div>
            </div>'
        );
    }

} 