<?php

namespace App\Services\Sso;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SsoService
{
    public function authorizationUrl(): string
    {
        $baseUrl = config('services.sso.base_url');
        $clientId = config('services.sso.client_id');

        if (! $baseUrl || ! $clientId) {
            throw new RuntimeException('SSO SERAYA belum dikonfigurasi.');
        }

        return rtrim($baseUrl, '/').'/oauth/authorize?'.http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('sso.callback'),
            'response_type' => 'code',
            'scope' => '',
        ]);
    }

    /** @return array<string, mixed> */
    public function exchangeCode(string $code): array
    {
        $baseUrl = config('services.sso.base_url');

        if (! $baseUrl) {
            throw new RuntimeException('SSO SERAYA belum dikonfigurasi.');
        }

        return $this->client()->asForm()->post(rtrim($baseUrl, '/').'/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.sso.client_id'),
            'client_secret' => config('services.sso.client_secret'),
            'redirect_uri' => route('sso.callback'),
            'code' => $code,
        ])->throw()->json();
    }

    /** @return array<string, mixed> */
    public function userFromToken(string $token): array
    {
        $baseUrl = config('services.sso.base_url');

        if (! $baseUrl || $token === '') {
            throw new RuntimeException('Token SSO tidak valid.');
        }

        return $this->client()->withToken($token)->get(rtrim($baseUrl, '/').'/api/user')->throw()->json();
    }

    /**
     * @param  array<string, mixed>  $profile
     * @param  array{nik_karyawan: string, nama_lengkap: string, tanggal_masuk: string}  $employee
     */
    public function syncUser(array $profile, array $employee): User
    {
        $map = config('services.sso.field_map');
        $nik = data_get($profile, $map['nik']);

        if (! $nik) {
            throw new RuntimeException('SSO tidak mengembalikan NIK.');
        }

        $user = User::firstOrNew(['nik' => (string) $nik]);
        if (! (bool) data_get($profile, $map['active'], true)) {
            throw new RuntimeException('Akun sekali_login tidak aktif.');
        }

        $user->fill([
            'name' => $employee['nama_lengkap'],
            'email' => data_get($profile, 'email', $user->email ?: $nik.'@sso'),
            'avatar_url' => data_get($profile, $map['avatar_url']),
            'active' => (bool) data_get($profile, $map['active'], true),
            'tanggal_masuk' => $employee['tanggal_masuk'],
        ]);
        $user->password ??= str()->random(32);
        $user->role ??= 'viewer';
        $user->save();

        return $user;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->timeout(10);
    }
}
