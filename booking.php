<?php

if (isset($_GET['pinjam_booking'])) {
    if (!isset($_SESSION['pengguna']['id_pengguna'])) {
        echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
        exit();
    }

    $id_booking = mysqli_real_escape_string($koneksi, $_GET['pinjam_booking']);
    $id_pengguna = $_SESSION['pengguna']['id_pengguna'];
    
    $query_get_booking = "SELECT * FROM booking_buku WHERE id_booking = '$id_booking' AND id_pengguna = '$id_pengguna' AND status_booking = 'masa booking'";
    $result_booking = mysqli_query($koneksi, $query_get_booking);
    
    if ($result_booking && mysqli_num_rows($result_booking) > 0) {
        $booking_data = mysqli_fetch_assoc($result_booking);
        $id_buku = $booking_data['id_buku'];
        $tanggal_peminjaman = date('Y-m-d');
        
        $batas_booking = $booking_data['batas_booking'];

        if (strtotime($tanggal_peminjaman) <= strtotime($batas_booking)) {
            $query_insert_pinjam = "INSERT INTO transaksi_peminjaman 
                                   (id_pengguna, id_buku, tanggal_peminjaman, tanggal_pengembalian, status_peminjaman, status_tampil) 
                                   VALUES 
                                   ('$id_pengguna', '$id_buku', '$tanggal_peminjaman', NULL, 'dipinjam', 1)";
            
            if (mysqli_query($koneksi, $query_insert_pinjam)) {
                $query_update_booking = "UPDATE booking_buku 
                                        SET status_booking = 'buku dipinjam' 
                                        WHERE id_booking = '$id_booking'";
                mysqli_query($koneksi, $query_update_booking);
                
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

if (isset($_GET['update_status'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_GET['update_status']);
    $status = mysqli_real_escape_string($koneksi, $_GET['status']);
    
    $valid_statuses = ['masa booking', 'dibatalkan', 'buku dipinjam'];
    if (!in_array($status, $valid_statuses)) {
        echo "<script>alert('Status tidak valid!'); window.location.href='?page=booking';</script>";
        exit();
    }
    
    $query_get_booking = "SELECT id_buku, id_pengguna FROM booking_buku WHERE id_booking = '$id_booking'";
    $result_get_booking = mysqli_query($koneksi, $query_get_booking);
    
    $is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';
    $id_pengguna = isset($_SESSION['pengguna']['id_pengguna']) ? $_SESSION['pengguna']['id_pengguna'] : 0;
    
    if ($is_admin) {
        $query_update = "UPDATE booking_buku 
                         SET status_booking = '$status' 
                         WHERE id_booking = '$id_booking'";
    } else {
        $query_update = "UPDATE booking_buku 
                         SET status_booking = '$status' 
                         WHERE id_booking = '$id_booking' 
                         AND id_pengguna = '$id_pengguna'";
    }
    
    if (mysqli_query($koneksi, $query_update)) {
        if ($status == 'dibatalkan' && $result_get_booking && mysqli_num_rows($result_get_booking) > 0) {
            $booking_data = mysqli_fetch_assoc($result_get_booking);
            
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

            $result_cek = mysqli_query($koneksi, $query_cek_aktif);
            $cek_data = mysqli_fetch_assoc($result_cek);
            
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

if (isset($_GET['delete_booking'])) {
    $id_booking = mysqli_real_escape_string($koneksi, $_GET['delete_booking']);
    $id_pengguna_login = isset($_SESSION['pengguna']['id_pengguna']) ? $_SESSION['pengguna']['id_pengguna'] : 0;
    
    $query_get = "SELECT id_buku, status_booking, id_pengguna FROM booking_buku WHERE id_booking = '$id_booking'";
    $result_get = mysqli_query($koneksi, $query_get);
    
    $is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';
    
    if ($is_admin) {
        $query_delete = "DELETE FROM booking_buku WHERE id_booking = '$id_booking'";
    } else {
        $query_delete = "UPDATE booking_buku 
                        SET status_tampil = 0 
                        WHERE id_booking = '$id_booking' 
                        AND id_pengguna = '$id_pengguna_login'";
    }
    
    if (mysqli_query($koneksi, $query_delete)) {
        
        if ($is_admin && $result_get && mysqli_num_rows($result_get) > 0) {
            $booking_data = mysqli_fetch_assoc($result_get);
            
            if ($booking_data['status_booking'] == 'masa booking') {
                $query_cek_aktif = "SELECT COUNT(*) as total FROM (
                                        SELECT id_buku FROM booking_buku 
                                        WHERE id_buku = '" . $booking_data['id_buku'] . "' 
                                        AND status_booking = 'masa booking'
                                        UNION ALL
                                        SELECT id_buku FROM transaksi_peminjaman 
                                        WHERE id_buku = '" . $booking_data['id_buku'] . "' 
                                        AND status_peminjaman = 'dipinjam'
                                    ) as aktif";
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

if (isset($_GET['id'])) {
    if (!isset($_SESSION['pengguna']['id_pengguna'])) {
        echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
        exit();
    }

    $id_buku = mysqli_real_escape_string($koneksi, $_GET['id']);
    $id_pengguna = $_SESSION['pengguna']['id_pengguna'];

    $query_cek = "SELECT * FROM buku WHERE id_buku = '$id_buku'";
    $result_cek = mysqli_query($koneksi, $query_cek);

    if (mysqli_num_rows($result_cek) == 0) {
        echo "<script>alert('Buku tidak ditemukan!'); window.location.href='?page=buku';</script>";
        exit();
    }

    $buku = mysqli_fetch_assoc($result_cek);

    $query_cek_booking = "SELECT * FROM booking_buku 
                          WHERE id_pengguna = '$id_pengguna' 
                          AND id_buku = '$id_buku' 
                          AND status_booking = 'masa booking'";
    $result_cek_booking = mysqli_query($koneksi, $query_cek_booking);

    if (mysqli_num_rows($result_cek_booking) > 0) {
        echo "<script>alert('Anda sudah melakukan booking untuk buku ini!'); window.location.href='?page=buku';</script>";
        exit();
    }

    $query_cek_peminjaman = "SELECT * FROM transaksi_peminjaman 
                             WHERE id_pengguna = '$id_pengguna' 
                             AND id_buku = '$id_buku' 
                             AND status_peminjaman = 'dipinjam'";
    $result_cek_peminjaman = mysqli_query($koneksi, $query_cek_peminjaman);

    if (mysqli_num_rows($result_cek_peminjaman) > 0) {
        echo "<script>alert('Anda sudah meminjam buku ini! Tidak perlu booking.'); window.location.href='?page=buku';</script>";
        exit();
    }

    $tanggal_booking = date('Y-m-d');
    $batas_booking = date('Y-m-d', strtotime('+2 days'));
    $status_booking = 'masa booking';
    
    $query_insert = "INSERT INTO booking_buku 
                     (id_pengguna, id_buku, tanggal_booking, batas_booking, status_booking, status_tampil) 
                     VALUES 
                     ('$id_pengguna', '$id_buku', '$tanggal_booking', '$batas_booking', '$status_booking', 1)";

    if (mysqli_query($koneksi, $query_insert)) {
        $query_update_buku = "UPDATE buku SET status_buku = 'dibooking' WHERE id_buku = '$id_buku'";
        $update_result = mysqli_query($koneksi, $query_update_buku);
        
        if (!$update_result) {
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

$query_auto_cancel = "UPDATE booking_buku 
                      SET status_booking = 'dibatalkan' 
                      WHERE status_booking = 'masa booking' 
                      AND batas_booking < CURDATE()";
mysqli_query($koneksi, $query_auto_cancel);

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

$is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';

if ($is_admin) {
    $query = "SELECT bb.id_booking, bb.id_pengguna, bb.id_buku,
                     bb.tanggal_booking, bb.batas_booking, bb.status_booking,
                     b.judul, b.penulis, b.penerbit,
                     p.nama, p.email 
              FROM booking_buku bb 
              LEFT JOIN buku b ON bb.id_buku = b.id_buku 
              LEFT JOIN pengguna p ON bb.id_pengguna = p.id_pengguna 
              ORDER BY bb.tanggal_booking DESC";
} else {
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
                                    <a href="?page=booking&delete_booking=<?php echo $row['id_booking']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                <?php else: ?>
                                    <?php if ($row['status_booking'] == 'masa booking' && $masih_masa_booking): ?>
                                    <a href="?page=booking&pinjam_booking=<?php echo $row['id_booking']; ?>" 
                                       class="btn btn-primary btn-sm mb-1" 
                                       onclick="return confirm('Pinjam buku ini sekarang?')">
                                        Pinjam
                                    </a>
                                    <br>
                                    <a href="?page=booking&update_status=<?php echo $row['id_booking']; ?>&status=dibatalkan" 
                                       class="btn btn-warning btn-sm mb-1" 
                                       onclick="return confirm('Batalkan booking ini?')">Batalkan</a>
                                    <br>
                                    <?php endif; ?>
                                    
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