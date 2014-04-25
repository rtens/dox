<?php
namespace rtens\dox;

use watoki\collections\Liste;

class Reader {

    private $parser;
    private $config;

    function __construct(ProjectConfiguration $config) {
        $this->config = $config;
        $this->parser = new Parser();
    }

    public function readSpecification(Liste $path) {
        $file = $this->config->getFullSpecFolder() . '/' . $path->join('/') . $this->parser->CLASS_SUFFIX . '.php';
        return $this->parser->parse(file_get_contents($file));
    }

} 