<div style="margin-top: 2rem">
    <p class="m-0 ml-1 mb-1">
        {{ $data->trans->TipeTransHd === 'BKK' || $data->trans->TipeTransHd === 'BBK' ? 'Pembayaran Untuk' : 'Terima Dari' }}
        : {{ $data->trans->Note }}</p>
    <table class="table-border w-100">
        <thead>
            <tr>
                <th class="w-3--12 text-center align-center">No. Perkiraan Lawan</th>
                <th class="w-5--12 text-center align-center">Nama Perkiraan Lawan / Uraian</th>
                <th class="w-2--12 text-center align-center">Jumlah (Valas)</th>
                <th class="w-2--12 text-center align-center">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->detail as $item)
                <tr>
                    <td class="p-1 text-center align-center">{{ $item->Perkiraan }}</td>
                    <td class="p-1 text-left align-center">{{ $item->KeteranganPerkiraan }} / {{ $item->Keterangan }}
                    </td>
                    <td class="p-1 align-center" style="position: relative">
                        {{-- <div style="position: relative;width:100%;height:100%;"> --}}
                        <p class="m-0 m-1 p-0" style="position: absolute; top:0; left:0;">{{ $item->Valas }}</p>
                        <p class="m-0 m-1 p-0" style="position: absolute; top:0; right:0;">
                            {{ number_format($item->Debet, 2, ',', '.') }}</p>
                        {{-- </div> --}}
                    </td>
                    <td class="p-1 text-right align-center">
                        {{ number_format($item->DebetRp, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">{{ number_to_word($data->detail->sum('DebetRp'), 2) }}</td>
                <td class="p-1 text-right align-center">{{ number_format($data->detail->sum('Debet'), 2, ',', '.') }}
                </td>
                <td class="p-1 text-right align-center">{{ number_format($data->detail->sum('DebetRp'), 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div style="margin-top: 2rem">
    <table class="w-100">
        <tbody>
            <tr>
                <td class="w-3--12"></td>
                <td class="w-5--12 m-0 p-0 align-top text-center">
                    <table class="w-100 table-border">
                        <tbody>
                            <tr>
                                <td class="w-4--12 m-0 p-0 align-top text-center">
                                    Mengetahui
                                </td>
                                <td class="w-4--12 m-0 p-0 align-top text-center">
                                    Dibayar Kasir
                                </td>
                                <td class="w-4--12 m-0 p-0 align-top text-center">
                                    DiBukukan Oleh
                                </td>
                            </tr>
                            <tr>
                                <td style="height: 100px"></td>
                                <td style="height: 100px"></td>
                                <td style="height: 100px"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td class="w-1--12"></td>
                <td class="w-3--12">
                    <table class="w-100">
                        <tbody>
                            <tr>
                                <td class="w-4--12 m-0 p-0 align-top text-center">
                                    Surabaya, {{ Carbon\Carbon::parse($data->trans->Tanggal)->format('d F Y') }} <br>
                                    Penerima
                                </td>
                            </tr>
                            <tr>
                                <td style="height: 80px">
                                </td>
                            </tr>
                            <tr>
                                <td class="m-0 p-0 align-top text-center">
                                    (.................................)<br>
                                    FM/KA/0102 - 01 November 2017 - 00
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>
