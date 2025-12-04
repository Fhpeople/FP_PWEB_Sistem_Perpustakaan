<?php
    include "koneksi.php";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Register Sistem Perpustakaan</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5 mb-5">
                                    <div class="card-header"><h3 class="text-center font-weight-light my-4">Register Sistem Perpustakaan</h3></div>
                                    <div class="card-body">
                                        <?php
                                            if (isset($_POST['register'])) {
                                                $nama = $_POST['nama'];
                                                $email = $_POST['email'];

                                                $cek_duplicat_email = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE email='$email'");
                                                if (mysqli_num_rows($cek_duplicat_email) > 0) {
                                                    echo '<script>alert("Tidak bisa menggunakan email karena email ini sudah digunakan.");</script>';
                                                }
                                                else
                                                {
                                                    $no_telp = $_POST['no_telp'];
                                                    $alamat = $_POST['alamat'];
                                                    $status_pengguna = $_POST['status_pengguna'];
                                                    $password = md5($_POST['password']);

                                                    $insert = mysqli_query($koneksi, "INSERT INTO pengguna(nama,email,no_telp,alamat,password,status_pengguna) VALUES('$nama','$email','$no_telp','$alamat','$password','$status_pengguna')");
                                                    if ($insert) {
                                                        echo '<script>alert("Selamat, register berhasil. Silakan login."); location.href="login.php"</script>';
                                                    }
                                                    else
                                                    {
                                                        echo '<script>alert("Register gagal. Silakan coba lagi.");</script>';
                                                    }
                                                }
                                            }
                                        ?>
                                        <form method="post">
                                            <div class="form-group">
                                                <label class="small mb-1">Name</label>
                                                <input class="form-control py-4" type="text" required name="nama" placeholder="Enter Your Name" />
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputEmail">Email address</label>
                                                <input class="form-control py-4" id="inputEmail" type="email" required name="email" placeholder="name@example.com" />
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputPhone">Phone number</label>
                                                <input class="form-control py-4" id="inputPhone" type="text" required name="no_telp" placeholder="08xxxxxxxx" />
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputEmail">Address</label>
                                                <textarea name="alamat" rows="5" required class="form-control"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1" for="inputPassword">Password</label>
                                                <input class="form-control py-4" id="inputPassword" type="password" required name="password" placeholder="Enter Password" />
                                            </div>
                                            <div class="form-group">
                                                <label class="small mb-1">Status</label>
                                                <select name="status_pengguna" required class="form-select py-4">
                                                    <option value="admin">Admin</option>
                                                    <option value="anggota">Anggota</option>
                                                </select>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <button class="btn btn-danger" type="submit" name="register">Register</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">FP PWEB (M) 2025</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>