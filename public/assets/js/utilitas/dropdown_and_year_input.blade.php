<select name="bulan" class="form-control">
    @foreach (range(1, 12) as $bulan)
        <option value="{{ $bulan }}" {{ $periode->BULAN == $bulan ? 'selected' : '' }}>
            {{ date('F', mktime(0, 0, 0, $bulan, 1)) }}
        </option>
    @endforeach
</select>
<input type="number" name="tahun" class="form-control" value="{{ $periode->TAHUN }}" min="2000" max="2099">