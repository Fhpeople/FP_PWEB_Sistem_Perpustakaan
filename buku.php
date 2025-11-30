<div class="card">
    <div class="card-body">
        <h1 class="mt-4">Katalog Buku</h1>
        <div class="row">
            <div class="col-md-12">
                <?php if ($_SESSION['pengguna']['status_pengguna'] != 'anggota') { ?>
                    <a href="?page=buku_tambah" class="btn btn-primary mb-2">+ Tambah Buku</a>
                <?php } ?>
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <tr>
                        <th>No.</th>
                        <th>Cover</th>
                        <th>Kategori</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Penerbit</th>
                        <th>Tahun Terbit</th>
                        <th>Deskripsi</th>
                        <th>Status Buku</th>
                        <th>Aksi</th>
                    </tr>
                    <?php
                    $i = 1;
                        $query = mysqli_query($koneksi, "SELECT * FROM buku LEFT JOIN kategori_buku on buku.id_kategori = kategori_buku.id_kategori");
                        while($data = mysqli_fetch_array($query)) {
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <img src="uploads/<?php echo $data['cover']; ?>" width="60">
                                </td>
                                <td><?php echo $data['kategori']; ?></td>
                                <td><?php echo $data['judul']; ?></td>
                                <td><?php echo $data['penulis']; ?></td>
                                <td><?php echo $data['penerbit']; ?></td>
                                <td><?php echo $data['tahun_terbit']; ?></td>
                                <td><?php echo $data['deskripsi']; ?></td>
                                <td><?php echo $data['status_buku']; ?></td>
                                <td>
                                    <?php if ($_SESSION['pengguna']['status_pengguna'] != 'anggota') { ?>
                                        <a href="?page=buku_ubah&id=<?php echo $data['id_buku']; ?>" class="btn btn-info mb-2">Ubah</a>
                                        <a onclick="return confirm('Yakin ingin menghapus buku?');" 
                                        href="?page=buku_hapus&id=<?php echo $data['id_buku']; ?>" 
                                        class="btn btn-danger">Hapus</a>
                                    <?php } else { ?>
                                    
                                    <?php if ($data['status_buku'] == 'tersedia') { ?>
                                        <a href="?page=pinjam&id=<?php echo $data['id_buku']; ?>" class="btn btn-success mb-2">Pinjam</a>
                                        <a href="?page=booking&id=<?php echo $data['id_buku']; ?>" class="btn btn-warning">Booking</a>
                                    <?php } else { ?>
                                        <button class="btn btn-secondary" disabled>Tidak Tersedia</button>
                                    <?php } ?>
                                    
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php
                        }
                    ?>
                </table>
            </div>
        </div>
    </div>
</div>