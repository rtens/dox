<?php
namespace rtens\dox;

use PhpParser\Comment;
use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

class MarkdownParser {

    /** @var \PhpParser\PrettyPrinter\Standard */
    private $printer;

    /** @var \PhpParser\Parser */
    private $parser;

    function __construct() {
        $this->parser = new Parser(new Lexer());
        $this->printer = new Standard();
    }

    public function parse($code) {
        $stmts = $this->parser->parse($code);
        $out = $this->parseCommentsAndCode($stmts);
        return implode("\n\n", $out);
    }

    /**
     * @param Node[] $stmts
     * @return array|string[]
     */
    private function parseCommentsAndCode($stmts) {
        $out = array();
        $collectedStmts = array();

        foreach ($stmts as $stmt) {
            $comments = $stmt->getAttribute('comments');
            if ($comments) {
                if ($collectedStmts) {
                    $out[] = $this->printCodeBlock($collectedStmts);
                    $collectedStmts = array();
                }

                $out = array_merge($out, $this->parseComments($comments));
                $stmt->setAttribute('comments', array());
            }

            $collectedStmts[] = $stmt;
        }

        if ($collectedStmts) {
            $out[] = $this->printCodeBlock($collectedStmts);
        }
        return $out;
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
                return $line;
            };

            $filterLine = function ($line) {
                return $line != '*';
            };

            $out[] = implode("\n", array_filter(array_map($trimLine, $lines), $filterLine));
        }
        return $out;
    }

    /**
     * @param Node[] $stmts
     * @return string
     */
    private function printCodeBlock($stmts) {
        return "```php\n" . $this->printer->prettyPrint($stmts) . "\n```";
    }
}