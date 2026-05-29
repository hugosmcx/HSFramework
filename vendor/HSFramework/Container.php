<?php
    namespace HSFramework;

    use ReflectionClass;
    use Exception;

    class Container
    {
        // Guarda as resoluções (closings) de como criar as classes
        private array $bindings = [];
        
        // Guarda as instâncias que devem ser Singleton (uma única cópia)
        private array $instances = [];

        /**
         * Registra um serviço no container (Similar ao AddTransient do C#)
         */
        public function bind(string $key, callable $resolver): void
        {
            $this->bindings[$key] = $resolver;
        }

        /**
         * Registra um Singleton (Similar ao AddSingleton do C#)
         */
        public function singleton(string $key, callable $resolver): void
        {
            $this->bindings[$key] = function ($container) use ($resolver, $key) {
                if (!isset($this->instances[$key])) {
                    $this->instances[$key] = $resolver($container);
                }
                return $this->instances[$key];
            };
        }

        /**
         * Resolve e instancia a classe, injetando suas dependências automaticamente
         */
        public function get(string $className)
        {
            // 1. Se a classe tiver uma regra customizada registrada no bind/singleton, usa ela
            if (isset($this->bindings[$className])) {
                return $this->bindings[$className]($this);
            }

            // 2. Se não tiver regra, vamos usar Reflection para tentar adivinhar o Construtor
            $reflector = new ReflectionClass($className);

            if (!$reflector->isInstantiable()) {
                throw new Exception("A classe {$className} não pode ser instanciada.");
            }

            $constructor = $reflector->getConstructor();

            // Se não tiver construtor, instancia a classe limpa
            if (is_null($constructor)) {
                return new $className();
            }

            // 3. Se tiver construtor, inspeciona os parâmetros dele
            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                if (!$type || $type->isBuiltin()) {
                    // Se for um tipo primitivo (string, int) e não tiver valor padrão, quebra
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new Exception("Não foi possível resolver o parâmetro dinâmico '{$parameter->getName()}' na classe {$className}.");
                    }
                } else {
                    // Se for uma classe/interface, chama o próprio Container recursivamente para resolvê-la!
                    $dependencies[] = $this->get($type->getName());
                }
            }

            // Retorna a classe instanciada com todas as dependências injetadas
            return $reflector->newInstanceArgs($dependencies);
        }
    }