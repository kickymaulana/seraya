<?php

namespace App\Services\Hrd;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HrdService
{
    /**
     * @return array{nik_karyawan: string, nama_lengkap: string, tanggal_masuk: string}
     */
    public function employeeByNik(string $nik): array
    {
        $response = $this->client()->get(config('services.hrd.employee_url'), [
            'nik_karyawan' => $nik,
        ])->throw()->json();

        if (! is_array($response) || ($response['status'] ?? false) !== true || ! is_array($response['data'] ?? null)) {
            throw new RuntimeException($response['message'] ?? 'Data karyawan HRD tidak ditemukan.');
        }

        $data = $response['data'];
        $responseNik = (string) ($data['nik_karyawan'] ?? '');
        $namaLengkap = trim((string) ($data['nama_lengkap'] ?? ''));
        $tanggalMasuk = (string) ($data['tanggal_masuk'] ?? '');

        if ($responseNik !== $nik || $namaLengkap === '') {
            throw new RuntimeException('NIK dari HRD tidak sesuai dengan NIK SSO.');
        }

        try {
            $tanggalMasuk = CarbonImmutable::createFromFormat('!Y-m-d', $tanggalMasuk)->format('Y-m-d');
        } catch (\Throwable) {
            throw new RuntimeException('Tanggal masuk dari HRD tidak valid.');
        }

        return [
            'nik_karyawan' => $responseNik,
            'nama_lengkap' => $namaLengkap,
            'tanggal_masuk' => $tanggalMasuk,
        ];
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->timeout((int) config('services.hrd.timeout', 10));
    }
}
