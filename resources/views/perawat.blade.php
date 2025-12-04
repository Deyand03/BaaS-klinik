<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Daftar Antrian Perawat</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">

    <h1 class="text-2xl font-bold mb-4">Daftar Antrian Booking</h1>

    <table class="min-w-full bg-white border border-gray-300">
        <thead>
            <tr class="bg-gray-200 text-center">
                <th class="px-4 py-2 border">No Antrian</th>
                <th class="px-4 py-2 border">Nama</th>
                <th class="px-4 py-2 border">Jenis Kelamin</th>
                <th class="px-4 py-2 border">Uaasia</th>
                <th class="px-4 py-2 border">Keluhan</th>
                <th class="px-4 py-2 border">Aksiffff</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($antrian))
                @foreach($antrian as $item)
                    <tr class="text-center border">
                        <td class="px-4 py-2 border">{{ $item['no_antrian'] }}</td>
                        <td class="px-4 py-2 border">{{ $item['nama_lengkap'] }}</td>
                        <td class="px-4 py-2 border">{{ $item['jenis_kelamin'] }}</td>
                        <td class="px-4 py-2 border">{{ $item['usia'] }}</td>
                        <td class="px-4 py-2 border">{{ $item['keluhan'] }}</td>
                        <td class="px-4 py-2 border">
                            <form method="POST" action="/perawat/input-vital/{{ $item['id'] }}">
                                @csrf
                                <input type="text" name="tensi_darah" placeholder="Tensi" required>
                                <input type="text" name="berat_badan" placeholder="Berat" required>
                                <input type="text" name="suhu_badan" placeholder="Suhu" required>
                                <input type="text" name="anamnesa" placeholder="Anamnesa" required>
                                <button type="submit" class="bg-green-500 text-white px-2 py-1 rounded">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center p-4">Tidak ada data antrian</td>
                </tr>
            @endif
        </tbody>

    </table>

</body>

</html>