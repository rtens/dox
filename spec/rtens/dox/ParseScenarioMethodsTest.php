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
        $this->parser->thenTheScenarioShouldBe(
            '```php
            $some = 3;
            $code = $some + 1;
            ```'
        );
    }

    public function testJustComment() {
        $this->parser->whenIParseTheMethodBody('// Just a comment');
        $this->parser->thenTheScenarioShouldBe('');
    }

    public function testJustCommentHack() {
        $this->parser->whenIParseTheMethodBody(
            '// Just a comment
            null;'
        );
        $this->parser->thenTheScenarioShouldBe(
            'Just a comment'
        );
    }

    public function testCodeWithLineComment() {
        $this->parser->whenIParseTheMethodBody(
            '// Some comment describing the code
            $code = 3 + 4;'
        );
        $this->parser->thenTheScenarioShouldBe(
            'Some comment describing the code

            ```php
            $code = 3 + 4;
            ```'
        );
    }

    public function testCodeWithTwoLineComments() {
        $this->parser->whenIParseTheMethodBody(
            '// Some comment describing the code
            // Some more commenting
            $code = 3 + 4;
            $more = $code + 1;'
        );
        $this->parser->thenTheScenarioShouldBe(
            'Some comment describing the code

            Some more commenting

            ```php
            $code = 3 + 4;
            $more = $code + 1;
            ```'
        );
    }

    public function testCodeWithBlockComment() {
        $this->parser->whenIParseTheMethodBody(
            '/*
              * This is some
              * *block* comment.
              */
            $code = 1 + 1;'
        );
        $this->parser->thenTheScenarioShouldBe(
            'This is some
            *block* comment.

            ```php
            $code = 1 + 1;
            ```'
        );
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
        $this->parser->thenTheScenarioShouldBe(
            'This is some comment.

            With two paragraphs.

            ```php
            $code = 1 + 1;
            ```'
        );
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
        $this->parser->thenTheScenarioShouldBe(
            'Some comment

            ```php
            $code = 1 + 1;
            ```

            More comments

            ```php
            $more = $code;
            ```'
        );
    }

} 