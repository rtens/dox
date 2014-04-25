<?php
namespace rtens\dox;

use PhpParser\Comment;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser as PhpParser;
use rtens\dox\content\Content;
use rtens\dox\content\item\CommentItem;
use rtens\dox\model\Method;
use rtens\dox\model\Specification;

class Parser {

    public $CLASS_SUFFIX = 'Test';

    public $SCENARIO_PREFIX = 'test';

    /** @var \PhpParser\Parser */
    private $parser;

    /** @var \rtens\dox\content\item\CommentItem */
    private $commentItem;

    function __construct() {
        $this->parser = new PhpParser(new Lexer());
        $this->commentItem = new CommentItem();
    }

    public function parse($code) {
        $nodes = $this->parser->parse($code);
        $classStmt = $this->findClassStatement($nodes);
        return $this->parseSpecification($classStmt);
    }

    /**
     * @param Node|Node[] $in
     * @throws \Exception If class statement cannot be found
     * @return Node\Stmt\Class_
     */
    private function findClassStatement($in) {
        foreach ($in as $node) {
            if ($node instanceof Node\Stmt\Class_) {
                return $node;
            }
            try {
                return $this->findClassStatement($node);
            } catch (\Exception $e) {
            }
        }
        throw new \Exception('Could not find class definition');
    }

    /**
     * @param Node\Stmt\Class_ $classStmt
     * @return Specification
     */
    private function parseSpecification($classStmt) {
        $name = $this->uncamelize(substr($classStmt->name, 0, -strlen($this->CLASS_SUFFIX)));
        $specification = new Specification($name);

        if ($classStmt->getAttribute('comments')) {
            $specification->description = $this->commentItem->copy(array($classStmt));
        }

        $this->parseMethods($classStmt, $specification);

        return $specification;
    }

    private function parseMethods(Node\Stmt\Class_ $class, Specification $specification) {
        $scenarios = array();
        foreach ($class->stmts as $methodStmt) {
            if ($methodStmt instanceof Node\Stmt\ClassMethod && $methodStmt->isPublic()) {
                $name = $methodStmt->name;

                $isScenario = substr($name, 0, strlen($this->SCENARIO_PREFIX)) == $this->SCENARIO_PREFIX;
                if ($isScenario) {
                    $name = $this->uncamelize(substr($name, strlen($this->SCENARIO_PREFIX)));
                }

                $method = new Method($name);

                if ($methodStmt->getAttribute('comments')) {
                    $method->description = $this->commentItem->copy(array($methodStmt));
                }
                $method->content = new Content($methodStmt->stmts);

                if ($isScenario) {
                    $specification->scenarios[] = $method;
                } else {
                    $specification->methods[] = $method;
                }
            }

        }
        return $scenarios;
    }

    public function uncamelize($string) {
        return str_replace(' i ', ' I ', ucfirst(trim(strtolower(preg_replace('/([A-Z])/', ' $1', $string)))));
    }
}