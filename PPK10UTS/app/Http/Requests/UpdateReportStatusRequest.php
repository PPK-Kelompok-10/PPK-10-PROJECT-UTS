<?php

namespace App\Http\Requests;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReportStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isPetugas();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['diproses', 'selesai', 'ditolak'])],
            'resolution_note' => ['nullable', 'string', 'max:2000', 'required_if:status,selesai,ditolak'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Pilih status laporan.',
            'status.in' => 'Status laporan tidak valid.',
            'resolution_note.required_if' => 'Catatan resolusi wajib diisi jika laporan diselesaikan atau ditolak.',
            'resolution_note.max' => 'Catatan resolusi maksimal :max karakter.',
        ];
    }

    /**
     * Cek alur status (lihat Report::TRANSITIONS) di sisi server,
     * supaya laporan yang sudah final tidak bisa diubah lewat request manual.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('status')) {
                return;
            }

            $report = $this->route('report');
            $target = $this->input('status');

            if ($report instanceof Report && ! $report->canTransitionTo($target)) {
                $validator->errors()->add('status', sprintf(
                    'Status laporan tidak bisa diubah dari "%s" menjadi "%s".',
                    $report->statusLabel(),
                    Report::STATUSES[$target] ?? $target
                ));
            }
        });
    }
}
