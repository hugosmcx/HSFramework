<?php
    namespace App\Framework;

    use RuntimeException;

    class View
    {
        private string $viewsPath;

        public function __construct(string $viewsPath = __DIR__ . '/../Views')
        {
            $this->viewsPath = rtrim($viewsPath, '/\\');
        }

        public function render(string $name, array $data = []): string
        {
            $name = $this->sanitizeViewName($name);
            $file = $this->viewsPath . '/' . $name . '.php';

            if (! file_exists($file)) {
                throw new RuntimeException("View não encontrada: {$name}");
            }

            extract($data, EXTR_SKIP);

            ob_start();
            include $file;
            return ob_get_clean();
        }

        private function sanitizeViewName(string $name): string
        {
            $name = str_replace(['\\', '..'], ['/', ''], $name);
            $name = trim($name, '/');
            return preg_replace('/[^a-zA-Z0-9_\/\-]/', '', $name);
        }
    }