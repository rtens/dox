<?php
namespace rtens\dox;

class VcsService {

    /** @var Executer <- */
    public $executer;

    public function update(Project $config) {
        $fullPath = $config->getFullProjectFolder();

        if (!$this->isUnderGit($fullPath)) {
            throw new \Exception("Not a git repository");
        }

        $error = $this->executer->execute("cd $fullPath && git pull origin master");

        if ($error) {
            throw new \Exception("FAILED with return code " . $error . ' (see logs for details)');
        }
    }

    public function checkout(Project $project) {
        $dir = $project->getFullProjectFolder();
        if (file_exists($dir)) {
            throw new \Exception("Project folder already exists [$dir]");
        }

        $repository = $project->getRepositoryUrl();
        if (!$repository) {
            throw new \InvalidArgumentException("Cannot clone [{$project->getName()}]. No repository set.");
        }

        if ($error = $this->executer->execute("mkdir $dir")) {
            throw new \Exception("Could not create project folder [$dir] (code $error)");
        }

        if ($error = $this->executer->execute("cd $dir && git clone $repository .")) {
            throw new \Exception("FAILED with return code $error (see logs for details)");
        }
    }

    private function isUnderGit($fullPath) {
        return file_exists($fullPath . '/.git');
    }
}