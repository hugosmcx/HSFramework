<?php
    namespace App\Middlewares;

    use HSFramework\Middleware;

    class AuthMiddleware implements Middleware
    {
        public function handle(): bool
        {
            // Iniciamos a sessão para testar (em um app real isso estaria no index.php)
            if (session_status() === PHP_SESSION_NONE)
            {
                session_start();
            }

            // Simulação: Se não existir a chave 'usuario' na sessão, barra o acesso
            if (!isset($_SESSION['usuario']))
            {
                http_response_code(401);
                echo "Acesso negado: Você precisa estar logado para acessar esta página.";
                return false; 
            }
            return true;
        }
    }