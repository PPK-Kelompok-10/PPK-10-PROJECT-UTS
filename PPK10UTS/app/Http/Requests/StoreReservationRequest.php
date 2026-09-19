<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya Pengguna yang login boleh mengajukan reservasi (route sudah dijaga
        // middleware role:pengguna juga, ini lapis kedua).
        return $this->user() && $this->user()->isPengguna();
    }

    public function rules(): array
    {
        return [
            'purpose' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'purpose.required' => 'Tujuan penggunaan wajib diisi.',
            'date.after_or_equal' => 'Tanggal reservasi tidak boleh di masa lalu.',
            'start_time.date_format' => 'Format jam mulai tidak valid.',
            'end_time.date_format' => 'Format jam selesai tidak valid.',
        ];
    }

    /**
     * Validasi tambahan yang butuh logika (bukan cuma aturan siap pakai):
     * - jam mulai & selesai wajib dalam jam operasional 07.00-20.00
     * - wajib kelipatan slot 30 menit
     * - jam selesai wajib setelah jam mulai
     *
     * Semua dilakukan di sisi SERVER, sesuai ketentuan "Ketentuan Waktu Reservasi"
     * di dokumen tugas — tidak cukup hanya validasi di kalender/tampilan.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('start_time') || $validator->errors()->has('end_time')) {
                return;
            }

            $operationalStart = '07:00';
            $operationalEnd = '20:00';

            if ($this->start_time < $operationalStart || $this->start_time >= $operationalEnd) {
                $validator->errors()->add('start_time', 'Jam mulai harus dalam jam operasional 07.00-20.00.');
            }

            if ($this->end_time <= $operationalStart || $this->end_time > $operationalEnd) {
                $validator->errors()->add('end_time', 'Jam selesai harus dalam jam operasional 07.00-20.00.');
            }

            if (!$this->isMultipleOf30Minutes($this->start_time)) {
                $validator->errors()->add('start_time', 'Jam mulai harus kelipatan slot 30 menit (mis. 07.00, 07.30).');
            }

            if (!$this->isMultipleOf30Minutes($this->end_time)) {
                $validator->errors()->add('end_time', 'Jam selesai harus kelipatan slot 30 menit (mis. 07.30, 08.00).');
            }

            if ($this->start_time >= $this->end_time) {
                $validator->errors()->add('end_time', 'Jam selesai harus setelah jam mulai.');
            }
        });
    }

    private function isMultipleOf30Minutes(?string $time): bool
    {
        if (!$time) {
            return false;
        }

        [$hour, $minute] = array_pad(explode(':', $time), 2, '0');

        return in_array((int) $minute, [0, 30], true);
    }

    /**
     * Gabungkan tanggal + jam jadi datetime penuh, dipakai controller setelah validasi lolos.
     */
    public function startDateTime(): Carbon
    {
        return Carbon::parse("{$this->date} {$this->start_time}");
    }

    public function endDateTime(): Carbon
    {
        return Carbon::parse("{$this->date} {$this->end_time}");
    }
}
