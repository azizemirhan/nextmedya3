<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:20'],

            // reCAPTCHA v3 - 0.5 score threshold
            'recaptcha-response' => ['required', 'recaptcha:0.5'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ad Soyad zorunludur.',
            'name.max' => 'Ad Soyad en fazla 120 karakter olabilir.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.max' => 'E-posta adresi en fazla 190 karakter olabilir.',
            'subject.required' => 'Konu zorunludur.',
            'subject.max' => 'Konu en fazla 190 karakter olabilir.',
            'message.required' => 'Mesaj zorunludur.',
            'message.min' => 'Mesaj en az 10 karakter olmalıdır.',
            'message.max' => 'Mesaj en fazla 5000 karakter olabilir.',
            'phone.max' => 'Telefon numarası en fazla 20 karakter olabilir.',

            // reCAPTCHA hata mesajları
            'recaptcha-response.required' => 'reCAPTCHA doğrulaması gereklidir.',
            'recaptcha-response.recaptcha' => 'reCAPTCHA doğrulaması başarısız oldu. Bot şüphesi tespit edildi.',
        ];
    }

    /**
     * Validation başarısız olduğunda özel response
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // reCAPTCHA hatası varsa özel loglama
        if ($validator->errors()->has('recaptcha-response')) {
            \Log::warning('reCAPTCHA validation failed for contact form', [
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'errors' => $validator->errors()->get('recaptcha-response'),
            ]);
        }

        parent::failedValidation($validator);
    }
}