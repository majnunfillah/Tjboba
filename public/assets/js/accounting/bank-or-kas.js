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
            $defaultOpt: {
                buttons: [
                    {
                        $keyButton: "tambah",
                        className: "btn-kas-bank",
                    },
                    "colvis",
                    "refresh",
                    {
                        $keyButton: "excel-pdf",
                        className: "btn-kas-bank",
                    },
                    "flexiblefixed",
                    {
                        $keyButton: "excel",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 7, 8, 10, 11],
                        },
                    },
                    {
                        $keyButton: "pdf",
                        exportOptions: {
                            columns: [1, 2, 3, 4, 5, 7, 8, 10, 11],
                        },
                    },
                ],
            },
            initComplete: function (settings, json) {
                if (json.data.length == 0) {
                    $(document)
                        .find(
                            "#datatableMain_wrapper button.btn-default.btn-sm"
                        )
                        .remove();
                    $(document)
                        .find(
                            '#datatableMain_wrapper div.dropdown-menu[role="menu"]'
                        )
                        .remove();
                } else {
                    if (!json.data[0].canExport) {
                        $(document)
                            .find(
                                "#datatableMain_wrapper button.btn-default.btn-sm"
                            )
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
                        "#datatableMain_wrapper .btn-export-excel.btn-kas-bank",
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
                        "#datatableMain_wrapper .btn-export-pdf.btn-kas-bank",
                        function (e) {
                            e.preventDefault();
                            let btnGroup = $(this).closest(".btn-group");
                            let btnExportExcel = btnGroup.find(
                                ".buttons-pdf.buttons-html5"
                            );
                            btnExportExcel.trigger("click");
                        }
                    );

                    $(document).on(
                        "click",
                        ".download-excel,.download-pdf",
                        function (e) {
                            e.preventDefault();
                            let NoBukti =
                                "bukti=" +
                                encodeURIComponent($(this).data("bukti"));
                            let type = $(this).hasClass("download-excel")
                                ? "excel"
                                : "pdf";
                            let url = `${publicURL}/accounting/transaksi-bank-or-kas/download-kasbank?${NoBukti}&type=${type}`;
                            window.open(url, "_blank");
                        }
                    );
                }

                return true;
            },
        }),
        ajax: {
            url: $(this).data("server"),
        },
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
            { data: "IsOtorisasi2Html" },
            { data: "OtoUser2" },
            { data: "TglOto2" },
            {
                data: "action",
                orderable: false,
                searchable: false,
                className: "text-center parentBtnRow",
            },
        ],
        fnRowCallback: function (
            nRow,
            aData,
            iDisplayIndex,
            iDisplayIndexFull
        ) {
            if (aData.indikatorExpand === false) {
                $(nRow).find("td.dt-control").addClass("indicator-white");
            }

            if (aData.IsOtorisasi1 == 1) {
                $(nRow).addClass("yellowClass");
            }

            if (aData.IsOtorisasi2 == 1) {
                $(nRow).addClass("redClass");
            }
        },
    });

    const options = {};

    $(document).on(
        "click",
        "#datatableMain > tbody td.dt-control",
        function () {
            var tr = $(this).closest("tr");
            var row = datatableMain.row(tr);
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass("shown");
            } else {
                // Open this row
                showChildDatatable(row, tr);
            }
        }
    );

    $(document).on(
        "change",
        'input[name="IsOtorisasi2"],input[name="IsOtorisasi1"] ',
        function (e) {
            e.preventDefault();
            let tr = $(this).closest("tr");
            const data = datatableMain.row(tr).data();
            confirm(
                "Apakah anda yakin akan mengubah status otorisasi?",
                function confirmed() {
                    baseAjax({
                        url:
                            publicURL +
                            "/accounting/transaksi-bank-or-kas/set-otorisasi",
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
                            $(e.target).prop(
                                "checked",
                                !$(e.target).is(":checked")
                            );
                        },
                    });
                },
                function dismissed() {
                    $(e.target).prop("checked", !$(e.target).is(":checked"));
                }
            );
            return;
        }
    );

    $(document).on(
        "click",
        "#datatableMain_wrapper .buttons-add.btn-kas-bank",
        function (e) {
            e.preventDefault();
            options.data = {
                resource: "components.accounting.kasbank.modal-insert",
                modalId: "modalAddKasBank",
                formId: "formAddKasBank",
                modalWidth: "lg",
                url: publicURL + "/accounting/transaksi-bank-or-kas",
                fnData: {
                    class: "\\BankOrKasController",
                    function: "getKasBankByNoBukti",
                    params: [
                        $(this).data("bukti") == undefined
                            ? null
                            : $(this).data("bukti"),
                    ],
                },
                checkPermission: true,
                codeAccess: "02001",
                access: "ISTAMBAH",
            };
            options.callback = function (response, modal) {
                modalAddEditKasbank(response, modal);
            };
            getModal(options);
        }
    );

    $(document).on(
        "click",
        "#datatableMain_wrapper .btnEditBukti",
        function (e) {
            e.preventDefault();
            options.data = {
                resource: "components.accounting.kasbank.modal-insert",
                modalId: "modalAddKasBank",
                formId: "formAddKasBank",
                modalWidth: "lg",
                url: publicURL + "/accounting/transaksi-bank-or-kas",
                fnData: {
                    class: "\\BankOrKasController",
                    function: "getKasBankByNoBukti",
                    params: [
                        $(this).data("bukti") == undefined
                            ? null
                            : $(this).data("bukti"),
                    ],
                },
                checkPermission: true,
                codeAccess: "02001",
                access: "ISTAMBAH",
            };
            options.callback = function (response, modal) {
                modalAddEditKasbank(response, modal);
            };
            getModal(options);
        }
    );

    $(document).on("click", ".btnGlobalDelete.kasbank", function (e) {
        e.preventDefault();
        let data = {
            NoBukti: $(this).data("id"),
        };
        globalDelete(
            $(this).data("url"),
            datatableMain,
            $(this).data("key"),
            data
        );
    });

    function modalAddEditKasbank(response, modal) {
        let data = response.res;
        var tahunPeriode = $("#spanYear").text(),
            bulanPeriode = $("#spanMonth").text();
        if (Object.keys(response.res).length !== 0) {
            let Tanggal = moment(data.Tanggal).format("YYYY-MM-DD");
            if (data.canEdit) {
                modal
                    .find("form")
                    .attr(
                        "action",
                        publicURL + "/accounting/transaksi-bank-or-kas"
                    );
                modal.find("select").prop("disabled", true);
            } else {
                modal.find("input,select").prop("disabled", true);
                modal.find('button[type="submit"]').hide();
            }
            modal.find('select[name="TipeTransHd"]').val(data.TipeTransHd);
            modal
                .find('select[name="PerkiraanHd"]')
                .empty()
                .append(
                    `<option value="${data.PerkiraanHd}">${data.PerkiraanHd} - ${data.Keterangan}</option>`
                )
                .trigger("select2.change");
            modal.find('input[name="NoBukti"]').val(data.NoBukti);
            modal.find('input[name="Tanggal"]').val(Tanggal);
            modal.find('input[name="Note"]').val(data.Note);
            modal.find('input[name="Lawan"]').val(data.PerkiraanHd);
            modal.find('input[name="NoUrut"]').val(data.NOURUT);
            modal
                .find('input[name="Lawan_val"]')
                .val(`${data.PerkiraanHd} - ${data.Keterangan}`);
        }
        modal.on("change", 'select[name="TipeTransHd"]', function (e) {
            let kode = "KAS";
            if ($(this).val() == "BKK") {
                modal.find("label.label-lawan").text("Kas");
                modal.find("label.label-note").text("Kepada");
                kode = "KAS";
            } else if ($(this).val() == "BKM") {
                modal.find("label.label-lawan").text("Kas");
                modal.find("label.label-note").text("Terima Dari");
                kode = "KAS";
            } else if ($(this).val() == "BBK") {
                modal.find("label.label-lawan").text("Bank");
                modal.find("label.label-note").text("Kepada");
                kode = "BANK";
            } else if ($(this).val() == "BBM") {
                modal.find("label.label-lawan").text("Bank");
                modal.find("label.label-note").text("Terima Dari");
                kode = "BANK";
            }

            modal.find("select[name='PerkiraanHd']").removeAttr("readonly");
            applyPlugins(modal, [
                {
                    element: "select[name='PerkiraanHd']",
                    plugin: "select2-search",
                    ajax: "setSelectAjax",
                    path: "/get-kelompok-kas-bank-select?kode=" + kode,
                },
            ]);
        });

        modal.on("change", 'select[name="PerkiraanHd"]', function (e) {
            let tipe = modal.find('select[name="TipeTransHd"]').val();
            let optionData = $(this).select2("data")[0];
            baseAjax({
                url:
                    publicURL +
                    "/accounting/transaksi-bank-or-kas/get-nomor-bukti",
                type: "POST",
                param: { tipe: tipe },
                successCallback: function (res) {
                    modal.find('input[name="NoBukti"]').val(res.NoBukti);
                    modal.find('input[name="NoUrut"]').val(res.NoUrut);
                    tahunPeriode = res.Tahun;
                    bulanPeriode = res.Bulan;
                    let TanggalVal = modal.find('input[name="Tanggal"]').val();
                    if (TanggalVal != "") {
                        //check if date in range periode
                        let Tanggal = moment(TanggalVal);
                        let Tahun = Tanggal.format("YYYY");
                        let Bulan = Tanggal.format("MM");
                        if (Tahun != tahunPeriode || Bulan != bulanPeriode) {
                            modal.find('input[name="Tanggal"]').val("");
                            modal.find('input[name="Tanggal"]').focus();
                            alert(
                                "Tanggal tidak dalam periode aktif. Periode aktif adalah " +
                                    bulanPeriode +
                                    "/" +
                                    tahunPeriode
                            );
                        }
                    }

                    modal.find('input[name="Tanggal"]').focus();
                    modal.find('input[name="Lawan"]').val(`${optionData.id}`);
                    modal
                        .find('input[name="Lawan_val"]')
                        .val(`${optionData.text}`);
                },
            });
        });

        modal.on("change", 'input[name="Tanggal"]', function (e) {
            let TanggalVal = $(this).val();
            if (TanggalVal != "") {
                //check if date in range periode
                let Tanggal = moment(TanggalVal);
                let Tahun = Tanggal.format("YYYY");
                let Bulan = Tanggal.format("MM");
                if (Tahun != tahunPeriode || Bulan != bulanPeriode) {
                    $(this).val("");
                    $(this).focus();
                    alert(
                        "Tanggal tidak dalam periode aktif. Periode aktif adalah " +
                            bulanPeriode +
                            "/" +
                            tahunPeriode
                    );
                }
            }
        });

        modal.on("submit", "form", function (e) {
            e.preventDefault();
            let ctx = this;
            function submitKasbank(nextNoBukti = false) {
                formAjax({
                    form: $(ctx),
                    callbackSerialize: function ($form, option) {
                        if (Object.keys(response.res).length !== 0) {
                            if (data.canEdit) {
                                $($form).find(
                                    'input[type="hidden"][name="NoBukti"]'
                                ).length == 0
                                    ? $($form).append(
                                          `<input type="hidden" name="NoBukti" value="${data.NoBukti}">`
                                      )
                                    : $($form)
                                          .find(
                                              'input[type="hidden"][name="NoBukti"]'
                                          )
                                          .val(data.NoBukti);
                            }
                        }
                        if (nextNoBukti) {
                            $($form).find(
                                'input[type="hidden"][name="nextNoBukti"]'
                            ).length == 0
                                ? $($form).append(
                                      `<input type="hidden" name="nextNoBukti" value="true">`
                                  )
                                : $($form)
                                      .find(
                                          'input[type="hidden"][name="nextNoBukti"]'
                                      )
                                      .val(true);
                        }
                        return true;
                    },
                    callbackSuccess: function (data, status, jqxhr, form) {
                        modal.modal("hide");
                        datatableMain.ajax.reload();
                        baseSwal(
                            "success",
                            "Berhasil",
                            "Data berhasil disimpan"
                        );
                    },
                    callbackError: function (xhr) {
                        if (xhr.status == 501) {
                            swalConfirm({
                                title: "Peringatan",
                                text: "NoBukti telah terpakai, gunakan NoBukti selanjutnya?",
                                callback: function () {
                                    submitKasbank(true);
                                },
                            });
                            return true;
                        } else {
                            return false;
                        }
                    },
                });
            }
            submitKasbank();
        });

        modal.find('select[name="TipeTransHd"]').trigger("change");
    }

    function showChildDatatable(row, tr) {
        let child = $(row.data().table_expand);
        var datatableExpand = child.find("table").DataTable({
            ...mergeWithDefaultOptions({
                $defaultOpt: {
                    buttons: [
                        "colvis",
                        "refresh",
                        // "excel-pdf",
                        "flexiblefixed",
                        // {
                        //     $keyButton: "pdf",
                        //     exportOptions: {},
                        //     action: function (e, dt, node, config) {
                        //         console.log("pdf");
                        //     },
                        // },
                        // {
                        //     $keyButton: "excel",
                        //     exportOptions: {
                        //         columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        //     },
                        // },
                    ],
                },
                initComplete: function (settings, json) {
                    setTimeout(() => {
                        child.css({
                            width:
                                tr.closest(".dataTables_wrapper").width() - 40,
                        });
                        let canAdd = json.canAdd;
                        if (
                            canAdd &&
                            child.find(".dt-buttons > .buttons-add").length == 0
                        ) {
                            let btnAdd = $(
                                '<button class="btn btn-success btn-sm mr-2 buttons-add btn--detail" ><i class="fa fa-plus mr-2"></i>Tambah</button>'
                            );
                            btnAdd.insertBefore(
                                child.find(".dt-buttons > .btn-group")
                            );
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
            columns: [
                { data: "Perkiraan" },
                { data: "Lawan" },
                { data: "Keterangan" },
                { data: "TPHC" },
                { data: "Valas" },
                { data: "Kurs", className: "text-right" },
                { data: "Debet", className: "text-right" },
                { data: "DebetRp", className: "text-right" },
                { data: "KreditRp", className: "text-right" },
                {
                    data: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center parentBtnRow",
                },
            ],
            footerCallback: function (tfoot, data, start, end, display) {
                var api = this.api();
                let foot6 = 0.0;
                for (let i = 0; i < api.column(6).data().length; i++) {
                    let text = api.column(6).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot6 += parseFloat(text);
                }

                let foot7 = 0.0;
                for (let i = 0; i < api.column(7).data().length; i++) {
                    let text = api.column(7).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot7 += parseFloat(text);
                }

                // format ro currency
                foot6 = foot6.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });

                foot7 = foot7.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });

                $(tfoot).find("th").eq(1).html(foot6);
                $(tfoot).find("th").eq(2).html(foot7);
            },
        });

        row.child(child).show();
        tr.addClass("shown");

        child.on("click", ".buttons-add.btn--detail", function (e) {
            $injectScript({
                url: "accounting/kasbank-detail.js",
                fn: "modalAddEditDetailKasbank",
                args: [row, child, datatableExpand, $(this)],
            });
        });

        child.on("click", ".btnEditKasBank.btn--detail", function () {
            $injectScript({
                url: "accounting/kasbank-detail.js",
                fn: "modalAddEditDetailKasbank",
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
