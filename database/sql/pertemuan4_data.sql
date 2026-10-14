
USE praktikum_web_2401020108;

INSERT INTO program_studi (nama_prodi) VALUES
    ('Teknik Informatika'),
    ('Teknik Perkapalan');

INSERT INTO mahasiswa
    (nim, nama, email, usia, program_studi_id)
VALUES
    ('2401020128', 'Velano', 'velano@example.com', 20, 1),
    ('2401020106', 'Arif Himawan', 'arif.himawan@example.com', 20, 1),
    ('2401030105', 'Azril', 'azril@example.com', 21, 2),
    ('2401020104', 'grant', 'grant@example.com', 18, 2);

UPDATE mahasiswa
SET email = 'velano.praktikum@example.com'
WHERE nim = '2401020128';

DELETE FROM mahasiswa
WHERE nim = '2401020199';

SELECT m.nim, m.nama, m.email, m.usia, p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;
