@extends('layout')
@section('content')
<h2>Buku Besar</h2>
<script>if(!localStorage.getItem('token')) location.href='/login';</script>
<form id="form-buku-besar" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="akun_id" placeholder="Akun ID" required></div>
        <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" required></div>
        <div class="col"><input type="date" class="form-control" name="start_date" required></div>
        <div class="col"><input type="date" class="form-control" name="end_date" required></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
<div id="result-buku-besar"></div>
<script>
const form = document.getElementById('form-buku-besar');
const resultDiv = document.getElementById('result-buku-besar');
form.onsubmit = async function(e) {
    e.preventDefault();
    resultDiv.innerHTML = 'Loading...';
    const fd = new FormData(form);
    const params = new URLSearchParams(fd).toString();
    const token = localStorage.getItem('token') || prompt('Masukkan token Bearer:');
    localStorage.setItem('token', token);
    const res = await fetch(`/api/laporan/buku-besar?${params}`, {
        headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    if (data.saldo_awal !== undefined) {
        let html = `<p>Saldo Awal: <b>${data.saldo_awal}</b></p>`;
        html += `<table class='table table-bordered'><thead><tr><th>Tanggal</th><th>Keterangan</th><th>Debit</th><th>Kredit</th><th>Saldo Berjalan</th></tr></thead><tbody>`;
        for (const j of data.jurnals) {
            html += `<tr><td>${j.jurnal?.tanggal || ''}</td><td>${j.keterangan || ''}</td><td>${j.debit}</td><td>${j.kredit}</td><td>${j.saldo_berjalan}</td></tr>`;
        }
        html += `</tbody></table><p>Saldo Akhir: <b>${data.saldo_akhir}</b></p>`;
        resultDiv.innerHTML = html;
    } else {
        resultDiv.innerHTML = '<span class="text-danger">Data tidak ditemukan atau token salah.</span>';
    }
}
</script>
@endsection 