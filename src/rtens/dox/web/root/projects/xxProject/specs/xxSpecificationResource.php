<?php
namespace rtens\dox\web\root\projects\xxProject\specs;

use rtens\dox\Configuration;
use rtens\dox\content\item\CodeItem;
use rtens\dox\content\item\CommentItem;
use rtens\dox\content\Item;
use rtens\dox\content\item\StepsItem;
use rtens\dox\model\Method;
use rtens\dox\model\Specification;
use rtens\dox\Reader;
use rtens\dox\Report;
use rtens\dox\web\Presenter;
use rtens\dox\web\root\projects\xxProjectResource;
use watoki\curir\resource\DynamicResource;
use watoki\curir\Responder;
use watoki\dom\Element;

class xxSpecificationResource extends DynamicResource {

    /** @var Configuration <- */
    public $config;

    /** @var \Parsedown <- */
    public $markdown;

    /** @var Report <- */
    public $report;

    protected function getPlaceholderKey() {
        return 'path';
    }

    public function doGet($projectName, $path) {
        $reader = new Reader($this->config->getProject($projectName));
        $specification = $reader->readSpecification(str_replace('__', '/', $path));

        return new Presenter($this, $this->assembleModel($specification, $projectName));
    }

    private function assembleModel(Specification $specification, $projectName) {
        return array(
            'back' => array('href' => $this->getProjectResource()->getUrl()->toString()),
            'specification' => $this->assembleSpecification($specification, $projectName),
            'navigation' => $this->getProjectResource()->assembleNavigation($this->config->getProject($projectName))
        );
    }

    private function assembleSpecification(Specification $specification, $projectName) {
        return array(
            'name' => $specification->name,
            'description' => $this->asHtml(array($specification->description)),
            'scenario' => $this->assembleScenarios($specification, $projectName),
            'method' => $this->assembleMethods($specification)
        );
    }

    private function assembleScenarios(Specification $specification, $projectName) {
        $scenarios = array();
        foreach ($specification->scenarios as $scenario) {
            $scenarioModel = $this->assembleMethod($scenario);

            $status = $this->report->getStatus($projectName, $specification->path, $scenario->key);
            $scenarioModel['status'] = $status;
            $scenarioModel['class'] = function (Element $element) use ($status) {
                $classes = array(
                    Report::STATUS_FAILING => 'danger',
                    Report::STATUS_PASSING => 'success',
                    Report::STATUS_PENDING => 'warning',
                    Report::STATUS_UNKNOWN => 'default'
                );
                return str_replace('default', $classes[$status], $element->getAttribute('class')->getValue());
            };

            $scenarios[] = $scenarioModel;
        }
        return $scenarios;
    }

    private function assembleMethods($specification) {
        $scenarios = array();
        foreach ($specification->methods as $scenario) {
            $scenarios[] = $this->assembleMethod($scenario);
        }
        return $scenarios;
    }

    /**
     * @param Method $method
     * @return array
     */
    private function assembleMethod($method) {
        return array(
            'name' => $method->name,
            'description' => $this->asHtml(array($method->description)),
            'content' => $this->asHtml($method->content->items)
        );
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
                                $assembled[] = '<span class="arg">' . htmlentities($part['value']) . '</span>';
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

    /**
     * @return xxProjectResource
     */
    private function getProjectResource() {
        return $this->getAncestor(xxProjectResource::$CLASS);
    }

}