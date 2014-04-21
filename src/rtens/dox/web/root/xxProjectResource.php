<?php
namespace rtens\dox\web\root;

use rtens\dox\Configuration;
use rtens\dox\Parser;
use rtens\dox\ProjectConfiguration;
use rtens\dox\web\Presenter;
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

        return new Presenter($this, $this->assembleModel($config));
    }

    private function assembleModel(ProjectConfiguration $config) {
        return array(
            'navigation' => $this->assembleNavigation($config)
        );
    }

    public function assembleNavigation(ProjectConfiguration $config) {
        $list= array(
            "name" => $config->getName(),
            "folder" => $this->assembleFolders($config->getFolder(), $config),
            "specification" => $this->assembleSpecificationList($config->getFolder(), $config)
        );
        return $list;
    }

    private function assembleSpecificationList($dir, ProjectConfiguration $config) {
        $fileSuffix = $config->getClassSuffix() . '.php';

        $list = array();
        foreach (glob($dir . '/*') as $file) {
            if (!is_dir($file) && substr($file, -strlen($fileSuffix)) == $fileSuffix) {
                $path = substr($file, strlen($config->getFolder()), -strlen($fileSuffix));
                $url = $this->getUrl() . $path;

                $list[] = array(
                    "name" => $this->parser->uncamelize(substr(basename($file), 0, -strlen($fileSuffix))),
                    "href" => $url
                );
            }
        }
        return $list;
    }

    private function assembleFolders($dir, ProjectConfiguration $config) {
        $list = array();
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $specifications = $this->assembleSpecificationList($file, $config);
                if ($specifications) {
                    $list[] = array(
                        'name' => substr($file, strlen($config->getFolder())+1),
                        'specification' => $specifications
                    );
                }
                $list = array_merge($list, $this->assembleFolders($file, $config));
            }
        }
        return $list;
    }

} 