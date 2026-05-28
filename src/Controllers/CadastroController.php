<?php
    namespace App\Controllers;

    use App\Requests\UserStoreRequest;

    class CadastroController
    {
        public function index(UserStoreRequest $request)
        {
            echo "Bem-vindo à página de cadastro!";
        }
    }