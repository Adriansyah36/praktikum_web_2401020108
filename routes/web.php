<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/latihan-php', function () {
    $nama = 'M.Adriansyah';
    $nilai = [60, 65, 70, 75, 70];

    $hitungRataRata = function (array $data): float {
        $total = 0;

        foreach ($data as $angka) {
            $total += $angka;
        }

        return $total / count($data);
    };

    $rataRata = $hitungRataRata($nilai);

    if ($rataRata >= 75) {
        $status = 'Lulus';
    } else {
        $status = 'Perlu Perbaikan';
    }

    return view('latihan-php', compact(
        'nama',
        'nilai',
        'rataRata',
        'status'
    ));
});

Route::get('/form-mahasiswa', function () {
    return view('form-mahasiswa');
});
Route::post('/form-mahasiswa', function (Request $request) {

    $dataBersih = [
        'nama' => strip_tags(
            trim((string) $request->input('nama'))
        ),

        'email' => filter_var(
            (string) $request->input('email'),
            FILTER_SANITIZE_EMAIL
        ),

        'usia' => trim(
            (string) $request->input('usia')
        ),

        'nim' => trim(
            (string) $request->input('nim')
        ),
    ];

    // Validasi data
    $validator = Validator::make(
        $dataBersih,
        [
            'nama' => [
                'required',
                'min:3',
                'max:50'
            ],
            'email' => [
                'required',
                'email'
            ],
            'usia' => [
                'required',
                'integer',
                'min:17',
                'max:60'
            ],
            'nim' => [
                'required',
                'digits_between:8,12'
            ],
        ],
        [
            'nama.required' => 'Nama wajib diisi.',
            'nama.min' => 'Nama minimal 3 karakter.',
            'nama.max' => 'Nama maksimal 50 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',

            'usia.required' => 'Usia wajib diisi.',
            'usia.integer' => 'Usia harus berupa angka.',
            'usia.min' => 'Usia minimal 17 tahun.',
            'usia.max' => 'Usia maksimal 60 tahun.',

            'nim.required' => 'NIM wajib diisi.',
            'nim.digits_between' => 'NIM harus terdiri dari 8 sampai 12 digit.',
        ]
    );

    // Jika validasi gagal
    if ($validator->fails()) {
        return redirect('/form-mahasiswa')
            ->withErrors($validator)
            ->withInput();
    }

    // Mengambil data yang sudah lolos validasi
    $data = $validator->validated();

    // Mengubah usia menjadi integer
    $data['usia'] = (int) $data['usia'];

    // Menampilkan halaman hasil
    return view('hasil-form', [
        'data' => $data
    ]);
});

Route::get('/mahasiswa', function () {
    try {
        $pdo = DB::connection()->getPdo();

        // Mengambil data mahasiswa beserta nama program studi
        $sqlMahasiswa = '
            SELECT
                m.nim,
                m.nama,
                m.email,
                m.usia,
                p.nama_prodi
            FROM mahasiswa AS m
            INNER JOIN program_studi AS p
                ON p.id = m.program_studi_id
            ORDER BY m.nim
        ';

        $statementMahasiswa = $pdo->prepare($sqlMahasiswa);
        $statementMahasiswa->execute();

        $daftarMahasiswa = $statementMahasiswa->fetchAll(
            \PDO::FETCH_ASSOC
        );

        // Mengambil daftar program studi
        $sqlProgramStudi = '
            SELECT id, nama_prodi
            FROM program_studi
            ORDER BY nama_prodi
        ';

        $statementProgramStudi = $pdo->prepare($sqlProgramStudi);
        $statementProgramStudi->execute();

        $daftarProgramStudi = $statementProgramStudi->fetchAll(
            \PDO::FETCH_ASSOC
        );

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Koneksi atau query basis data gagal. Periksa konfigurasi database.',
            500
        );
    }
})->name('mahasiswa.index');
Route::post('/mahasiswa', function (Request $request) {
    $validated = $request->validate(
        [
            'nim' => 'required|string|max:20',
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'usia' => 'required|integer|min:15|max:100',
            'program_studi_id' => 'required|integer',
        ],
        [
            'nim.required' => 'NIM wajib diisi.',
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'usia.required' => 'Usia wajib diisi.',
            'usia.integer' => 'Usia harus berupa angka bulat.',
            'usia.min' => 'Usia minimal 15 tahun.',
            'usia.max' => 'Usia maksimal 100 tahun.',
            'program_studi_id.required' => 'Program studi wajib dipilih.',
        ]
    );

    try {
        $pdo = DB::connection()->getPdo();

        $stmtCek = $pdo->prepare(
            'SELECT COUNT(*)
             FROM mahasiswa
             WHERE nim = :nim OR email = :email'
        );

        $stmtCek->execute([
            'nim' => $validated['nim'],
            'email' => $validated['email'],
        ]);

        $jumlahDuplikat = (int) $stmtCek->fetchColumn();

        if ($jumlahDuplikat > 0) {
            return redirect()
                ->back()
                ->withInput()
                ->with('gagal', 'NIM atau email sudah digunakan.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO mahasiswa
                (nim, nama, email, usia, program_studi_id)
             VALUES
                (:nim, :nama, :email, :usia, :program_studi_id)'
        );

        $stmt->execute([
            'nim' => $validated['nim'],
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'usia' => $validated['usia'],
            'program_studi_id' => $validated['program_studi_id'],
        ]);

        return redirect()
            ->route('mahasiswa.index')
            ->with('berhasil', 'Data mahasiswa berhasil ditambahkan.');
    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->back()
            ->withInput()
            ->with('gagal', 'Data mahasiswa gagal ditambahkan. Periksa kembali data dan koneksi database.');
    }
})->name('mahasiswa.store');