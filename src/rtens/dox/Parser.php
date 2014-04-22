<?php
namespace rtens\dox;

use PhpParser\Comment;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser as PhpParser;
use rtens\dox\content\Content;
use rtens\dox\content\item\CommentItem;

class Parser {

    public $CLASS_SUFFIX = 'Test';

    public $METHOD_PREFIX = 'test';

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

        foreach ($this->parseScenarios($classStmt) as $scenario) {
            $specification->scenarios[] = $scenario;
        }

        return $specification;
    }

    private function parseScenarios(Node\Stmt\Class_ $class) {
        $scenarios = array();
        foreach ($class->stmts as $method) {
            if ($method instanceof Node\Stmt\ClassMethod
                && $method->isPublic()
                && substr($method->name, 0, strlen($this->METHOD_PREFIX)) == $this->METHOD_PREFIX
            ) {
                $scenario = new Scenario($this->uncamelize(substr($method->name, strlen($this->METHOD_PREFIX))));

                if ($method->getAttribute('comments')) {
                    $scenario->description = $this->commentItem->copy(array($method));
                }

                $scenario->content = new Content($method->stmts);

                $scenarios[] = $scenario;
            }

        }
        return $scenarios;
    }

    public function uncamelize($string) {
        return str_replace(' i ', ' I ', ucfirst(trim(strtolower(preg_replace('/([A-Z])/', ' $1', $string)))));
    }
}