<?php
namespace rtens\dox;

class Specification {

    private $name;

    private $description;

    /** @var array|Scenario[] */
    private $scenarios = array();

    function __construct($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getScenarios() {
        return $this->scenarios;
    }

    public function addScenario(Scenario $scenario) {
        $this->scenarios[] = $scenario;
    }

} 