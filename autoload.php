<?php

spl_autoload_register(function (string $class) {
	// Se for do app (namespace App\), mapeia para /app/
	$appPrefix = 'App\\';
	if (strncmp($appPrefix, $class, strlen($appPrefix)) === 0) {
		$relative = substr($class, strlen($appPrefix));
		$file = __DIR__ . '/app/' . str_replace('\\', '/', $relative) . '.php';
		if (file_exists($file)) {
			require_once $file;
			return;
		}
	}

	// Para qualquer outro namespace raiz, assume-se um pacote em /vendor/<RootNamespace>/
	$parts = explode('\\', $class);
	$root = $parts[0];
	$relativeParts = array_slice($parts, 1);
	$vendorDir = __DIR__ . '/vendor/' . $root . '/';

	if (!empty($relativeParts)) {
		$file = $vendorDir . implode('/', $relativeParts) . '.php';
		if (file_exists($file)) {
			require_once $file;
			return;
		}
	} else {
		// Se não houver sub-namespace, tenta alguns caminhos razoáveis
		$try1 = __DIR__ . '/vendor/' . $root . '.php';
		if (file_exists($try1)) {
			require_once $try1;
			return;
		}
		$try2 = $vendorDir . $root . '.php';
		if (file_exists($try2)) {
			require_once $try2;
			return;
		}
	}

	// Último recurso: tenta carregar relativo à raiz do projeto
	$file = __DIR__ . '/' . str_replace('\\', '/', $class) . '.php';
	if (file_exists($file)) {
		require_once $file;
	}
});