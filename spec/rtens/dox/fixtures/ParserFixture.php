<?php
namespace spec\rtens\dox\fixtures;

use rtens\dox\Parser;
use watoki\factory\Factory;
use watoki\scrut\Fixture;
use watoki\scrut\Specification;

/**
 * @property \rtens\dox\Specification $specification
 */
class ParserFixture extends Fixture {

    public function __construct(Specification $spec, Factory $factory) {
        parent::__construct($spec, $factory);
        $this->parser = new Parser();
    }

    public function whenIParse($code) {
        $this->specification = $this->parser->parse('<?php ' . $this->trimLines($code));
    }

    public function whenIParseTheMethodBody($code) {
        $this->whenIParse(
            "class SomeClass {
                function testThis() {
                    $code
                }
            }"
        );
    }

    public function thenTheScenarioShouldBe($string) {
        $this->thenScenario_ShouldHaveTheContent(1, $string);
    }

    public function thenThereShouldBeASpecificationWithTheName($name) {
        $this->spec->assertTrue($this->specification instanceof \rtens\dox\Specification);
        $this->spec->assertEquals($name, $this->specification->getName());
    }

    public function thenTheSpecificationShouldHaveTheDescription($string) {
        $this->spec->assertEquals($this->trimLines($string), $this->specification->getDescription());
    }

    public function thenThereShouldBe_Scenarios($int) {
        $this->spec->assertCount($int, $this->specification->getScenarios());
    }

    public function thenScenario_ShouldHaveTheName($pos, $string) {
        $scenarios = $this->specification->getScenarios();
        $this->spec->assertEquals($string, $scenarios[$pos - 1]->getName());
    }

    public function thenScenario_ShouldHaveTheDescription($pos, $string) {
        $scenarios = $this->specification->getScenarios();
        $this->spec->assertEquals($this->trimLines($string), $scenarios[$pos - 1]->getDescription());
    }

    public function givenTheMethodPrefixIs($string) {
        $this->parser->METHOD_PREFIX = $string;
    }

    public function thenScenario_ShouldHaveTheContent($pos, $string) {
        $scenarios = $this->specification->getScenarios();
        $this->spec->assertEquals($this->trimLines($string), $scenarios[$pos - 1]->getContent());
    }

    private function trimLines($string) {
        $string = implode("\n", array_map(function ($line) {
            return trim($line);
        }, explode("\n", $string)));
        return $string;
    }

} 