<?php
	spl_autoload_register(function (string $class) {
		// 1. Defina o prefixo do namespace do seu framework/app
		$prefix = 'App\\';
	
		// 2. Onde as classes desse prefixo estão guardadas? (Diretório base)
		$baseDir = __DIR__ . '/';
	
		// 3. Verifica se a classe que está sendo chamada usa o nosso prefixo
		$len = strlen($prefix);
		if (strncmp($prefix, $class, $len) !== 0) {
			// Se não usar (ex: uma biblioteca externa futura), sai da função e deixa outro autoloader tratar
			return;
		}
	
		// 4. Pega o nome relativo da classe (ex: 'Controllers\HomeController')
		$relativeClass = substr($class, $len);
	
		// 5. Substitui as barras invertidas do namespace (\) pelas barras de diretório do sistema (/)
		//    E adiciona a extensão .php
		$file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
	
		// 6. Se o arquivo existir, inclui ele!
		if (file_exists($file)) {
			require_once $file;
		}
	});