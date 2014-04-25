<?php
namespace spec\rtens\dox\fixtures;

use rtens\dox\Parser;
use watoki\factory\Factory;
use watoki\scrut\Fixture;
use watoki\scrut\Specification;

/**
 * @property \rtens\dox\model\Specification $specification
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

    public function thenThereShouldBeASpecificationWithTheName($name) {
        $this->spec->assertTrue($this->specification instanceof \rtens\dox\model\Specification);
        $this->spec->assertEquals($name, $this->specification->name);
    }

    public function thenTheSpecificationShouldHaveTheDescription($string) {
        $this->spec->assertEquals($this->trimLines($string), $this->specification->description->content);
    }

    public function thenThereShouldBe_Scenarios($int) {
        $this->spec->assertCount($int, $this->specification->scenarios);
    }

    public function thenScenario_ShouldHaveTheName($pos, $string) {
        $scenarios = $this->specification->scenarios;
        $this->spec->assertEquals($string, $scenarios[$pos - 1]->name);
    }

    public function thenScenario_ShouldHaveTheDescription($pos, $string) {
        $scenarios = $this->specification->scenarios;
        $this->spec->assertEquals($this->trimLines($string), $scenarios[$pos - 1]->description->content);
    }

    public function givenTheMethodPrefixIs($string) {
        $this->parser->SCENARIO_PREFIX = $string;
    }

    public function thenTheScenarioShouldBe($json) {
        $this->thenScenario_ShouldHaveTheContent(1, $json);
    }

    public function thenTheScenarioShouldContain($json) {
        $decoded = json_decode($json);
        if ($decoded === null) {
            $this->spec->fail("Invalid JSON: " . $json);
        }
        $scenarios = $this->specification->scenarios;
        $this->spec->assertContains($this->trimLines(json_encode($decoded, JSON_PRETTY_PRINT)),
            $this->trimLines(json_encode($scenarios[0]->content->items, JSON_PRETTY_PRINT)));
    }

    public function thenScenario_ShouldHaveTheContent($pos, $json) {
        $decoded = json_decode($json);
        if ($decoded === null) {
            $this->spec->fail("Invalid JSON: " . $json);
        }
        $scenarios = $this->specification->scenarios;
        $this->spec->assertEquals($this->trimLines(json_encode($decoded, JSON_PRETTY_PRINT)),
            $this->trimLines(json_encode($scenarios[$pos - 1]->content->items, JSON_PRETTY_PRINT)));
    }

    public function thenThereShouldBe_Methods($int) {
        $this->spec->assertCount($int, $this->specification->methods);
    }

    private function trimLines($string) {
        $string = implode("\n", array_map(function ($line) {
            return trim($line);
        }, explode("\n", $string)));
        return $string;
    }

    public function thenMethod_ShouldHaveTheName($pos, $name) {
        $this->spec->assertEquals($name, $this->specification->methods[$pos - 1]->name);
    }

} 