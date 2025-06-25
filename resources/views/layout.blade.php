<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">Laporan Keuangan</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="http://localhost/AkuntansiKeuangan/backend_keuangan/buku-besar">Buku Besar</a></li>
        <li class="nav-item"><a class="nav-link" href="http://localhost/AkuntansiKeuangan/backend_keuangan/neraca-saldo">Neraca Saldo</a></li>
        <li class="nav-item"><a class="nav-link" href="http://localhost/AkuntansiKeuangan/backend_keuangan/posisi-keuangan">Posisi Keuangan</a></li>
        <li class="nav-item"><a class="nav-link" href="http://localhost/AkuntansiKeuangan/backend_keuangan/aktivitas">Aktivitas</a></li>
        <li class="nav-item"><a class="nav-link" href="http://localhost/AkuntansiKeuangan/backend_keuangan/perbandingan-bulan">Perbandingan Bulan</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
    @yield('content')
</div>
<script>
// Set token dummy jika belum ada
document.addEventListener('DOMContentLoaded', function() {
    if(!localStorage.getItem('token')) {
        localStorage.setItem('token', '1|w0ZZkv4R5EoBnz5ffRrKN4BMwOZCoPPsSfsO5PLZc5ba0d7a');
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 