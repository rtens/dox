<?php
namespace rtens\dox\web\root\projects\xxProject;

use rtens\dox\Report;
use watoki\curir\http\Request;
use watoki\curir\resource\DynamicResource;
use watoki\curir\Responder;

class ReportsResource extends DynamicResource {

    /** @var Report <- */
    public $report;

    public function doPost($projectName, Request $request) {
        $this->report->save(trim($request->getBody()), $projectName);
    }

} 