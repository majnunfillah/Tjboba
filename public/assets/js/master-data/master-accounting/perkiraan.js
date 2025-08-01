import $globalVariable, { publicURL } from "../../base-function.js";

(function ($, { baseSwal, formAjax, getModal, globalDelete, newExportAction, mergeWithDefaultOptions }) {
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
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        },
                    },
                    {
                        $keyButton: "pdf",
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
                        },
                    },
                ],
            },
            initComplete: function (settings, json) {
                $(
                    '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddPerkiraan " ><i class="fa fa-plus mr-2"></i>Tambah</button>'
                ).insertBefore(".buttons-colvis");
    
                return true;
            },
        }),
        ajax: {
            url: $(this).data("server"),
        },
        columns: [
            { data: "Perkiraan" },
            { data: "Keterangan" },
            { data: "Kelompok" },
            { data: "Tipe" },
            { data: "DK" },
            { data: "Valas" },
            { data: "Simbol", searchable: false, orderable: false },
            { data: "KodeAK" },
            { data: "KodeSAK" },
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
            "components.master_data.master_accounting.perkiraan.modal-create",
        modalId: "modalAddPerkiraan",
        formId: "formAddPerkiraan",
        modalWidth: "md",
        plugins: [
            {
                element: '.select2[name="Kelompok"]',
                plugin: "select2",
            },
            {
                element: '.select2[name="Tipe"]',
                plugin: "select2",
            },
            {
                element: '.select2[name="DK"]',
                plugin: "select2",
            },
            {
                element: '.select2[name="Valas"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-valas-select",
            },
            {
                element: '.select2[name="KodeAK"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-arus-kas-select",
            },
            {
                element: '.select2[name="KodeSAK"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-arus-kas-det-select",
            },
        ],
    };

    $(document).on("click", ".btnAddPerkiraan", function (e) {
        e.preventDefault();
        options.data = {
            ...options.data,
            url: publicURL + "/master-data/master-accounting/perkiraan",
            fnData: {
                class: "\\PerkiraanController",
                function: "getPerkiraan",
                params: [
                    $(this).data("perkiraan") == undefined
                        ? null
                        : $(this).data("perkiraan"),
                ],
            },
            checkPermission: true,
            codeAccess: "01001001",
            access: "ISTAMBAH",
        };
        getModal(options);
    });

    $(document).on("click", ".btnEditPerkiraan", function (e) {
        e.preventDefault();
        options.data = $.extend(options.data, {
            fnData: {
                class: "\\PerkiraanController",
                function: "getPerkiraan",
                params: [
                    $(this).data("perkiraan") == undefined
                        ? null
                        : $(this).data("perkiraan"),
                ],
            },
            url: $(this).data("url"),
            checkPermission: true,
            codeAccess: "01001001",
            access: "ISKOREKSI",
        });
        options.callback = function (res, modal) {
            modal.find('input[name="Perkiraan"]').prop("readonly", true);
        };
        getModal(options);
    });

    $(document).on("click", ".btnGetSaldoAwal", function (e) {
        e.preventDefault();
        let option = {};
        option.data = {
            resource:
                "components.master_data.master_accounting.perkiraan.modal-saldo-awal",
            modalId: "modalSaldoAwal",
            formId: "formSaldoAwal",
            modalWidth: "xl",
            fnData: {
                class: "\\PerkiraanController",
                function: "getSaldoAwal",
                params: [
                    $(this).data("perkiraan") == undefined
                        ? null
                        : $(this).data("perkiraan"),
                ],
            },
            plugins: [
                {
                    element: 'input[name="AwalD"]',
                    plugin: "maskMoney",
                    options: {
                        prefix: "Rp.",
                        allowNegative: false,
                    },
                },
                {
                    element: 'input[name="AwalDRp"]',
                    plugin: "maskMoney",
                    options: {
                        prefix: "Rp.",
                        allowNegative: false,
                    },
                },
                {
                    element: 'input[name="kurs"]',
                    plugin: "maskMoney",
                    options: {
                        prefix: "Rp.",
                        allowNegative: false,
                    },
                },
            ],
            url: $(this).data("url"),
        };
        option.callback = function (response, modal) {
            modal.find("#contentSetSaldo").css("display", "none");
            let datatableSaldo = modal.find("#datatableSaldo").DataTable({
                dom:
                    "<'row'<'col-auto button-table'><'col'><'col'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col'i><'col'p>>",
                order: [[0, "desc"]],
                columnDefs: [
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        visible: false,
                    },
                    {
                        targets: 2,
                        orderable: false,
                        searchable: false,
                        visible: false,
                    },
                    {
                        targets: 3,
                        orderable: false,
                        searchable: false,
                        visible: false,
                    },
                ],
                initComplete: function (settings, json) {
                    modal
                        .find(".dataTables_filter")
                        .find("input")
                        .attr("placeholder", "Search...");

                    modal
                        .find("#datatableSaldo")
                        .wrap("<div class='scrolledTable'></div>");
                    
                    return false
                },
            });

            $(".btnSetSaldoAwal").on("click", function (e) {
                let btn = $(this);
                let contentSaldo = modal.find("#contentSetSaldo");

                let row = btn.closest(".rowSaldo");
                let data = datatableSaldo.row(row).data();

                let Perkiraan = row.find(".Perkiraan");
                let AwalDRp = row.find(".AwalDRp");
                let AwalD = row.find(".AwalD");
                let valas = row.find(".valas");
                let kurs = row.find(".kurs");
                let Devisi = data[3];
                let Tahun = data[2];
                let Bulan = data[1];

                contentSaldo
                    .find('input[name="AwalDRp"]')
                    .val(
                        AwalDRp.text()
                            .replace(",", "#")
                            .replaceAll(".", "")
                            .replace("#", ".")
                    )
                    .maskMoney("mask");
                contentSaldo
                    .find('input[name="AwalD"]')
                    .val(
                        AwalD.text()
                            .replace(",", "#")
                            .replaceAll(".", "")
                            .replace("#", ".")
                    )
                    .maskMoney("mask");
                contentSaldo.find('input[name="valas"]').val(valas.text());
                contentSaldo
                    .find('input[name="kurs"]')
                    .val(
                        kurs
                            .text()
                            .replace(",", "#")
                            .replaceAll(".", "")
                            .replace("#", ".")
                    )
                    .maskMoney("mask");
                contentSaldo.find('input[name="Devisi"]').val(Devisi);
                contentSaldo.find('input[name="Tahun"]').val(Tahun);
                contentSaldo.find('input[name="Bulan"]').val(Bulan);

                contentSaldo.css("display", "block");
                $(modal).animate(
                    {
                        scrollTop: $(contentSaldo).offset().top,
                    },
                    1000
                );

                let url =
                    publicURL +
                    "/master-data/master-accounting/set-saldo-awal/" +
                    Perkiraan.text();

                contentSaldo.find("form").attr("action", url);

                row.addClass("editing");
            });
        };
        getModal(option);
    });

    $(document).on("click", ".btnGetBudget", function (e) {
        e.preventDefault();
        let option = {};
        option.data = {
            resource:
                "components.master_data.master_accounting.perkiraan.modal-budget",
            modalId: "modalBudget",
            formId: "formBudget",
            modalWidth: "lg",
            fnData: {
                class: "\\PerkiraanController",
                function: "getBudget",
                params: [
                    $(this).data("perkiraan") == undefined
                        ? null
                        : $(this).data("perkiraan"),
                ],
            },
            plugins: [
                {
                    element: 'input[name="Budget"]',
                    plugin: "maskMoney",
                    options: {
                        prefix: "Rp.",
                        allowNegative: false,
                    },
                },
            ],
            url: $(this).data("url"),
        };
        option.callback = function (response, modal) {
            modal.find("#contentSetBudget").css("display", "none");
            let datatableSaldo = modal.find("#datatableBudget").DataTable({
                dom:
                    "<'row'<'col-auto button-table'><'col'><'col'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col'i><'col'p>>",
                order: [[0, "desc"]],
                columnDefs: [
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        visible: true,
                    },
                ],
                initComplete: function (settings, json) {
                    modal
                        .find(".dataTables_filter")
                        .find("input")
                        .attr("placeholder", "Search...");

                    modal
                        .find("#datatableBudget")
                        .wrap("<div class='scrolledTable'></div>");
                },
            });

            $(".btnSetBudget").on("click", function (e) {
                let btn = $(this);
                let contentBudget = modal.find("#contentSetBudget");

                let row = btn.closest(".rowBudget");
                let data = datatableSaldo.row(row).data();

                let Perkiraan = row.find(".Perkiraan");
                let Budget = row.find(".Budget");
                let Devisi = data[1];
                let Tahun = row.find(".Tahun");
                let Bulan = row.find(".Bulan");

                contentBudget
                    .find('input[name="Budget"]')
                    .val(
                        Budget.text()
                            .replace(",", "#")
                            .replaceAll(".", "")
                            .replace("#", ".")
                    )
                    .maskMoney("mask");
                contentBudget.find('input[name="Devisi"]').val(Devisi);
                contentBudget.find('input[name="Tahun"]').val(Tahun.text());
                contentBudget.find('input[name="Bulan"]').val(Bulan.text());

                contentBudget.css("display", "block");

                $(modal).animate(
                    {
                        scrollTop: $(contentBudget).offset().top,
                    },
                    1000
                );

                let url =
                    publicURL +
                    "/master-data/master-accounting/set-budget/" +
                    Perkiraan.text();

                contentBudget.find("form").attr("action", url);

                row.addClass("editing");
            });
        };
        getModal(option);
    });

    $(document).on("submit", "#formBudget", function (e) {
        e.preventDefault();

        formAjax({
            form: $(this),
            modal: $(document).find("#modalBudget"),
            callbackSerialize: function ($form, options) {
                let Budget = $form
                    .find("input[name='Budget']")
                    .maskMoney("unmasked")[0];
                $form.find("input[name='Budget_val']").val(Budget);

                return true;
            },
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                if (data.status) {
                    baseSwal("success", "Berhasil", data.message, "success");
                    let contentBudget = modal.find("#contentSetBudget");

                    let row = modal.find(".rowBudget.editing");
                    let Budget = row.find(".Budget");

                    Budget.text(
                        parseFloat(
                            contentBudget.find('input[name="Budget_val"]').val()
                        ).toLocaleString("id-ID", {
                            style: "decimal",
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        })
                    );
                    row.removeClass("editing");

                    form.get(0).reset();
                    modal.find("#contentSetBudget").css("display", "none");
                } else {
                    baseSwal("danger", "Gagal", data.message, "error");
                }
            },
        });
    });

    $(document).on("submit", "#formSetSaldo", function (e) {
        e.preventDefault();

        formAjax({
            form: $(this),
            modal: $(document).find("#modalSaldoAwal"),
            callbackSerialize: function ($form, options) {
                let AwalDRp = $form
                    .find("input[name='AwalDRp']")
                    .maskMoney("unmasked")[0];
                let AwalD = $form
                    .find("input[name='AwalD']")
                    .maskMoney("unmasked")[0];
                let kurs = $form
                    .find("input[name='kurs']")
                    .maskMoney("unmasked")[0];
                $form.find("input[name='AwalDRp_val']").val(AwalDRp);
                $form.find("input[name='AwalD_val']").val(AwalD);
                $form.find("input[name='kurs_val']").val(kurs);

                return true;
            },
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                if (data.status) {
                    baseSwal("success", "Berhasil", data.message, "success");
                    let contentSaldo = modal.find("#contentSetSaldo");

                    let row = modal.find(".rowSaldo.editing");
                    let AwalDRp = row.find(".AwalDRp");
                    let AwalD = row.find(".AwalD");
                    let valas = row.find(".valas");
                    let kurs = row.find(".kurs");

                    AwalDRp.text(
                        parseFloat(
                            contentSaldo.find('input[name="AwalDRp_val"]').val()
                        ).toLocaleString("id-ID", {
                            style: "decimal",
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        })
                    );
                    AwalD.text(
                        parseFloat(
                            contentSaldo.find('input[name="AwalD_val"]').val()
                        ).toLocaleString("id-ID", {
                            style: "decimal",
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        })
                    );
                    valas.text(contentSaldo.find('input[name="valas"]').val());
                    kurs.text(
                        parseFloat(
                            contentSaldo.find('input[name="kurs_val"]').val()
                        ).toLocaleString("id-ID", {
                            style: "decimal",
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        })
                    );
                    row.removeClass("editing");

                    form.get(0).reset();
                    modal.find("#contentSetSaldo").css("display", "none");
                } else {
                    baseSwal("danger", "Gagal", data.message, "error");
                }
            },
        });
    });

    $(document).on("submit", "#formAddPerkiraan", function (e) {
        e.preventDefault();
        formAjax({
            form: $(this),
            modal: $(document).find("#modalAddPerkiraan"),
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                if (data.status) {
                    baseSwal("success", "Berhasil", data.message, "success");
                    modal.modal("hide");
                    // console.log($(document).find(`#${data.data.datatable}_wrapper .buttons-refresh`));
                    // $(document).find(`#${data.data.datatable}_wrapper .buttons-refresh`).click();
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
