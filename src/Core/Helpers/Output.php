<?php

namespace GoFrame\Core\Helpers;

class Output {
    public function buildOutput(array $array) {

        if (isset($array['status_code'])) {
            http_response_code($array['status_code']);
           
        }

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
