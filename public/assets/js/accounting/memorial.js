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
                        className: "btn-memorial",
                    },
                    "colvis",
                    "refresh",
                    {
                        $keyButton: "excel-pdf",
                        className: "btn-memorial",
                    },
                    "flexiblefixed",
                    {
                        $keyButton: "excel",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8],
                        },
                    },
                    {
                        $keyButton: "pdf",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 6, 7, 8],
                        },
                    },
                ],
            },
            initComplete: function (settings, json) {
                if (json.data.length == 0) {
                    $(document).find("#datatableMain_wrapper button.btn-default.btn-sm").remove();
                    $(document).find('#datatableMain_wrapper div.dropdown-menu[role="menu"]').remove();
                } else {
                    if (!json.data[0].canExport) {
                        $(document).find("#datatableMain_wrapper button.btn-default.btn-sm").remove();
                        $(document).find('#datatableMain_wrapper div.dropdown-menu[role="menu"]').remove();
                        return;
                    }

                    $(document).on("click", "#datatableMain_wrapper .btn-export-excel.btn-memorial", function (e) {
                        e.preventDefault();
                        let btnGroup = $(this).closest(".btn-group");
                        let btnExportExcel = btnGroup.find(".buttons-excel.buttons-html5");
                        btnExportExcel.trigger("click");
                    });

                    $(document).on("click", "#datatableMain_wrapper .btn-export-pdf.btn-memorial", function (e) {
                        e.preventDefault();
                        let btnGroup = $(this).closest(".btn-group");
                        let btnExportPdf = btnGroup.find(".buttons-pdf.buttons-html5");
                        btnExportPdf.trigger("click");
                    });

                    $(document).on("click", ".download-excel,.download-pdf", function (e) {
                        e.preventDefault();
                        let NoBukti = "bukti=" + encodeURIComponent($(this).data("bukti"));
                        let type = $(this).hasClass("download-excel") ? "excel" : "pdf";
                        let url = `${publicURL}/accounting/transaksi-memorial/download-memorial?${NoBukti}&type=${type}`;
                        window.open(url, "_blank");
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
            },
            { data: "NoBukti" },
            { data: "Tanggal" },
            { data: "Note" },
            { data: "TotalD", className: "text-right" },
            { data: "TotalRp", className: "text-right" },
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
            if (aData.indikatorExpand === false) {
                $(nRow).find("td.dt-control").addClass("indicator-white");
            }

            if (aData.IsOtorisasi1 == 1) {
                $(nRow).addClass("yellowClass");
            }
        },
    });

    const options = {};

    // Handle expand/collapse detail rows
    $(document).on("click", "#datatableMain > tbody td.dt-control", function () {
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass("shown");
        } else {
            showChildDatatable(row, tr);
        }
    });

    // Handle otorisasi changes
    $(document).on("change", 'input[name="IsOtorisasi1"]', function (e) {
        e.preventDefault();
        let tr = $(this).closest("tr");
        const data = datatableMain.row(tr).data();
        confirm(
            "Apakah anda yakin akan mengubah status otorisasi?",
            function confirmed() {
                baseAjax({
                    url: publicURL + "/accounting/transaksi-memorial/set-otorisasi",
                    type: "POST",
                    param: {
                        NoBukti: data.NoBukti,
                        otoLevel: $(e.target).attr("name"),
                        status: $(e.target).is(":checked") ? 1 : 0,
                    },
                    successCallback: function (res) {
                        datatableMain.ajax.reload();
                    },
                    errorCallback: function (xhr) {
                        $(e.target).prop("checked", !$(e.target).is(":checked"));
                    },
                });
            },
            function dismissed() {
                $(e.target).prop("checked", !$(e.target).is(":checked"));
            }
        );
    });

    // Add new memorial
    $(document).on("click", "#datatableMain_wrapper .buttons-add.btn-memorial", function (e) {
        e.preventDefault();
        options.data = {
            resource: "components.accounting.memorial.modal-insert",
            modalId: "modalAddMemorial",
            formId: "formAddMemorial",
            modalWidth: "lg",
            url: publicURL + "/accounting/transaksi-memorial",
            fnData: {
                class: "\\MemorialController",
                function: "getMemorialByNoBukti",
                params: [$(this).data("bukti") == undefined ? null : $(this).data("bukti")],
            },
            checkPermission: true,
            codeAccess: "02002",
            access: "ISTAMBAH",
        };
        options.callback = function (response, modal) {
            modalAddEditMemorial(response, modal);
        };
        getModal(options);
    });

    // Edit memorial
    $(document).on("click", "#datatableMain_wrapper .btnEditBukti", function (e) {
        e.preventDefault();
        options.data = {
            resource: "components.accounting.memorial.modal-insert",
            modalId: "modalAddMemorial",
            formId: "formAddMemorial",
            modalWidth: "lg",
            url: publicURL + "/accounting/transaksi-memorial",
            fnData: {
                class: "\\MemorialController",
                function: "getMemorialByNoBukti",
                params: [$(this).data("bukti") == undefined ? null : $(this).data("bukti")],
            },
            checkPermission: true,
            codeAccess: "02002",
            access: "ISTAMBAH",
        };
        options.callback = function (response, modal) {
            modalAddEditMemorial(response, modal);
        };
        getModal(options);
    });

    // Delete memorial
    $(document).on("click", ".btnGlobalDelete.memorial", function (e) {
        e.preventDefault();
        let data = {
            NoBukti: $(this).data("id"),
        };
        globalDelete($(this).data("url"), datatableMain, $(this).data("key"), data);
    });

    function modalAddEditMemorial(response, modal) {
        let data = response.res;
        var tahunPeriode = $("#spanYear").text(),
            bulanPeriode = $("#spanMonth").text();

        if (Object.keys(response.res).length !== 0) {
            let Tanggal = moment(data.Tanggal).format("YYYY-MM-DD");
            if (data.canEdit) {
                modal.find("form").attr("action", publicURL + "/accounting/transaksi-memorial");
            } else {
                modal.find("input,select").prop("disabled", true);
                modal.find('button[type="submit"]').hide();
            }
            
            modal.find('input[name="NoBukti"]').val(data.NoBukti);
            modal.find('input[name="Tanggal"]').val(Tanggal);
            modal.find('input[name="Note"]').val(data.Note);
        }

        // Add TipeTransHd change handler
        modal.on("change", 'select[name="TipeTransHd"]', function (e) {
            let kode = "MEMORIAL";
            // modal.find("select[name='TipeTransHd']").removeAttr("readonly");
            // applyPlugins(modal, [
            //     {
            //         element: "select[name='PerkiraanHd']",
            //         plugin: "select2-search",
            //         ajax: "setSelectAjax",
            //         path: "/get-kelompok-kas-bank-select?kode=" + kode,
            //     },
            // ]);


            // Get new nomor bukti when type changes
            baseAjax({
                url: publicURL + "/accounting/transaksi-memorial/get-nomor-bukti",
                type: "POST",
                param: { tipe: $(this).val() },
                successCallback: function (res) {
                    console.log('Response:', res); // Debug log
                    if (!res || typeof res !== 'object') {
                        console.error('Invalid response format');
                        return;
                    }
                    modal.find('input[name="NoBukti"]').val(res.NoBukti || '');
                    modal.find('input[name="NoUrut"]').val(res.NoUrut || '');
                    
                    // Update period info
                    tahunPeriode = res.Tahun || '';
                    bulanPeriode = res.Bulan || '';

                    // Validate date against active period
                    let TanggalVal = modal.find('input[name="Tanggal"]').val();
                    if (TanggalVal != "") {
                        let Tanggal = moment(TanggalVal);
                        let Tahun = Tanggal.format("YYYY");
                        let Bulan = Tanggal.format("MM");
                        if (Tahun != tahunPeriode || Bulan != bulanPeriode) {
                            modal.find('input[name="Tanggal"]').val("");
                            modal.find('input[name="Tanggal"]').focus();
                            alert("Tanggal tidak dalam periode aktif. Periode aktif adalah " + bulanPeriode + "/" + tahunPeriode);
                        }
                    }
                }
            });
        });

        // Initialize TipeTransHd change event
        modal.find('select[name="TipeTransHd"]').trigger("change");

        modal.on("change", 'input[name="Tanggal"]', function (e) {
            let TanggalVal = $(this).val();
            if (TanggalVal != "") {
                let Tanggal = moment(TanggalVal);
                let Tahun = Tanggal.format("YYYY");
                let Bulan = Tanggal.format("MM");
                if (Tahun != tahunPeriode || Bulan != bulanPeriode) {
                    $(this).val("");
                    $(this).focus();
                    alert("Tanggal tidak dalam periode aktif. Periode aktif adalah " + bulanPeriode + "/" + tahunPeriode);
                }
            }
        });

        modal.on("submit", "form", function (e) {
            e.preventDefault();
            let ctx = this;
            
            function submitMemorial(nextNoBukti = false) {
                debugger; // Add breakpoint here
                formAjax({
                    form: $(ctx),
                    callbackSerialize: function ($form, option) {
                        if (Object.keys(response.res).length !== 0) {
                            if (data.canEdit) {
                                $($form).find('input[type="hidden"][name="NoBukti"]').length == 0
                                    ? $($form).append(`<input type="hidden" name="NoBukti" value="${data.NoBukti}">`)
                                    : $($form).find('input[type="hidden"][name="NoBukti"]').val(data.NoBukti);
                            }
                        }
                        if (nextNoBukti) {
                            $($form).find('input[type="hidden"][name="nextNoBukti"]').length == 0
                                ? $($form).append(`<input type="hidden" name="nextNoBukti" value="true">`)
                                : $($form).find('input[type="hidden"][name="nextNoBukti"]').val(true);
                        }
                        return true;
                    },
                    callbackSuccess: function (data, status, jqxhr, form) {
                        debugger; // Add breakpoint here
                        modal.modal("hide");
                        datatableMain.ajax.reload();
                        baseSwal("success", "Berhasil", "Data berhasil disimpan");
                    },
                    callbackError: function (xhr) {
                        debugger; // Add breakpoint here
                        if (xhr.status == 501) {
                            swalConfirm({
                                title: "Peringatan",
                                text: "NoBukti telah terpakai, gunakan NoBukti selanjutnya?",
                                callback: function () {
                                    submitMemorial(true);
                                },
                            });
                            return true;
                        } else {
                            return false;
                        }
                    },
                });
            }
            submitMemorial();
        });
    }

    function showChildDatatable(row, tr) {
        let child = $(row.data().table_expand);
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
                url: child.find("table").data("server"),
                type: "POST",
                headers: { "X-CSRF-TOKEN": csfr_token },
                data: { NoBukti: row.data().NoBukti },
            },
            // Keep original columns
            columns: [
                { data: "Perkiraan", name: "Perkiraan" },
                { data: "Lawan", name: "Lawan" },
                { data: "Keterangan", name: "Keterangan" },
                { 
                    data: "JumlahRp", 
                    name: "JumlahRp",
                    className: "text-right"
                },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center parentBtnRow"
                }
            ],
            footerCallback: function (tfoot, data, start, end, display) {
                var api = this.api();
                let total = 0.0;
                
                // Sum JumlahRp column
                for (let i = 0; i < api.column(3).data().length; i++) {
                    let text = api.column(3).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    total += parseFloat(text);
                }

                // Format to currency
                total = total.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });

                $(tfoot).find("th").eq(1).html(total);
            },
        });

        row.child(child).show();
        tr.addClass("shown");

        // Event handlers
        child.on("click", ".buttons-add.btn--detail", function (e) {
            $injectScript({
                url: "accounting/memorial-detail.js",
                fn: "modalAddEditDetailMemorial", 
                args: [row, child, datatableExpand, $(this)],
            });
        });

        child.on("click", ".btnEditMemorial.btn--detail", function () {
            $injectScript({
                url: "accounting/memorial-detail.js",
                fn: "modalAddEditDetailMemorial",
                args: [row, child, datatableExpand, $(this)],
            });
        });

        child.on("click", ".btnGlobalDelete.btn--detail", function (e) {
            let data = {
                NoBukti: $(this).data("id"),
                Urut: $(this).data("urut"),
            };
            globalDelete(
                $(this).data("url"),
                datatableExpand,
                $(this).data("key"),
                data
            );
        });
    }


})(jQuery, $globalVariable);
