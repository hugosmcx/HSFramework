<?php
    namespace App\Controllers;

    use HSFramework\Validator;
    use App\Requests\UserStoreRequest;

    class TestController
    {
        public function index()
        {
            $validator = new Validator();
            // Dados simulados vindos de um $_POST
            $dadosDaRequisicao = [
                'nome'  => 'Hu',
                'email' => 'hugo.invalido@',
                'idade' => 'dezesseis'
            ];

            // Regras no estilo Laravel
            $regras = [
                'nome'  => 'required|min:3',
                'email' => 'required|email',
                'idade' => 'required|numeric|min:18'
            ];

            $validator->make($dadosDaRequisicao, $regras);

            if ($validator->fails()) {
                echo "A validação falhou!\n";
                print_r($validator->errors());
            } else {
                echo "Tudo limpo e validado!";
            }
        }

        public function store(UserStoreRequest $request)
        {
            $dadosValidados = $request->all();

            return json_encode([
                'status' => 'Sucesso',
                'mensagem' => 'O framework validou tudo sozinho!',
                'dados' => $dadosValidados
            ]);
        }
    }