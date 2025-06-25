@extends('layout')
@section('content')
<h2>Laporan Aktivitas (Laba Rugi)</h2>
<form method="GET" action="http://103.23.103.43/AkuntansiKeuangan/backend_keuangan/aktivitas" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" value="{{ $periode }}" required></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
@if($data)
    <h4>Pendapatan</h4>
    <ul>
        @foreach($data['pendapatan'] as $a)
            <li>{{ $a->account_code }} - {{ $a->account_name }}: {{ $a->saldo }}</li>
        @endforeach
    </ul>
    <h4>Beban</h4>
    <ul>
        @foreach($data['beban'] as $a)
            <li>{{ $a->account_code }} - {{ $a->account_name }}: {{ $a->saldo }}</li>
        @endforeach
    </ul>
    <p><b>Total Pendapatan:</b> {{ $data['total_pendapatan'] }}</p>
    <p><b>Total Beban:</b> {{ $data['total_beban'] }}</p>
    <p><b>Laba Bersih:</b> {{ $data['laba_bersih'] }}</p>
@endif
@endsection 