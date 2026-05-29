<?php
    namespace HSFramework;

    class Controller
    {
        public function ShowView($name, $data = [])
        {
            extract($data);
            include __DIR__ . "/../Views/{$name}.php";
        }
    }