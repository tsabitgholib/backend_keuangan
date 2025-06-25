@extends('layout')
@section('content')
<div style="max-width:400px;margin:auto;">
    <h2>Login</h2>
    <form id="form-login">
        <div class="mb-3">
            <input type="text" class="form-control" name="login" placeholder="Email atau Username" required>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <div id="login-msg" class="mt-3"></div>
</div>
<script>
const form = document.getElementById('form-login');
const msg = document.getElementById('login-msg');
form.onsubmit = async function(e) {
    e.preventDefault();
    msg.innerHTML = 'Loading...';
    const fd = new FormData(form);
    const body = {};
    fd.forEach((v, k) => body[k] = v);
    const res = await fetch('http://localhost/AkuntansiKeuangan/backend_keuangan/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body)
    });
    const data = await res.json();
    if (data.token) {
        localStorage.setItem('token', data.token);
        msg.innerHTML = '<span class="text-success">Login berhasil! Redirect...</span>';
        setTimeout(() => window.location.href = '/buku-besar', 1000);
    } else {
        msg.innerHTML = '<span class="text-danger">Login gagal: ' + (data.message || 'Cek email/password') + '</span>';
    }
}
</script>
@endsection 