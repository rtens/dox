<?php
namespace rtens\dox\web\root\projects\xxProject;

use watoki\curir\http\Request;
use watoki\curir\resource\DynamicResource;
use watoki\curir\Responder;

class ReportsResource extends DynamicResource {

    const TAP_VERSION = 'TAP version 13';

    public function doPost(Request $request) {
        $body = trim($request->getBody());

        if (substr($body, 0, strlen(self::TAP_VERSION)) == self::TAP_VERSION) {

        } else {
            throw new \Exception('Format not recognized');
        }
    }

} 