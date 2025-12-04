<?php

// PROSES KONFIRMASI PENGEMBALIAN (hanya untuk admin)

if (isset($_GET['kembalikan']) && isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin') {
   
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_GET['kembalikan']);
    $tanggal_pengembalian = date('Y-m-d');
    
    $query_get_pinjam = "SELECT id_buku, id_pengguna FROM transaksi_peminjaman WHERE id_peminjaman = '$id_peminjaman'";
    $result_get_pinjam = mysqli_query($koneksi, $query_get_pinjam);

    $query_update = "UPDATE transaksi_peminjaman 
                     SET tanggal_pengembalian = '$tanggal_pengembalian', 
                         status_peminjaman = 'dikembalikan' 
                     WHERE id_peminjaman = '$id_peminjaman'";
    
    if (mysqli_query($koneksi, $query_update)) {
        
        if ($result_get_pinjam && mysqli_num_rows($result_get_pinjam) > 0) {
            $pinjam_data = mysqli_fetch_assoc($result_get_pinjam);
            
            $query_cek_aktif = "SELECT COUNT(*) as total FROM (
                                    SELECT id_buku FROM booking_buku 
                                    WHERE id_buku = '" . $pinjam_data['id_buku'] . "' 
                                    AND status_booking = 'masa booking'
                                    UNION ALL
                                    SELECT id_buku FROM transaksi_peminjaman 
                                    WHERE id_buku = '" . $pinjam_data['id_buku'] . "' 
                                    AND status_peminjaman = 'dipinjam'
                                    AND id_peminjaman != '$id_peminjaman'
                                ) as aktif";
           
            $result_cek = mysqli_query($koneksi, $query_cek_aktif);
            $cek_data = mysqli_fetch_assoc($result_cek);
            
            if ($cek_data['total'] == 0) {
                $query_update_buku = "UPDATE buku SET status_buku = 'tersedia' WHERE id_buku = '" . $pinjam_data['id_buku'] . "'";
                mysqli_query($koneksi, $query_update_buku);
            }
            
            $query_update_booking = "UPDATE booking_buku 
                                    SET status_booking = 'buku dipinjam' 
                                    WHERE id_pengguna = '" . $pinjam_data['id_pengguna'] . "' 
                                    AND id_buku = '" . $pinjam_data['id_buku'] . "' 
                                    AND status_booking = 'masa booking'";
            mysqli_query($koneksi, $query_update_booking);
        }
        
        echo "<script>alert('Pengembalian berhasil dikonfirmasi!'); window.location.href='?page=peminjaman';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($koneksi) . "');</script>";
    }
}

// PROSES DELETE 
if (isset($_GET['delete_peminjaman'])) {
  
    $id_peminjaman = mysqli_real_escape_string($koneksi, $_GET['delete_peminjaman']);
    $id_pengguna_login = isset($_SESSION['pengguna']['id_pengguna']) ? $_SESSION['pengguna']['id_pengguna'] : 0;
    
    $query_get = "SELECT id_buku, status_peminjaman, id_pengguna FROM transaksi_peminjaman WHERE id_peminjaman = '$id_peminjaman'";
    $result_get = mysqli_query($koneksi, $query_get);
    
    $is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';
    
    if ($is_admin) {
        
        $query_delete = "DELETE FROM transaksi_peminjaman WHERE id_peminjaman = '$id_peminjaman'";
    } else {
       
        $query_delete = "UPDATE transaksi_peminjaman 
                        SET status_tampil = 0 
                        WHERE id_peminjaman = '$id_peminjaman' 
                        AND id_pengguna = '$id_pengguna_login'
                        AND status_peminjaman = 'dikembalikan'"; 
    }
    
    if (mysqli_query($koneksi, $query_delete)) {
        
        if ($is_admin && $result_get && mysqli_num_rows($result_get) > 0) {
            $pinjam_data = mysqli_fetch_assoc($result_get);
            
            if ($pinjam_data['status_peminjaman'] == 'dipinjam') {
               
                $query_cek_aktif = "SELECT COUNT(*) as total FROM (
                                        SELECT id_buku FROM booking_buku 
                                        WHERE id_buku = '" . $pinjam_data['id_buku'] . "' 
                                        AND status_booking = 'masa booking'
                                        UNION ALL
                                        SELECT id_buku FROM transaksi_peminjaman 
                                        WHERE id_buku = '" . $pinjam_data['id_buku'] . "' 
                                        AND status_peminjaman = 'dipinjam'
                                    ) as aktif";
                $result_cek = mysqli_query($koneksi, $query_cek_aktif);
                $cek_data = mysqli_fetch_assoc($result_cek);
                
                if ($cek_data['total'] == 0) {
                    $query_update_book = "UPDATE buku SET status_buku = 'tersedia' WHERE id_buku = '" . $pinjam_data['id_buku'] . "'";
                    mysqli_query($koneksi, $query_update_book);
                }
            }
        }
        
        echo "<script>alert('Data peminjaman berhasil dihapus!'); window.location.href='?page=peminjaman';</script>";
        exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($koneksi) . "');</script>";
    }
}

$is_admin = isset($_SESSION['pengguna']['status_pengguna']) && $_SESSION['pengguna']['status_pengguna'] == 'admin';

if ($is_admin) {
    
    $query = "SELECT tp.id_peminjaman, tp.id_pengguna, tp.id_buku, 
                     tp.tanggal_peminjaman, tp.tanggal_pengembalian, tp.status_peminjaman,
                     b.judul, b.penulis, b.penerbit,
                     p.nama, p.email 
              FROM transaksi_peminjaman tp 
              LEFT JOIN buku b ON tp.id_buku = b.id_buku 
              LEFT JOIN pengguna p ON tp.id_pengguna = p.id_pengguna 
              ORDER BY tp.tanggal_peminjaman DESC";
} else {
    
    $id_pengguna = isset($_SESSION['pengguna']['id_pengguna']) ? $_SESSION['pengguna']['id_pengguna'] : 0;
    $query = "SELECT tp.id_peminjaman, tp.id_pengguna, tp.id_buku,
                     tp.tanggal_peminjaman, tp.tanggal_pengembalian, tp.status_peminjaman,
                     b.judul, b.penulis, b.penerbit 
              FROM transaksi_peminjaman tp 
              LEFT JOIN buku b ON tp.id_buku = b.id_buku 
              WHERE tp.id_pengguna = '$id_pengguna' 
              AND tp.status_tampil = 1
              ORDER BY tp.tanggal_peminjaman DESC";
}

$result = mysqli_query($koneksi, $query);

if (!$result) {
    $error_message = mysqli_error($koneksi);
}
?>

<div class="card">
    <div class="card-body">
        <h1 class="mt-4">
            <?php echo $is_admin ? 'Kelola Peminjaman Buku' : 'Daftar Peminjaman Saya'; ?>
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
                            <th>Nama Peminjam</th>
                            <th>Email</th>
                            <?php endif; ?>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Penerbit</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Kembali</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result && mysqli_num_rows($result) > 0):
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
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
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_peminjaman'])); ?></td>
                            <td><?php echo $row['tanggal_pengembalian'] ? date('d/m/Y', strtotime($row['tanggal_pengembalian'])) : '-'; ?></td>
                            <td>
                                <?php 
                                $status = $row['status_peminjaman'] ?? '';
                                $badge_class = ($status == 'dipinjam') ? 'bg-warning text-dark' : 'bg-success text-white';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['status_peminjaman'] == 'dipinjam' && $is_admin): ?>
                                <a href="?page=peminjaman&kembalikan=<?php echo $row['id_peminjaman']; ?>" 
                                   class="btn btn-success btn-sm mb-1" 
                                   onclick="return confirm('Konfirmasi pengembalian buku ini?')">Kembalikan</a>
                                <br>
                                <?php endif; ?>
                                
                                <?php if ($is_admin): ?>
                            
                                <a href="?page=peminjaman&delete_peminjaman=<?php echo $row['id_peminjaman']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                <?php elseif ($row['status_peminjaman'] == 'dikembalikan'): ?>
                                
                                <a href="?page=peminjaman&delete_peminjaman=<?php echo $row['id_peminjaman']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus riwayat ini?')">Hapus</a>
                                <?php else: ?>
                                <span class="text-muted">Menunggu pengembalian</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="<?php echo $is_admin ? '10' : '8'; ?>" class="text-center">
                                <?php echo $is_admin ? 'Belum ada data peminjaman dari anggota.' : 'Anda belum memiliki riwayat peminjaman.'; ?>
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