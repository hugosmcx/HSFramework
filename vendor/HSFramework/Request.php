<?php
    namespace HSFramework;

    class Request
    {
        // Retorna todos os dados da requisição (POST, GET e JSON)
        public function all(): array
        {
            $json = json_decode(file_get_contents('php://input'), true) ?? [];
            return array_merge($_GET, $_POST, $json);
        }

        public function method(): string
        {
            return $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }
    }