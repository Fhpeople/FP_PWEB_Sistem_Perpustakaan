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
            <div class="col-md-12">
                <form method="post">
                    <?php
                        if (isset($_POST['submit'])) {
                            $kategori = $_POST['kategori'];
                            $query = mysqli_query($koneksi, "INSERT INTO kategori_buku(kategori) VALUES('$kategori')");

                            if ($query) {
                                echo '<script>alert("Kategori telah ditambahkan."); location.href="?page=kategori";</script>';
                            }
                            else
                            {
                                echo '<script>alert("Gagal menambah kategori.");</script>';
                            }
                        }
                    ?>
                    <div class="row mb-3">
                        <div class="col-md-2">Nama Kategori</div>
                        <div class="col-md-8"><input type="text" class="form-control" name="kategori"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary" name="submit" value="submit">Tambahkan</button>
                            <a href="?page=kategori" class="btn btn-danger">Batalkan</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>