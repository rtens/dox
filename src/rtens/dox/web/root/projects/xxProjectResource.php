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

    /** @var \Parsedown <- */
    public $markdown;

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
        $readmeText = $this->markdown->text($project->getReadmeText());
        return array(
            'back' => array('href' => $this->getAncestor(RootResource::$CLASS)->getUrl('home')->toString()),
            'navigation' => $this->assembleNavigation($project, array($this, 'decorateSpec')),
            'readme' => $readmeText ? array(
                    'text' => $readmeText
                ) : null
        );
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function decorateSpec($file) {
        $description = $this->parser->getSpecificationDescription(file_get_contents($file));
        $description = strip_tags($this->markdown->text($description), '<strong><em>');

        return array(
            'description' => $description
        );
    }

    public function assembleNavigation(Project $project, $specDecorator = null) {
        $dir = $project->getFullSpecFolder();
        if (!file_exists($dir)) {
            $this->vcs->checkout($project);
        }

        $list = array(
            "name" => $project->getName(),
            "folder" => $this->assembleFolders($dir, $project, $specDecorator),
            "specification" => $this->assembleSpecificationList($dir, $project, $specDecorator)
        );
        return $list;
    }

    private function assembleSpecificationList($dir, Project $project, $specDecorator) {
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

                $specModel = array(
                    "name" => $this->parser->uncamelize($name),
                    "href" => $url
                );
                if ($specDecorator) {
                    $specModel = array_merge($specModel, $specDecorator($file));
                }
                $list[] = $specModel;
            }
        }
        return $list;
    }

    private function assembleFolders($dir, Project $project, $specDecorator) {
        $list = array();
        foreach (glob($dir . '/*') as $file) {
            if (is_dir($file)) {
                $specifications = $this->assembleSpecificationList($file, $project, $specDecorator);
                if ($specifications) {
                    $list[] = array(
                        'name' => substr($file, strlen($project->getFullSpecFolder()) + 1),
                        'specification' => $specifications
                    );
                }
                $list = array_merge($list, $this->assembleFolders($file, $project, $specDecorator));
            }
        }
        return $list;
    }

}