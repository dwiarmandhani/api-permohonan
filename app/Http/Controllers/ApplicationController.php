<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Nasabah;
use App\Models\Job;
use App\Models\Document;
use App\Models\FinancingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function __construct()
    {
        // Ensure that all methods except for index and create require authentication
        $this->middleware('auth:sanctum')->except(['index', 'create']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        // Implement searching logic here
        $applications = Application::with('nasabah', 'documents','job', 'financingRequest')->get();

        return response()->json([
            'success' => true,
            'applications' => $applications,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        // Load additional data if needed (like branches)
        return response()->json([
            'success' => true,
            'message' => 'Ready to create a new application',
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        // Validate request data
        $validator = Validator::make($request->all(), [
            // Validation for Nasabah
            'nasabah.nama' => 'required|string|max:255',
            'nasabah.nik' => 'required|string|max:20',
            'nasabah.tempat_lahir' => 'required|string|max:255',
            'nasabah.tanggal_lahir' => 'required|date',
            'nasabah.jenis_kelamin' => 'required|string|in:L,P',
            'nasabah.alamat_lengkap' => 'required|string|max:255',
            'nasabah.kelurahan' => 'required|string|max:255',
            'nasabah.kecamatan' => 'required|string|max:255',
            'nasabah.kabupaten' => 'required|string|max:255',
            'nasabah.provinsi' => 'required|string|max:255',
            'nasabah.kode_pos' => 'required|string|max:10',
            'nasabah.no_rekening_tabungan' => 'required|string|max:20',
            'nasabah.no_hp' => 'required|string|max:15',
            'nasabah.email' => 'required|string|email|max:255',
            'nasabah.ktp' => 'required|string',
            // Validation for Job
            'job.nama_instansi' => 'required|string|max:255',
            'job.no_instansi' => 'required|string|max:50',
            'job.golongan_jabatan' => 'required|string|max:50',
            'job.nip' => 'required|string|max:50',
            'job.masa_kerja_hari' => 'required|integer|min:0',
            'job.masa_kerja_bulan' => 'required|integer|min:0',
            'job.masa_kerja_tahun' => 'required|integer|min:0',
            'job.nama_atasan' => 'required|string|max:255',
            'job.alamat_kantor' => 'required|string|max:255',
            // Validation for Application
            'nama_ao' => 'required|string|max:255',
            'jumlah_penghasilan' => 'required|numeric|min:0',
            'jumlah_permohonan' => 'required|numeric|min:0',
            'jumlah_penghasilan_lainnya' => 'nullable|numeric|min:0',
            'jangka_waktu' => 'required|integer|min:1',
            'maksimal_pembiayaan' => 'required|numeric|min:0',
            'tujuan_pembiayaan' => 'required|string|max:255',
            'status_perkawinan' => 'required|string|in:Single,Married,Widowed,Divorced',
            'upload_npwp' => 'required|string',
            'slip_gaji' => 'required|string',
            // Validation for Documents
            'documents' => 'required|array',
            'documents.*.name' => 'required|string',
            'documents.*.status' => 'required|string',
            'documents.*.file_path' => 'required|string|max:255',
            // Validation for Financing Request
            'financing_request.total_angsuran_biaya' => 'required|numeric',
            'financing_request.jangka_waktu' => 'required|integer|min:1',
            'financing_request.cabang' => 'required|string|max:255',
            'financing_request.capem' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create new Nasabah
            $nasabah = Nasabah::create($request->nasabah);

            // Create new Job
            $job = Job::create(array_merge($request->job, ['nasabah_id' => $nasabah->id]));

            // Create new Application
            $application = Application::create([
                'nasabah_id' => $nasabah->id,
                'no_aplikasi' => 'APP-' . uniqid(),
                'tanggal_aplikasi' => now(),
                'nama_ao' => $request->nama_ao,
                'jumlah_penghasilan' => $request->jumlah_penghasilan,
                'jumlah_permohonan' => $request->jumlah_permohonan,
                'jumlah_penghasilan_lainnya' => $request->jumlah_penghasilan_lainnya,
                'jangka_waktu' => $request->jangka_waktu,
                'maksimal_pembiayaan' => $request->maksimal_pembiayaan,
                'tujuan_pembiayaan' => $request->tujuan_pembiayaan,
                'status_perkawinan' => $request->status_perkawinan,
                'upload_npwp' => $request->upload_npwp,
                'slip_gaji' => $request->slip_gaji,
            ]);

            // Create related documents
            foreach ($request->documents as $doc) {
                Document::create([
                    'application_id' => $application->id,
                    'dokumen_name' => $doc['name'],
                    'checklist_status' => $doc['status'],
                    'file_path' => $doc['file_path'],
                ]);
            }

            // Create Financing Request
            $financingRequest = FinancingRequest::create(array_merge($request->financing_request, ['application_id' => $application->id]));

            return response()->json([
                'success' => true,
                'message' => 'Application created successfully!',
                'application' => $application,
                'nasabah' => $nasabah,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        try {
            // dd($application);
            $application = Application::with('nasabah', 'documents','job', 'financingRequest')->findOrFail($id);

            return response()->json([
                'success' => true,
                'application' => $application,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application not Found.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        // Validate request data
        $validator = Validator::make($request->all(), [
            'nasabah.nama' => 'sometimes|required|string|max:255',
            'nasabah.nik' => 'sometimes|required|string|max:20',
            'nasabah.tempat_lahir' => 'sometimes|required|string|max:255',
            'nasabah.tanggal_lahir' => 'sometimes|required|date',
            'nasabah.jenis_kelamin' => 'sometimes|required|string|in:L,P',
            'nasabah.alamat_lengkap' => 'sometimes|required|string|max:255',
            'nasabah.kelurahan' => 'sometimes|required|string|max:255',
            'nasabah.kecamatan' => 'sometimes|required|string|max:255',
            'nasabah.kabupaten' => 'sometimes|required|string|max:255',
            'nasabah.provinsi' => 'sometimes|required|string|max:255',
            'nasabah.kode_pos' => 'sometimes|required|string|max:10',
            'nasabah.no_rekening_tabungan' => 'sometimes|required|string|max:20',
            'nasabah.no_hp' => 'sometimes|required|string|max:15',
            'nasabah.email' => 'sometimes|required|string|email|max:255',
            'nasabah.ktp' => 'sometimes|required|string',
            'job.nama_instansi' => 'sometimes|required|string|max:255',
            'job.no_instansi' => 'sometimes|required|string|max:50',
            'job.golongan_jabatan' => 'sometimes|required|string|max:50',
            'job.nip' => 'sometimes|required|string|max:50',
            'job.masa_kerja_hari' => 'sometimes|required|integer|min:0',
            'job.masa_kerja_bulan' => 'sometimes|required|integer|min:0',
            'job.masa_kerja_tahun' => 'sometimes|required|integer|min:0',
            'job.nama_atasan' => 'sometimes|required|string|max:255',
            'job.alamat_kantor' => 'sometimes|required|string|max:255',
            'nama_ao' => 'sometimes|required|string|max:255',
            'jumlah_penghasilan' => 'sometimes|required|numeric|min:0',
            'jumlah_permohonan' => 'sometimes|required|numeric|min:0',
            'jumlah_penghasilan_lainnya' => 'sometimes|required|numeric|min:0',
            'jangka_waktu' => 'sometimes|required|integer|min:1',
            'maksimal_pembiayaan' => 'sometimes|required|numeric|min:0',
            'tujuan_pembiayaan' => 'sometimes|required|string|max:255',
            'status_perkawinan' => 'sometimes|required|string|in:Single,Married,Widowed,Divorced',
            'upload_npwp' => 'sometimes|required|string',
            'slip_gaji' => 'sometimes|required|string',
            'documents' => 'sometimes|required|array',
            'documents.*.name' => 'sometimes|required|string',
            'documents.*.status' => 'sometimes|required|string',
            // 'documents.*.file_path' => 'sometimes|required|string|max:255',
            'financing_request.total_angsuran_biaya' => 'sometimes|required|numeric',
            'financing_request.jangka_waktu' => 'sometimes|required|integer|min:1',
            'financing_request.cabang' => 'sometimes|required|string|max:255',
            'financing_request.capem' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find the application by ID
            $application = Application::findOrFail($id);

            // Update nasabah details if present in the request
            if ($request->has('nasabah')) {
                $nasabah = $application->nasabah;
                $nasabah->update($request->nasabah);
            }

            // Update job details if present in the request
            if ($request->has('job')) {
                $job = Job::where('nasabah_id', $application->nasabah_id)->first();
                $job->update($request->job);
            }

            // Update application details
            $application->update([
                'nama_ao' => $request->nama_ao,
                'jumlah_penghasilan' => $request->jumlah_penghasilan,
                'jumlah_permohonan' => $request->jumlah_permohonan,
                'jumlah_penghasilan_lainnya' => $request->jumlah_penghasilan_lainnya,
                'jangka_waktu' => $request->jangka_waktu,
                'maksimal_pembiayaan' => $request->maksimal_pembiayaan,
                'tujuan_pembiayaan' => $request->tujuan_pembiayaan,
                'status_perkawinan' => $request->status_perkawinan,
                'upload_npwp' => $request->upload_npwp,
                'slip_gaji' => $request->slip_gaji,
            ]);

            // Update related documents
            // if ($request->has('documents')) {
            //     // Optionally remove existing documents first or handle differently
            //     foreach ($request->documents as $doc) {
            //         Document::updateOrCreate(
            //             ['application_id' => $application->id, 'dokumen_name' => $doc['name']],
            //             ['checklist_status' => $doc['status']],
            //             ['file_path' => $doc['file_path']]
            //         );
            //     }
            // }
            
             // Update related documents
            if ($request->has('documents')) {
                $existingDocuments = Document::where('application_id', $application->id)->pluck('dokumen_name')->toArray();
    
                foreach ($request->documents as $doc) {
                    // Buat atau update dokumen
                    Document::updateOrCreate(
                        ['application_id' => $application->id, 'dokumen_name' => $doc['name']],
                        ['checklist_status' => $doc['status']]
                    );
    
                    // Hapus dokumen yang tidak ada di request
                    if (($key = array_search($doc['name'], $existingDocuments)) !== false) {
                        unset($existingDocuments[$key]);
                    }
                }
    
                // Hapus dokumen yang sudah tidak ada dalam request
                foreach ($existingDocuments as $docName) {
                    Document::where('application_id', $application->id)
                        ->where('dokumen_name', $docName)
                        ->delete();
                }
            }

            // Update financing request
            if ($request->has('financing_request')) {
                $financingRequest = FinancingRequest::where('application_id', $application->id)->first();
                $financingRequest->update($request->financing_request);
            }

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully!',
                'application' => $application,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        try {
            // Temukan aplikasi berdasarkan ID
            $application = Application::with(['documents', 'financingRequest', 'job', 'nasabah'])->findOrFail($id);

            // Hapus semua dokumen yang berhubungan
            foreach ($application->documents as $document) {
                $document->delete();
            }

            // Hapus financing request jika ada
            if ($application->financingRequest) {
                $application->financingRequest->delete();
            }

            // Hapus semua job terkait
            if ($application->job) {
                $application->job->delete();
            }

            // Hapus nasabah jika ada
            if ($application->nasabah) {
                $application->nasabah->delete();
            }

            // Hapus aplikasi itu sendiri
            $application->delete();

            return response()->json([
                'success' => true,
                'message' => 'Application and related data deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



}
