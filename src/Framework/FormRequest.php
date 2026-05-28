<?php
    namespace App\Framework;

    use App\Framework\Validator;
    use App\Framework\Request;

    abstract class FormRequest extends Request
    {
        protected Validator $validator;

        public function __construct(Validator $validator)
        {
            $this->validator = $validator;
        }

        // O Controller filho vai implementar este método com as regras
        abstract public function rules(): array;

        // O Controller filho pode opcionalmente customizar as mensagens aqui
        public function messages(): array
        {
            return [];
        }

        /**
         * Executa a validação dos dados da requisição.
         */
        public function validateFields(): void
        {
            // Alimenta o Validator com os dados da Request, regras e mensagens personalizadas do FormRequest child
            $this->validator->make($this->all(), $this->rules(), $this->messages());

            // Se passar, não faz nada e o fluxo segue normal
            if (!$this->validator->fails()) {
                return;
            }

            $errors = $this->validator->errors();

            // Se a requisição espera JSON (API)
            if ($this->wantsJson()) {
                $this->respondWithJsonErrors($errors);
            }

            // Se for um formulário web tradicional
            $this->redirectWithErrors($errors);
        }

        protected function wantsJson(): bool
        {
            $headers = getallheaders();
            $accept = $headers['Accept'] ?? $headers['accept'] ?? '';
            $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';

            return str_contains($accept, 'application/json') || str_contains($contentType, 'application/json');
        }

        protected function respondWithJsonErrors(array $errors): void
        {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'message' => 'Os dados fornecidos são inválidos.',
                'errors'  => $errors
            ]);
            exit; // Interrompe o ciclo da requisição aqui
        }

        protected function redirectWithErrors(array $errors): void
        {
            // Garante que a sessão está ativa para persistir os erros
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Salva os erros e os dados antigos (old input) na sessão
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old'] = $this->all();

            // Redireciona de volta para a página anterior (ou para a home se não achar o referer)
            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referer}");
            exit; // Interrompe o ciclo da requisição aqui
        }
    }