<?php
namespace rtens\dox;

use rtens\dox\content\Content;
use rtens\dox\content\item\CommentItem;

class Scenario {

    /** @var string */
    public $name;

    /** @var CommentItem */
    public $description;

    /** @var null|Content */
    public $content;

    function __construct($name) {
        $this->name = $name;
    }
} 