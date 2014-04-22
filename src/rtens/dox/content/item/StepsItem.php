<?php
namespace rtens\dox\content\item;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use rtens\dox\content\Item;

class StepsItem extends Item {

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
     * @param Node[] $nodes
     * @return string
     */
    public function toString($nodes) {
        return $this->printSteps($nodes);
    }

    /**
     * @param MethodCall[] $nodes
     * @return string
     */
    private function printSteps($nodes) {
        $map = array(
            'give' => 'context',
            'when' => 'action',
            'then' => 'assertion'
        );

        $groups = array();
        foreach ($nodes as $node) {
            $groups[substr($node->name, 0, 4)][] = $this->printStep($node);
        }

        $out = '<div class="steps">';
        foreach ($groups as $key => $steps) {
            $out .= "\n" . '<div class="step-group ' . $map[$key] . '">' . "\n";
            $out .= implode("\n", $steps);
            $out .= '</div>' . "";
        }
        return $out . '</div>';
    }

    private function printStep(MethodCall $step) {
        $code = htmlentities($this->printer->prettyPrintExpr($step));

        return '<div class="step" title="' . $code . '">'
        . $this->printStepName($step) . '</div>';
    }

    private function printStepName(MethodCall $step) {
        $args = array_map(function (Node\Arg $arg) {
            $printed = $this->printer->pArg($arg);
            $printed = preg_replace('/_NO_INDENT_\d+/', '', $printed);
            return ' <span class="arg">' . $printed . '</span>';
        }, $step->args);

        $uncamelized = $this->uncamelize($step->name);

        while ($args) {
            if (strpos($uncamelized, '_') !== false) {
                $uncamelized = preg_replace('/_/', array_shift($args), $uncamelized, 1);
            } else {
                $uncamelized .= array_shift($args);
            }
        }

        return $uncamelized;
    }

    private function uncamelize($string) {
        return str_replace(' i ', ' I ', ucfirst(trim(strtolower(preg_replace('/([A-Z])/', ' $1', $string)))));
    }
}