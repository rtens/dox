<?php
namespace rtens\dox;

use PhpParser\Comment;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

class MarkdownParser {

    public function parse($code) {
        $parser = new Parser(new Lexer());
        $printer = new Standard();

        $stmts = $parser->parse($code);
        $out = array();
        $collectedStmts = array();

        foreach ($stmts as $stmt) {
            /** @var Comment[] $comments */
            $comments = $stmt->getAttribute('comments');
            if ($comments) {
                if ($collectedStmts) {
                    $out[] = "```php\n" . $printer->prettyPrint($collectedStmts) . "\n```";
                    $collectedStmts = array();
                }

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
                $stmt->setAttribute('comments', array());
            }

            $collectedStmts[] = $stmt;
        }

        if ($collectedStmts) {
            $out[] = "```php\n" . $printer->prettyPrint($collectedStmts) . "\n```";
        }

        return implode("\n\n", $out);
    }
}