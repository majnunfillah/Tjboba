import $globalVariable, { publicURL } from "../../base-function.js";

(function (
    $,
    { baseSwal, formAjax, getModal, globalDelete, mergeWithDefaultOptions }
) {
    // "use strict";

    const options = {};

    var datatableMain = $("#datatableMain").DataTable({
        ...mergeWithDefaultOptions({
            $defaultOpt: {
                buttons: [
                    "colvis",
                    "refresh",
                    "excel-pdf",
                    "flexiblefixed",
                    {
                        $keyButton: "excel",
                        exportOptions: {
                            columns: [
                                1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
                                15,
                            ],
                        },
                    },
                    {
                        $keyButton: "pdf",
                        exportOptions: {
                            columns: [
                                1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14,
                                15,
                            ],
                        },
                    },
                ],
            },
            initComplete: function (settings, json) {
                $(
                    '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddAktiva" ><i class="fa fa-plus mr-2"></i>Tambah</button>'
                ).insertBefore(".buttons-colvis");
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
            { data: "KodeAktiva" },
            { data: "Keterangan" },
            { data: "Tanggal" },
            { data: "NamaDevisi" },
            { data: "MyTipe" },
            { data: "NamaPerkiraan" },
            { data: "kodeBag" },
            { data: "Quantity" },
            { data: "Susut" },
            { data: "Metode" },
            { data: "akumulasi" },
            { data: "Biaya" },
            { data: "PersenBiaya1" },
            { data: "Biaya2" },
            { data: "PersenBiaya2" },
            {
                data: "action",
                searchable: false,
                orderable: false,
                className: "text-center parentBtnRow",
            },
        ],
    });

    options.data = {
        resource:
            "components.master_data.master_accounting.aktiva.modal-create",
        modalId: "modalAddAktiva",
        formId: "formAddAktiva",
        modalWidth: "md",
        plugins: [
            {
                element: ".mask-money",
                plugin: "maskMoney",
            },
            {
                element: '.select2[name="TipeAktiva"]',
                plugin: "select2",
            },
            {
                element: '.select2[name="Tipe"]',
                plugin: "select2",
            },
            {
                element: '.select2[name="NoMuka"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-group-aktiva-select",
            },
            {
                element: '.select2[name="Devisi"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-devisi-select",
            },
            {
                element: '.select2[name="Akumulasi"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path:
                    "/get-akumulasi-penyusutan-select?perkiraan=" +
                    $(document).find('.select2[name="NoMuka"]').val(),
            },
            {
                element: '.select2[name="Biaya"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-biaya-select",
            },
        ],
    };

    $(document).on("click", "#datatableMain tbody td.dt-control", function () {
        var tr = $(this).closest("tr");
        var row = datatableMain.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass("shown");
        } else {
            // Open this row
            let child = $(row.data().table_expand);
            child.css({
                width: tr.closest(".dataTables_wrapper").width() - 20,
            });
            child.find("table").DataTable({
                responsive: false,
                scrollX: true,
                authoWidth: false,
                order: [[1, "asc"]],
                // width: tr.closest('.dataTables_scroll').width() - 20,
                // height: '500px',
            });

            row.child(child).show();
            tr.addClass("shown");
        }
    });

    $(document).on("change", '.select2[name="NoMuka"]', function () {
        let data = $(this).select2("data")[0];
        let Persen = data.Persen;
        let Biaya1 = data.Biaya1;
        let PersenBiaya1 = data.PersenBiaya1;
        let KeteranganBiaya = data.KeteranganBiaya;
        let dtAkumulasi = data.Akumulasi;
        let Keterangan = data.Keterangan;

        let akumulasi = $(document).find('.select2[name="Akumulasi"]');
        let biaya1 = $(document).find('.select2[name="Biaya"]');
        let persenbiaya = $(document).find('input[name="PersenBiaya1"]');

        akumulasi
            .empty()
            .append(
                `<option value="${dtAkumulasi}">${dtAkumulasi} - ${Keterangan}</option>`
            )
            .trigger("change");
        biaya1
            .empty()
            .append(
                `<option value="${Biaya1}">${Biaya1} - ${KeteranganBiaya}</option>`
            )
            .trigger("change");

        $(document).find('input[name="Persen"]').val(Persen);
        // $(document).find('input[name="Biaya"]').val(Biaya1);
        persenbiaya.val(PersenBiaya1).trigger("change");

        let nomuka = $(this).val();
        let noBelakang = $(document).find('input[name="NoBelakang"]');
        if (noBelakang.val()) {
            $(document)
                .find('input[name="Perkiraan"]')
                .val(`${nomuka}.${noBelakang.val()}`);
        } else {
            $(document).find('input[name="Perkiraan"]').val("");
        }
    });

    $(document).on(
        {
            focusout: function () {
                if (this.value.length < 5) {
                    baseSwal("warning", "Warning", "No Urut harus 5 digit");
                    this.focus();
                } else {
                    let nomuka = $(document)
                        .find('.select2[name="NoMuka"]')
                        .val();
                    if (nomuka) {
                        $(document)
                            .find('input[name="Perkiraan"]')
                            .val(`${nomuka}.${this.value}`);
                    } else {
                        $(document).find('input[name="Perkiraan"]').val("");
                        baseSwal("warning", "Warning", "No Muka belum dipilih");
                        this.focus();
                    }
                }
            },
        },
        'input[name="NoBelakang"]'
    );

    $(document).on("click", ".btnAddAktiva", function (e) {
        e.preventDefault();
        options.data = {
            ...options.data,
            url: publicURL + "/master-data/master-accounting/aktiva",
            fnData: {
                class: "\\AktivaController",
                function: "getAktiva",
                params: [
                    $(this).data("perkiraan") == undefined
                        ? null
                        : $(this).data("perkiraan"),
                    $(this).data("devisi") == undefined
                        ? null
                        : $(this).data("devisi"),
                ],
            },
            checkPermission: true,
            codeAccess: "01001002",
            access: "ISTAMBAH",
        };
        getModal(options);
    });

    $(document).on("click", ".btnEditAktiva", function (e) {
        e.preventDefault();
        options.data = {
            ...options.data,
            url: $(this).data("url"),
            fnData: {
                class: "\\AktivaController",
                function: "getAktiva",
                params: [
                    $(this).data("perkiraan") == undefined
                        ? null
                        : $(this).data("perkiraan"),
                    $(this).data("devisi") == undefined
                        ? null
                        : $(this).data("devisi"),
                ],
            },
            checkPermission: true,
            codeAccess: "01001002",
            access: "ISKOREKSI",
        };
        options.callback = function (res, modal) {
            modal.find('.select2[name="NoMuka"]').prop("disabled", true);
            modal.find('.select2[name="Devisi"]').prop("disabled", true);
        };
        getModal(options);
    });

    $(document).on("click", ".btnSaldoAwal", function (e) {
        e.preventDefault();
        let option = {};
        option.data = {
            resource:
                "components.master_data.master_accounting.aktiva.modal-saldo-awal",
            modalId: "modalSaldoAwal",
            formId: "formSaldoAwal",
            modalWidth: "md",
            url: $(this).data("url"),
            fnData: {
                class: "\\AktivaController",
                function: "getSaldoAwal",
                params: [
                    $(this).data("perkiraan") == undefined
                        ? null
                        : $(this).data("perkiraan"),
                    $(this).data("devisi") == undefined
                        ? null
                        : $(this).data("devisi"),
                ],
            },
            plugins: [
                {
                    element: "input.mask-money",
                    plugin: "maskMoney",
                },
            ],
        };
        option.callback = function (res, modal) {
            modal.find("input.mask-money").maskMoney("mask");
        };
        getModal(option);
    });

    $(document).on("submit", "#formSaldoAwal", function (e) {
        e.preventDefault();

        formAjax({
            form: $(this),
            modal: $(this).closest(".modal"),
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                if (data.status) {
                    baseSwal("success", "Berhasil", data.message, "success");
                    modal.modal("hide");
                    eval(data.data.datatable).ajax.reload();
                } else {
                    baseSwal("danger", "Gagal", data.message, "error");
                }
            },
        });
    });

    $(document).on("submit", "#formAddAktiva", function (e) {
        e.preventDefault();

        formAjax({
            form: $(this),
            modal: $(document).find("#modalAddAktiva"),
            callbackSerialize: function ($form, options) {
                let Biaya = $form.find("input[name='Biaya']");
                let Biaya2 = $form.find("input[name='Biaya2']");
                let Biaya3 = $form.find("input[name='Biaya3']");
                let PersenBiaya1 = $form.find("input[name='PersenBiaya1']");
                let PersenBiaya2 = $form.find("input[name='PersenBiaya2']");
                let persenbiaya3 = $form.find("input[name='persenbiaya3']");

                let persen =
                    (PersenBiaya1.val() != ""
                        ? parseInt(PersenBiaya1.val())
                        : 0) +
                    (PersenBiaya2.val() != ""
                        ? parseInt(PersenBiaya2.val())
                        : 0) +
                    (persenbiaya3.val() != ""
                        ? parseInt(persenbiaya3.val())
                        : 0);

                if (persen > 100 || persen < 100) {
                    baseSwal(
                        "warning",
                        "Warning",
                        "Total persen biaya harus 100%"
                    );
                    return false;
                }

                if (PersenBiaya1.val() != "" && Biaya.val() == "") {
                    baseSwal(
                        "warning",
                        "Warning",
                        "Biaya 1 tidak boleh kosong"
                    );
                    return false;
                }

                if (PersenBiaya2.val() != "" && Biaya2.val() == "") {
                    baseSwal(
                        "warning",
                        "Warning",
                        "Biaya 2 tidak boleh kosong"
                    );
                    return false;
                }

                if (persenbiaya3.val() != "" && Biaya3.val() == "") {
                    baseSwal(
                        "warning",
                        "Warning",
                        "Biaya 3 tidak boleh kosong"
                    );
                    return false;
                }

                return true;
            },
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                if (data.status) {
                    baseSwal("success", "Berhasil", data.message, "success");
                    modal.modal("hide");
                    eval(data.data.datatable).ajax.reload();
                } else {
                    baseSwal("danger", "Gagal", data.message, "error");
                }
            },
        });
    });

    $(document).on("click", ".btnGlobalDelete", function (e) {
        globalDelete($(this).data("url"), datatableMain, $(this).data("key"));
    });
})(jQuery, $globalVariable);
