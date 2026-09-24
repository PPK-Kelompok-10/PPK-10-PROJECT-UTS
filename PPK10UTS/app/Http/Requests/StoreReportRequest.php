<?php

namespace App\Http\Requests;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route sudah dijaga middleware role:pengguna, ini lapis kedua.
        return (bool) $this->user()?->isPengguna();
    }

    public function rules(): array
    {
        return [
            'facility_id' => [
                'required',
                'integer',
                Rule::exists('facilities', 'id')->whereIn('status', Report::REPORTABLE_FACILITY_STATUSES),
            ],
            'category' => ['required', 'string', Rule::in(array_keys(Report::CATEGORIES))],
            'description' => ['required', 'string', 'min:10', 'max:2000'],

            // Validasi upload dilakukan di server: harus file gambar asli (dicek dari isi file,
            // bukan cuma nama), ekstensi terbatas, dan ukuran maksimal 2 MB.
            'photo' => [
                'required',
                'file',
                'image',
                'mimes:' . implode(',', Report::PHOTO_MIMES),
                'max:' . Report::PHOTO_MAX_KB,
            ],
        ];
    }

    public function messages(): array
    {
        $formats = strtoupper(implode(', ', Report::PHOTO_MIMES));
        $maxMb = Report::PHOTO_MAX_KB / 1024;

        return [
            'facility_id.required' => 'Pilih fasilitas yang ingin dilaporkan.',
            'facility_id.exists' => 'Fasilitas tidak ditemukan atau sedang dinonaktifkan.',
            'category.required' => 'Pilih kategori kerusakan.',
            'category.in' => 'Kategori kerusakan tidak valid.',
            'description.required' => 'Deskripsi kerusakan wajib diisi.',
            'description.min' => 'Deskripsi minimal :min karakter supaya petugas paham masalahnya.',
            'description.max' => 'Deskripsi maksimal :max karakter.',
            'photo.required' => 'Foto bukti wajib diunggah.',
            'photo.uploaded' => "Foto gagal diunggah. Pastikan ukurannya tidak lebih dari {$maxMb} MB.",
            'photo.file' => 'Foto bukti harus berupa file.',
            'photo.image' => 'File yang diunggah harus berupa gambar.',
            'photo.mimes' => "Format foto harus {$formats}.",
            'photo.max' => "Ukuran foto maksimal {$maxMb} MB.",
        ];
    }
}
