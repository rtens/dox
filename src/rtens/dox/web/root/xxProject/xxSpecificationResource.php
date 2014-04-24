<?php
namespace rtens\dox\web\root\xxProject;

use rtens\dox\Configuration;
use rtens\dox\content\item\CodeItem;
use rtens\dox\content\item\CommentItem;
use rtens\dox\content\Item;
use rtens\dox\content\item\StepsItem;
use rtens\dox\Reader;
use rtens\dox\Specification;
use rtens\dox\web\Presenter;
use rtens\dox\web\root\xxProjectResource;
use watoki\curir\http\Path;
use watoki\curir\http\Request;
use watoki\curir\resource\DynamicResource;
use watoki\curir\Responder;

class xxSpecificationResource extends DynamicResource {

    /** @var Configuration <- */
    public $config;

    /** @var \Parsedown <- */
    public $markdown;

    /** @var Path */
    private $path;

    public function respond(Request $request) {
        $this->path = $request->getTarget()->copy();
        $this->path->insert($this->getUrl()->getPath()->last(), 0);

        return parent::respond($request);
    }


    public function doGet() {
        $project = $this->getUrl()->getPath()->get(-2);
        $reader = new Reader($this->config->getProject($project));
        $specification = $reader->readSpecification($this->path);

        return new Presenter($this, $this->assembleModel($specification, $project));
    }

    private function assembleModel(Specification $specification, $project) {
        return array(
            'back' => array('href' => '../' . $project),
            'specification' => $this->assembleSpecification($specification),
            'navigation' => $this->getParent()->assembleNavigation($this->config->getProject($project))
        );
    }

    /**
     * @return xxProjectResource
     */
    public function getParent() {
        return parent::getParent();
    }

    private function assembleSpecification(Specification $specification) {
        return array(
            'name' => $specification->name,
            'description' => $this->asHtml(array($specification->description)),
            'scenario' => $this->assembleScenarios($specification)
        );
    }

    private function assembleScenarios(Specification $specification) {
        $scenarios = array();
        foreach ($specification->scenarios as $scenario) {
            $scenarios[] = array(
                'name' => $scenario->name,
                'description' => $this->asHtml(array($scenario->description)),
                'content' => $this->asHtml($scenario->content->items)
            );
        }
        return $scenarios;
    }

    /**
     * @param Item[] $items
     * @return null|string
     */
    private function asHtml($items) {
        $out = array();
        foreach ($items as $item) {
            if ($item instanceof CommentItem && $item->content) {
                $out[] = $this->markdown->text($item->content);
            } else if ($item instanceof CodeItem) {
                $out[] = "<pre><code>" . htmlentities($item->content) . "</code></pre>";
            } else if ($item instanceof StepsItem) {
                $groupElements = array();
                foreach ($item->groups as $steps) {
                    $stepElements = array();
                    foreach ($steps as $step) {
                        $assembled = array();
                        foreach ($step['step'] as $part) {
                            if (is_array($part)) {
                                $assembled[] = '<span class="arg">' . $part['value'] . '</span>';
                            } else {
                                $assembled[] = $part;
                            }
                        }
                        $stepElements[] = '<div title="' . htmlentities($step['code']) . '" class="step">'
                            . implode(" ", $assembled) . '</div>';
                    }
                    $groupElements[] = '<div class="step-group">' . "\n"
                        . implode("\n", $stepElements)  . "\n"
                        . '</div>';
                }
                $out[] = '<div class="steps">' . "\n"
                    . implode("\n", $groupElements) . "\n"
                    . '</div>';
            }
        }
        return $out ? implode("\n", $out) : null;

    }

}