<?php
namespace rtens\dox;

use rtens\dox\content\item\CommentItem;

class Specification {

    public $name;

    /** @var CommentItem */
    public $description;

    /** @var array|Scenario[] */
    public $scenarios = array();

    function __construct($name) {
        $this->name = $name;
    }

} 