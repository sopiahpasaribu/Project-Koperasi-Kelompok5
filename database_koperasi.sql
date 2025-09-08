CREATE DATABASE koperasi;
USE koperasi;


CREATE OR REPLACE TABLE admin(
	id_admin INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	nik_user CHAR(21),
	nama_user VARCHAR (50),
	tmp_lahir VARCHAR(20),
	tgl_lahir DATE,
	j_kelamin CHAR(1),
	agama VARCHAR(17),
	alamat VARCHAR(20),
	STATUS VARCHAR(30),
	nohp CHAR(12)
);

CREATE OR REPLACE TABLE ROLE(
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    jabatan VARCHAR(35),
    tgl_awal_kerja TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_id FOREIGN KEY(admin_id) REFERENCES admin(id_admin)	
    
);


CREATE OR REPLACE TABLE login(
	id INT AUTO_INCREMENT PRIMARY KEY,
	admin_id INT,
	username VARCHAR(20),
	PASSWORD VARCHAR(10),
	email VARCHAR(30),
	CONSTRAINT fk_admin_id1 FOREIGN KEY(admin_id) REFERENCES admin(id_admin)	

);

CREATE OR REPLACE TABLE anggota(
	id_anggota INT AUTO_INCREMENT PRIMARY KEY,
	admin_id INT(1),
	no_ktp CHAR(16),
	nama VARCHAR(50),
	tmp_lahir VARCHAR(30),
	tgl_lahir DATE,
	j_kelamin CHAR(1),
	agama VARCHAR(17),
	alamat VARCHAR(20),
	telp CHAR(12),
	pekerjaan VARCHAR(50),
	tgl_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	STATUS VARCHAR(9),
	alamat_gmb VARCHAR(100),
	simpanan_pokok INT(10),
	CONSTRAINT fk_id_admin FOREIGN KEY(admin_id) REFERENCES admin(id_admin)
);

CREATE OR REPLACE TABLE simpanan_wajib(
	id_simpan INT AUTO_INCREMENT PRIMARY KEY,
	admin_id INT(1),
	anggota_id INT,
	tgl_transaksi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	saldo_masuk INT(10),
	jumlah_saldo INT(10),
	jh_simpanan DATE NOT NULL,
	saldo_keluar INT(10),
	CONSTRAINT fk_id_anggota FOREIGN KEY(anggota_id) REFERENCES anggota(id_anggota),
	CONSTRAINT fk_id_admin1 FOREIGN KEY(admin_id) REFERENCES admin(id_admin)
);

CREATE OR REPLACE TABLE pinjaman(
	id_pinjam INT AUTO_INCREMENT PRIMARY KEY,
	admin_id INT(1),
	anggota_id INT,
	tgl_pinjam DATE,
	jh_pinjam DATE,
	jumlah_pinjaman INT(7),
	biaya_angsuran INT(7),
	adm INT(7),
	bunga INT(7),
	lma_angsur CHAR(2),
	jh_angsur DATE,
	status_pinjaman VARCHAR(20),
	CONSTRAINT fk_id_anggota1 FOREIGN KEY(anggota_id) REFERENCES anggota(id_anggota),
	CONSTRAINT fk_id_admin2 FOREIGN KEY(admin_id) REFERENCES admin(id_admin)
);

CREATE OR REPLACE TABLE angsuran(
	no_angsuran INT(7) AUTO_INCREMENT PRIMARY KEY,
	admin_id INT(1),
	anggota_id INT,
	pinjam_id INT(5),
	angsuran_ke VARCHAR(2),
	jh_angsuran INT(10),
	tgl_jh_tempo DATE,
	tgl_bayar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT fk_id_pinjam FOREIGN KEY(pinjam_id) REFERENCES pinjaman(id_pinjam),
	CONSTRAINT fk_id_anggota2 FOREIGN KEY(anggota_id) REFERENCES anggota(id_anggota),
	CONSTRAINT fk_id_admin3 FOREIGN KEY(admin_id) REFERENCES admin(id_admin)
);


INSERT INTO admin (nik_user, nama_user, tmp_lahir, tgl_lahir, j_kelamin, agama, alamat,STATUS, nohp) VALUES 
('034256178231', 'Putri Ayunda', 'Majalengka', '2004-08-19', 'P', 'Islam', 'Majalengka','aktif', '082118908801');

INSERT INTO ROLE (admin_id, jabatan) VALUES
('1', 'Ketua Koperasi');

INSERT INTO login (admin_id, username, PASSWORD, email) VALUES
('1','Ayunda', 'admin123', 'gustiara1923@gmail.com');
