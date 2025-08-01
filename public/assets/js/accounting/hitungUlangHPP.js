/**
 * Hitung Ulang HPP JavaScript
 * Based on Delphi FrmRata2 functionality
 */

class HitungUlangHPP {
    constructor() {
        this.progressInterval = null;
        this.currentProgress = 0;
        this.totalItems = 0;
        this.processType = '';
        this.isProcessing = false;
        
        this.initializeEventListeners();
        this.initializeDataTable();
    }

    /**
     * Initialize event listeners
     */
    initializeEventListeners() {
        // Handle jenis barang change
        $('#jenis_barang').on('change', (e) => {
            this.handleJenisBarangChange(e.target.value);
        });

        // Handle proses button click
        $('#btn_proses').on('click', () => {
            this.handleProsesClick();
        });

        // Handle export button click
        $('#btn_export').on('click', () => {
            this.handleExportClick();
        });

        // Handle Enter key navigation
        $('input').on('keydown', (e) => {
            if (e.keyCode === 13) {
                e.preventDefault();
                $(e.target).closest('.form-group').next().find('input, select').focus();
            }
        });

        // Handle Escape key
        $(document).on('keydown', (e) => {
            if (e.keyCode === 27) {
                window.location.href = '/dashboard';
            }
        });
    }

    /**
     * Initialize DataTable
     */
    initializeDataTable() {
        this.stockMinusTable = $('#stock_minus_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/hitung-ulang-hpp/get-stock-minus',
                type: 'POST',
                data: (d) => {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                }
            },
            columns: [
                {data: 'Urut', name: 'Urut'},
                {data: 'KodeGdg', name: 'KodeGdg'},
                {data: 'KodeBrg', name: 'KodeBrg'},
                {data: 'JenisBahan', name: 'JenisBahan'}
            ],
            order: [[0, 'asc']],
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json'
            }
        });
    }

    /**
     * Handle jenis barang change
     */
    handleJenisBarangChange(value) {
        if (value === 'per_barang') {
            $('#barang_range').show();
        } else {
            $('#barang_range').hide();
        }
    }

    /**
     * Handle proses button click
     */
    handleProsesClick() {
        if (this.isProcessing) {
            return;
        }

        if (!this.validateForm()) {
            return;
        }

        const formData = this.getFormData();
        
        // Start progress
        this.startProgress();
        
        // Disable button
        $('#btn_proses').prop('disabled', true);
        this.isProcessing = true;

        // Call API to start process
        $.ajax({
            url: '/hitung-ulang-hpp/proses',
            type: 'POST',
            data: formData,
            success: (response) => {
                if (response.success) {
                    this.totalItems = response.total_items;
                    this.processType = response.process_type;
                    
                    // Start progress monitoring
                    this.startProgressMonitoring();
                    
                    // Execute the actual process
                    this.executeProcess(response);
                } else {
                    this.stopProgress();
                    this.showError('Error: ' + response.message);
                    this.enableProsesButton();
                }
            },
            error: (xhr, status, error) => {
                this.stopProgress();
                this.showError('Terjadi kesalahan: ' + error);
                this.enableProsesButton();
            }
        });
    }

    /**
     * Handle export button click
     */
    handleExportClick() {
        window.location.href = '/hitung-ulang-hpp/export';
    }

    /**
     * Get form data
     */
    getFormData() {
        return {
            bulan: $('#bulan').val(),
            tahun: $('#tahun').val(),
            jenis_barang: $('#jenis_barang').val(),
            kode_barang_awal: $('#kode_barang_awal').val(),
            kode_barang_akhir: $('#kode_barang_akhir').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
    }

    /**
     * Validate form
     */
    validateForm() {
        const bulan = $('#bulan').val();
        const tahun = $('#tahun').val();
        const jenisBarang = $('#jenis_barang').val();

        if (!bulan || bulan < 1 || bulan > 12) {
            this.showError('Bulan harus antara 1-12');
            return false;
        }

        if (!tahun || tahun < 1999) {
            this.showError('Tahun harus minimal 1999');
            return false;
        }

        if (jenisBarang === 'per_barang') {
            const kodeAwal = $('#kode_barang_awal').val();
            const kodeAkhir = $('#kode_barang_akhir').val();
            
            if (!kodeAwal || !kodeAkhir) {
                this.showError('Kode barang awal dan akhir harus diisi');
                return false;
            }
        }

        return true;
    }

    /**
     * Start progress
     */
    startProgress() {
        $('#progress_bar').show();
        $('#progress_text').show();
        $('#progress_message').text('Memulai proses...');
        this.currentProgress = 0;
        this.updateProgressBar(0);
    }

    /**
     * Stop progress
     */
    stopProgress() {
        $('#progress_bar').hide();
        $('#progress_text').hide();
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }
    }

    /**
     * Update progress bar
     */
    updateProgressBar(percentage) {
        $('.progress-bar').css('width', percentage + '%');
        $('.progress-bar').text(percentage + '%');
    }

    /**
     * Start progress monitoring
     */
    startProgressMonitoring() {
        this.progressInterval = setInterval(() => {
            if (this.currentProgress < 100) {
                this.currentProgress += Math.random() * 5;
                if (this.currentProgress > 100) this.currentProgress = 100;
                
                this.updateProgressBar(Math.round(this.currentProgress));
                
                // Update progress message based on process type
                if (this.processType === 'hitung_ulang_hpp') {
                    this.updateProgressMessage();
                }
            }
        }, 500);
    }

    /**
     * Update progress message
     */
    updateProgressMessage() {
        const messages = [
            'Inisialisasi data...',
            'Memproses bahan...',
            'Update HPP ke transaksi...',
            'Memproses kemasan...',
            'Memproses akhir bulan...',
            'Menyelesaikan proses...'
        ];
        
        const messageIndex = Math.floor((this.currentProgress / 100) * messages.length);
        if (messageIndex < messages.length) {
            $('#progress_message').text(messages[messageIndex]);
        }
    }

    /**
     * Execute the actual process
     */
    executeProcess(initialResponse) {
        $.ajax({
            url: '/hitung-ulang-hpp/execute',
            type: 'POST',
            data: {
                bulan: initialResponse.bulan,
                tahun: initialResponse.tahun,
                jenis_barang: initialResponse.jenis_barang,
                kode_barang_awal: initialResponse.kode_barang_awal,
                kode_barang_akhir: initialResponse.kode_barang_akhir,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                this.stopProgress();
                
                if (response.success) {
                    this.updateProgressBar(100);
                    $('#progress_message').text('Proses hitung ulang HPP selesai.');
                    
                    // Show export button
                    $('#btn_export').show();
                    
                    // Refresh DataTable
                    this.stockMinusTable.ajax.reload();
                    
                    this.showSuccess('Proses hitung ulang HPP berhasil diselesaikan!');
                } else {
                    this.showError('Error: ' + response.message);
                }
                
                this.enableProsesButton();
            },
            error: (xhr, status, error) => {
                this.stopProgress();
                this.showError('Terjadi kesalahan: ' + error);
                this.enableProsesButton();
            }
        });
    }

    /**
     * Enable proses button
     */
    enableProsesButton() {
        $('#btn_proses').prop('disabled', false);
        this.isProcessing = false;
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        // You can use SweetAlert2 or any other notification library
        alert(message);
    }

    /**
     * Show error message
     */
    showError(message) {
        // You can use SweetAlert2 or any other notification library
        alert(message);
    }

    /**
     * Get progress info for other processes
     */
    getProgressInfo() {
        return {
            currentProgress: this.currentProgress,
            totalItems: this.totalItems,
            processType: this.processType,
            isProcessing: this.isProcessing
        };
    }

    /**
     * Update progress from external source
     */
    updateProgress(progress, message) {
        this.currentProgress = progress;
        this.updateProgressBar(progress);
        if (message) {
            $('#progress_message').text(message);
        }
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.hitungUlangHPP = new HitungUlangHPP();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HitungUlangHPP;
} 