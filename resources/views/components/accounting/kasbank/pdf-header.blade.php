<header class="h-20">
    <table class="w-100">
        <thead>
            <tr>
                <th class="w-3--12 text-left align-top text-normal">
                    <p class="text-capitalize m-0 p-0">
                        PT PERURI WIRA TIMUR
                    </p>
                    <p class="text-capitalize m-0 mt-2 p-0">
                        SURABAYA
                    </p>
                </th>
                <th class="w-6--12 m-0 p-0 align-top">
                    <p class="text-capitalize m-0 p-0 fs-24">
                        @if ($data->trans->TipeTransHd === 'BKK')
                            BUKTI KAS KELUAR
                        @elseif($data->trans->TipeTransHd === 'BKM')
                            BUKTI KAS MASUK
                        @elseif($data->trans->TipeTransHd === 'BBK')
                            BUKTI BANK KELUAR
                        @elseif($data->trans->TipeTransHd === 'BBM')
                            BUKTI BANK MASUK
                        @endif
                    </p>
                    <p class="text-capitalize m-0 p-0 fs-18">
                        {{ $data->trans->PerkiraanHd }} <br>
                        {{ $data->trans->TipeTransHd === 'BKK' || $data->trans->TipeTransHd === 'BKM' ? 'KAS' : 'BANK' }}
                    </p>
                </th>
                <th class="w-3--12 m-0 p-0 text-normal align-top">
                    <p class="text-capitalize m-0 p-0">
                        No. Bukti <br> {{ $data->trans->NoBukti }}
                    </p>
                </th>
            </tr>
        </thead>
    </table>
</header>
