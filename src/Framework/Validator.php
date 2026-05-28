<?php
    namespace App\Framework;

    use InvalidArgumentException;

    class Validator
    {
        protected array $data = [];
        protected array $rules = [];
        protected array $errors = [];
        protected array $customMessages = [];

        // Armazena as mensagens padrão para cada regra
        protected array $messages = [
            'required' => 'O campo :attribute é obrigatório.',
            'email'    => 'O campo :attribute deve ser um e-mail válido.',
            'numeric'  => 'O campo :attribute deve ser um número.',
            'integer'  => 'O campo :attribute deve ser um número inteiro.',
            'float'    => 'O campo :attribute deve ser um número de ponto flutuante.',
            'date'     => 'O campo :attribute deve ser uma data no formato YYYY-MM-DD.',
            'datetime' => 'O campo :attribute deve ser uma data e hora no formato YYYY-MM-DD HH:II:SS.',
            'min'      => 'O campo :attribute deve ter no mínimo :value.',
            'max'      => 'O campo :attribute deve ter no máximo :value.',
        ];

        public function make(array $data, array $rules, array $messages = []): self
        {
            $this->data = $data;
            $this->rules = $rules;
            $this->customMessages = $messages;
            $this->errors = [];

            return $this;
        }

        public function validate(): bool
        {
            foreach ($this->rules as $field => $fieldRules) {
                // Se as regras forem passadas como string (ex: 'required|email'), transforma em array
                if (is_string($fieldRules)) {
                    $fieldRules = explode('|', $fieldRules);
                }

                foreach ($fieldRules as $rule) {
                    $this->applyRule($field, $rule);
                }
            }

            return empty($this->errors);
        }

        protected function applyRule(string $field, string $rule): void
        {
            $parameters = [];

            // Se a regra tiver parâmetros (ex: min:18), separa o nome da regra e o valor
            if (strpos($rule, ':') !== false) {
                [$rule, $paramString] = explode(':', $rule, 2);
                $parameters = explode(',', $paramString);
            }

            // Transforma o nome da regra em CamelCase para encontrar o método (ex: min -> validateMin)
            $method = 'validate' . ucfirst($rule);

            if (!method_code_exists($this, $method)) {
                throw new InvalidArgumentException("A regra de validação '{$rule}' não existe.");
            }

            // Pega o valor do campo (ou null se não existir)
            $value = $this->data[$field] ?? null;

            // Executa o método de validação dinamicamente
            if (!$this->$method($field, $value, $parameters)) {
                $this->addError($field, $rule, $parameters);
            }
        }

        public function errors(): array
        {
            return $this->errors;
        }

        public function fails(): bool
        {
            return !$this->validate();
        }

        ## REGRAS DE VALIDAÇÃO (Os Métodos Dinâmicos)

        protected function validateRequired(string $field, $value): bool
        {
            if (is_null($value)) {
                return false;
            }
            if (is_string($value) && trim($value) === '') {
                return false;
            }
            if (is_array($value) && count($value) === 0) {
                return false;
            }
            return true;
        }

        protected function validateEmail(string $field, $value): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true; // Se não for obrigatório e estiver vazio, passa.
            }
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }

        protected function validateNumeric(string $field, $value): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true;
            }
            return is_numeric($value);
        }

        protected function validateInteger(string $field, $value): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true;
            }
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        }

        protected function validateFloat(string $field, $value): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true;
            }
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        }

        protected function validateDate(string $field, $value): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true;
            }

            $format = 'Y-m-d';
            $date = DateTime::createFromFormat($format, $value);
            return $date && $date->format($format) === $value;
        }

        protected function validateDatetime(string $field, $value): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true;
            }

            $format = 'Y-m-d H:i:s';
            $date = DateTime::createFromFormat($format, $value);
            return $date && $date->format($format) === $value;
        }

        protected function validateMin(string $field, $value, array $parameters): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true;
            }
            
            $min = $parameters[0] ?? 0;

            if (is_numeric($value)) {
                return $value >= $min;
            }
            
            if (is_string($value)) {
                return mb_strlen($value) >= $min;
            }

            if (is_array($value)) {
                return count($value) >= $min;
            }

            return false;
        }

        protected function validateMax(string $field, $value, array $parameters): bool
        {
            if (!$this->validateRequired($field, $value)) {
                return true;
            }

            $max = $parameters[0] ?? 0;

            if (is_numeric($value)) {
                return $value <= $max;
            }

            if (is_string($value)) {
                return mb_strlen($value) <= $max;
            }

            if (is_array($value)) {
                return count($value) <= $max;
            }

            return false;
        }

        ## MANIPULAÇÃO DE ERROS E PLACEHOLDERS

        protected function addError(string $field, string $rule, array $parameters): void
        {
            $customKey = "{$field}.{$rule}";
            if (isset($this->customMessages[$customKey])) {
                $message = $this->customMessages[$customKey];
            } elseif (isset($this->customMessages[$field])) {
                $message = $this->customMessages[$field];
            } else {
                $message = $this->messages[$rule] ?? "O campo {$field} é inválido.";
            }

            // Substitui o placeholder :attribute pelo nome do campo
            $message = str_replace(':attribute', $field, $message);

            // Se houver parâmetros (como no min:18), substitui o :value pelo primeiro parâmetro
            if (!empty($parameters)) {
                $message = str_replace(':value', $parameters[0], $message);
            }

            // Guarda o erro agrupado por campo
            $this->errors[$field][] = $message;
        }
    }

    // Função auxiliar para checar métodos dinâmicos (caso use PHP antigo ou queira garantir)
    function method_code_exists($object, $method) {
        return method_exists($object, $method);
    }