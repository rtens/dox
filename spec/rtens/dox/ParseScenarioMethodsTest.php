<?php
namespace spec\rtens\dox;

use spec\rtens\dox\fixtures\ParserFixture;
use watoki\scrut\Specification;

/**
 * @property ParserFixture parser <-
 */
class ParseScenarioMethodsTest extends Specification {

    public function testJustCode() {
        $this->parser->whenIParseTheMethodBody(
            '$some = 3;
            $code = $some + 1;'
        );
        $this->parser->thenTheScenarioShouldBe('[
            {
                "content": "$some = 3;\n$code = $some + 1;",
                "type": "code"
            }
        ]');
    }

    public function testJustComment() {
        $this->parser->whenIParseTheMethodBody('// Just a comment');
        $this->parser->thenTheScenarioShouldBe('[]');
    }

    public function testJustCommentHack() {
        $this->parser->whenIParseTheMethodBody(
            '// Just a comment
            null;'
        );
        $this->parser->thenTheScenarioShouldBe('[
            {
                "content": "Just a comment",
                "type": "comment"
            }
        ]');
    }

    public function testCodeWithLineComment() {
        $this->parser->whenIParseTheMethodBody(
            '// Some comment describing the code
            $code = 3 + 4;'
        );
        $this->parser->thenTheScenarioShouldBe('[
            {
                "content": "Some comment describing the code",
                "type": "comment"
            },
            {
                "content": "$code = 3 + 4;",
                "type": "code"
            }
        ]');
    }

    public function testCodeWithTwoLineComments() {
        $this->parser->whenIParseTheMethodBody(
            '// Some comment describing the code
            // Some more commenting
            $code = 3 + 4;
            $more = $code + 1;'
        );
        $this->parser->thenTheScenarioShouldBe('[
            {
                "content": "Some comment describing the code\n\nSome more commenting",
                "type": "comment"
            },
            {
                "content": "$code = 3 + 4;\n$more = $code + 1;",
                "type": "code"
            }
        ]');
    }

    public function testCodeWithBlockComment() {
        $this->parser->whenIParseTheMethodBody(
            '/*
              * This is some
              * *block* comment.
              */
            $code = 1 + 1;'
        );
        $this->parser->thenTheScenarioShouldBe('[
            {
                "content": "This is some\n*block* comment.",
                "type": "comment"
            },
            {
                "content": "$code = 1 + 1;",
                "type": "code"
            }
        ]');
    }

    public function testBlockCommentWithParagraphs() {
        $this->parser->whenIParseTheMethodBody(
            '/*
              * This is some comment.
              *
              * With two paragraphs.
              */
            $code = 1 + 1;'
        );
        $this->parser->thenTheScenarioShouldBe('[
            {
                "content": "This is some comment.\n\nWith two paragraphs.",
                "type": "comment"
            },
            {
                "content": "$code = 1 + 1;",
                "type": "code"
            }
        ]');
    }

    public function testMultipleCommentsAndCode() {
        $this->parser->whenIParseTheMethodBody(
            '// Some comment
            $code = 1 + 1;

            /*
             * More comments
             */
            $more = $code;'
        );
        $this->parser->thenTheScenarioShouldBe('[
            {
                "content": "Some comment",
                "type": "comment"
            },
            {
                "content": "$code = 1 + 1;",
                "type": "code"
            },
            {
                "content": "More comments",
                "type": "comment"
            },
            {
                "content": "$more = $code;",
                "type": "code"
            }
        ]');
    }

} 