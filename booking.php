<?php

// BAGIAN 0: PROSES PINJAM DARI BOOKING (untuk user/anggota)
if (isset($_GET['pinjam_booking'])) {
    // Cek apakah user sudah login
    if (!isset($_SESSION['pengguna']['id_pengguna'])) {
        echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
        exit();
    }

    // Mengambil ID booking dari URL dan ID pengguna dari session untuk validasi pemilik booking
    $id_booking = mysqli_real_escape_string($koneksi, $_GET['pinjam_booking']);
    $id_pengguna = $_SESSION['pengguna']['id_pengguna'];
    
    // Ambil data booking
    $query_get_booking = "SELECT * FROM booking_buku WHERE id_booking = '$id_booking' AND id_pengguna = '$id_pengguna' AND status_booking = 'masa booking'";
    $result_booking = mysqli_query($koneksi, $query_get_booking);
    
    // Mengecek apakah data booking ditemukan dan masih aktif untuk diproses menjadi peminjaman
    if ($result_booking && mysqli_num_rows($result_booking) > 0) {
        $booking_data = mysqli_fetch_assoc($result_booking);
        $id_buku = $booking_data['id_buku']; // Mengambil id buku dari data booking untuk proses peminjaman
        $tanggal_peminjaman = date('Y-m-d'); // Menentukan tanggal hari ini sebagai tanggal peminjaman
        
        // Cek apakah masih dalam masa booking
        $batas_booking = $booking_data['batas_booking'];

        // Mengecek apakah peminjaman masih berada dalam batas waktu booking
        if (strtotime($tanggal_peminjaman) <= strtotime($batas_booking)) {
            // Insert ke transaksi_peminjaman dengan status_tampil = 1
            $query_insert_pinjam = "INSERT INTO transaksi_peminjaman 
                                   (id_pengguna, id_buku, tanggal_peminjaman, tanggal_pengembalian, status_peminjaman, status_tampil) 
                                   VALUES 
                                   ('$id_pengguna', '$id_buku', '$tanggal_peminjaman', NULL, 'dipinjam', 1)";
            
            // Mengecek apakah proses insert peminjaman ke database berhasil dijalankan
            if (mysqli_query($koneksi, $query_insert_pinjam)) {
                // Update status booking menjadi 'buku dipinjam' (sesuai ENUM database)
                $query_update_booking = "UPDATE booking_buku 
                                        SET status_booking = 'buku dipinjam' 
                                        WHERE id_booking = '$id_booking'";
                mysqli_query($koneksi, $query_update_booking);
                
                // Update status buku menjadi 'dipinjam'
                $query_update_buku = "UPDATE buku SET status_buku = 'dipinjam' WHERE id_buku = '$id_buku'";
                mysqli_query($koneksi, $query_update_buku);
                
                echo "<script>alert('Berhasil meminjam buku! Silakan cek menu Peminjaman.'); window.location.href='?page=booking';</script>";
                exit();
            } else {
                echo "<script>alert('Gagal meminjam buku: " . mysqli_error($koneksi) . "'); window.location.href='?page=booking';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Masa booking sudah habis! Tidak dapat meminjam buku.'); window.location.href='?page=booking';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Data booking tidak ditemukan atau sudah tidak aktif!'); window.location.href='?page=booking';</script>";
        exit();
    }
}

// BAGIAN 1: PROSES UPDATE STATUS (untuk admin dan user)
// Mengambil ID booking dan status baru dari URL untuk proses update
if (isset($_GET['update_status'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_GET['update_status']);
    $status = mysqli_real_escape_string($koneksi, $_GET['status']);
    
    // Validasi status sesuai ENUM database
    $valid_statuses = ['masa booking', 'dibatalkan', 'buku dipinjam'];
    // Mengecek apakah status yang dikirim sesuai dengan pilihan yang diizinkan sistem
    if (!in_array($status, $valid_statuses)) {
        echo "<script>alert('Status tidak valid!'); window.location.href='?page=booking';</script>";
        exit();
    }
    
    // Ambil data booking untuk update status buku
    $query_get_booking = "SELECT id_buku, id_pengguna FROM booking_buku WHERE id_booking = '$id_booking'";
    $result_get_booking = mysqli_query($koneksi, $query_get_booking);
    
    // Cek apakah ini admin atau user yang membatalkan booking sendiri
    $is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';
    $id_pengguna = isset($_SESSION['pengguna']['id_pengguna']) ? $_SESSION['pengguna']['id_pengguna'] : 0;
    
    // Menentukan query update berdasarkan apakah yang mengubah adalah admin atau pengguna biasa
    if ($is_admin) {
        // Admin bisa update semua booking
        $query_update = "UPDATE booking_buku 
                         SET status_booking = '$status' 
                         WHERE id_booking = '$id_booking'";
    } else {
        // User hanya bisa update booking sendiri
        $query_update = "UPDATE booking_buku 
                         SET status_booking = '$status' 
                         WHERE id_booking = '$id_booking' 
                         AND id_pengguna = '$id_pengguna'";
    }
    
    // Mengecek apakah proses update status booking ke database berhasil dijalankan
    if (mysqli_query($koneksi, $query_update)) {
        // PERBAIKAN: Jika dibatalkan, update status buku jadi tersedia lagi
        if ($status == 'dibatalkan' && $result_get_booking && mysqli_num_rows($result_get_booking) > 0) {
            $booking_data = mysqli_fetch_assoc($result_get_booking);
            
            // CEK: Hanya update buku jadi tersedia jika tidak ada booking/peminjaman aktif lain
            $query_cek_aktif = "SELECT COUNT(*) as total FROM (
                                    SELECT id_buku FROM booking_buku 
                                    WHERE id_buku = '" . $booking_data['id_buku'] . "' 
                                    AND status_booking = 'masa booking' 
                                    AND id_booking != '$id_booking'
                                    UNION ALL
                                    SELECT id_buku FROM transaksi_peminjaman 
                                    WHERE id_buku = '" . $booking_data['id_buku'] . "' 
                                    AND status_peminjaman = 'dipinjam'
                                ) as aktif";

            // Mengecek apakah masih ada booking atau peminjaman lain yang aktif pada buku tersebut
            $result_cek = mysqli_query($koneksi, $query_cek_aktif);
            $cek_data = mysqli_fetch_assoc($result_cek);
            
            // Mengubah status buku menjadi tersedia jika tidak ada peminjaman atau booking aktif lain
            if ($cek_data['total'] == 0) {
                $query_update_buku = "UPDATE buku SET status_buku = 'tersedia' WHERE id_buku = '" . $booking_data['id_buku'] . "'";
                mysqli_query($koneksi, $query_update_buku);
            }
        }
        
        echo "<script>alert('Status booking berhasil diupdate!'); window.location.href='?page=booking';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($koneksi) . "');</script>";
    }
}

// BAGIAN 2: PROSES DELETE (SOFT DELETE untuk anggota, HARD DELETE untuk admin)
// Mengecek apakah ada permintaan hapus booking dari URL dan mengambil ID booking serta ID pengguna yang login
if (isset($_GET['delete_booking'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_GET['delete_booking']);
    $id_pengguna_login = isset($_SESSION['pengguna']['id_pengguna']) ? $_SESSION['pengguna']['id_pengguna'] : 0;
    
    // Ambil data booking untuk update status buku
    $query_get = "SELECT id_buku, status_booking, id_pengguna FROM booking_buku WHERE id_booking = '$id_booking'";
    $result_get = mysqli_query($koneksi, $query_get);
    
    // Menentukan apakah pengguna yang melakukan aksi adalah admin atau anggota biasa
    $is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';
    
    // Menentukan jenis penghapusan data apakah hard delete oleh admin atau soft delete oleh anggota
    if ($is_admin) {
        // ADMIN: Hard delete (benar-benar hapus dari database)
        $query_delete = "DELETE FROM booking_buku WHERE id_booking = '$id_booking'";
    } else {
        // ANGGOTA: Soft delete (hanya sembunyikan dari view anggota)
        $query_delete = "UPDATE booking_buku 
                        SET status_tampil = 0 
                        WHERE id_booking = '$id_booking' 
                        AND id_pengguna = '$id_pengguna_login'";
    }
    
    // Mengecek apakah proses penghapusan booking berhasil dijalankan di database
    if (mysqli_query($koneksi, $query_delete)) {
        
        // Mengecek apakah data booking yang dihapus adalah booking aktif milik admin
        if ($is_admin && $result_get && mysqli_num_rows($result_get) > 0) {
            $booking_data = mysqli_fetch_assoc($result_get);
            
            // Mengecek apakah status booking yang dihapus masih dalam masa booking
            if ($booking_data['status_booking'] == 'masa booking') {
                // CEK: Hanya update buku jadi tersedia jika tidak ada booking/peminjaman aktif lain
                $query_cek_aktif = "SELECT COUNT(*) as total FROM (
                                        SELECT id_buku FROM booking_buku 
                                        WHERE id_buku = '" . $booking_data['id_buku'] . "' 
                                        AND status_booking = 'masa booking'
                                        UNION ALL
                                        SELECT id_buku FROM transaksi_peminjaman 
                                        WHERE id_buku = '" . $booking_data['id_buku'] . "' 
                                        AND status_peminjaman = 'dipinjam'
                                    ) as aktif";
                // Mengecek apakah masih ada booking atau peminjaman lain yang aktif pada buku tersebut
                $result_cek = mysqli_query($koneksi, $query_cek_aktif);
                $cek_data = mysqli_fetch_assoc($result_cek);
                
                
                if ($cek_data['total'] == 0) {
                    $query_update_book = "UPDATE buku SET status_buku = 'tersedia' WHERE id_buku = '" . $booking_data['id_buku'] . "'";
                    mysqli_query($koneksi, $query_update_book);
                }
            }
        }
        echo "<script>alert('Data booking berhasil dihapus!'); window.location.href='?page=booking';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($koneksi) . "');</script>";
    }
}

// BAGIAN 3: PROSES BOOKING (jika ada parameter id)
// Mengecek apakah ada permintaan booking dari URL berdasarkan ID buku
if (isset($_GET['id'])) {
    // Cek apakah user sudah login
    if (!isset($_SESSION['pengguna']['id_pengguna'])) {
        echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
        exit();
    }

    // Ambil data dari URL
    $id_buku = mysqli_real_escape_string($koneksi, $_GET['id']);
    $id_pengguna = $_SESSION['pengguna']['id_pengguna'];

    // Cek apakah buku ada
    $query_cek = "SELECT * FROM buku WHERE id_buku = '$id_buku'";
    $result_cek = mysqli_query($koneksi, $query_cek);

    if (mysqli_num_rows($result_cek) == 0) {
        echo "<script>alert('Buku tidak ditemukan!'); window.location.href='?page=buku';</script>";
        exit();
    }

    // Mengambil data buku untuk ditampilkan di alert setelah proses booking berhasil
    $buku = mysqli_fetch_assoc($result_cek);

    // Cek apakah user sudah booking buku ini dan masih masa booking
    $query_cek_booking = "SELECT * FROM booking_buku 
                          WHERE id_pengguna = '$id_pengguna' 
                          AND id_buku = '$id_buku' 
                          AND status_booking = 'masa booking'";
    $result_cek_booking = mysqli_query($koneksi, $query_cek_booking);

    if (mysqli_num_rows($result_cek_booking) > 0) {
        echo "<script>alert('Anda sudah melakukan booking untuk buku ini!'); window.location.href='?page=buku';</script>";
        exit();
    }

    // Cek apakah user sudah meminjam buku ini
    $query_cek_peminjaman = "SELECT * FROM transaksi_peminjaman 
                             WHERE id_pengguna = '$id_pengguna' 
                             AND id_buku = '$id_buku' 
                             AND status_peminjaman = 'dipinjam'";
    $result_cek_peminjaman = mysqli_query($koneksi, $query_cek_peminjaman);

    if (mysqli_num_rows($result_cek_peminjaman) > 0) {
        echo "<script>alert('Anda sudah meminjam buku ini! Tidak perlu booking.'); window.location.href='?page=buku';</script>";
        exit();
    }

    // Insert data booking - BATAS 2 HARI OTOMATIS TERISI dengan status_tampil = 1
    $tanggal_booking = date('Y-m-d');
    $batas_booking = date('Y-m-d', strtotime('+2 days')); // OTOMATIS 2 HARI DARI SEKARANG
    $status_booking = 'masa booking';
    
    // Menyimpan data booking baru ke dalam database
    $query_insert = "INSERT INTO booking_buku 
                     (id_pengguna, id_buku, tanggal_booking, batas_booking, status_booking, status_tampil) 
                     VALUES 
                     ('$id_pengguna', '$id_buku', '$tanggal_booking', '$batas_booking', '$status_booking', 1)";

    // Mengecek apakah proses insert booking ke database berhasil dijalankan
    if (mysqli_query($koneksi, $query_insert)) {
        // Update status buku menjadi dibooking
        $query_update_buku = "UPDATE buku SET status_buku = 'dibooking' WHERE id_buku = '$id_buku'";
        $update_result = mysqli_query($koneksi, $query_update_buku);
        
        if (!$update_result) {
            // Log jika gagal update status buku
            error_log("Gagal update status buku: " . mysqli_error($koneksi));
        }
        
        echo "<script>
            alert('Booking berhasil untuk buku \"" . addslashes($buku['judul']) . "\"!\\nBatas ambil buku sampai " . date('d/m/Y', strtotime($batas_booking)) . " (2 hari dari sekarang)');
            window.location.href='?page=booking';
        </script>";
        exit();
    } else {
        echo "<script>alert('Gagal melakukan booking: " . mysqli_error($koneksi) . "'); window.location.href='?page=buku';</script>";
        exit();
    }
}

// BAGIAN 4: AUTO-UPDATE STATUS (Tidak perlu migrasi, langsung gunakan status yang benar)
// AUTO-CANCEL BOOKING YANG LEWAT BATAS
$query_auto_cancel = "UPDATE booking_buku 
                      SET status_booking = 'dibatalkan' 
                      WHERE status_booking = 'masa booking' 
                      AND batas_booking < CURDATE()";
mysqli_query($koneksi, $query_auto_cancel);

// PERBAIKAN: Update status buku jadi tersedia lagi untuk booking yang dibatalkan
// Hanya jika tidak ada booking/peminjaman aktif lain
$query_update_buku_cancel = "UPDATE buku b
                              SET b.status_buku = 'tersedia'
                              WHERE b.status_buku = 'dibooking'
                              AND NOT EXISTS (
                                  SELECT 1 FROM booking_buku bb 
                                  WHERE bb.id_buku = b.id_buku 
                                  AND bb.status_booking = 'masa booking'
                              )
                              AND NOT EXISTS (
                                  SELECT 1 FROM transaksi_peminjaman tp 
                                  WHERE tp.id_buku = b.id_buku 
                                  AND tp.status_peminjaman = 'dipinjam'
                              )";
mysqli_query($koneksi, $query_update_buku_cancel);

// BAGIAN 5: TAMPILAN DAFTAR BOOKING
$is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';

// Query data booking berdasarkan status pengguna
if ($is_admin) {
    // Query untuk admin - tampilkan SEMUA booking (termasuk yang di-soft delete anggota)
    $query = "SELECT bb.id_booking, bb.id_pengguna, bb.id_buku,
                     bb.tanggal_booking, bb.batas_booking, bb.status_booking,
                     b.judul, b.penulis, b.penerbit,
                     p.nama, p.email 
              FROM booking_buku bb 
              LEFT JOIN buku b ON bb.id_buku = b.id_buku 
              LEFT JOIN pengguna p ON bb.id_pengguna = p.id_pengguna 
              ORDER BY bb.tanggal_booking DESC";
} else {
    // Query untuk anggota - hanya tampilkan booking sendiri yang TIDAK di-soft delete
    $id_pengguna = isset($_SESSION['pengguna']['id_pengguna']) ? $_SESSION['pengguna']['id_pengguna'] : 0;
    $query = "SELECT bb.id_booking, bb.id_pengguna, bb.id_buku,
                     bb.tanggal_booking, bb.batas_booking, bb.status_booking,
                     b.judul, b.penulis, b.penerbit 
              FROM booking_buku bb 
              LEFT JOIN buku b ON bb.id_buku = b.id_buku 
              WHERE bb.id_pengguna = '$id_pengguna' 
              AND bb.status_tampil = 1
              ORDER BY bb.tanggal_booking DESC";
}

$result = mysqli_query($koneksi, $query);

// Cek jika query gagal
if (!$result) {
    $error_message = mysqli_error($koneksi);
}
?>

<div class="card">
    <div class="card-body">
        <h1 class="mt-4">
            <?php echo $is_admin ? 'Kelola Booking Buku' : 'Daftar Booking Saya'; ?>
        </h1>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <strong>Error Query:</strong> <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php else: ?>
        
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php if ($is_admin): ?>
                            <th>Nama Anggota</th>
                            <th>Email</th>
                            <?php endif; ?>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tanggal Booking</th>
                            <th>Batas Booking</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0):
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
                                $batas_lewat = strtotime($row['batas_booking']) < strtotime(date('Y-m-d'));
                                $tanggal_sekarang = date('Y-m-d');
                                $masih_masa_booking = strtotime($tanggal_sekarang) <= strtotime($row['batas_booking']);
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <?php if ($is_admin): ?>
                            <td><?php echo htmlspecialchars($row['nama'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($row['judul'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['penulis'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['penerbit'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_booking'])); ?></td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($row['batas_booking'])); ?>
                                <?php if ($batas_lewat && $row['status_booking'] == 'masa booking'): ?>
                                <br><span class="badge bg-danger text-white">⚠️ Lewat batas!</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $status = $row['status_booking'] ?? '';
                                
                                if (empty($status)) {
                                    echo '<span class="badge bg-secondary">Tidak ada status</span>';
                                } else {
                                    $badge_class = '';
                                    $text_class = 'text-white';
                                    
                                    switch($status) {
                                        case 'masa booking':
                                            $badge_class = 'bg-warning';
                                            $text_class = 'text-dark';
                                            break;
                                        case 'dibatalkan':
                                            $badge_class = 'bg-danger';
                                            break;
                                        case 'buku dipinjam':
                                            $badge_class = 'bg-success';
                                            break;
                                        default:
                                            $badge_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?> <?php echo $text_class; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $status)); ?>
                                    </span>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($is_admin): ?>
                                    <!-- Tombol untuk Admin -->
                                    <a href="?page=booking&delete_booking=<?php echo $row['id_booking']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                <?php else: ?>
                                    <!-- Tombol untuk User/Anggota -->
                                    <?php if ($row['status_booking'] == 'masa booking' && $masih_masa_booking): ?>
                                    <!-- Tombol Pinjam (hanya muncul jika masih dalam masa booking) -->
                                    <a href="?page=booking&pinjam_booking=<?php echo $row['id_booking']; ?>" 
                                       class="btn btn-primary btn-sm mb-1" 
                                       onclick="return confirm('Pinjam buku ini sekarang?')">
                                        Pinjam
                                    </a>
                                    <br>
                                    <!-- Tombol Batalkan (hanya muncul jika masih dalam masa booking) -->
                                    <a href="?page=booking&update_status=<?php echo $row['id_booking']; ?>&status=dibatalkan" 
                                       class="btn btn-warning btn-sm mb-1" 
                                       onclick="return confirm('Batalkan booking ini?')">Batalkan</a>
                                    <br>
                                    <?php endif; ?>
                                    
                                    <!-- Tombol Hapus Riwayat (selalu muncul, hanya hapus riwayat sendiri) -->
                                    <a href="?page=booking&delete_booking=<?php echo $row['id_booking']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus riwayat booking ini?')">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="<?php echo $is_admin ? '10' : '8'; ?>" class="text-center">
                                <?php echo $is_admin ? 'Belum ada data booking dari anggota.' : 'Anda belum memiliki riwayat booking.'; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>