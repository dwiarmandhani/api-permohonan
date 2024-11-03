<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user for authentication
        $this->user = User::factory()->create([
            'password' => Hash::make('password'), // Password is 'password'
        ]);
    }

    /**
     * Test to get a list of applications
     */
    public function test_index()
    {
        $response = $this->actingAs($this->user, 'api')->getJson('/api/applications');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test to create a new application
     */
    public function test_store()
    {
        $data = [
            'nasabah' => [
                'nama' => 'John Doe',
                'nik' => '1234567890123456',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat_lengkap' => 'Jl. Kebon Jati',
                'kelurahan' => 'Kebon Jati',
                'kecamatan' => 'Kota Bandung',
                'kabupaten' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40111',
                'no_rekening_tabungan' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'john@example.com',
                'ktp' => base64_encode('dummy_ktp_content'),
            ],
            'nama_ao' => 'AO Test',
            'documents' => [
                ['name' => 'KTP', 'status' => '2'],
                ['name' => 'SLIP GAJI', 'status' => '1'],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/applications', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Application created successfully!',
            ]);
    }

    public function test_show()
    {
        $data = [
            'nasabah' => [
                'nama' => 'John Doe',
                'nik' => '1234567890123456',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat_lengkap' => 'Jl. Kebon Jati',
                'kelurahan' => 'Kebon Jati',
                'kecamatan' => 'Kota Bandung',
                'kabupaten' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40111',
                'no_rekening_tabungan' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'john@example.com',
                'ktp' => base64_encode('dummy_ktp_content'),
            ],
            'nama_ao' => 'AO Test',
            'documents' => [
                ['name' => 'KTP', 'status' => '2'],
                ['name' => 'SLIP GAJI', 'status' => '1'],
            ],
        ];

        // Mengirim permintaan POST untuk menyimpan data
        $response = $this->actingAs($this->user, 'api')->postJson('/api/applications', $data);
        // dd(json_decode($response));
        $applicationId = $response->json('application.id');

        // Melakukan permintaan API untuk menampilkan aplikasi
        $responses = $this->actingAs($this->user, 'api')->getJson('/api/applications/' . $applicationId);

        // Memastikan status respons dan struktur
        $responses->assertStatus(200)
            ->assertJson([
                'success' => true,
                'application' => [
                    'id' => $applicationId,
                    'nasabah' => [
                        'nama' => 'John Doe',
                        'nik' => '1234567890123456',
                        // Anda bisa menambahkan lebih banyak field di sini sesuai kebutuhan
                    ],
                    'nama_ao' => 'AO Test',
                    // Jika ada, tambahkan data lain yang relevan untuk diuji
                ],
            ]);
    }



    /**
     * Test to update a specific application
     */
    public function test_update()
    {
        $data = [
            'nasabah' => [
                'nama' => 'John Doe',
                'nik' => '1234567890123456',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat_lengkap' => 'Jl. Kebon Jati',
                'kelurahan' => 'Kebon Jati',
                'kecamatan' => 'Kota Bandung',
                'kabupaten' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40111',
                'no_rekening_tabungan' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'john@example.com',
                'ktp' => base64_encode('dummy_ktp_content'),
            ],
            'nama_ao' => 'AO Test',
            'documents' => [
                ['name' => 'KTP', 'status' => '2'],
                ['name' => 'SLIP GAJI', 'status' => '1'],
            ],
        ];

        // Mengirim permintaan POST untuk menyimpan data
        $response = $this->actingAs($this->user, 'api')->postJson('/api/applications', $data);
        // dd(json_decode($response));
        $applicationId = $response->json('application.id');

        $data_new = [
            'nasabah' => [
                'nama' => 'John Updated',
                'nik' => '1234567890123456',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat_lengkap' => 'Jl. Kebon Jati',
                'kelurahan' => 'Kebon Jati',
                'kecamatan' => 'Kota Bandung',
                'kabupaten' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40111',
                'no_rekening_tabungan' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'john_updated@example.com',
                'ktp' => base64_encode('updated_dummy_ktp_content'),
            ],
            'nama_ao' => 'AO Updated',
        ];

        $responses = $this->actingAs($this->user, 'api')->putJson('/api/applications/' . $applicationId, $data);

        $responses->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Application updated successfully!',
            ]);
    }

    /**
     * Test to delete a specific application
     */
    public function test_destroy()
    {
        $data = [
            'nasabah' => [
                'nama' => 'John Doe',
                'nik' => '1234567890123456',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat_lengkap' => 'Jl. Kebon Jati',
                'kelurahan' => 'Kebon Jati',
                'kecamatan' => 'Kota Bandung',
                'kabupaten' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40111',
                'no_rekening_tabungan' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'john@example.com',
                'ktp' => base64_encode('dummy_ktp_content'),
            ],
            'nama_ao' => 'AO Test',
            'documents' => [
                ['name' => 'KTP', 'status' => '2'],
                ['name' => 'SLIP GAJI', 'status' => '1'],
            ],
        ];

        // Mengirim permintaan POST untuk menyimpan data
        $response = $this->actingAs($this->user, 'api')->postJson('/api/applications', $data);
        // dd(json_decode($response));
        $applicationId = $response->json('application.id');

        $responses = $this->actingAs($this->user, 'api')->deleteJson('/api/applications/' . $applicationId);

        $responses->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Application deleted successfully!',
            ]);
    }

    public function test_index_without_authentication()
    {
        $response = $this->getJson('/api/applications');

        $response->assertStatus(401) // Unauthorized
        ->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function test_store_with_missing_fields()
    {
        $data = [
            'nasabah' => [
                'nama' => '', // Missing name
                'nik' => '1234567890123456',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat_lengkap' => 'Jl. Kebon Jati',
                'kelurahan' => 'Kebon Jati',
                'kecamatan' => 'Kota Bandung',
                'kabupaten' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40111',
                'no_rekening_tabungan' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'john@example.com',
                'ktp' => base64_encode('dummy_ktp_content'),
            ],
            'nama_ao' => 'AO Test',
            'documents' => [
                ['name' => 'KTP', 'status' => '2'],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')->postJson('/api/applications', $data);

        $response->assertStatus(422) // Unprocessable Entity
            ->assertJsonValidationErrors(['nasabah.nama']);
    }

    public function test_show_non_existing_application()
    {
        $applicationId = 999; // Assuming this ID does not exist

        $response = $this->actingAs($this->user, 'api')->getJson('/api/applications/' . $applicationId);

        // dd($response);
        $response->assertStatus(500) // Not Found
            ->assertJson([
                'success' => false,
                'message' => 'Application not Found.',
            ]);
    }

    public function test_update_non_existing_application()
    {
        $applicationId = 999; // Assuming this ID does not exist
        $data = [
            'nasabah' => [
                'nama' => 'John Updated',
                'nik' => '1234567890123456',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1990-01-01',
                'jenis_kelamin' => 'L',
                'alamat_lengkap' => 'Jl. Kebon Jati',
                'kelurahan' => 'Kebon Jati',
                'kecamatan' => 'Kota Bandung',
                'kabupaten' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'kode_pos' => '40111',
                'no_rekening_tabungan' => '1234567890',
                'no_hp' => '08123456789',
                'email' => 'john_updated@example.com',
                'ktp' => base64_encode('updated_dummy_ktp_content'),
            ],
            'nama_ao' => 'AO Updated',
        ];

        $response = $this->actingAs($this->user, 'api')->putJson('/api/applications/' . $applicationId, $data);

        $response->assertStatus(500) // Not Found
            ->assertJson([
                'success' => false,
                'message' => 'Application update failed.',
            ]);
    }

    public function test_destroy_non_existing_application()
    {
        $applicationId = 999; // Assuming this ID does not exist

        $response = $this->actingAs($this->user, 'api')->deleteJson('/api/applications/' . $applicationId);

        $response->assertStatus(500) // Not Found
            ->assertJson([
                'success' => false,
                'message' => 'Application deletion failed.',
            ]);
    }
}
