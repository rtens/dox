<?php
namespace rtens\dox\content\item;

use PhpParser\Comment;
use PhpParser\Node;
use rtens\dox\content\Item;

class CommentItem extends Item {

    public $content;

    /**
     * @param Node $node
     * @return boolean
     */
    public function matches(Node $node) {
        return $node->getAttribute('comments');
    }

    /**
     * @param Node[] $nodes
     * @return Item
     */
    public function copy($nodes) {
        $item = new CommentItem();
        $item->content = $this->parseComments($nodes[0]->getAttribute('comments'));
        return $item;
    }

    /**
     * @param Comment[] $comments
     * @return array
     */
    private function parseComments($comments) {
        $out = array();
        foreach ($comments as $comment) {
            $lines = explode("\n", trim($comment->getText()));

            $trimLine = function ($line) {
                $line = trim($line, "/ ");
                if (substr($line, 0, 2) == '* ') {
                    $line = substr($line, 2);
                }
                if (!trim($line, '*') || substr($line, 0, 1) == '@') {
                    $line = '';
                }
                return $line;
            };

            $out[] = trim(implode("\n", array_map($trimLine, $lines)));
        }
        return implode("\n\n", $out);
    }
}