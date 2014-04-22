<?php
namespace rtens\dox\content;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

abstract class Item {

    /** @var \PhpParser\PrettyPrinter\Standard */
    protected $printer;

    function __construct() {
        $this->printer = new Standard();
    }

    /**
     * @param Node $node
     * @return boolean
     */
    abstract public function matches(Node $node);

    /**
     * @param Node[] $nodes
     * @return string
     */
    abstract public function toString($nodes);
}