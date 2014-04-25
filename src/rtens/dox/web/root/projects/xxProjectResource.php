<?php
namespace rtens\dox\web\root\projects;

use rtens\dox\Configuration;
use rtens\dox\Executer;
use rtens\dox\Parser;
use rtens\dox\ProjectConfiguration;
use rtens\dox\web\Presenter;
use rtens\dox\web\RootResource;
use watoki\curir\resource\Container;

class xxProjectResource extends Container {

    public static $CLASS = __CLASS__;

    /** @var Configuration <- */
    public $config;

    /** @var Parser <- */
    public $parser;

    /** @var Executer <- */
    public $executer;

    public function doGet() {
        return new Presenter($this, $this->assembleModel($this->getProjectConfig()));
    }

    public function doPost() {
        $config = $this->getProjectConfig();
        $error = $this->executer->execute('cd ' . $config->getFullProjectFolder() . ' && git pull origin master');

        if ($error) {
            throw new \Exception("FAILED with return code " . $error . ' (see logs for details)');
        }

        return 'OK - Updated ' . $config->getName();
    }

    private function assembleModel(ProjectConfiguration $config) {
        return array(
            'back' => array('href' => $this->getAncestor(RootResource::$CLASS)->getUrl('home')->toString()),
            'navigation' => $this->assembleNavigation($config)
        );
    }

    public function assembleNavigation(ProjectConfiguration $config) {
        $dir = $config->getFullSpecFolder();
        $list= array(
            "name" => $config->getName(),
            "folder" => $this->assembleFolders($config->getFullSpecFolder(), $config),
            "specification" => $this->assembleSpecificationList($dir, $config)
        );
        return $list;
    }

    private function assembleSpecificationList($dir, ProjectConfiguration $config) {
        $fileSuffix = $config->getClassSuffix() . '.php';

        $list = array();
        foreach (glob($dir . '/*') as $file) {
            if (!is_dir($file) && substr($file, -strlen($fileSuffix)) == $fileSuffix) {
                $path = substr($file, strlen($config->getFullSpecFolder()), -strlen($fileSuffix));
                $url = $this->getUrl('specs' . $path)->toString();

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
                        'name' => substr($file, strlen($config->getFullSpecFolder())+1),
                        'specification' => $specifications
                    );
                }
                $list = array_merge($list, $this->assembleFolders($file, $config));
            }
        }
        return $list;
    }

    /**
     * @return ProjectConfiguration
     */
    private function getProjectConfig() {
        $project = $this->getUrl()->getPath()->last();
        return $this->config->getProject($project);
    }

} 