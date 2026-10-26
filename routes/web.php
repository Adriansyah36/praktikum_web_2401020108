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


Route::get('/mahasiswa', function (Request $request) {
    try {
        $pdo = DB::connection()->getPdo();

        // Mengambil kata kunci pencarian dan filter program studi
        $kataKunci = trim($request->query('cari', ''));
        $programStudiId = $request->query('program_studi_id', '');

        // Query dasar untuk mengambil data mahasiswa
        $sqlMahasiswa = '
            SELECT
                m.id,
                m.nim,
                m.nama,
                m.email,
                m.usia,
                m.program_studi_id,
                p.nama_prodi
            FROM mahasiswa AS m
            INNER JOIN program_studi AS p
                ON p.id = m.program_studi_id
            WHERE (
                       m.nim LIKE :nim
                      OR m.nama LIKE :nama
                      OR m.email LIKE :email
)
        ';

        $parameter = [
            'nim' => '%' . $kataKunci . '%',
            'nama' => '%' . $kataKunci . '%',
            'email' => '%' . $kataKunci . '%',
        ];

        // Filter berdasarkan program studi jika dipilih
        if ($programStudiId !== '') {
            $sqlMahasiswa .= '
                AND m.program_studi_id = :program_studi_id
            ';

            $parameter['program_studi_id'] = $programStudiId;
        }

        $sqlMahasiswa .= ' ORDER BY m.nim';

        $statementMahasiswa = $pdo->prepare($sqlMahasiswa);
        $statementMahasiswa->execute($parameter);

        $daftarMahasiswa = $statementMahasiswa->fetchAll(
            \PDO::FETCH_ASSOC
        );

        // Mengambil daftar program studi untuk pilihan filter
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
            'daftarProgramStudi',
            'kataKunci',
            'programStudiId'
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

/*
|--------------------------------------------------------------------------
| Update dan Delete Mahasiswa
|--------------------------------------------------------------------------
*/

// Menampilkan formulir edit mahasiswa
Route::get('/mahasiswa/{id}/edit', function ($id) {
    $mahasiswa = DB::table('mahasiswa')
        ->where('id', $id)
        ->first();

    if (!$mahasiswa) {
        abort(404);
    }

    $daftarProgramStudi = DB::table('program_studi')
        ->orderBy('nama_prodi')
        ->get();

    return view('mahasiswa-edit', compact(
        'mahasiswa',
        'daftarProgramStudi'
    ));
})->name('mahasiswa.edit');


// Menyimpan perubahan data mahasiswa
Route::put('/mahasiswa/{id}', function (Request $request, $id) {
    if (!DB::table('mahasiswa')->where('id', $id)->exists()) {
        abort(404);
    }

    $data = $request->validate([
        'nim' => 'required|string|max:10|unique:mahasiswa,nim,' . $id . ',id',
        'nama' => 'required|string|max:100',
        'email' => 'required|email|max:100|unique:mahasiswa,email,' . $id . ',id',
        'usia' => 'required|integer|min:15|max:100',
        'program_studi_id' => 'required|integer|exists:program_studi,id',
    ]);

    DB::table('mahasiswa')
        ->where('id', $id)
        ->update($data);

    return redirect()
        ->route('mahasiswa.index')
        ->with('berhasil', 'Data mahasiswa berhasil diperbarui.');
})->name('mahasiswa.update');


// Menghapus data mahasiswa
Route::delete('/mahasiswa/{id}', function ($id) {
    $jumlahDihapus = DB::table('mahasiswa')
        ->where('id', $id)
        ->delete();

    if ($jumlahDihapus > 0) {
        return redirect()
            ->route('mahasiswa.index')
            ->with('berhasil', 'Data mahasiswa berhasil dihapus.');
    }

    return redirect()
        ->route('mahasiswa.index')
        ->with('gagal', 'Data mahasiswa tidak ditemukan.');
})->name('mahasiswa.destroy');
