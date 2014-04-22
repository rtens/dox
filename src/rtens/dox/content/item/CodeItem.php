<?php
namespace rtens\dox\content\item;

use PhpParser\Node;
use rtens\dox\content\Item;

class CodeItem extends Item {

    /**
     * @param Node $node
     * @return boolean
     */
    public function matches(Node $node) {
        return true;
    }

    /**
     * @param Node[] $nodes
     * @return string
     */
    public function toString($nodes) {
        return $this->printCodeBlock($nodes);
    }

    /**
     * @param Node[] $nodes
     * @return string
     */
    private function printCodeBlock($nodes) {
        if ($this->isIgnorable($nodes)) {
            return '';
        }
        return "```php\n" . $this->printer->prettyPrint($nodes) . "\n```";
    }

    private function isIgnorable($nodes) {
        if (count($nodes) != 1) {
            return false;
        }
        $node = $nodes[0];
        return $node instanceof Node\Expr\ConstFetch && (string)$node->name == 'null';
    }
}