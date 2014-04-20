<?php
namespace spec\rtens\dox\fixtures;

use watoki\scrut\Fixture;

class FileFixture extends Fixture {

    public function tmpDir() {
        return __DIR__ . '/tmp';
    }

    protected function setUp() {
        parent::setUp();

        $this->cleanUp();
        mkdir($this->tmpDir(), 0777, true);

        $this->spec->undos[] = function () {
            $this->cleanUp();
        };
    }

    private function delete($dir) {
        foreach (glob($dir . '/*') as $file) {
            if (is_file($file)) {
                @unlink($file);
            } else {
                $this->delete($file);
            }
        }
        @rmdir($dir);
    }

    public function cleanUp() {
        $this->delete($this->tmpDir());
    }

    public function givenTheFolder($name) {
        $folder = $this->tmpDir() . '/' . $name;
        @mkdir($folder, 0777, true);
        $this->spec->undos[] = function () use ($folder) {
            @rmdir($folder);
        };
    }

    public function givenTheFile_WithContent($path, $content) {
        $this->givenTheFolder(dirname($path));

        $file = $this->tmpDir() . '/' . $path;
        file_put_contents($file, $content);
        $this->spec->undos[] = function () use ($file) {
            @unlink($file);
        };
    }

} 