<?php
namespace rtens\dox\web;

use watoki\tempan\Renderer;

class Presenter extends \watoki\curir\responder\Presenter {

    public function renderJson() {
        return json_encode($this->getModel(), JSON_PRETTY_PRINT);
    }

    public function renderHtml($template) {
        $renderer = new Renderer($template);
        return $renderer->render($this->getModel());
    }

} 