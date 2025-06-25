@extends('welcome')
@section('content')
<div class="container mt-4">
    <h2>Neraca Saldo</h2>
    <form id="form-neraca-saldo" class="mb-3">
        <div class="row g-2">
            <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" required></div>
            <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
        </div>
    </form>
    <div id="result-neraca-saldo"></div>
</div>
<script>
const form = document.getElementById('form-neraca-saldo');
const resultDiv = document.getElementById('result-neraca-saldo');
form.onsubmit = async function(e) {
    e.preventDefault();
    resultDiv.innerHTML = 'Loading...';
    const fd = new FormData(form);
    const params = new URLSearchParams(fd).toString();
    const token = localStorage.getItem('token') || prompt('Masukkan token Bearer:');
    localStorage.setItem('token', token);
    const res = await fetch(`/api/laporan/neraca-saldo?${params}`, {
        headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    if (Array.isArray(data)) {
        let html = `<table class='table table-bordered'><thead><tr><th>Kode Akun</th><th>Nama Akun</th><th>Saldo Awal</th><th>Total Debit</th><th>Total Kredit</th><th>Saldo Akhir</th></tr></thead><tbody>`;
        for (const row of data) {
            html += `<tr><td>${row.account_code}</td><td>${row.account_name}</td><td>${row.saldo_awal}</td><td>${row.total_debit}</td><td>${row.total_kredit}</td><td>${row.saldo_akhir}</td></tr>`;
        }
        html += `</tbody></table>`;
        resultDiv.innerHTML = html;
    } else {
        resultDiv.innerHTML = '<span class="text-danger">Data tidak ditemukan atau token salah.</span>';
    }
}
</script>
@endsection 