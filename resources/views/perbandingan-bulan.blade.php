@extends('layout')
@section('content')
<h2>Perbandingan Bulan</h2>
<form id="form-perbandingan-bulan" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="periode1_id" placeholder="Periode 1 ID" required></div>
        <div class="col"><input type="number" class="form-control" name="periode2_id" placeholder="Periode 2 ID" required></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
<div id="result-perbandingan-bulan"></div>
<script>
if(!localStorage.getItem('token')) location.href='/login';
const form = document.getElementById('form-perbandingan-bulan');
const resultDiv = document.getElementById('result-perbandingan-bulan');
form.onsubmit = async function(e) {
    e.preventDefault();
    resultDiv.innerHTML = 'Loading...';
    const fd = new FormData(form);
    const params = new URLSearchParams(fd).toString();
    const token = localStorage.getItem('token') || prompt('Masukkan token Bearer:');
    localStorage.setItem('token', token);
    const res = await fetch(`/api/laporan/perbandingan-bulan?${params}`, {
        headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    if (data.periode1 && data.periode2) {
        let html = `<div class='row'><div class='col'><h4>Periode 1</h4><table class='table table-bordered'><thead><tr><th>Kode Akun</th><th>Nama Akun</th><th>Saldo</th></tr></thead><tbody>`;
        for (const a of data.periode1) html += `<tr><td>${a.account_code}</td><td>${a.account_name}</td><td>${a.saldo}</td></tr>`;
        html += `</tbody></table></div><div class='col'><h4>Periode 2</h4><table class='table table-bordered'><thead><tr><th>Kode Akun</th><th>Nama Akun</th><th>Saldo</th></tr></thead><tbody>`;
        for (const a of data.periode2) html += `<tr><td>${a.account_code}</td><td>${a.account_name}</td><td>${a.saldo}</td></tr>`;
        html += `</tbody></table></div></div>`;
        resultDiv.innerHTML = html;
    } else {
        resultDiv.innerHTML = '<span class=\"text-danger\">Data tidak ditemukan atau token salah.</span>';
    }
}
</script>
@endsection 