<?php
    namespace App\Requests;

    use HSFramework\FormRequest;

    class UserStoreRequest extends FormRequest
    {
        public function rules(): array
        {
            return [
                'nome'  => 'required|min:3',
                'email' => 'required|email',
                'idade' => 'required|numeric|min:18'
            ];
        }

        public function messages(): array
        {
            return [
                'nome.required' => 'O nome é obrigatório.',
                'nome.min'      => 'O nome deve ter pelo menos 3 caracteres.',
                'email.required' => 'O email é obrigatório.',
                'email.email'    => 'O email deve ser válido.',
                'idade.required'   => 'A idade é obrigatória.',
                'idade.numeric'    => 'A idade deve ser um número.',
                'idade.min'        => 'A idade deve ser no mínimo 18 anos.'
            ];
        }
    }