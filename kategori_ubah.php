<?php
if ($_SESSION['pengguna']['status_pengguna'] == 'anggota') {
    echo "<script>alert('Akses ditolak.'); location.href='?page=buku';</script>";
    exit;
}
?>
<h1 class="mt-4">Kategori Buku</h1>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div clas="col-md-12">
                <form method="post">
                    <?php
                        $id = $_GET['id'];
                        if (isset($_POST['submit'])) {
                            $kategori_buku = $_POST['kategori_buku'];
                            $query = mysqli_query($koneksi, "UPDATE kategori_buku SET kategori='$kategori_buku' WHERE id_kategori=$id");

                            if ($query) {
                                echo '<script>alert("Kategori telah diubah."); location.href="?page=kategori";</script>';
                            }
                            else
                            {
                                echo '<script>alert("Gagal mengubah kategori.");</script>';
                            }
                        }
                        $query = mysqli_query($koneksi, "SELECT * FROM kategori_buku WHERE id_kategori=$id");
                        $data = mysqli_fetch_array($query);
                    ?>
                    <div class="row mb-3">
                        <div class="col-md-2">Nama Kategori</div>
                        <div class="col-md-8"><input type="text" class="form-control" value="<?php echo $data['kategori']; ?>" name="kategori_buku"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">Ubah</button>
                            <a href="?page=kategori" class="btn btn-danger">Batalkan</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>