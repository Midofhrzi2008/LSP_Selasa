<?php

function readData()
{
    $file = "inventory.json";

    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }

    $data = file_get_contents($file);
    return json_decode($data, true) ?? [];
}

function saveData($data)
{
    file_put_contents("inventory.json", json_encode($data, JSON_PRETTY_PRINT));
}

$data = readData();
$errors = [];

// TAMBAH DATA
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $foto = $_POST['foto'];
    $kategori = $_POST['kategori'];
    $deskripsi = $_POST['deskripsi'];

    if ($nama == "") $errors[] = "Nama wajib diisi";

    if ($foto == "") {
        $errors[] = "URL foto wajib diisi.";
    } elseif (!filter_var($foto, FILTER_VALIDATE_URL)) {
        $errors[] = "Format URL tidak valid.";
    }

    if ($kategori == "") $errors[] = "Kategori wajib dipilih";

    if (empty($errors)) {
        $data[] = [
            "id" => time(),
            "nama" => $nama,
            "foto" => $foto,
            "kategori" => $kategori,
            "deskripsi" => $deskripsi
        ];

        saveData($data);
        header("Location: index.php");
        exit;
    }
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $data = array_filter($data, function ($item) use ($id) {
        return $item["id"] != $id;
    });

    saveData(array_values($data));
    header("Location: index.php");
    exit;
}

// EDIT
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    foreach ($data as $item) {
        if ($item['id'] == $id) {
            $editData = $item;
        }
    }

    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $foto = $_POST['foto'];
        $kategori = $_POST['kategori'];
        $deskripsi = $_POST['deskripsi'];

        if ($nama == "") $errors[] = "Nama wajib diisi";

        if (empty($errors)) {
            foreach ($data as &$item) {
                if ($item['id'] == $id) {
                    $item['nama'] = $nama;
                    $item['foto'] = $foto;
                    $item['kategori'] = $kategori;
                    $item['deskripsi'] = $deskripsi;
                }
            }

            saveData($data);
            header("Location: index.php");
            exit;
        }
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pharmacy Inventory Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .card-item {
            transition: all 0.3s ease;
        }
    </style>

</head>

<body>

    <div class="container py-5">

        <div class="card shadow p-4">
            <h2 class="mb-4">Pharmacy Inventory Manager</h2>

            <?php if (!empty($errors)) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="nama" class="form-control mb-3" placeholder="Nama Obat" value="<?= $editData['nama'] ?? '' ?>">

                <input type="text" name="foto" class="form-control mb-3" placeholder="Link gambar" value="<?= $editData['foto'] ?? '' ?>">

                <textarea name="deskripsi" class="form-control mb-3" placeholder="Deskripsi"><?= $editData['deskripsi'] ?? '' ?></textarea>

                <select name="kategori" class="form-select mb-3">
                    <option value="">Pilih kategori</option>
                    <option value="tablet" <?= isset($editData) && $editData['kategori'] == 'tablet' ? 'selected' : '' ?>>Tablet</option>
                    <option value="sirup" <?= isset($editData) && $editData['kategori'] == 'sirup' ? 'selected' : '' ?>>Sirup</option>
                    <option value="vitamin" <?= isset($editData) && $editData['kategori'] == 'vitamin' ? 'selected' : '' ?>>Vitamin</option>
                    <option value="ALatkesehatan" <?= isset($editData) && $editData['kategori'] == 'ALatkesehatan' ? 'selected' : '' ?>>Alat Kesehatan</option>
                </select>

                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                    <button name="update" class="btn btn-warning w-100">Update</button>
                <?php else: ?>
                    <button name="tambah" class="btn btn-success w-100">Tambah</button>
                <?php endif; ?>

            </form>
        </div>

        <!-- FILTER -->
        <div class="mt-4">
            <button class="btn btn-primary filter-btn" data-filter="semua">Semua</button>
            <button class="btn btn-primary filter-btn" data-filter="tablet">Tablet</button>
            <button class="btn btn-primary filter-btn" data-filter="sirup">Sirup</button>
            <button class="btn btn-primary filter-btn" data-filter="vitamin">Vitamin</button>
            <button class="btn btn-primary filter-btn" data-filter="ALatkesehatan">Alat Kesehatan</button>
        </div>

        <input type="text" id="searchBar" class="form-control mt-3" placeholder="Search...">

        <div class="row mt-4">

            <?php foreach ($data as $item) : ?>
                <div class="col-md-4 mb-4 card-item"
                    data-kategori="<?= $item['kategori'] ?>"
                    data-nama="<?= strtolower($item['nama']) ?>">

                    <div class="card h-100 shadow">
                        <img src="<?= $item['foto'] ?>" style="height:200px;object-fit:cover;">
                        <div class="card-body text-center">
                            <h5><?= $item['nama'] ?></h5>
                            <p class="text-muted small"><?= $item['deskripsi'] ?? '' ?></p>

                            <!-- WARNA BADGE SESUAI KATEGORI -->
                            <?php
                            $warna = [
                                "tablet" => "primary",
                                "sirup" => "success",
                                "vitamin" => "warning",
                                "ALatkesehatan" => "dark"
                            ];
                            ?>
                            <span class="badge bg-<?= $warna[$item['kategori']] ?>">
                                <?= $item['kategori'] ?>
                            </span>

                            <div class="mt-3">
                                <a href="?edit=<?= $item['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?hapus=<?= $item['id'] ?>" class="btn btn-danger btn-sm">Hapus</a>
                            </div>

                        </div>
                    </div>

                </div>
            <?php endforeach; ?>

        </div>

    </div>

    <script>
        // SEARCH
        document.getElementById("searchBar").addEventListener("keyup", function() {
            let keyword = this.value.toLowerCase();

            document.querySelectorAll(".card-item").forEach((card) => {
                let nama = card.dataset.nama;

                if (nama.includes(keyword)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        });

        // WARNA KATEGORI
        const warnaKategori = {
            tablet: "btn-primary",
            sirup: "btn-success",
            vitamin: "btn-warning",
            ALatkesehatan: "btn-dark"
        };

        const buttons = document.querySelectorAll(".filter-btn");

        buttons.forEach((button) => {
            button.addEventListener("click", function() {

                // reset semua jadi biru
                buttons.forEach(btn => {
                    btn.classList.remove("btn-primary", "btn-success", "btn-warning", "btn-dark");
                    btn.classList.add("btn-primary");
                });

                let kategori = this.dataset.filter;

                // ubah warna sesuai kategori
                if (kategori !== "semua") {
                    this.classList.remove("btn-primary");
                    this.classList.add(warnaKategori[kategori]);
                }

                document.querySelectorAll(".card-item").forEach((card) => {

                    if (kategori === "semua" || card.dataset.kategori === kategori) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }

                });

            });
        });
    </script>

</body>

</html>