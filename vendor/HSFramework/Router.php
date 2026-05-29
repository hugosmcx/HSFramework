<?php
	namespace HSFramework;

	class Router
	{
		private array $routes = [];
		private Container $container;

		public function __construct(Container $container)
		{
			$this->container = $container;
		}

		// Agora guardamos a rota como um array contendo a ação e os middlewares
		public function get(string $path, array|callable $action, array $middlewares = []): void
		{
			$this->routes['GET'][$this->normalizePath($path)] = [
				'action' => $action,
				'middlewares' => $middlewares
			];
		}

		public function post(string $path, array|callable $action, array $middlewares = []): void
		{
			$this->routes['POST'][$this->normalizePath($path)] = [
				'action' => $action,
				'middlewares' => $middlewares
			];
		}

		public function resolve(string $uri, string $method)
		{
			$path = $this->normalizePath(parse_url($uri, PHP_URL_PATH));
			$method = strtoupper($method);
			
			// Buscamos estritamente no método correto (POST ou GET)
			$routesForMethod = $this->routes[$method] ?? [];

			foreach ($routesForMethod as $routePath => $routeData) {
				// 1. Escapa os caracteres da rota para não quebrar a Regex (como as barras /)
				$quotedRoute = preg_quote($routePath, '#');
				
				// 2. Transforma as chaves {parametro} em grupos capturáveis da Regex
				$pattern = '#^' . preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '(?P<$1>[^/]+)', $quotedRoute) . '$#';

				if (preg_match($pattern, $path, $matches)) {
					$params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
					$action = $routeData['action'];
					
					// 3. Processa os Middlewares APENAS se deu match exato nesta rota
					foreach ($routeData['middlewares'] as $middlewareClass) {
						if (class_exists($middlewareClass)) {
							$middlewareInstance = $this->container->get($middlewareClass);
							
							// Se o middleware falhar, barra aqui e não deixa o script continuar
							if (!$middlewareInstance->handle()) {
								return; 
							}
						}
					}

					// 4. Executa se for uma Closure/função anônima
					if (is_callable($action)) {
						return call_user_func_array($action, $params);
					}

					// 5. Injeção de Dependência Avançada no Controller
					if (is_array($action)) {
						[$controller, $controllerMethod] = $action;

						if (class_exists($controller)) {
							$controllerInstance = $this->container->get($controller);
							
							if (method_exists($controllerInstance, $controllerMethod)) {
								
								// Analisa os argumentos do método do Controller
								$reflectionMethod = new \ReflectionMethod($controller, $controllerMethod);
								$methodParameters = $reflectionMethod->getParameters();
								$methodDependencies = [];

								foreach ($methodParameters as $parameter) {
									$type = $parameter->getType();

									if (!$type || $type->isBuiltin()) {
										// Parâmetros dinâmicos da URL (ex: {nome})
										$paramName = $parameter->getName();
										if (isset($params[$paramName])) {
											$methodDependencies[] = $params[$paramName];
										} elseif ($parameter->isDefaultValueAvailable()) {
											$methodDependencies[] = $parameter->getDefaultValue();
										} else {
											$methodDependencies[] = null;
										}
									} else {
										// Resolve objetos pelo Container (incluindo o FormRequest)
										$className = $type->getName();
										$instance = $this->container->get($className);

										// Se a classe herdar de FormRequest, valida na hora!
										if (is_subclass_of($className, FormRequest::class)) {
											$instance->validateFields(); // Se falhar, dá exit lá dentro
										}

										$methodDependencies[] = $instance;
									}
								}

								// Invoca o método injetando a Request validada automaticamente
								return $reflectionMethod->invokeArgs($controllerInstance, $methodDependencies);
							}
						}
					}
					
					// Rota encontrada e processada, encerra o resolve
					return;
				}
			}

			// Se percorreu todo o array do método e não achou a rota
			http_response_code(404);
			echo "404 - Rota não encontrada";
		}

		private function normalizePath(string $path): string
		{
			$path = trim($path, '/');
			return $path === '' ? '/' : '/' . $path;
		}
	}