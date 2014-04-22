<?php
namespace rtens\dox\content\item;

use PhpParser\Node;
use rtens\dox\content\Item;

class CodeItem extends Item {

    public $content;

    /**
     * @param Node $node
     * @return boolean
     */
    public function matches(Node $node) {
        return true;
    }

    /**
     * @param Node[] $nodes
     * @return Item
     */
    public function copy($nodes) {
        if ($this->isIgnorable($nodes)) {
            return null;
        }

        $item = new CodeItem();
        $item->content = $this->printer->prettyPrint($nodes);
        return $item;
    }

    private function isIgnorable($nodes) {
        if (count($nodes) != 1) {
            return false;
        }
        $node = $nodes[0];
        return $node instanceof Node\Expr\ConstFetch && (string)$node->name == 'null';
    }
}