@extends('welcome')
@section('content')
<div class="container mt-4">
    <h2>Laporan Aktivitas (Laba Rugi)</h2>
    <form id="form-aktivitas" class="mb-3">
        <div class="row g-2">
            <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" required></div>
            <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
        </div>
    </form>
    <div id="result-aktivitas"></div>
</div>
<script>
const form = document.getElementById('form-aktivitas');
const resultDiv = document.getElementById('result-aktivitas');
form.onsubmit = async function(e) {
    e.preventDefault();
    resultDiv.innerHTML = 'Loading...';
    const fd = new FormData(form);
    const params = new URLSearchParams(fd).toString();
    const token = localStorage.getItem('token') || prompt('Masukkan token Bearer:');
    localStorage.setItem('token', token);
    const res = await fetch(`/api/laporan/aktivitas?${params}`, {
        headers: { 'Authorization': 'Bearer ' + token }
    });
    const data = await res.json();
    if (data.pendapatan) {
        let html = `<h4>Pendapatan</h4><ul>`;
        for (const a of data.pendapatan) html += `<li>${a.account_code} - ${a.account_name}: ${a.saldo}</li>`;
        html += `</ul><h4>Beban</h4><ul>`;
        for (const a of data.beban) html += `<li>${a.account_code} - ${a.account_name}: ${a.saldo}</li>`;
        html += `</ul><p><b>Total Pendapatan:</b> ${data.total_pendapatan}</p><p><b>Total Beban:</b> ${data.total_beban}</p><p><b>Laba Bersih:</b> ${data.laba_bersih}</p>`;
        resultDiv.innerHTML = html;
    } else {
        resultDiv.innerHTML = '<span class="text-danger">Data tidak ditemukan atau token salah.</span>';
    }
}
</script>
@endsection 