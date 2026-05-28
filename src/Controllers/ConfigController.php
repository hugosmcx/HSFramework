<?php
    namespace App\Controllers;

    use App\Repositories\ProdutoRepository;

    class ConfigController
    {
        private ProdutoRepository $produtoRepo;

        public function __construct(ProdutoRepository $produtoRepo)
        {
            $this->produtoRepo = $produtoRepo;
        }
        
        public function index(string $nome = null)
        {
            if ($nome) {    
                echo $nome . "<br/>";
            }

            var_dump($this->produtoRepo->getAll()); // Mesmo que o ProdutoRepository dependa de um PDO, aqui ele é injetado vazio, sem conexões ativas
        }
    }