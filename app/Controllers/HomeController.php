<?php
	namespace App\Controllers;
	use PDO;
	use HSFramework\Controller;
	use HSFramework\View;

	class HomeController
	{
		private PDO $db;
		private View $view;

		public function __construct(PDO $db, View $view)
		{
			$this->db = $db;
			$this->view = $view;
		}

		public function index()
		{
			echo $this->view->render('home.index');
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