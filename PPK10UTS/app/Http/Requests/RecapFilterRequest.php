<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RecapFilterRequest extends FormRequest
{
    /** Batas rentang rekap supaya query tetap ringan. */
    private const MAX_RANGE_DAYS = 366;

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /** Default periode: bulan berjalan. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'from' => $this->input('from') ?: now()->startOfMonth()->toDateString(),
            'to' => $this->input('to') ?: now()->endOfMonth()->toDateString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.date_format' => 'Format tanggal awal tidak valid.',
            'to.date_format' => 'Format tanggal akhir tidak valid.',
            'to.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['from', 'to'])) {
                return;
            }

            if (Carbon::parse($this->input('from'))->diffInDays(Carbon::parse($this->input('to'))) > self::MAX_RANGE_DAYS) {
                $validator->errors()->add('to', 'Rentang rekap maksimal ' . self::MAX_RANGE_DAYS . ' hari.');
            }
        });
    }

    public function from(): Carbon
    {
        return Carbon::parse($this->validated('from'))->startOfDay();
    }

    public function to(): Carbon
    {
        return Carbon::parse($this->validated('to'))->endOfDay();
    }
}
