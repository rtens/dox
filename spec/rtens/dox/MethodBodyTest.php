<?php
namespace spec\rtens\dox;

use rtens\dox\MarkdownParser;
use watoki\scrut\Specification;

/**
 * @property string out
 */
class MethodBodyTest extends Specification {

    public function testJustCode() {
        $this->whenIParse(
            '$some = 3;
            $code = $some + 1;'
        );
        $this->thenTheOutputShouldBe(
            '```php
            $some = 3;
            $code = $some + 1;
            ```'
        );
    }

    public function testJustComment() {
        $this->whenIParse('// Just a comment');
        $this->thenTheOutputShouldBe('');
    }

    public function testCodeWithLineComment() {
        $this->whenIParse(
            '// Some comment describing the code
            $code = 3 + 4;'
        );
        $this->thenTheOutputShouldBe(
            'Some comment describing the code

            ```php
            $code = 3 + 4;
            ```'
        );
    }

    public function testCodeWithTwoLineComments() {
        $this->whenIParse(
            '// Some comment describing the code
            // Some more commenting
            $code = 3 + 4;
            $more = $code + 1;'
        );
        $this->thenTheOutputShouldBe(
            'Some comment describing the code

            Some more commenting

            ```php
            $code = 3 + 4;
            $more = $code + 1;
            ```'
        );
    }

    public function testCodeWithBlockComment() {
        $this->whenIParse(
            '/*
              * This is some
              * *block* comment.
              */
            $code = 1 + 1;'
        );
        $this->thenTheOutputShouldBe(
            'This is some
            *block* comment.

            ```php
            $code = 1 + 1;
            ```'
        );
    }

    public function testMultipleCommentsAndCode() {
        $this->whenIParse(
            '// Some comment
            $code = 1 + 1;

            /*
             * More comments
             */
            $more = $code;'
        );
        $this->thenTheOutputShouldBe(
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

    private function whenIParse($code) {
        $parser = new MarkdownParser();
        $this->out = $parser->parse('<?php ' . $this->trimLines($code));
    }

    private function thenTheOutputShouldBe($string) {
        $this->assertEquals($this->trimLines($string), $this->out);
    }

    private function trimLines($string) {
        $string = implode("\n", array_map(function ($line) {
            return trim($line);
        }, explode("\n", $string)));
        return $string;
    }

} 