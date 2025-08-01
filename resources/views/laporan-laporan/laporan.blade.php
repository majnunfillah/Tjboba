@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Laporan</h4>
        </div>
        <div class="card-body">
            <form id="formLaporan" onsubmit="return false;">
                @csrf
                <x-period-selector :bulan="$periode->BULAN" :tahun="$periode->TAHUN" />

                <div class="form-group mt-3">
                    <label for="access">Jenis Laporan</label>
                    <select class="form-control" id="access" name="access">
                        @foreach($laporan as $item)
                            <option value="{{ $item->ACCESS }}">{{ $item->KETERANGAN }}</option>
                        @endforeach
                    </select>
            </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-primary" onclick="generateReport()">Generate</button>
                    <button type="button" class="btn btn-success" onclick="exportPDF()">Export PDF</button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Keluar</button>
                </div>
            </form>

            <div id="reportContainer" class="mt-4">
                <!-- Report content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function generateReport() {
    const form = document.getElementById('formLaporan');
    const formData = new FormData(form);

    fetch('{{ route("laporan-laporan.generate") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            document.getElementById('reportContainer').innerHTML = data.html;
        } else {
            throw new Error('Failed to generate report');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
}

function exportPDF() {
    const form = document.getElementById('formLaporan');
    const formData = new FormData(form);
    formData.append('export_pdf', true);

    // Submit form for PDF download
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '{{ route("laporan-laporan.generate") }}';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    tempForm.appendChild(csrfToken);

    // Add form data
    for (const pair of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = pair[0];
        input.value = pair[1];
        tempForm.appendChild(input);
    }

    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
}
</script>
@endpush
@endsection
