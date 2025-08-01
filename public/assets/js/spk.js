import $globalVariable, { publicURL, csfr_token } from "../base-function.js";

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
    console.log('SPK.js: Initializing DataTable...'); // Debug log
    
    var datatableMain = $("#datatableMain").DataTable({
        ...mergeWithDefaultOptions({
            ajax: {
                url: $("#datatableMain").data("server"),
                type: 'POST',
                data: function(d) {
                    d.bulan = $('#bulan').val();
                    d.tahun = $('#tahun').val();
                    d.tampil_valid = $('#tampil_valid').val();
                    d._token = '{{ csrf_token() }}';
                }
            },
            $defaultOpt: {
                buttons: [
                    { $keyButton: "tambah", className: "btn-spk" },
                    "colvis", "refresh",
                    { $keyButton: "excel-pdf", className: "btn-spk" },
                    "flexiblefixed",
                    { $keyButton: "excel", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23] } },
                    { $keyButton: "pdf", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23] } }
                ]
            },
            initComplete: function (settings, json) {
                console.log('SPK DataTable initialized with data:', json.data); // Debug log
                console.log('First row data:', json.data[0]); // Debug log
                
                if (json.data.length == 0) {
                    $(document).find("#datatableMain_wrapper button.btn-default.btn-sm").remove();
                    $(document).find('#datatableMain_wrapper div.dropdown-menu[role="menu"]').remove();
                } else {
                    if (!json.data[0].canExport) {
                        $(document).find("#datatableMain_wrapper button.btn-default.btn-sm").remove();
                        $(document).find('#datatableMain_wrapper div.dropdown-menu[role="menu"]').remove();
                        return;
                    }
                    // Export button handlers
                    $(document).on("click", "#datatableMain_wrapper .btn-export-excel.btn-spk", function (e) {
                        e.preventDefault();
                        let btnGroup = $(this).closest(".btn-group");
                        let btnExportExcel = btnGroup.find(".buttons-excel.buttons-html5");
                        btnExportExcel.trigger("click");
                    });
                    $(document).on("click", "#datatableMain_wrapper .btn-export-pdf.btn-spk", function (e) {
                        e.preventDefault();
                        let btnGroup = $(this).closest(".btn-group");
                        let btnExportPdf = btnGroup.find(".buttons-pdf.buttons-html5");
                        btnExportPdf.trigger("click");
                    });
                }
                return true;
            },
        }),
        columns: [
            { 
                data: null, 
                orderable: false, 
                searchable: false, 
                defaultContent: "", 
                className: "dt-control",
                width: "30px"
            },
            { data: "NoBukti" },
            { 
                data: "Tanggal", 
                render: function(data) {
                    return moment(data).format('DD/MM/YYYY');
                }
            },
            { data: "NoSO" },
            { data: "KodeBrg" },
            { data: "NamaBrg" },
            { 
                data: "Qnt", 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { data: "SATBJ" },
            { data: "IsOtorisasi1Html" },
            { data: "OtoUser1" },
            { 
                data: "TglOto1", 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY HH:mm') : '';
                }
            },
            { data: "IsOtorisasi2Html" },
            { data: "OtoUser2" },
            { 
                data: "TglOto2", 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY HH:mm') : '';
                }
            },
            { data: "IsOtorisasi3Html" },
            { data: "OtoUser3" },
            { 
                data: "TglOto3", 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY HH:mm') : '';
                }
            },
            { data: "IsOtorisasi4Html" },
            { data: "OtoUser4" },
            { 
                data: "TglOto4", 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY HH:mm') : '';
                }
            },
            { data: "IsOtorisasi5Html" },
            { data: "OtoUser5" },
            { 
                data: "TglOto5", 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY HH:mm') : '';
                }
            },
            { 
                data: "NeedOtorisasi", 
                render: function(data) {
                    return data ? '<span class="badge badge-warning">Ya</span>' : '<span class="badge badge-success">Tidak</span>';
                }
            },
            { 
                data: null, 
                orderable: false,
                searchable: false,
                className: "text-center parentBtnRow",
                render: function(data, type, row) {
                    let actions = '';
                    actions += '<button type="button" class="btn btn-sm btn-info btn-action" onclick="viewItem(\'' + row.NoBukti + '\')" title="Detail"><i class="fas fa-eye"></i></button>';
                    actions += '<button type="button" class="btn btn-sm btn-primary btn-action" onclick="editItem(\'' + row.NoBukti + '\')" title="Edit"><i class="fas fa-edit"></i></button>';
                    
                    if (row.NeedOtorisasi) {
                        actions += '<button type="button" class="btn btn-sm btn-warning btn-action" onclick="authorizeItem(\'' + row.NoBukti + '\')" title="Otorisasi"><i class="fas fa-check"></i></button>';
                    }
                    
                    actions += '<button type="button" class="btn btn-sm btn-danger btn-action" onclick="deleteItem(\'' + row.NoBukti + '\')" title="Hapus"><i class="fas fa-trash"></i></button>';
                    
                    return actions;
                }
            }
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
        }
    });

    // Handle expand/collapse detail rows
    $(document).on("click", "#datatableMain > tbody td.dt-control", function () {
        console.log('Expand button clicked!'); // Debug log
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        console.log('Row data:', row.data()); // Debug log
        
        if (row.child.isShown()) {
            console.log('Hiding child row'); // Debug log
            row.child.hide();
            tr.removeClass("shown");
        } else {
            console.log('Showing child row'); // Debug log
            showChildDatatable(row, tr);
        }
    });

    // Outstanding SO Table
    let outstandingTable = $('#outstanding-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("spk.getOutstandingSO") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            }
        },
        columns: [
            { data: 'NOBUKTI' },
            { data: 'URUT' },
            { data: 'KODEBRG' },
            { data: 'NAMABRG' },
            { data: 'Satuan' },
            { 
                data: 'QntSO', 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { 
                data: 'QntSPK', 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { 
                data: 'Saldo', 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { 
                data: 'tglmulai', 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY') : '';
                }
            },
            { 
                data: 'tglkirim', 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY') : '';
                }
            },
            { 
                data: 'tglselesai', 
                render: function(data) {
                    return data ? moment(data).format('DD/MM/YYYY') : '';
                }
            }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            url: '{{ asset("assets/plugins/datatables/Indonesian.json") }}'
        }
    });

    // Stock Table
    let stockTable = $('#stock-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("spk.getStockData") }}',
            type: 'POST',
            data: function(d) {
                d.bulan = $('#bulan').val();
                d.tahun = $('#tahun').val();
                d._token = '{{ csrf_token() }}';
            }
        },
        columns: [
            { data: 'RowNum' },
            { data: 'KODEBRG' },
            { data: 'NAMABRG' },
            { data: 'SAT1' },
            { 
                data: 'StockR', 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { 
                data: 'OutPO', 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { 
                data: 'OutSPK', 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            },
            { 
                data: 'StockAV', 
                className: "text-right",
                render: function(data) {
                    return parseFloat(data).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            url: '{{ asset("assets/plugins/datatables/Indonesian.json") }}'
        }
    });

    // Add button handler
    $(document).on("click", "#datatableMain_wrapper .buttons-add.btn-spk", function (e) {
        e.preventDefault();
        options.data = {
            resource: "components.produksi.spk.modal-insert",
            modalId: "modalAddSPK",
            formId: "formAddSPK",
            modalWidth: "lg",
            url: publicURL + "/spk/store",
            fnData: {
                class: "\\App\\Http\\Controllers\\SPKController",
                function: "getDataByNoBukti",
                params: [null]
            },
            checkPermission: true,
            codeAccess: "08103",
            access: "ISTAMBAH"
        };
        options.callback = function (response, modal) {
            modalAddEditSPK(response, modal);
        };
        getModal(options);
    });

    // Outstanding SO button
    $(document).on("click", "#datatableMain_wrapper .btn-outstanding", function (e) {
        e.preventDefault();
        $('#outstanding-modal').modal('show');
        outstandingTable.ajax.reload();
    });

    // Stock button
    $(document).on("click", "#datatableMain_wrapper .btn-stock", function (e) {
        e.preventDefault();
        $('#stock-modal').modal('show');
        stockTable.ajax.reload();
    });

    // Show child datatable
    function showChildDatatable(row, tr) {
        row.child(formatChildTable(row.data())).show();
        tr.addClass("shown");
        
        let childTable = row.child().find('#datatableExpand').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: `${publicURL}/produksi/transaksi-spk/detail`,
                type: 'POST',
                headers: { "X-CSRF-TOKEN": csfr_token },
                data: { NoBukti: row.data().NoBukti }
            },
            columns: [
                { data: "Urut" },
                { data: "KodeBrg" },
                { data: "NamaBrg" },
                { data: "Qnt", className: "text-right" },
                { data: "Satuan" },
                { data: "Keterangan" }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            responsive: true,
            language: {
                url: '{{ asset("assets/plugins/datatables/Indonesian.json") }}'
            }
        });
    }

    // Format child table HTML
    function formatChildTable(data) {
        return `
            <div class="row">
                <div class="col-md-12">
                    <table id="datatableExpand" class="table table-bordered table-striped table-hover nowrap w-100"
                        data-server="${data.detailUrl}">
                        <thead>
                            <tr>
                                <th>Urut</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        `;
    }

})(jQuery, $globalVariable);

// Global functions
function viewItem(noBukti) {
    options.data = {
        resource: "components.produksi.spk.modal-insert",
        modalId: "modalViewSPK",
        formId: "formViewSPK",
        modalWidth: "lg",
        url: publicURL + "/spk/view",
        fnData: {
            class: "\\App\\Http\\Controllers\\SPKController",
            function: "getDataByNoBukti",
            params: [noBukti]
        },
        checkPermission: true,
        codeAccess: "08103",
        access: "ILIHAT"
    };
    options.callback = function (response, modal) {
        modalViewSPK(response, modal);
    };
    getModal(options);
}

function editItem(noBukti) {
    options.data = {
        resource: "components.produksi.spk.modal-insert",
        modalId: "modalEditSPK",
        formId: "formEditSPK",
        modalWidth: "lg",
        url: publicURL + "/spk/update",
        fnData: {
            class: "\\App\\Http\\Controllers\\SPKController",
            function: "getDataByNoBukti",
            params: [noBukti]
        },
        checkPermission: true,
        codeAccess: "08103",
        access: "IUBAH"
    };
    options.callback = function (response, modal) {
        modalAddEditSPK(response, modal);
    };
    getModal(options);
}

function authorizeItem(noBukti) {
    confirm("Apakah anda yakin akan mengubah status otorisasi?", 
        function confirmed() {
            baseAjax({
                url: publicURL + "/spk/set-otorisasi",
                type: "POST",
                param: {
                    NoBukti: noBukti,
                    otoLevel: "IsOtorisasi1",
                    status: 1
                },
                successCallback: function (res) {
                    datatableMain.ajax.reload();
                },
                errorCallback: function (xhr) {
                    // Handle error
                }
            });
        },
        function dismissed() {
            // Handle dismissal
        }
    );
}

function deleteItem(noBukti) {
    globalDelete({
        url: publicURL + "/spk/delete",
        param: { NoBukti: noBukti },
        callback: function () {
            datatableMain.ajax.reload();
        }
    });
}

// Modal handling functions
function modalAddEditSPK(response, modal) {
    if (response.success) {
        modal.find('#formAddSPK, #formEditSPK').attr('action', response.data.url);
        
        // Populate form fields if editing
        if (response.data.formData) {
            Object.keys(response.data.formData).forEach(function(key) {
                let field = modal.find(`[name="${key}"]`);
                if (field.length > 0) {
                    if (field.hasClass('select2')) {
                        field.val(response.data.formData[key]).trigger('change');
                    } else {
                        field.val(response.data.formData[key]);
                    }
                }
            });
        }
        
        // Initialize plugins
        applyPlugins(modal);
    }
}

function modalViewSPK(response, modal) {
    if (response.success) {
        // Populate form fields for view only
        if (response.data.formData) {
            Object.keys(response.data.formData).forEach(function(key) {
                let field = modal.find(`[name="${key}"]`);
                if (field.length > 0) {
                    field.val(response.data.formData[key]).prop('readonly', true);
                }
            });
        }
        
        // Disable form submission
        modal.find('form').off('submit');
    }
} 