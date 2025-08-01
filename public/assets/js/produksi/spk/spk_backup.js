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
            },
            initComplete: function (settings, json) {
                if (json.data.length == 0) {
                    $(document)
                        .find("#datatableMain_wrapper button.btn-default.btn-sm")
                        .remove();
                    $(document)
                        .find(
                            '#datatableMain_wrapper div.dropdown-menu[role="menu"]'
                        )
                        .remove();
                } else {
                    if (!json.data[0].canExport) {
                        $(document)
                            .find("#datatableMain_wrapper button.btn-default.btn-sm")
                            .remove();
                        $(document)
                            .find(
                                '#datatableMain_wrapper div.dropdown-menu[role="menu"]'
                            )
                            .remove();
                        return;
                    }                    
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

    // Expand/collapse handler for Level 1 (SPK Detail)
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
            showChildDatatable(row, tr);
        }
    });

    // Expand/collapse handler for Level 2 (Jadwal Produksi) from Main Table
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
            showLevel2MainDetail(row, tr);
        }
    });

    // Show child datatable function
    function showChildDatatable(row, tr) {
        console.log('showChildDatatable called with data:', row.data()); // Debug log
        let child = $(row.data().table_expand);
        console.log('Child element:', child); // Debug log
        console.log('Child table element found:', child.find("table").length); // Debug log
        
        var datatableExpand = child.find("table").DataTable({
            ...mergeWithDefaultOptions({
                $defaultOpt: {
                    buttons: [
                        "colvis",
                        "refresh",
                        "flexiblefixed"
                    ],
                },
                initComplete: function (settings, json) {
                    setTimeout(() => {
                        child.css({
                            width: tr.closest(".dataTables_wrapper").width() - 40,
                        });
                        let canAdd = json.canAdd;
                        if (canAdd && child.find(".dt-buttons > .buttons-add").length == 0) {
                            let btnAdd = $('<button class="btn btn-success btn-sm mr-2 buttons-add btn--detail"><i class="fa fa-plus mr-2"></i>Tambah</button>');
                            btnAdd.insertBefore(child.find(".dt-buttons > .btn-group"));
                        }
                        window.dispatchEvent(new Event("resize"));
                    }, 300);
                    return true;
                },
            }),
            ajax: {
                url: `${publicURL}/produksi/transaksi-spk/detail`,
                type: "POST",
                headers: { "X-CSRF-TOKEN": csfr_token },
                data: { NoBukti: row.data().NoBukti },
                dataSrc: function(json) {
                    console.log('Child DataTable AJAX response:', json); // Debug log
                    console.log('Child data length:', json.data ? json.data.length : 0); // Debug log
                    if (json.data && json.data.length > 0) {
                        console.log('First row sample:', json.data[0]); // Debug log
                        console.log('First row keys:', Object.keys(json.data[0])); // Debug log
                    }
                    return json.data || [];
                }
            },
            columns: [
                { data: "Urut", width: "10%" },
                { data: "KodeBrg", width: "20%" },
                { data: "NamaBrg", width: "25%" },
                { data: "Qnt", className: "text-right", width: "15%" },
                { data: "Satuan", width: "10%" },
                {
                    data: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center parentBtnRow",
                    width: "20%"
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
                }
            }
        });

        console.log('Child DataTable created:', datatableExpand); // Debug log

        row.child(child).show();
        tr.addClass("shown");
    }

    // Show level 2 detail from main table function
    function showLevel2MainDetail(row, tr) {
        console.log('showLevel2MainDetail called with data:', row.data()); // Debug log
        let level2MainChild = $(`
            <tr class="level2-main-row">
                <td colspan="13">
                    <div class="level2-main-expand">
                        <h5><i class="fa fa-calendar text-success"></i> Jadwal Produksi untuk ${row.data().NoBukti}</h5>
                        <table class="table table-bordered table-hover table-sm">
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
                                        <i class="fa fa-spinner fa-spin"></i> Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        `);

        var datatableLevel2Main = level2MainChild.find("table").DataTable({
            ajax: {
                url: `${publicURL}/produksi/transaksi-spk/detail-level2-all`,
                type: "GET",
                data: { 
                    NoBukti: row.data().NoBukti
                },
                dataSrc: function(json) {
                    console.log('Level 2 Main AJAX response:', json); // Debug log
                    console.log('Level 2 Main data length:', json.data ? json.data.length : 0); // Debug log
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
        tr.after(level2MainChild);
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
            }
        });
    });

    // Modal handlers
    var options = {};
    $(document).on("click", "#datatableMain_wrapper .buttons-add.btn-module", function (e) {
        e.preventDefault();
        options.data = {
            resource: "components.produksi.spk.modal-insert",
            modalId: "modalAddSPK",
            formId: "formAddSPK",
            modalWidth: "lg",
            url: publicURL + "/produksi/transaksi-spk/store",
            fnData: {
                class: "\\App\\Http\\Controllers\\SPKController",
                function: "getDataByNoBukti",
                params: [$(this).data("bukti") || null],
            },
            checkPermission: true,
            codeAccess: "05001",
            access: "ISTAMBAH",
        };
        options.callback = function (response, modal) {
            modalAddEditSPK(response, modal);
        };
        getModal(options);
    });

    // Edit SPK handler
    $(document).on("click", "#datatableMain_wrapper .btnEditBukti", function (e) {
        e.preventDefault();
        options.data = {
            resource: "components.produksi.spk.modal-insert",
            modalId: "modalAddSPK",
            formId: "formAddSPK",
            modalWidth: "lg",
            url: publicURL + "/produksi/transaksi-spk/store",
            fnData: {
                class: "\\App\\Http\\Controllers\\SPKController",
                function: "getDataByNoBukti",
                params: [$(this).data("bukti") || null],
            },
            checkPermission: true,
            codeAccess: "05001",
            access: "ISKOREKSI",
        };
        options.callback = function (response, modal) {
            modalAddEditSPK(response, modal);
        };
        getModal(options);
    });

    // Delete SPK handler
    $(document).on("click", ".btnGlobalDelete.spk", function (e) {
        e.preventDefault();
        let data = {
            NoBukti: $(this).data("id"),
        };
        globalDelete($(this).data("url"), datatableMain, $(this).data("key"), data);
    });

    // Modal handling function
    function modalAddEditSPK(response, modal) {
        console.log('modalAddEditSPK called');
        // Apply plugins to modal elements
        applyPlugins(modal);
        
        // TODO: Add specific SPK modal logic here
    }

    // Debug: Check if event listeners are working
    console.log('SPK.js loaded successfully');
    console.log('Global variables:', { publicURL, csfr_token });
    
    // Test checkbox detection
    setTimeout(() => {
        console.log('SPK: Checking for checkboxes...');
        const checkboxes = $('input[name="IsOtorisasi1"]');
        console.log('SPK: Found checkboxes:', checkboxes.length, checkboxes);
        
        if (checkboxes.length > 0) {
            console.log('SPK: First checkbox HTML:', checkboxes.first().get(0).outerHTML);
        }
    }, 2000);

})($, $globalVariable);