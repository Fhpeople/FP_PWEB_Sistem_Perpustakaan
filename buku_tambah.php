<?php
if ($_SESSION['pengguna']['status_pengguna'] == 'anggota') {
    echo "<script>alert('Akses ditolak.'); location.href='?page=buku';</script>";
    exit;
}
?>
<h1 class="mt-4">Katalog Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form method="post" enctype="multipart/form-data">
                    <?php
                        if (isset($_POST['submit'])) {
                            $id_kategori = $_POST['id_kategori'];
                            $judul = $_POST['judul'];
                            $penulis = $_POST['penulis'];
                            $penerbit = $_POST['penerbit'];
                            $tahun_terbit = $_POST['tahun_terbit'];
                            $deskripsi = $_POST['deskripsi'];
                            $cover = $_FILES['cover']['name'];
                            $tmp = $_FILES['cover']['tmp_name'];

                            if ($cover != "") {
                                $path = "uploads/" . $cover;
                                move_uploaded_file($tmp, $path);
                            }

                            $query = mysqli_query($koneksi, "INSERT INTO buku(id_kategori, judul, penulis, penerbit, tahun_terbit, deskripsi, cover)
                                                                VALUES('$id_kategori', '$judul', '$penulis', '$penerbit', '$tahun_terbit', '$deskripsi', '$cover')");

                            if ($query) {
                                echo '<script>alert("Buku telah ditambahkan."); location.href="?page=buku";</script>';
                            } 
                            else
                            {
                                echo '<script>alert("Gagal menambah buku.");</script>';
                            }
                        }
                    ?>
                    <div class="row mb-3">
                        <div class="col-md-2">Kategori</div>
                        <div class="col-md-8">
                            <select name="id_kategori" class="form-control">
                                <?php
                                    $kat = mysqli_query($koneksi, "SELECT * FROM kategori_buku");
                                    while($kategori = mysqli_fetch_array($kat)) {
                                        ?>
                                        <option value="<?php echo $kategori['id_kategori']; ?>"><?php echo $kategori['kategori']; ?></option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Judul</div>
                        <div class="col-md-8"><input type="text" class="form-control" name="judul"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Penulis</div>
                        <div class="col-md-8"><input type="text" class="form-control" name="penulis"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Penerbit</div>
                        <div class="col-md-8"><input type="text" class="form-control" name="penerbit"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Tahun Terbit</div>
                        <div class="col-md-8"><input type="number" class="form-control" name="tahun_terbit"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Deskripsi</div>
                        <div class="col-md-8">
                            <textarea name="deskripsi" rows="5" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">Cover</div>
                        <div class="col-md-8">
                            <input type="file" class="form-control" name="cover">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">Tambahkan</button>
                            <a href="?page=buku" class="btn btn-danger">Batalkan</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>