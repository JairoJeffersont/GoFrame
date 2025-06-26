<?php

namespace GoFrame\Core\Helpers;

class Output {
    public function buildOutput(array $array) {
        // Verifica se existe um código HTTP e o define
        if (isset($array['status_code'])) {
            http_response_code($array['status_code']);
           
        }

        // Define o header de conteúdo como JSON
        header('Content-Type: application/json; charset=utf-8');

        // Envia o array como JSON
        echo json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
