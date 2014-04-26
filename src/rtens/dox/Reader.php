<?php
namespace rtens\dox;

class Reader {

    private $parser;
    private $config;

    function __construct(Project $config) {
        $this->config = $config;
        $this->parser = new Parser();
    }

    public function readSpecification($path) {
        $file = $this->config->getFullSpecFolder() . '/' . $path . $this->parser->CLASS_SUFFIX . '.php';
        return $this->parser->parse(file_get_contents($file));
    }

} 