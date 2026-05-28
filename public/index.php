<?php
	require_once __DIR__ . '/../src/autoload.php';

	//die("Bati no index.php correto! O metodo enviado foi: " . $_SERVER['REQUEST_METHOD']);

	use App\Framework\Container;
	use App\Framework\Router;
	use App\Framework\Validator;
	use App\Controllers\HomeController;
	use App\Controllers\ConfigController;
	use App\Controllers\TestController;
	use App\Middlewares\AuthMiddleware;

	// 1. Inicializa o Container de Injeção de Dependência
	$container = new Container();

	// 2. Registra os seus Serviços (Igualzinho ao Program.cs do C#)
	$container->singleton(PDO::class, function() {
		return new PDO("sqlite::memory:"); // Usando SQLite em memória para simular o PDO sem precisar de um banco real
	});

	// Registramos o Validator como Singleton no Container
	$container->singleton(Validator::class, function($c) {
		return new Validator();
	});


	// 3. Inicializa o Router passando o Container configurado
	$router = new Router($container);

	// --- DEFINIÇÃO DAS ROTAS ---

	// Controller que PRECISA de banco (O Container vai injetar o PDO)
	//$router->get('/', [HomeController::class, 'index',], [AuthMiddleware::class]); // Rota protegida por Middleware de Autenticação
	$router->get('/login', [HomeController::class, 'login']);
	$router->get('/logout', [HomeController::class, 'logout']);

	// Controller que NÃO PRECISA de banco (O Container vai instanciar limpo)
	//$router->get('/config/{nome}', [ConfigController::class, 'index']);
	$router->get('/test', [TestController::class, 'index']);
	$router->post('/formulario', [TestController::class, 'store']);

	// Executa o Roteador
	$router->resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);