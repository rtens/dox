<?php
namespace rtens\dox\content;

use PhpParser\Comment;
use PhpParser\Node;

class Content {

    public $items = array();

    /**
     * @param Node[] $nodes
     */
    function __construct($nodes) {
        $repo = new ItemRepository();
        $nodes = $this->normalize($nodes);
        $this->items = $this->parse($nodes, $repo->getItems());
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function normalize($nodes) {
        $normalized = array();
        foreach ($nodes as $node) {
            if ($node->getAttribute('comments')) {
                $normalized[] = $node;
                $node = clone $node;
                $node->setAttribute('comments', array());
            }
            $normalized[] = $node;
        }
        return $normalized;
    }

    /**
     * @param Node[] $nodes
     * @param Item[] $items
     * @return array|Item[]
     */
    private function parse($nodes, $items) {
        $nodes[] = null;

        /** @var Item $currentItem */
        $currentItem = null;
        $out = [];

        /** @var Node[] $buffer */
        $buffer = array();

        while ($nodes) {
            $node = array_shift($nodes);

            $nextItem = null;
            if ($node != null) {
                foreach ($items as $item) {
                    if ($item->matches($node)) {
                        $nextItem = $item;
                        break;
                    }
                }
            }

            if ($currentItem && ($nextItem != $currentItem || !$nodes)) {
                $out[] = $currentItem->copy($buffer);
                $buffer = array();
            }
            $currentItem = $nextItem;

            $buffer[] = $node;
        }

        return array_filter($out);
    }

}