@extends('layout')
@section('content')
<h2>Posisi Keuangan (Neraca)</h2>
<form method="GET" action="/posisi-keuangan" class="mb-3">
    <div class="row g-2">
        <div class="col"><input type="number" class="form-control" name="periode_id" placeholder="Periode ID" value="{{ $periode }}" required></div>
        <div class="col"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
    </div>
</form>
@if($data)
    <h4>Aset</h4>
    <ul>
        @foreach($data['asset'] as $a)
            <li>{{ $a->account_code }} - {{ $a->account_name }}: {{ $a->saldo }}</li>
        @endforeach
    </ul>
    <h4>Kewajiban</h4>
    <ul>
        @foreach($data['kewajiban'] as $a)
            <li>{{ $a->account_code }} - {{ $a->account_name }}: {{ $a->saldo }}</li>
        @endforeach
    </ul>
    <h4>Ekuitas</h4>
    <ul>
        @foreach($data['ekuitas'] as $a)
            <li>{{ $a->account_code }} - {{ $a->account_name }}: {{ $a->saldo }}</li>
        @endforeach
    </ul>
    <p><b>Total Aset:</b> {{ $data['total_asset'] }}</p>
    <p><b>Total Kewajiban + Ekuitas:</b> {{ $data['total_kewajiban_ekuitas'] }}</p>
@endif
@endsection 