<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Berita Acara {{ $handover->number }}</title>
    <style>
        @page { margin: 42px 58px 48px; }
        body { color: #000; font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.35; }
        .header-image { display: block; margin: 0 auto 18px; max-height: 78px; max-width: 100%; }
        h1 { margin: 0 0 20px; font-family: 'DejaVu Sans', sans-serif; font-size: 16px; text-align: center; text-decoration: underline; }
        .party { margin-bottom: 12px; }
        .party-title { margin-bottom: 4px; }
        .party-table { border-collapse: collapse; width: 100%; }
        .party-table td { padding: 1px 0; vertical-align: top; }
        .party-table td:first-child { width: 82px; }
        .party-table td:nth-child(2) { width: 16px; }
        .intro { margin: 18px 0 10px; }
        table.items { border-collapse: collapse; margin: 0 0 14px; table-layout: fixed; width: 100%; }
        .items th, .items td { border: 1px solid #000; padding: 5px 6px; vertical-align: top; }
        .items th { text-align: center; }
        .items th:nth-child(1), .items td:nth-child(1) { width: 7%; text-align: center; }
        .items th:nth-child(2), .items td:nth-child(2) { width: 43%; }
        .items th:nth-child(3), .items td:nth-child(3) { width: 18%; text-align: center; }
        .items th:nth-child(4), .items td:nth-child(4) { width: 32%; }
        .statement { margin: 18px 0; text-align: justify; }
        .date { margin-top: 18px; }
        .signatures { margin-top: 10px; width: 100%; }
        .signatures td { text-align: center; vertical-align: top; width: 50%; }
        .signature-space { height: 24px; }
        .approval-stamp { border: 2px solid #15803d; color: #15803d; display: inline-block; font-size: 10px; font-weight: bold; margin: 0 0 2px; padding: 3px 9px; transform: rotate(-5deg); }
        .approval-date { color: #15803d; font-size: 9px; margin: 0 0 2px; }
    </style>
</head>
<body>
    <img class="header-image" src="{{ public_path('pdf/header.png') }}" alt="Header">
    <h1>BERITA ACARA SERAH TERIMA</h1>

    <div class="party">
        <div class="party-title">Yang menyerahkan barang,</div>
        <table class="party-table">
            <tr><td>Nama</td><td>:</td><td>{{ $handover->sender->name }}</td></tr>
            <tr><td>Jabatan</td><td>:</td><td>{{ $handover->sender->position?->name ?? '-' }}</td></tr>
            <tr><td>Departemen</td><td>:</td><td>{{ $handover->sender->departments->pluck('name')->join(', ') ?: '-' }}</td></tr>
        </table>
    </div>

    <div class="party">
        <div class="party-title">Yang menerima barang,</div>
        <table class="party-table">
            <tr><td>Nama</td><td>:</td><td>{{ $handover->receiver->name }}</td></tr>
            <tr><td>Jabatan</td><td>:</td><td>{{ $handover->receiver->position?->name ?? '-' }}</td></tr>
            <tr><td>Departemen</td><td>:</td><td>{{ $handover->receiver->departments->pluck('name')->join(', ') ?: '-' }}</td></tr>
        </table>
    </div>

    <p class="intro">Rincian serah terima barang sebagai berikut:</p>

    <table class="items">
        <thead>
            <tr><th>No</th><th>Nama Barang</th><th>Kuantitas</th><th>Catatan Barang</th></tr>
        </thead>
        <tbody>
            @foreach ($handover->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->itemType?->name ?? $item->manual_name }}</td>
                    <td>{{ $item->quantity }} {{ $item->itemType?->unit ?? $item->manual_unit ?? '' }}</td>
                    <td>{{ $item->notes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="statement">Demikian serah terima ini diperbuat, keterangan barang-barang tersebut di atas adalah dalam kondisi baik. Setelah ini semua barang-barang tersebut di atas menjadi tanggung jawab penerima untuk memelihara, merawat, dan digunakan untuk keperluan sesuai dengan kebutuhan. Segala kehilangan atau kerusakan yang terjadi akan menjadi tanggung jawab penerima.</p>

    <div class="date">{{ config('app.city', 'Tanjung Morawa') }}, {{ $handover->transaction_date->translatedFormat('d F Y') }}</div>

    <table class="signatures">
        <tr>
            <td>Yang menyerahkan,</td>
            <td>Yang menerima,</td>
        </tr>
        <tr>
            <td>
                <div class="signature-space"></div>
                @if ($handover->sender_confirmed_at)
                    <div class="approval-stamp">DISETUJUI</div>
                    <div class="approval-date">{{ $handover->sender_confirmed_at->translatedFormat('d F Y, H:i') }}</div>
                @endif
                <strong>{{ $handover->sender->name }}</strong>
            </td>
            <td>
                <div class="signature-space"></div>
                @if ($handover->receiver_confirmed_at)
                    <div class="approval-stamp">DISETUJUI</div>
                    <div class="approval-date">{{ $handover->receiver_confirmed_at->translatedFormat('d F Y, H:i') }}</div>
                @endif
                <strong>{{ $handover->receiver->name }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
