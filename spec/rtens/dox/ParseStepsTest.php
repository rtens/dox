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
        $this->parser->thenTheScenarioShouldContain(
            '<div class="step" title="$this-&gt;givenSomething()">Given something</div>'
        );
    }

    public function testCallOnProperty() {
        $this->parser->whenIParseTheMethodBody(
            '$this->property->givenSomething();'
        );
        $this->parser->thenTheScenarioShouldContain(
            '<div class="step" title="$this-&gt;property-&gt;givenSomething()">Given something</div>'
        );
    }

    public function testInlineArguments() {
        $this->parser->whenIParseTheMethodBody(
            '$this->given_Has_Cows("Bart", 2);
            $this->given_Is("Bart", 10);'
        );
        $this->parser->thenTheScenarioShouldContain(
            "Given <span class=\"arg\">'Bart'</span> has <span class=\"arg\">2</span> cows"
        );
        $this->parser->thenTheScenarioShouldContain(
            "Given <span class=\"arg\">'Bart'</span> is <span class=\"arg\">10</span>"
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
                    <div class="step" title="$this-&gt;givenSomething()">Given something</div>
                    <div class="step" title="$this-&gt;givenSomethingElse()">Given something else</div></div></div>'
        );
    }

    public function testDifferentGroups() {
        $this->parser->whenIParseTheMethodBody(
            '$this->givenSomething();
            $this->givenSomethingElse();
            $this->whenSomethingHappens();
            $this->thenItShouldBeOk();'
        );
        $this->parser->thenTheScenarioShouldBe(
            '<div class="steps">
                <div class="step-group context">
                    <div class="step" title="$this-&gt;givenSomething()">Given something</div>
                    <div class="step" title="$this-&gt;givenSomethingElse()">Given something else</div></div>
                <div class="step-group action">
                    <div class="step" title="$this-&gt;whenSomethingHappens()">When something happens</div></div>
                <div class="step-group assertion">
                    <div class="step" title="$this-&gt;thenItShouldBeOk()">Then it should be ok</div></div></div>'
        );
    }

    public function testCamelCaseExceptions() {
        $this->parser->whenIParseTheMethodBody(
            '$this->whenIDoSomething();'
        );
        $this->parser->thenTheScenarioShouldContain(
            "When I do something"
        );
    }

    public function testArrayArguments() {
        $this->parser->whenIParseTheMethodBody(
            '$this->given(array());'
        );
        $this->parser->thenTheScenarioShouldContain(
            "Given <span class=\"arg\">array()</span>"
        );
    }

    public function testIndentedString() {
        $this->parser->whenIParseTheMethodBody(
            '$this->given("
                Something
                indented
            ");'
        );
        $this->parser->thenTheScenarioShouldContain(
            "Given <span class=\"arg\">'
            Something
            indented
            '"
        );
    }

} 