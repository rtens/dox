<?php
namespace rtens\dox\web\root;

use rtens\dox\Configuration;
use rtens\dox\Parser;
use rtens\dox\ProjectConfiguration;
use watoki\curir\resource\Container;
use watoki\curir\responder\Redirecter;

class xxProjectResource extends Container {

    /** @var Configuration <- */
    public $config;

    /** @var Parser <- */
    public $parser;

    public function doGet() {
        $project = $this->getUrl()->getPath()->last();
        $config = $this->config->getProject($project);
        $start = $config->getStartSpecification();
        if ($start) {
            return new Redirecter($this->getUrl($start));
        }

        return json_encode($this->assembleModel($config), JSON_PRETTY_PRINT);
    }

    public function assembleNavigation(ProjectConfiguration $config) {
        $tree = $this->assembleSubTree($config->getFolder(), $config);
        $tree['name'] = $config->getName();
        return $tree;
    }

    private function assembleModel(ProjectConfiguration $config) {
        return array(
            'navigation' => $this->assembleNavigation($config)
        );
    }

    private function assembleSubTree($dir, ProjectConfiguration $config) {
        $tree = array(
            "name" => basename($dir),
            "folder" => array(),
            "specification" => array()
        );
        $fileSuffix = $config->getClassSuffix() . '.php';

        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $subTree = $this->assembleSubTree($file, $config);
                if ($subTree) {
                    $tree["folder"][] = $subTree;
                }
            } else if (substr($file, -strlen($fileSuffix)) == $fileSuffix) {
                $path = substr($file, strlen($config->getFolder()), -strlen($fileSuffix));
                $url = $this->getUrl() . $path;

                $tree["specification"][] = array(
                    "name" => $this->parser->uncamelize(substr(basename($file), 0, -strlen($fileSuffix))),
                    "href" => $url
                );
            }
        }
        return $tree['specification'] ? $tree : null;
    }

} 