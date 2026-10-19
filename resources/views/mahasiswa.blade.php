<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
</head>
<body>

    <h1>Data Mahasiswa</h1>

    @if (session('berhasil'))
        <p style="color: green;">
            {{ session('berhasil') }}
        </p>
    @endif

    @if (session('gagal'))
        <p style="color: red;">
            {{ session('gagal') }}
        </p>
    @endif

    @if ($errors->any())
        <div>
            <p>Terjadi kesalahan pada input:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h2>Tambah Mahasiswa</h2>

    <form action="{{ route('mahasiswa.store') }}" method="POST">
        @csrf

        <p>
            <label for="nim">NIM:</label><br>
            <input
                type="text"
                id="nim"
                name="nim"
                value="{{ old('nim') }}"
                maxlength="20"
                required
            >
        </p>

        <p>
            <label for="nama">Nama:</label><br>
            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama') }}"
                maxlength="100"
                required
            >
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                maxlength="100"
                required
            >
        </p>

        <p>
            <label for="usia">Usia:</label><br>
            <input
                type="number"
                id="usia"
                name="usia"
                value="{{ old('usia') }}"
                min="15"
                max="100"
                required
            >
        </p>

        <p>
            <label for="program_studi_id">Program Studi:</label><br>

            <select
                id="program_studi_id"
                name="program_studi_id"
                required
            >
                <option value="">-- Pilih Program Studi --</option>

                @foreach ($daftarProgramStudi as $program)
                    <option
                        value="{{ $program['id'] }}"
                        {{ old('program_studi_id') == $program['id'] ? 'selected' : '' }}
                    >
                        {{ $program['nama_prodi'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Tambah Mahasiswa</button>
    </form>

    <hr>

    <h2>Daftar Mahasiswa</h2>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Usia</th>
                <th>Program Studi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarMahasiswa as $mahasiswa)
                <tr>
                    <td>{{ $mahasiswa['nim'] }}</td>
                    <td>{{ $mahasiswa['nama'] }}</td>
                    <td>{{ $mahasiswa['email'] }}</td>
                    <td>{{ $mahasiswa['usia'] }}</td>
                    <td>{{ $mahasiswa['nama_prodi'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data mahasiswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>