<?php
namespace rtens\dox\web\root\projects;

use rtens\dox\Configuration;
use rtens\dox\Parser;
use rtens\dox\Project;
use rtens\dox\VcsService;
use rtens\dox\web\Presenter;
use rtens\dox\web\RootResource;
use watoki\curir\resource\Container;

class xxProjectResource extends Container {

    public static $CLASS = __CLASS__;

    /** @var Configuration <- */
    public $config;

    /** @var Parser <- */
    public $parser;

    /** @var VcsService <- */
    public $vcs;

    protected function getPlaceholderKey() {
        return 'projectName';
    }

    public function doGet($projectName) {
        return new Presenter($this, $this->assembleModel($this->config->getProject($projectName)));
    }

    public function doPost($projectName) {
        $project = $this->config->getProject($projectName);
        $this->vcs->update($project);
        return 'OK - Updated ' . $project->getName();
    }

    private function assembleModel(Project $project) {
        return array(
            'back' => array('href' => $this->getAncestor(RootResource::$CLASS)->getUrl('home')->toString()),
            'navigation' => $this->assembleNavigation($project)
        );
    }

    public function assembleNavigation(Project $project) {
        $dir = $project->getFullSpecFolder();
        if (!file_exists($dir)) {
            $this->vcs->checkout($project);
        }

        $list = array(
            "name" => $project->getName(),
            "folder" => $this->assembleFolders($dir, $project),
            "specification" => $this->assembleSpecificationList($dir, $project)
        );
        return $list;
    }

    private function assembleSpecificationList($dir, Project $project) {
        $fileSuffix = $project->getClassSuffix() . '.php';

        $list = array();
        foreach (glob($dir . '/*') as $file) {
            if (!is_dir($file) && substr($file, -strlen($fileSuffix)) == $fileSuffix) {
                $path = substr($file, strlen($project->getFullSpecFolder()) + 1, -strlen($fileSuffix));
                $url = $this->getUrl('specs/' . str_replace(array('\\', '/'), '__', $path))->toString();

                $name = substr(basename($file), 0, -strlen($fileSuffix));
                if (!$name) {
                    continue;
                }

                $list[] = array(
                    "name" => $this->parser->uncamelize($name),
                    "href" => $url
                );
            }
        }
        return $list;
    }

    private function assembleFolders($dir, Project $project) {
        $list = array();
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $specifications = $this->assembleSpecificationList($file, $project);
                if ($specifications) {
                    $list[] = array(
                        'name' => substr($file, strlen($project->getFullSpecFolder())+1),
                        'specification' => $specifications
                    );
                }
                $list = array_merge($list, $this->assembleFolders($file, $project));
            }
        }
        return $list;
    }

}