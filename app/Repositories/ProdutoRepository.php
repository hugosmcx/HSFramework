<?php
    namespace App\Repositories;

    use PDO;

    class ProdutoRepository
    {
        private PDO $db;

        public function __construct(PDO $db)
        {
            $this->db = $db;
        }

        public function getAll()
        {
            // Simulação de dados, sem conexão real com banco de dados
            return [
                ['id' => 1, 'nome' => 'Produto A', 'preco' => 10.0],
                ['id' => 2, 'nome' => 'Produto B', 'preco' => 20.0],
                ['id' => 3, 'nome' => 'Produto C', 'preco' => 30.0],
            ];
        }
    }