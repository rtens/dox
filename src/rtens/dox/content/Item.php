<?php
namespace rtens\dox\content;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

abstract class Item {

    /** @var \PhpParser\PrettyPrinter\Standard */
    protected $printer;

    public $type;

    function __construct() {
        $this->printer = new Standard();
        $this->type = $this->getTypeName();
    }

    public function getTypeName() {
        return lcfirst(substr(get_class($this), strrpos(get_class($this), '\\') + 1, -strlen('Item')));
    }

    /**
     * @param Node $node
     * @return boolean
     */
    abstract public function matches(Node $node);

    /**
     * @param Node[] $nodes
     * @return Item
     */
    abstract public function copy($nodes);
}