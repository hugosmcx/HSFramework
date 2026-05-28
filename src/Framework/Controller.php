<?php
    namespace App\Framework;

    class Controller
    {
        public function ShowView($name, $data = [])
        {
            extract($data);
            include __DIR__ . "/../Views/{$name}.php";
        }
    }