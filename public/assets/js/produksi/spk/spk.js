import $globalVariable, { publicURL, csfr_token } from "../../base-function.js";

console.log('SPK.js: Starting to load...');

(function ($, globalFunctions) {
    console.log('SPK.js: Inside main function...');
    console.log('GlobalFunctions available:', globalFunctions); // Debug log
    
    var datatableMain = $("#datatableMain").DataTable({
        ...globalFunctions.mergeWithDefaultOptions({
            ajax: {
                url: $("#datatableMain").data("server"),
                type: 'GET',
                error: function (xhr, error, thrown) {
                    console.log('DataTables error:', error);
                    console.log('Exception:', thrown);
                    console.log('Response:', xhr.responseText);
                }
            },
            $defaultOpt: {
                buttons: [
                    {
                        $keyButton: "tambah",
                        className: "btn-module",
                    },
                    "colvis",
                    "refresh",
                    {
                        $keyButton: "excel-pdf",
                        className: "btn-module",
                    },
                    "flexiblefixed",
                    {
                        $keyButton: "excel",
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                        },
                    },
                    {
                        $keyButton: "pdf",
                        exportOptions: {
                            columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                        },
                    },
                ],
                initComplete: function () {
                    $(document).on(
                        "click",
                        "#datatableMain_wrapper .btn-tambah.btn-module",
                        function (e) {
                            e.preventDefault();
                            let url = $(this).data("url");
                            let modal = globalFunctions.getModal("lg");
                            modal.find(".modal-title").text("Tambah SPK");
                            modal.find(".modal-body").load(url, function () {
                                modal.modal("show");
                                globalFunctions.applyPlugins();
                            });
                        }
                    );
                    $(document).on(
                        "click",
                        "#datatableMain_wrapper .btn-export-excel.btn-module",
                        function (e) {
                        e.preventDefault();
                        let btnGroup = $(this).closest(".btn-group");
                            let btnExportExcel = btnGroup.find(
                                ".buttons-excel.buttons-html5"
                            );
                        btnExportExcel.trigger("click");
                        }
                    );
                    $(document).on(
                        "click",
                        "#datatableMain_wrapper .btn-export-pdf.btn-module",
                        function (e) {
                        e.preventDefault();
                        let btnGroup = $(this).closest(".btn-group");
                            let btnExportPdf = btnGroup.find(
                                ".buttons-pdf.buttons-html5"
                            );
                        btnExportPdf.trigger("click");
                        }
                    );
                    
                    // Add PDF download handler
                    $(document).on("click", ".download-pdf", function (e) {
                        e.preventDefault();
                        let NoBukti = "bukti=" + encodeURIComponent($(this).data("bukti"));
                        let url = `${publicURL}/produksi/transaksi-spk/download-pdf?${NoBukti}`;
                        window.open(url, "_blank");
                    });
                    
                    return true;
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    defaultContent: "",
                    className: "dt-control",
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    defaultContent: "",
                    className: "dt-control-level2-main",
                },
                { data: "NoBukti" },
                { data: "Tanggal" },
                { data: "NoSO" },
                { data: "KodeBrg" },
                { data: "NamaBrg" },
                { data: "Qnt", className: "text-right" },
                { data: "Satuan" },
                { data: "IsOtorisasi1Html" },
                { data: "OtoUser1" },
                { data: "TglOto1" },
                {
                    data: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center parentBtnRow",
                },
            ],
            fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                console.log('SPK Row data:', aData); // Debug log
                console.log('indikatorExpand value:', aData.indikatorExpand); // Debug log
                
                if (aData.indikatorExpand === false) {
                    $(nRow).find("td.dt-control").addClass("indicator-white");
                    $(nRow).find("td.dt-control-level2-main").addClass("indicator-white");
                    console.log('Added indicator-white to row', iDisplayIndex); // Debug log
                } else {
                    console.log('Row should have expand button', iDisplayIndex); // Debug log
                }
                
                if (aData.IsOtorisasi1 == 1) {
                    $(nRow).addClass("yellowClass");
                }
            },
        }),
    });

    // Helper function to create safe CSS ID from NoBukti
    function createSafeId(noBukti) {
        return noBukti.replace(/[^a-zA-Z0-9]/g, '-');
    }

    // Expand/collapse handler for Level 1 (SPK Detail) - DAPAT TAMPIL BERSAMAAN
    $(document).on("click", "#datatableMain > tbody td.dt-control", function () {
        console.log('Level 1 Expand button clicked!'); // Debug log
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        console.log('Level 1 Row data:', row.data()); // Debug log
        
        if (row.child.isShown()) {
            // Check if Level 1 section exists
            let existingChild = row.child();
            let level1Section = existingChild.find('.level1-section');
            
            if (level1Section.length > 0) {
                // Level 1 already shown, remove it
                level1Section.remove();
                // If no sections left, hide the child completely
                if (existingChild.find('.level1-section, .level2-section').length === 0) {
                    row.child.hide();
                    tr.removeClass("shown");
                }
                tr.removeClass("shown-level1");
            } else {
                // Level 1 not shown, add it to existing child
                addLevel1Section(row, tr);
            }
        } else {
            // No child shown, create new one with Level 1
            createCombinedChild(row, tr, 'level1');
        }
    });

    // Expand/collapse handler for Level 2 (Jadwal Produksi) - DAPAT TAMPIL BERSAMAAN
    $(document).on("click", "#datatableMain > tbody td.dt-control-level2-main", function () {
        console.log('Level 2 Main Expand button clicked!'); // Debug log
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        console.log('Level 2 Main Row data:', row.data()); // Debug log
        
        if (row.child.isShown()) {
            // Check if Level 2 section exists
            let existingChild = row.child();
            let level2Section = existingChild.find('.level2-section');
            
            if (level2Section.length > 0) {
                // Level 2 already shown, remove it
                level2Section.remove();
                // If no sections left, hide the child completely
                if (existingChild.find('.level1-section, .level2-section').length === 0) {
                    row.child.hide();
                    tr.removeClass("shown");
                }
                tr.removeClass("shown-level2-main");
            } else {
                // Level 2 not shown, add it to existing child
                addLevel2Section(row, tr);
            }
        } else {
            // No child shown, create new one with Level 2
            createCombinedChild(row, tr, 'level2');
        }
    });

    // Create Combined Child Container
    function createCombinedChild(row, tr, initialLevel) {
        console.log('createCombinedChild called, initialLevel:', initialLevel, 'data:', row.data());
        
        let combinedChild = $(`
            <div class="combined-expand">
                <!-- Sections will be added here dynamically -->
            </div>
        `);

        row.child(combinedChild).show();
        tr.addClass("shown");
        
        if (initialLevel === 'level1') {
            addLevel1Section(row, tr);
        } else if (initialLevel === 'level2') {
            addLevel2Section(row, tr);
        }
    }

    // Add Level 1 Section
    function addLevel1Section(row, tr) {
        console.log('addLevel1Section called');
        
        let safeId = createSafeId(row.data().NoBukti);
        let existingChild = row.child();
        
        let level1Section = $(`
            <div class="level1-section mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fa fa-list text-primary"></i> SPK Detail untuk ${row.data().NoBukti}</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success btn-add-level1" data-bukti="${row.data().NoBukti}">
                            <i class="fa fa-plus"></i> Tambah Detail
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary btn-close-level1">
                            <i class="fa fa-times"></i> Tutup
                        </button>
                    </div>
                </div>
                <div class="table_expand">
                    <table id="level1-table-${safeId}" class="table table-bordered table-hover table-sm level1-table">
                        <thead class="thead-light">
                            <tr>
                                <th>Urut</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Quantity</th>
                                <th>Satuan</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <i class="fa fa-spinner fa-spin"></i> Loading SPK Detail...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th class="text-right total-qty">0</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `);

        existingChild.find('.combined-expand').append(level1Section);
        tr.addClass("shown-level1");

        // Initialize Level 1 DataTable
        var datatableLevel1 = level1Section.find(`#level1-table-${safeId}`).DataTable({
            ajax: {
                url: `${publicURL}/produksi/transaksi-spk/detail`,
                type: "POST",
                headers: { "X-CSRF-TOKEN": csfr_token },
                data: { NoBukti: row.data().NoBukti },
                dataSrc: function(json) {
                    console.log('Level 1 AJAX response:', json);
                    console.log('Level 1 data length:', json.data ? json.data.length : 0);
                    
                    // Calculate and update total quantity
                    if (json.data && json.data.length > 0) {
                        let totalQty = 0;
                        json.data.forEach(item => {
                            totalQty += parseFloat(item.Qnt || 0);
                        });
                        setTimeout(() => {
                            level1Section.find('.total-qty').text(totalQty.toLocaleString());
                        }, 100);
                    }
                    
                    return json.data || [];
                }
            },
            columns: [
                { data: "Urut" },
                { data: "KodeBrg" },
                { data: "NamaBrg" },
                { data: "Qnt", className: "text-right" },
                { data: "Satuan" },
                { 
                    data: null, 
                    orderable: false, 
                    searchable: false, 
                    className: "text-center",
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-warning btn-edit-level1" 
                                        data-bukti="${row.NoBukti}" data-urut="${row.Urut}" 
                                        title="Edit Detail">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-delete-level1" 
                                        data-bukti="${row.NoBukti}" data-urut="${row.Urut}" 
                                        title="Hapus Detail">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            searching: true,
            info: true,
            paging: true,
            responsive: true,
            order: [[0, 'asc']],
            language: {
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ SPK Detail",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 SPK Detail",
                infoFiltered: "(disaring dari _MAX_ total SPK Detail)",
                lengthMenu: "Tampilkan _MENU_ SPK Detail",
                search: "Cari SPK Detail:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                emptyTable: "Tidak ada detail SPK untuk SPK ini"
            }
        });
    }

    // Add Level 2 Section
    function addLevel2Section(row, tr) {
        console.log('addLevel2Section called');
        
        let safeId = createSafeId(row.data().NoBukti);
        let existingChild = row.child();
        
        let level2Section = $(`
            <div class="level2-section mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fa fa-calendar text-success"></i> Jadwal Produksi untuk ${row.data().NoBukti}</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-success btn-add-level2" data-bukti="${row.data().NoBukti}">
                            <i class="fa fa-plus"></i> Tambah Jadwal
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary btn-close-level2">
                            <i class="fa fa-times"></i> Tutup
                        </button>
                    </div>
                </div>
                <div class="table_expand">
                    <table id="level2-table-${safeId}" class="table table-bordered table-hover table-sm level2-table">
                        <thead class="thead-light">
                            <tr>
                                <th>No Urut</th>
                                <th>Kode Prs</th>
                                <th>Kode Mesin</th>
                                <th>Tanggal</th>
                                <th>Jam Awal</th>
                                <th>Jam Akhir</th>
                                <th>Qty SPK</th>
                                <th>Tarif Mesin</th>
                                <th>Tarif Tenaker</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="text-center">
                                    <i class="fa fa-spinner fa-spin"></i> Loading Jadwal Produksi...
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-right">Total:</th>
                                <th class="text-right total-qty-spk">0</th>
                                <th class="text-right total-tarif-mesin">0</th>
                                <th class="text-right total-tarif-tenaker">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `);

        existingChild.find('.combined-expand').append(level2Section);
        tr.addClass("shown-level2-main");

        // Initialize Level 2 DataTable
        var datatableLevel2 = level2Section.find(`#level2-table-${safeId}`).DataTable({
            ajax: {
                url: `${publicURL}/produksi/transaksi-spk/detail-level2-all`,
                type: "GET",
                data: { 
                    NoBukti: row.data().NoBukti
                },
                dataSrc: function(json) {
                    console.log('Level 2 AJAX response:', json);
                    console.log('Level 2 data length:', json.data ? json.data.length : 0);
                    
                    // Calculate and update totals
                    if (json.data && json.data.length > 0) {
                        let totalQtySpk = 0;
                        let totalTarifMesin = 0;
                        let totalTarifTenaker = 0;
                        
                        json.data.forEach(item => {
                            totalQtySpk += parseFloat(item.QNTSPK || 0);
                            totalTarifMesin += parseFloat(item.TarifMesin || 0);
                            totalTarifTenaker += parseFloat(item.TarifTenaker || 0);
                        });
                        
                        setTimeout(() => {
                            level2Section.find('.total-qty-spk').text(totalQtySpk.toLocaleString());
                            level2Section.find('.total-tarif-mesin').text(totalTarifMesin.toLocaleString());
                            level2Section.find('.total-tarif-tenaker').text(totalTarifTenaker.toLocaleString());
                        }, 100);
                    }
                    
                    return json.data || [];
                }
            },
            columns: [
                { data: "NoUrut" },
                { data: "KodePrs" },
                { data: "KODEMSN" },
                { data: "TANGGAL" },
                { data: "JAMAWAL" },
                { data: "JAMAKHIR" },
                { data: "QNTSPK", className: "text-right" },
                { data: "TarifMesin", className: "text-right" },
                { data: "TarifTenaker", className: "text-right" },
                { 
                    data: null, 
                    orderable: false, 
                    searchable: false, 
                    className: "text-center",
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-warning btn-edit-level2" 
                                        data-bukti="${row.NoBukti}" data-urut="${row.NoUrut}" 
                                        title="Edit Jadwal">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-delete-level2" 
                                        data-bukti="${row.NoBukti}" data-urut="${row.NoUrut}" 
                                        title="Hapus Jadwal">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            searching: true,
            info: true,
            paging: true,
            responsive: true,
            order: [[0, 'asc']],
            language: {
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ Jadwal Produksi",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 Jadwal Produksi",
                infoFiltered: "(disaring dari _MAX_ total Jadwal Produksi)",
                lengthMenu: "Tampilkan _MENU_ Jadwal Produksi",
                search: "Cari Jadwal Produksi:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                emptyTable: "Tidak ada jadwal produksi untuk SPK ini"
            }
        });
    }

    // Otorisasi handler
    $(document).on("change", 'input[name="IsOtorisasi1"]', function (e) {
        e.preventDefault();
        console.log('SPK: Checkbox clicked!');
        let tr = $(this).closest("tr");
        const data = datatableMain.row(tr).data();
        console.log('SPK: Data:', data);
        
        globalFunctions.swalConfirm({
            title: "Konfirmasi",
            text: "Apakah anda yakin akan mengubah status otorisasi?",
            callback: function () {
                console.log('SPK: Confirmed, sending AJAX...');
                globalFunctions.baseAjax({
                    url: publicURL + "/produksi/transaksi-spk/set-otorisasi",
                    type: "POST",
                    param: {
                        NoBukti: data.NoBukti,
                        otoLevel: $(e.target).attr("name"),
                        status: $(e.target).is(":checked") ? 1 : 0,
                    },
                    successCallback: function (res) {
                        console.log('SPK: Success:', res);
                        datatableMain.ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        console.log('SPK: Error:', xhr);
                        $(e.target).prop("checked", !$(e.target).is(":checked"));
                    },
                });
            },
            callbackDismiss: function () {
                console.log('SPK: Dismissed, reverting checkbox');
                $(e.target).prop("checked", !$(e.target).is(":checked"));
            },
        });
    });

    // Close Level 1 handler
    $(document).on("click", ".btn-close-level1", function () {
        let tr = $(this).closest("tr");
        let row = datatableMain.row(tr);
        let existingChild = row.child();
        let level1Section = existingChild.find('.level1-section');
        
        level1Section.remove();
        if (existingChild.find('.level1-section, .level2-section').length === 0) {
            row.child.hide();
            tr.removeClass("shown");
        }
        tr.removeClass("shown-level1");
    });

    // Close Level 2 handler
    $(document).on("click", ".btn-close-level2", function () {
        let tr = $(this).closest("tr");
        let row = datatableMain.row(tr);
        let existingChild = row.child();
        let level2Section = existingChild.find('.level2-section');
        
        level2Section.remove();
        if (existingChild.find('.level1-section, .level2-section').length === 0) {
            row.child.hide();
            tr.removeClass("shown");
        }
        tr.removeClass("shown-level2-main");
    });

    // Add Level 1 Detail handler
    $(document).on("click", ".btn-add-level1", function () {
        let noBukti = $(this).data("bukti");
        let modal = globalFunctions.getModal("lg");
        modal.find(".modal-title").text("Tambah SPK Detail");
        modal.find(".modal-body").load(`${publicURL}/produksi/transaksi-spk/detail/create?NoBukti=${noBukti}`, function () {
            modal.modal("show");
            globalFunctions.applyPlugins();
        });
    });

    // Edit Level 1 Detail handler (memorial style buttons)
    $(document).on("click", ".btnEditSpkDetail", function () {
        let noBukti = $(this).data("bukti");
        let urut = $(this).data("urut");
        let modal = globalFunctions.getModal("lg");
        modal.find(".modal-title").text("Edit SPK Detail");
        modal.find(".modal-body").load(`${publicURL}/produksi/transaksi-spk/detail/edit?NoBukti=${noBukti}&Urut=${urut}`, function () {
            modal.modal("show");
            globalFunctions.applyPlugins();
        });
    });

    // Delete Level 1 Detail handler (memorial style buttons)
    $(document).on("click", ".btnDeleteSpkDetail", function () {
        let noBukti = $(this).data("bukti");
        let urut = $(this).data("urut");
        
        globalFunctions.swalConfirm({
            title: "Konfirmasi Hapus",
            text: "Apakah anda yakin akan menghapus SPK Detail ini?",
            callback: function () {
                globalFunctions.baseAjax({
                    url: publicURL + "/produksi/transaksi-spk/detail/delete",
                    type: "POST",
                    param: {
                        NoBukti: noBukti,
                        Urut: urut,
                    },
                    successCallback: function (res) {
                        console.log('Delete Level 1 Success:', res);
                        // Refresh both main table and level 1 detail table
                        datatableMain.ajax.reload();
                        $(`#level1-table-${createSafeId(noBukti)}`).DataTable().ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        console.log('Delete Level 1 Error:', xhr);
                    },
                });
            },
        });
    });

    // Edit Level 2 Jadwal handler (memorial style buttons)
    $(document).on("click", ".btnEditSpkLevel2", function () {
        let noBukti = $(this).data("bukti");
        let urutParent = $(this).data("urut-parent");
        let urutChild = $(this).data("urut-child");
        let modal = globalFunctions.getModal("lg");
        modal.find(".modal-title").text("Edit Jadwal Produksi");
        modal.find(".modal-body").load(`${publicURL}/produksi/transaksi-spk/jadwal/edit?NoBukti=${noBukti}&NoUrut=${urutChild}`, function () {
            modal.modal("show");
            globalFunctions.applyPlugins();
        });
    });

    // Delete Level 2 Jadwal handler (memorial style buttons)
    $(document).on("click", ".btnDeleteSpkLevel2", function () {
        let noBukti = $(this).data("bukti");
        let urutParent = $(this).data("urut-parent");
        let urutChild = $(this).data("urut-child");
        
        globalFunctions.swalConfirm({
            title: "Konfirmasi Hapus",
            text: "Apakah anda yakin akan menghapus Jadwal Produksi ini?",
            callback: function () {
                globalFunctions.baseAjax({
                    url: publicURL + "/produksi/transaksi-spk/jadwal/delete",
                    type: "POST",
                    param: {
                        NoBukti: noBukti,
                        NoUrut: urutChild,
                    },
                    successCallback: function (res) {
                        console.log('Delete Level 2 Success:', res);
                        // Refresh both main table and level 2 detail table
                        datatableMain.ajax.reload();
                        $(`#level2-table-${createSafeId(noBukti)}`).DataTable().ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        console.log('Delete Level 2 Error:', xhr);
                    },
                });
            },
        });
    });

    // Edit Level 1 Detail handler (existing button class for compatibility)
    $(document).on("click", ".btn-edit-level1", function () {
        let noBukti = $(this).data("bukti");
        let urut = $(this).data("urut");
        let modal = globalFunctions.getModal("lg");
        modal.find(".modal-title").text("Edit SPK Detail");
        modal.find(".modal-body").load(`${publicURL}/produksi/transaksi-spk/detail/edit?NoBukti=${noBukti}&Urut=${urut}`, function () {
            modal.modal("show");
            globalFunctions.applyPlugins();
        });
    });

    // Delete Level 1 Detail handler (existing button class for compatibility)
    $(document).on("click", ".btn-delete-level1", function () {
        let noBukti = $(this).data("bukti");
        let urut = $(this).data("urut");
        
        globalFunctions.swalConfirm({
            title: "Konfirmasi Hapus",
            text: "Apakah anda yakin akan menghapus SPK Detail ini?",
            callback: function () {
                globalFunctions.baseAjax({
                    url: publicURL + "/produksi/transaksi-spk/detail/delete",
                    type: "POST",
                    param: {
                        NoBukti: noBukti,
                        Urut: urut,
                    },
                    successCallback: function (res) {
                        console.log('Delete Level 1 Success:', res);
                        // Refresh both main table and level 1 detail table
                        datatableMain.ajax.reload();
                        $(`#level1-table-${createSafeId(noBukti)}`).DataTable().ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        console.log('Delete Level 1 Error:', xhr);
                    },
                });
            },
        });
    });

    // Add Level 2 Jadwal handler
    $(document).on("click", ".btn-add-level2", function () {
        let noBukti = $(this).data("bukti");
        let modal = globalFunctions.getModal("lg");
        modal.find(".modal-title").text("Tambah Jadwal Produksi");
        modal.find(".modal-body").load(`${publicURL}/produksi/transaksi-spk/jadwal/create?NoBukti=${noBukti}`, function () {
            modal.modal("show");
            globalFunctions.applyPlugins();
        });
    });

    // Edit Level 2 Jadwal handler (memorial style buttons)
    $(document).on("click", ".btnEditSpkLevel2", function () {
        let noBukti = $(this).data("bukti");
        let urutChild = $(this).data("urut-child");
        let modal = globalFunctions.getModal("lg");
        modal.find(".modal-title").text("Edit Jadwal Produksi");
        modal.find(".modal-body").load(`${publicURL}/produksi/transaksi-spk/jadwal/edit?NoBukti=${noBukti}&NoUrut=${urutChild}`, function () {
            modal.modal("show");
            globalFunctions.applyPlugins();
        });
    });

    // Delete Level 2 Jadwal handler (memorial style buttons)
    $(document).on("click", ".btnDeleteSpkLevel2", function () {
        let noBukti = $(this).data("bukti");
        let urutChild = $(this).data("urut-child");
        
        globalFunctions.swalConfirm({
            title: "Konfirmasi Hapus",
            text: "Apakah anda yakin akan menghapus Jadwal Produksi ini?",
            callback: function () {
                globalFunctions.baseAjax({
                    url: publicURL + "/produksi/transaksi-spk/jadwal/delete",
                    type: "POST",
                    param: {
                        NoBukti: noBukti,
                        NoUrut: urutChild,
                    },
                    successCallback: function (res) {
                        console.log('Delete Level 2 Success:', res);
                        // Refresh both main table and level 2 detail table
                        datatableMain.ajax.reload();
                        $(`#level2-table-${createSafeId(noBukti)}`).DataTable().ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        console.log('Delete Level 2 Error:', xhr);
                    },
                });
            },
        });
    });

    // Edit Level 2 Jadwal handler (existing button class for compatibility)
    $(document).on("click", ".btn-edit-level2", function () {
        let noBukti = $(this).data("bukti");
        let urut = $(this).data("urut");
        let modal = globalFunctions.getModal("lg");
        modal.find(".modal-title").text("Edit Jadwal Produksi");
        modal.find(".modal-body").load(`${publicURL}/produksi/transaksi-spk/jadwal/edit?NoBukti=${noBukti}&NoUrut=${urut}`, function () {
            modal.modal("show");
            globalFunctions.applyPlugins();
        });
    });

    // Tab switching handler dan load outstanding SO
    $(document).on('shown.bs.tab', 'a[data-toggle="pill"]', function (e) {
        var target = $(e.target).attr("href"); // activated tab
        
        if (target === '#outstanding-so') {
            // Load outstanding SO ketika tab diklik
            loadOutstandingSO();
        } else if (target === '#daftar-spk') {
            // Refresh DataTable ketika kembali ke tab daftar SPK
            if ($.fn.DataTable.isDataTable('#datatableMain')) {
                datatableMain.columns.adjust().draw();
            }
        }
    });

    // Outstanding SO DataTable variable
    var outstandingSoTable;

    // Function untuk load Outstanding SO
    function loadOutstandingSO() {
        console.log('Loading Outstanding SO...');
        
        // Initialize Outstanding SO DataTable jika belum ada
        if (!$.fn.DataTable.isDataTable('#outstandingSoTable')) {
            initializeOutstandingSoTable();
        } else {
            // Reload data jika sudah ada
            outstandingSoTable.ajax.reload();
        }
        
        // Load summary statistics
        loadOutstandingSoSummary();
    }

    // Function untuk initialize Outstanding SO DataTable
    function initializeOutstandingSoTable() {
        outstandingSoTable = $("#outstandingSoTable").DataTable({
            ...globalFunctions.mergeWithDefaultOptions({
                ajax: {
                    url: publicURL + "/produksi/transaksi-spk/outstanding-so",
                    type: 'GET',
                    dataSrc: function(json) {
                        console.log('Outstanding SO data:', json);
                        
                        // Update totals in footer
                        if (json.data && json.data.length > 0) {
                            let totalQtySO = 0;
                            let totalQtySpk = 0;
                            let totalSaldo = 0;
                            
                            json.data.forEach(item => {
                                totalQtySO += parseFloat(item.QntSO || 0);
                                totalQtySpk += parseFloat(item.QntSPK || 0);
                                totalSaldo += parseFloat(item.Saldo || 0);
                            });
                            
                            setTimeout(() => {
                                $('#total-qty-so').text(totalQtySO.toLocaleString('id-ID', {minimumFractionDigits: 2}));
                                $('#total-qty-spk').text(totalQtySpk.toLocaleString('id-ID', {minimumFractionDigits: 2}));
                                $('#total-saldo').text(totalSaldo.toLocaleString('id-ID', {minimumFractionDigits: 2}));
                            }, 100);
                        }
                        
                        return json.data || [];
                    },
                    error: function (xhr, error, thrown) {
                        console.error('Outstanding SO DataTable error:', error, thrown);
                        console.error('Response:', xhr.responseText);
                    }
                },
                columns: [
                    { data: "NOBUKTI", className: "text-center" },
                    { data: "URUT", className: "text-center" },
                    { data: "KODEBRG", className: "text-center" },
                    { data: "NAMABRG" },
                    { 
                        data: "QntSO", 
                        className: "text-right",
                        render: function(data) {
                            return parseFloat(data || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
                        }
                    },
                    { 
                        data: "QntSPK", 
                        className: "text-right",
                        render: function(data) {
                            return parseFloat(data || 0).toLocaleString('id-ID', {minimumFractionDigits: 2});
                        }
                    },
                    { 
                        data: "Saldo", 
                        className: "text-right",
                        render: function(data, type, row) {
                            let saldo = parseFloat(data || 0);
                            let colorClass = saldo > 0 ? 'text-danger font-weight-bold' : 'text-success';
                            return `<span class="${colorClass}">${saldo.toLocaleString('id-ID', {minimumFractionDigits: 2})}</span>`;
                        }
                    },
                    { data: "Satuan", className: "text-center" },
                    { 
                        data: "tglmulai", 
                        className: "text-center",
                        render: function(data) {
                            return data ? new Date(data).toLocaleDateString('id-ID') : '-';
                        }
                    },
                    { 
                        data: "tglkirim", 
                        className: "text-center",
                        render: function(data, type, row) {
                            if (!data) return '-';
                            let tglKirim = new Date(data);
                            let today = new Date();
                            let diffDays = Math.ceil((tglKirim - today) / (1000 * 60 * 60 * 24));
                            
                            let colorClass = '';
                            if (diffDays < 0) colorClass = 'text-danger font-weight-bold'; // Overdue
                            else if (diffDays <= 7) colorClass = 'text-warning font-weight-bold'; // Urgent
                            
                            return `<span class="${colorClass}">${tglKirim.toLocaleDateString('id-ID')}</span>`;
                        }
                    },
                    { 
                        data: "tglselesai", 
                        className: "text-center",
                        render: function(data) {
                            return data ? new Date(data).toLocaleDateString('id-ID') : '-';
                        }
                    }
                ],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                order: [[9, 'asc']], // Order by tgl kirim ascending
                language: {
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ Outstanding SO",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 Outstanding SO",
                    infoFiltered: "(disaring dari _MAX_ total Outstanding SO)",
                    lengthMenu: "Tampilkan _MENU_ Outstanding SO",
                    search: "Cari Outstanding SO:",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada Outstanding SO"
                },
                $defaultOpt: {
                    buttons: [
                        "colvis",
                        "refresh", 
                        {
                            $keyButton: "excel",
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                            },
                        },
                        {
                            $keyButton: "pdf",
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                            },
                        }
                    ]
                }
            })
        });
    }

    // Function untuk load Outstanding SO Summary
    function loadOutstandingSoSummary() {
        console.log('Loading Outstanding SO Summary...');
        
        // Reset nilai summary
        $('#total-outstanding-items').text('-');
        $('#urgent-so-count').text('-');
        $('#overdue-so-count').text('-');
        $('#completion-percentage').text('-');
        
        // AJAX call untuk mendapatkan summary Outstanding SO
        globalFunctions.baseAjax({
            url: publicURL + "/produksi/transaksi-spk/outstanding-so-summary",
            type: "GET",
            successCallback: function (res) {
                console.log('Outstanding SO Summary loaded:', res);
                
                if (res.success) {
                    $('#total-outstanding-items').text(res.data.total_items || '0');
                    $('#urgent-so-count').text(res.data.urgent_count || '0');
                    $('#overdue-so-count').text(res.data.overdue_count || '0');
                    $('#completion-percentage').text((res.data.completion_rate || 0) + '%');
                } else {
                    console.warn('Outstanding SO Summary: Response not successful');
                }
            },
            errorCallback: function (xhr) {
                console.error('Error loading Outstanding SO summary:', xhr);
                $('#total-outstanding-items').text('0');
                $('#urgent-so-count').text('0');
                $('#overdue-so-count').text('0');
                $('#completion-percentage').text('0%');
            },
        });
    }

    // Delete Level 2 Jadwal handler (existing button class for compatibility)
    $(document).on("click", ".btn-delete-level2", function () {
        let noBukti = $(this).data("bukti");
        let urut = $(this).data("urut");
        
        globalFunctions.swalConfirm({
            title: "Konfirmasi Hapus",
            text: "Apakah anda yakin akan menghapus Jadwal Produksi ini?",
            callback: function () {
                globalFunctions.baseAjax({
                    url: publicURL + "/produksi/transaksi-spk/jadwal/delete",
                    type: "POST",
                    param: {
                        NoBukti: noBukti,
                        NoUrut: urut,
                    },
                    successCallback: function (res) {
                        console.log('Delete Level 2 Success:', res);
                        // Refresh both main table and level 2 detail table
                        datatableMain.ajax.reload();
                        $(`#level2-table-${createSafeId(noBukti)}`).DataTable().ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        console.log('Delete Level 2 Error:', xhr);
                    },
                });
            },
        });
    });

})($, $globalVariable);
