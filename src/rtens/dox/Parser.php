<?php
namespace rtens\dox;

use PhpParser\Comment;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser as PhpParser;
use PhpParser\PrettyPrinter\Standard;

class Parser {

    public $CLASS_SUFFIX = 'Test';

    public $METHOD_PREFIX = 'test';

    /** @var \PhpParser\PrettyPrinter\Standard */
    private $printer;

    /** @var \PhpParser\Parser */
    private $parser;

    function __construct() {
        $this->parser = new PhpParser(new Lexer());
        $this->printer = new Standard();
    }

    public function parse($code) {
        $stmts = $this->parser->parse($code);
        $classStmt = $this->findClassStatement($stmts);
        return $this->parseSpecification($classStmt);
    }

    /**
     * @param Node[] $stmts
     * @throws \Exception If class statement cannot be found
     * @return Node\Stmt\Class_
     */
    private function findClassStatement($stmts) {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Class_) {
                return $stmt;
            }
            try {
                return $this->findClassStatement($stmt);
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
            $specification->setDescription($this->parseComments($classStmt->getAttribute('comments')));
        }

        foreach ($this->parseScenarios($classStmt) as $scenario) {
            $specification->addScenario($scenario);
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
                    $scenario->setDescription($this->parseComments($method->getAttribute('comments')));
                }

                $scenario->setContent($this->parseMethodBody($method->stmts));

                $scenarios[] = $scenario;
            }

        }
        return $scenarios;
    }

    /**
     * @param Node[] $stmts
     * @return array|string[]
     */
    private function parseMethodBody($stmts) {
        $out = array();
        $collectedStmts = array();

        foreach ($stmts as $stmt) {
            $comments = $stmt->getAttribute('comments');
            if ($comments) {
                if ($collectedStmts) {
                    $out[] = $this->printCodeBlock($collectedStmts);
                    $collectedStmts = array();
                }

                $out[] = $this->parseComments($comments);
                $stmt->setAttribute('comments', array());
            }

            $collectedStmts[] = $stmt;
        }

        if ($collectedStmts && !$this->isIgnorable($collectedStmts)) {
            $out[] = $this->printCodeBlock($collectedStmts);
        }
        return implode("\n\n", $out);
    }

    private function isIgnorable($stmts) {
        if (count($stmts) != 1) {
            return false;
        }
        $stmt = $stmts[0];
        return $stmt instanceof Node\Expr\ConstFetch && (string)$stmt->name == 'null';
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

    /**
     * @param Node[] $stmts
     * @return string
     */
    private function printCodeBlock($stmts) {
        return "```php\n" . $this->printer->prettyPrint($stmts) . "\n```";
    }

    public function uncamelize($string) {
        return ucfirst(trim(strtolower(preg_replace('/([A-Z])/', ' $1', $string))));
    }
}