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

    private function assembleModel(ProjectConfiguration $config) {
        $tree = $this->assembleNavigationTree($config->getFolder(), $config);
        $tree['name'] = $config->getName();

        return array(
            'navigation' => $tree
        );
    }

    private function assembleNavigationTree($dir, ProjectConfiguration $config) {
        $tree = array(
            "name" => basename($dir),
            "folder" => array(),
            "specification" => array()
        );
        $fileSuffix = $config->getClassSuffix() . '.php';

        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $tree["folder"][] = $this->assembleNavigationTree($file, $config);
            } else if (substr($file, -strlen($fileSuffix)) == $fileSuffix) {
                $path = substr($file, strlen($config->getFolder()), -strlen($fileSuffix));
                $url = $this->getUrl() . $path;

                $tree["specification"][] = array(
                    "name" => $this->parser->uncamelize(substr(basename($file), 0, -strlen($fileSuffix))),
                    "href" => $url
                );
            }
        }
        return $tree;
    }

} 