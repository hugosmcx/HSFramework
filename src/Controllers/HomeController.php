<?php
	namespace App\Controllers;
	use PDO;

	class HomeController {
		private PDO $db;

		public function __construct(PDO $db)
		{
			$this->db = $db;
		}

		public function index()
		{
			echo "Bem-vindo à página inicial! O PDO foi injetado com sucesso.<br>";
			echo "Controller com acesso ao banco ativo!";
		}

		public function login()
		{
			session_start();
			$_SESSION['usuario'] = 'usario_exemplo';
			echo "Você está logado! Agora tente acessar a página inicial novamente.";
		}

		public function logout()
		{
			session_start();
			unset($_SESSION['usuario']);
			echo "Você saiu da sua conta.";
		}
	}