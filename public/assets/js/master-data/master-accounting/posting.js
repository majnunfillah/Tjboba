
import $globalVariable from "../../base-function.js";

(function ($, {baseSwal, formAjax, getModal, globalDelete, applyPlugins}) {
    // "use strict";

    const options = {};

    var optionDatatable = {
        dom:
            "<'row'<'col button-table'B><'col-auto'l><'col-auto'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col'i><'col'p>>",
        scrollX: true,
        processing: true,
        serverSide: true,
        searchDelay: 1000,
        destroy: true,
        columnDefs: [
            {
                targets: "_all",
                defaultContent: '<div class="text-center align-middle">-</div>',
            },
        ],
        buttons: [
            {
                text: "<i class='fa fa-sync mr-2'></i>Refresh",
                className: "btn btn-primary btn-sm mr-2 buttons-refresh",
                action: function () {
                    this.ajax.reload();
                },
                init: function (api, node, config) {
                    $(node).removeClass("btn-secondary");
                },
            },
        ],
    };

    var datatableMain = $("").DataTable();

    options.data = {
        resource:
            "components.master_data.master_accounting.posting.modal-posting",
        // modalId: "modalPosting",
        // formId: "formPosting",
    };

    $(document).on("click", ".cardPosting", function (e) {
        e.preventDefault();

        if (
            ($(this).data("component") == undefined &&
                $(this).data("component") == "") ||
            ($(this).data("width") == undefined && $(this).data("width") == "")
        ) {
            return false;
        }

        let id = $(this).attr("id");
        let component = $(this).attr("data-component");

        options.data = {
            ...options.data,
            modalId: `modalPosting${id}`,
            formId: `formPosting${id}`,
            modalWidth: $(this).data("width"),
            fnData: {
                class: "\\PostingController",
                function: "getModalPosting",
                params: [id, component],
            },
        };
        options.callback = function (response) {

            eval(response.res.callback);
        };
        getModal(options);
    });

    $(document).on("keyup", "#search-posting", function (e) {
        let value = $(this).val();
        for (let cardPosting of $(".cardPosting")) {
            let id = $(cardPosting).attr("id");
            let title = $(cardPosting).find("h6").text();
            let col = $(cardPosting).closest(".colPosting");
            if (
                title.toLowerCase().includes(value.toLowerCase()) ||
                id.toLowerCase().includes(value.toLowerCase())
            ) {
                if (col.hasClass("d-none")) {
                    col.removeClass("d-none");
                    col.addClass("d-block");
                }
            } else {
                if (col.hasClass("d-block")) {
                    col.removeClass("d-block");
                    col.addClass("d-none");
                }
            }
        }
    });

    function postingKAS(formId = "") {

        let form_ = $(document).find(`#${formId}`);
        let contentPosting = $(document).find("#contentForm");
        let modal_ = form_.closest(".modal");

        applyPlugins(form_.closest(".modal"), [
            {
                element: "select[name='Perkiraan']",
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-kelompok-kas-select",
            },
        ]);
        datatableMain = $(document)
            .find("#datatableMain")
            .DataTable({
                ...optionDatatable,
                ajax: {
                    url: $(document).find("#datatableMain").data("server"),
                },
                columns: [
                    { data: "Perkiraan" },
                    { data: "Keterangan" },
                    {
                        data: "action",
                        searchable: false,
                        orderable: false,
                        className: "text-center parentBtnRow",
                    },
                ],
                initComplete: function () {
                    $(

                        '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddPostingKAS"><i class="fa fa-plus mr-2"></i>Tambah </button>'
                    ).insertBefore(modal_.find(".buttons-refresh"));
                    // if (json.data.length > 0) {
                    //     if (json.data[0].canAdd === true) {
                    //     }
                    // }
                },
            });

        $(document).on("click", ".btnAddPostingKAS", function (e) {
            e.preventDefault();
            form_.find("input[name='oldPerkiraan']").val(null);
            form_
                .find("select[name='Perkiraan']")
                .empty()
                .trigger("select2:change");

            if (contentPosting.hasClass("d-none")) {
                contentPosting.removeClass("d-none");
                contentPosting.addClass("d-block");
            }

            $(modal_).animate(
                {
                    scrollTop: $(contentPosting).offset().top,
                },
                1000
            );
        });

        $(document).on("click", ".btnEditPostingKas", function (e) {
            e.preventDefault();
            let tr = $(this).closest("tr");
            let data = datatableMain.row(tr).data();
            let select2 = form_.find("select[name='Perkiraan']");
            form_.find("input[name='oldPerkiraan']").val(data.Perkiraan);

            if (contentPosting.hasClass("d-none")) {
                contentPosting.removeClass("d-none");
                contentPosting.addClass("d-block");
            }

            $(modal_).animate(
                {
                    scrollTop: $(contentPosting).offset().top,
                },
                1000
            );

            select2
                .empty()
                .append(
                    new Option(
                        `${data.Perkiraan} - ${data.Keterangan}`,
                        data.Perkiraan,
                        true,
                        true
                    )
                )
                .trigger("change");
        });

        $(form_).on("submit", function (e) {
            e.preventDefault();
            formAjax({
                form: $(this),
                callbackSuccess: function (data, status, jqxhr, form) {
                    alert("Berhasil!", data.message, "success");
                    form.get(0).reset();
                    contentPosting.removeClass("d-block");
                    contentPosting.addClass("d-none");
                    datatableMain.ajax.reload();
                },
            });
        });
    }

    function postingAktiva() {
        let form_ = $(document).find(`#formPostingAKTIVA`);
        let contentPosting = $(document).find("#contentForm");
        let modal_ = form_.closest(".modal");
        applyPlugins(form_.closest(".modal"), [
            {
                element: "select[name='Perkiraan']",
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-kelompok-kas-select",
            },
            {
                element: "select[name='Akumulasi']",
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-akumulasi-penyusutan-select",
            },
            {
                element: '.select2[name="Biaya1"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-biaya-select",
            },
        ]);
        datatableMain = $(document)
            .find("#datatableMain")
            .DataTable({
                ...optionDatatable,
                ajax: {
                    url: $(document).find("#datatableMain").data("server"),
                },
                columns: [
                    { data: "Perkiraan" },
                    { data: "Keterangan" },
                    { data: "Persen" },
                    { data: "Metode" },
                    { data: "Akumulasi" },
                    { data: "Biaya1" },
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
                fixedColumns: {
                    leftColumns: 1,
                    rightColumns: 1,
                },
                initComplete: function () {
                    $(
                        '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddPosting"><i class="fa fa-plus mr-2"></i>Tambah 3</button>'
                    ).insertBefore(modal_.find(".buttons-refresh"));
                    $(
                        `<div><input type="number" min="0" max="2" value="1" class="left-fixed-input"style="width:50px" /><input type="number" min="0" max="2" value="1" class="right-fixed-input" style="width:50px" /></div>`
                    ).insertAfter(modal_.find(".buttons-refresh"));

                    // if (json.data.length > 0) {
                    //     if (json.data[0].canAdd === true) {
                    //     }
                    // }
                },
            });

        $(document).on("click", ".btnAddPosting", function (e) {
            e.preventDefault();
            form_.find("input[name='oldPerkiraan']").val(null);
            form_
                .find("select[name='Perkiraan']")
                .empty()
                .trigger("select2:change");
            form_
                .find("select[name='Akumulasi']")
                .empty()
                .trigger("select2:change");
            form_
                .find("select[name='Biaya1']")
                .empty()
                .trigger("select2:change");
            form_.get(0).reset();

            if (contentPosting.hasClass("d-none")) {
                contentPosting.removeClass("d-none");
                contentPosting.addClass("d-block");
            }

            $(modal_).animate(
                {
                    scrollTop: $(contentPosting).offset().top,
                },
                1000
            );

            form_.find("select[name='Perkiraan']").select2({ disabled: false });
            applyPlugins(form_.closest(".modal"), [
                {
                    element: "select[name='Perkiraan']",
                    plugin: "select2-search",
                    ajax: "setSelectAjax",
                    path: "/get-kelompok-kas-select",
                },
            ]);
        });

        $(document).on("click", ".btnEditPosting", function (e) {
            e.preventDefault();
            let tr = $(this).closest("tr");
            let data = datatableMain.row(tr).data();
            let PerkiraanSelect2 = form_.find("select[name='Perkiraan']");
            let AkumulasiSelect2 = form_.find("select[name='Akumulasi']");
            let Biaya1Select2 = form_.find("select[name='Biaya1']");
            let TipeSelect = form_.find("select[name='Tipe']");
            let PersenBiaya1 = form_.find("input[name='PersenBiaya1']");
            let Biaya2 = form_.find("input[name='Biaya2']");
            let PersenBiaya2 = form_.find("input[name='PersenBiaya2']");
            let Persen = form_.find("input[name='Persen']");
            let oldPerkiraan = form_.find("input[name='oldPerkiraan']");
            form_.find("input[name='oldPerkiraan']").val(data.Perkiraan);

            if (contentPosting.hasClass("d-none")) {
                contentPosting.removeClass("d-none");
                contentPosting.addClass("d-block");
            }

            $(modal_).animate(
                {
                    scrollTop: $(contentPosting).offset().top,
                },
                1000
            );

            PerkiraanSelect2.empty()
                .append(
                    new Option(
                        `${data.Perkiraan} - ${data.Keterangan}`,
                        data.Perkiraan,
                        true,
                        true
                    )
                )
                .trigger("change");
            PerkiraanSelect2.select2({ disabled: "readonly" });
            AkumulasiSelect2.empty()
                .append(
                    new Option(
                        `${data.Akumulasi} - ${data.KeteranganAkumulasi}`,
                        data.Akumulasi,
                        true,
                        true
                    )
                )
                .trigger("change");
            Biaya1Select2.empty()
                .append(
                    new Option(
                        `${data.Biaya1} - ${data.KeteranganBiaya1}`,
                        data.Biaya1,
                        true,
                        true
                    )
                )
                .trigger("change");
            PersenBiaya1.val(data.PersenBiaya1);
            Biaya2.val(data.Biaya2);
            PersenBiaya2.val(data.PersenBiaya2);
            Persen.val(data.Persen);
            oldPerkiraan.val(data.Perkiraan);
            let val = "";
            switch (data.Metode) {
                case "[L]urus":
                    val = "L";
                    break;
                case "[M]enurun":
                    val = "M";
                case "[P]ajak":
                    val = "P";
                    break;
            }
            TipeSelect.val(val).trigger("change");
        });

        $(form_).on("submit", function (e) {
            e.preventDefault();
            formAjax({
                form: $(this),
                callbackSerialize: function ($form, options) {
                    let Biaya = $form.find("input[name='Biaya']");
                    let Biaya2 = $form.find("input[name='Biaya2']");
                    let PersenBiaya1 = $form.find("input[name='PersenBiaya1']");
                    let PersenBiaya2 = $form.find("input[name='PersenBiaya2']");

                    let persen =
                        (PersenBiaya1.val() != ""
                            ? parseInt(PersenBiaya1.val())
                            : 0) +
                        (PersenBiaya2.val() != ""
                            ? parseInt(PersenBiaya2.val())
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

                    return true;
                },
                callbackSuccess: function (data, status, jqxhr, form) {
                    alert("Berhasil!", data.message, "success");
                    form.get(0).reset();
                    contentPosting.removeClass("d-block");
                    contentPosting.addClass("d-none");
                    datatableMain.ajax.reload();
                },
            });
        });
    }

    $(document).on("click", ".btnGlobalDelete", function (e) {
        globalDelete(
            $(this).data("url"),
            datatableMain,
            $(this).data("key")
        );
    });
})(jQuery, $globalVariable);
