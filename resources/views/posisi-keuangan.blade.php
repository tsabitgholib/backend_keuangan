@extends('layout')
@section('content')
<h2>Posisi Keuangan (Neraca)</h2>
<form id="form-posisi-keuangan" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" required></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
<div id="result-posisi-keuangan"></div>
<script>
if(!localStorage.getItem('token')) location.href='/login';
const form = document.getElementById('form-posisi-keuangan');
const resultDiv = document.getElementById('result-posisi-keuangan');
form.onsubmit = async function(e) {
    e.preventDefault();
    resultDiv.innerHTML = 'Loading...';
    const fd = new FormData(form);
    const params = new URLSearchParams(fd).toString();
    const token = localStorage.getItem('token') || prompt('Masukkan token Bearer:');
    localStorage.setItem('token', token);
    const res = await fetch(`/api/laporan/posisi-keuangan?${params}`, {
        headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    if (data.asset) {
        let html = `<h4>Aset</h4><ul>`;
        for (const a of data.asset) html += `<li>${a.account_code} - ${a.account_name}: ${a.saldo}</li>`;
        html += `</ul><h4>Kewajiban</h4><ul>`;
        for (const a of data.kewajiban) html += `<li>${a.account_code} - ${a.account_name}: ${a.saldo}</li>`;
        html += `</ul><h4>Ekuitas</h4><ul>`;
        for (const a of data.ekuitas) html += `<li>${a.account_code} - ${a.account_name}: ${a.saldo}</li>`;
        html += `</ul><p><b>Total Aset:</b> ${data.total_asset}</p><p><b>Total Kewajiban + Ekuitas:</b> ${data.total_kewajiban_ekuitas}</p>`;
        resultDiv.innerHTML = html;
    } else {
        resultDiv.innerHTML = '<span class="text-danger">Data tidak ditemukan atau token salah.</span>';
    }
}
</script>
@endsection 