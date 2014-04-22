<?php
namespace rtens\dox\content\item;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use rtens\dox\content\Item;

class StepsItem extends Item {

    public $groups;

    /**
     * @param Node $node
     * @return boolean
     */
    public function matches(Node $node) {
        return $node instanceof MethodCall
        && (substr($node->name, 0, 5) == 'given'
            || substr($node->name, 0, 4) == 'when'
            || substr($node->name, 0, 4) == 'then');
    }

    /**
     * @param Node[]|MethodCall[] $nodes
     * @return string
     */
    public function copy($nodes) {
        $item = new StepsItem();
        $item->groups = $this->parseSteps($nodes);
        return $item;
    }

    /**
     * @param MethodCall[] $nodes
     * @return string
     */
    private function parseSteps($nodes) {
        $map = array(
            'give' => 'context',
            'when' => 'action',
            'then' => 'assertion'
        );

        $groups = array();
        foreach ($nodes as $node) {
            $groups[$map[substr($node->name, 0, 4)]][] = $this->parseStep($node);
        }
        return $groups;
    }

    private function parseStep(MethodCall $step) {
        return array(
            'code' => $this->printer->prettyPrintExpr($step),
            'step' => $this->parseStepName($step)
        );
    }

    private function parseStepName(MethodCall $step) {
        $args = array_map(function (Node\Arg $arg) {
            $printed = $this->printer->pArg($arg);
            $printed = preg_replace('/_NO_INDENT_\d+/', '', $printed);
            return array('value' => $printed);
        }, $step->args);

        $structure = array();
        foreach (explode('_', $this->uncamelize($step->name)) as $part) {
            $structure[] = trim($part);
            if ($args) {
                $structure[] = array_shift($args);
            }
        }
        $structure = array_merge($structure, $args);
        return $structure;
    }

    private function uncamelize($string) {
        return str_replace(' i ', ' I ', ucfirst(trim(strtolower(preg_replace('/([A-Z])/', ' $1', $string)))));
    }
}