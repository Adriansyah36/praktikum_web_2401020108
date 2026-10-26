
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
</head>
<body>

    <h1>Edit Data Mahasiswa</h1>

    @if ($errors->any())
        <div style="color: red;">
            <p>Periksa kembali input berikut:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('mahasiswa.update', $mahasiswa->id) }}"
          method="POST">
        @csrf
        @method('PUT')

        <p>
            <label for="nim">NIM:</label><br>
            <input
                type="text"
                id="nim"
                name="nim"
                value="{{ old('nim', $mahasiswa->nim) }}"
                maxlength="10"
                required
            >
        </p>

        <p>
            <label for="nama">Nama:</label><br>
            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama', $mahasiswa->nama) }}"
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
                value="{{ old('email', $mahasiswa->email) }}"
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
                value="{{ old('usia', $mahasiswa->usia) }}"
                min="15"
                max="100"
                required
            >
        </p>

        <p>
            <label for="program_studi_id">Program Studi:</label><br>
            <select id="program_studi_id"
                    name="program_studi_id"
                    required>
                <option value="">-- Pilih Program Studi --</option>

                @foreach ($daftarProgramStudi as $program)
                    <option
                        value="{{ $program->id }}"
                        {{ (string) old('program_studi_id', $mahasiswa->program_studi_id) === (string) $program->id ? 'selected' : '' }}
                    >
                        {{ $program->nama_prodi }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('mahasiswa.index') }}">Kembali</a>
    </form>

</body>
</html>
