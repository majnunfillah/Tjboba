$(document).ready(function() {
    let isProcessing = false;
    
    // Initialize form
    initializeForm();
    
    // Form submission
    $('#formTutupBuku').on('submit', function(e) {
        e.preventDefault();
        
        if (isProcessing) {
            return false;
        }
        
        if (!validateForm()) {
            return false;
        }
        
        const jenisProses = $('#jenis_proses').val();
        
        // Start processing
        startProcessing();
        
        // Start processing directly
        submitForm();
    });
    

    
    /**
     * Submit form normally
     */
    function submitForm() {
        // Ensure form has values
        if (!$('#bulan').val()) {
            $('#bulan').val(new Date().getMonth() + 1);
        }
        if (!$('#tahun').val()) {
            $('#tahun').val(new Date().getFullYear());
        }
        if (!$('#jenis_proses').val()) {
            $('#jenis_proses').val('1'); // Default to Proses Aktiva
        }
        
        // Create FormData and manually add values to ensure they're included
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('bulan', $('#bulan').val());
        formData.append('tahun', $('#tahun').val());
        formData.append('jenis_proses', $('#jenis_proses').val());
        
        // Get process type for progress simulation
        const processType = $('#jenis_proses').val();
        const processConfig = getProcessConfig(processType);
        
        // Simulasi progress server processing
        let serverProgressInterval;
        let currentProgress = 30;
        let estimatedCount = null; // Default/fallback, akan diupdate dari server
        
        $.ajax({
            url: $('#formTutupBuku').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        // Upload progress hanya 0-30% (karena data kecil)
                        const uploadPercent = (e.loaded / e.total) * 30;
                        updateProgress(uploadPercent, 'Mengupload data...');
                    }
                }, false);
                
                // Setelah upload selesai, mulai progress server processing
                xhr.addEventListener('loadstart', function() {
                    updateProgress(30, 'Data terkirim, memproses di server...');
                    
                    // Mulai simulasi progress server processing berdasarkan jenis proses
                    let itemProgress = 0;
                    serverProgressInterval = setInterval(function() {
                        if (currentProgress < 90) {
                            currentProgress += processConfig.progressIncrement;
                            itemProgress++;
                            
                            // Tampilkan progress sesuai jenis proses
                            if (estimatedCount !== null) {
                                updateProgress(currentProgress, `${processConfig.processingMessage} ${itemProgress}/${estimatedCount}...`);
                            } else {
                                updateProgress(currentProgress, `${processConfig.processingMessage} di server...`);
                            }
                        }
                    }, processConfig.updateInterval);
                });
                
                return xhr;
            },
            success: function(response) {
                // Clear server progress interval
                if (serverProgressInterval) {
                    clearInterval(serverProgressInterval);
                }
                
                // Update estimated count dari response server
                estimatedCount = getEstimatedCount(response, processType);
                
                if (response.success) {
                    updateProgress(100, 'Proses selesai!');
                    
                    // Update status dengan pesan sukses dan info progress
                    const statusMessage = response.message || 'Proses berhasil diselesaikan';
                    let finalStatusMessage = statusMessage;
                    
                    // Tampilkan info progress sesuai jenis proses
                    finalStatusMessage = getFinalStatusMessage(response, processType, statusMessage);
                    
                    updateStatus(finalStatusMessage);
                    showSuccess(response.message || 'Proses berhasil diselesaikan');
                } else {
                    showError(response.message || 'Terjadi kesalahan');
                }
                stopProcessing();
            },
            error: function(xhr, status, error) {
                // Clear server progress interval
                if (serverProgressInterval) {
                    clearInterval(serverProgressInterval);
                }
                
                let errorMessage = 'Terjadi kesalahan dalam proses';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showError(errorMessage);
                stopProcessing();
            }
        });
    }
    
    /**
     * Get process configuration based on process type
     */
    function getProcessConfig(processType) {
        const configs = {
            '1': { // Proses Aktiva
                processingMessage: 'Memproses aktiva',
                progressIncrement: 2,
                updateInterval: 500,
                itemName: 'aktiva'
            },
            '2': { // Hitung Ulang Neraca
                processingMessage: 'Memproses transaksi neraca',
                progressIncrement: 1,
                updateInterval: 300,
                itemName: 'transaksi'
            },
            '3': { // Hitung Ulang Aktiva
                processingMessage: 'Memproses hitung ulang aktiva',
                progressIncrement: 2,
                updateInterval: 400,
                itemName: 'aktiva'
            },
            '4': { // HPP dan Rugi Laba
                processingMessage: 'Memproses HPP dan rugi laba',
                progressIncrement: 1.5,
                updateInterval: 350,
                itemName: 'devisi'
            },
            '5': { // Proses Dashboard
                processingMessage: 'Memproses dashboard',
                progressIncrement: 3,
                updateInterval: 600,
                itemName: 'data'
            },
            '6': { // Proses Aktiva Fiskal
                processingMessage: 'Memproses aktiva fiskal',
                progressIncrement: 2,
                updateInterval: 500,
                itemName: 'aktiva fiskal'
            },
            '7': { // Hitung Ulang Aktiva Fiskal
                processingMessage: 'Memproses hitung ulang aktiva fiskal',
                progressIncrement: 2,
                updateInterval: 400,
                itemName: 'aktiva fiskal'
            }
        };
        
        return configs[processType] || configs['1']; // Default to aktiva config
    }
    
    /**
     * Get estimated count from response
     */
    function getEstimatedCount(response, processType) {
        // Try different response fields based on process type
        if (response.estimated_aktiva_count !== undefined) {
            return response.estimated_aktiva_count;
        } else if (response.total_aktiva !== undefined) {
            return response.total_aktiva;
        } else if (response.total_transactions !== undefined) {
            return response.total_transactions;
        } else if (response.total_accounts !== undefined) {
            return response.total_accounts;
        } else if (response.progress_info) {
            const progressInfo = response.progress_info;
            if (progressInfo.total_aktiva !== undefined) {
                return progressInfo.total_aktiva;
            } else if (progressInfo.total_transactions !== undefined) {
                return progressInfo.total_transactions;
            } else if (progressInfo.total_accounts !== undefined) {
                return progressInfo.total_accounts;
            }
        }
        return null;
    }
    
    /**
     * Get final status message based on process type and response
     */
    function getFinalStatusMessage(response, processType, baseMessage) {
        const config = getProcessConfig(processType);
        const itemName = config.itemName;
        
        // Check for specific progress info
        if (response.total_aktiva !== undefined && response.aktiva_processed !== undefined) {
            return `${baseMessage} (${response.aktiva_processed}/${response.total_aktiva} ${itemName} diproses)`;
        } else if (response.total_transactions !== undefined && response.transactions_processed !== undefined) {
            return `${baseMessage} (${response.transactions_processed}/${response.total_transactions} ${itemName} diproses)`;
        } else if (response.total_accounts !== undefined && response.accounts_processed !== undefined) {
            return `${baseMessage} (${response.accounts_processed}/${response.total_accounts} ${itemName} diproses)`;
        } else if (response.progress_info) {
            const progressInfo = response.progress_info;
            if (progressInfo.total_aktiva !== undefined && progressInfo.processed_count !== undefined) {
                return `${baseMessage} (${progressInfo.processed_count}/${progressInfo.total_aktiva} ${itemName} diproses)`;
            } else if (progressInfo.total_transactions !== undefined && progressInfo.processed_count !== undefined) {
                return `${baseMessage} (${progressInfo.processed_count}/${progressInfo.total_transactions} ${itemName} diproses)`;
            } else if (progressInfo.total_accounts !== undefined && progressInfo.processed_count !== undefined) {
                return `${baseMessage} (${progressInfo.processed_count}/${progressInfo.total_accounts} ${itemName} diproses)`;
            }
        }
        
        return baseMessage;
    }
    
    /**
     * Update progress bar and status
     */
    function updateProgress(percentage, message) {
        // Update progress bar
        $('#progressBar').css('width', percentage + '%').text(Math.round(percentage) + '%');
        
        // Update status message
        $('#statusProses').text('Status: ' + message);
        
        // Add visual feedback
        if (percentage > 0) {
            $('#progressBar').removeClass('bg-secondary').addClass('bg-primary');
        }
        
        if (percentage === 100) {
            $('#progressBar').removeClass('bg-primary').addClass('bg-success');
        }
        
        // Force browser to update
        $('#progressBar')[0].offsetHeight;
    }
    
    /**
     * Update status message for aktiva processing
     */
    function updateStatus(message) {
        // Update status message
        $('#statusProses').text('Status: ' + message);
        
        // Add visual feedback with different color
        $('#statusProses').removeClass('text-muted').addClass('text-info');
        
        // Force browser to update
        $('#statusProses')[0].offsetHeight;
    }
    
    /**
     * Show success message
     */
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            confirmButtonText: 'OK'
        });
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: message,
            confirmButtonText: 'OK'
        });
    }
    
    /**
     * Initialize form
     */
    function initializeForm() {
        // Set current month and year if not set
        if (!$('#bulan').val()) {
            $('#bulan').val(new Date().getMonth() + 1);
        }
        if (!$('#tahun').val()) {
            $('#tahun').val(new Date().getFullYear());
        }
        if (!$('#jenis_proses').val()) {
            $('#jenis_proses').val('1'); // Default to Proses Aktiva
        }
    }
    
    /**
     * Validate form
     */
    function validateForm() {
        const bulan = $('#bulan').val();
        const tahun = $('#tahun').val();
        const jenisProses = $('#jenis_proses').val();
        
        if (!bulan || !tahun) {
            showError('Bulan dan tahun harus diisi');
            return false;
        }
        
        if (tahun < 2000 || tahun > 2099) {
            showError('Tahun harus antara 2000-2099');
            return false;
        }
        
        if (!jenisProses) {
            showError('Jenis proses harus dipilih');
            return false;
        }
        
        return true;
    }
    
    /**
     * Start processing
     */
    function startProcessing() {
        isProcessing = true;
        updateUI(true);
        updateProgress(0, 'Memulai proses...');
    }
    
    /**
     * Stop processing
     */
    function stopProcessing() {
        isProcessing = false;
        updateUI(false);
    }
    
    /**
     * Update UI based on processing state
     */
    function updateUI(processing) {
        if (processing) {
            $('#btnProses').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...');
            $('#bulan, #tahun, #jenis_proses').prop('disabled', true);
        } else {
            $('#btnProses').prop('disabled', false).html('<i class="fas fa-play mr-2"></i>Proses');
            $('#bulan, #tahun, #jenis_proses').prop('disabled', false);
        }
    }
});
