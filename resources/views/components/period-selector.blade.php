@props(['bulan' => null, 'tahun' => null])

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="bulan">Bulan</label>
            <select class="form-control" id="bulan" name="bulan">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                        {{ Carbon\Carbon::create()->month($i)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="tahun">Tahun</label>
            <select class="form-control" id="tahun" name="tahun">
                @php
                    $currentYear = date('Y');
                    $startYear = $currentYear - 5;
                    $endYear = $currentYear + 5;
                @endphp
                @for ($year = $startYear; $year <= $endYear; $year++)
                    <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endfor
            </select>
        </div>
    </div>
</div> 