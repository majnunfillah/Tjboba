import $globalVariable, { publicURL, csfr_token } from "../../base-function.js";

console.log('SPK.js: Starting to load...');

(function (
    $,
    {
        baseSwal,
        baseAjax,
        formAjax,
        getModal,
        globalDelete,
        applyPlugins,
        mergeWithDefaultOptions,
        swalConfirm,
    }
) {
    console.log('SPK.js: Inside main function...');
    var datatableMain = $("#datatableMain").DataTable({
        ...mergeWithDefaultOptions({
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
                            let modal = getModal("lg");
                            modal.find(".modal-title").text("Tambah SPK");
                            modal.find(".modal-body").load(url, function () {
                                modal.modal("show");
                                applyPlugins();
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
                }
                return true;
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

    // Expand/collapse handler for Level 1 (SPK Detail) - INDEPENDENT
    $(document).on("click", "#datatableMain > tbody td.dt-control", function () {
        console.log('Level 1 Expand button clicked!'); // Debug log
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        console.log('Level 1 Row data:', row.data()); // Debug log
        
        if (row.child.isShown()) {
            console.log('Hiding level 1 child row'); // Debug log
            row.child.hide();
            tr.removeClass("shown");
        } else {
            showLevel1Detail(row, tr);
        }
    });

    // Expand/collapse handler for Level 2 (Jadwal Produksi) - INDEPENDENT
    $(document).on("click", "#datatableMain > tbody td.dt-control-level2-main", function () {
        console.log('Level 2 Main Expand button clicked!'); // Debug log
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        console.log('Level 2 Main Row data:', row.data()); // Debug log
        
        if (tr.hasClass('shown-level2-main')) {
            console.log('Hiding level 2 main child row'); // Debug log
            // Hide the level 2 child
            tr.next('.level2-main-row').remove();
            tr.removeClass("shown-level2-main");
        } else {
            showLevel2Detail(row, tr);
        }
    });

    // Show Level 1 Detail (SPK Detail) - HANYA SPK DETAIL, TIDAK ADA EXPAND LAGI
    function showLevel1Detail(row, tr) {
        console.log('showLevel1Detail called with data:', row.data()); // Debug log
        
        let level1Child = $(`
            <div class="level1-expand">
                <h5><i class="fa fa-list text-primary"></i> SPK Detail untuk ${row.data().NoBukti}</h5>
                <table class="table table-bordered table-hover table-sm level1-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Urut</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Quantity</th>
                            <th>Satuan</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center">
                                <i class="fa fa-spinner fa-spin"></i> Loading SPK Detail...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `);
        
        var datatableLevel1 = level1Child.find("table").DataTable({
            ajax: {
                url: `${publicURL}/produksi/transaksi-spk/detail`,
                type: "POST",
                headers: { "X-CSRF-TOKEN": csfr_token },
                data: { NoBukti: row.data().NoBukti },
                dataSrc: function(json) {
                    console.log('Level 1 AJAX response:', json); // Debug log
                    console.log('Level 1 data length:', json.data ? json.data.length : 0); // Debug log
                    return json.data || [];
                }
            },
            columns: [
                { data: "Urut", width: "10%" },
                { data: "KodeBrg", width: "20%" },
                { data: "NamaBrg", width: "35%" },
                { data: "Qnt", className: "text-right", width: "15%" },
                { data: "Satuan", width: "10%" },
                {
                    data: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    width: "10%"
                }
            ],
            order: [[0, 'asc']], // Order by Urut column
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            searching: true,
            info: true,
            paging: true,
            responsive: true,
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
                emptyTable: "Tidak ada detail SPK"
            }
        });

        row.child(level1Child).show();
        tr.addClass("shown");
    }

    // Show Level 2 Detail (Jadwal Produksi) - INDEPENDENT DARI MAIN TABLE
    function showLevel2Detail(row, tr) {
        console.log('showLevel2Detail called with data:', row.data()); // Debug log
        let level2Child = $(`
            <tr class="level2-main-row">
                <td colspan="13">
                    <div class="level2-main-expand">
                        <h5><i class="fa fa-calendar text-success"></i> Jadwal Produksi untuk ${row.data().NoBukti}</h5>
                        <table class="table table-bordered table-hover table-sm level2-table">
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <i class="fa fa-spinner fa-spin"></i> Loading Jadwal Produksi...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        `);

        var datatableLevel2 = level2Child.find("table").DataTable({
            ajax: {
                url: `${publicURL}/produksi/transaksi-spk/detail-level2-all`,
                type: "GET",
                data: { 
                    NoBukti: row.data().NoBukti
                },
                dataSrc: function(json) {
                    console.log('Level 2 AJAX response:', json); // Debug log
                    console.log('Level 2 data length:', json.data ? json.data.length : 0); // Debug log
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
                { data: "action", orderable: false, searchable: false, className: "text-center" }
            ],
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
            searching: true,
            info: true,
            paging: true,
            responsive: true,
            order: [[0, 'asc']], // Order by NoUrut
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

        // Insert after current row
        tr.after(level2Child);
        tr.addClass("shown-level2-main");
    }

    // Otorisasi handler
    $(document).on("change", 'input[name="IsOtorisasi1"]', function (e) {
        e.preventDefault();
        console.log('SPK: Checkbox clicked!');
        let tr = $(this).closest("tr");
        const data = datatableMain.row(tr).data();
        console.log('SPK: Data:', data);
        
        swalConfirm({
            title: "Konfirmasi",
            text: "Apakah anda yakin akan mengubah status otorisasi?",
            callback: function () {
                console.log('SPK: Confirmed, sending AJAX...');
                baseAjax({
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
})($globalVariable);
