import $globalVariable, { publicURL } from "../../base-function.js";

(function ($, { baseSwal, formAjax, getModal, globalDelete, applyPlugins }) {
    // "use strict";

    const options = {};

    var datatableMain = $("#datatableMain").DataTable({
        dom:
            "<'row'<'col button-table'B><'col-auto'l><'col-auto'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col'i><'col'p>>",
        scrollX: true,
        processing: true,
        serverSide: true,
        searchDelay: 1000,
        destroy: true,
        ajax: {
            url: $(this).data("server"),
        },
        columns: [
            { data: "KODEGRP" },
            { data: "NAMA" },
            {
                data: "action",
                searchable: false,
                orderable: false,
                className: "text-center parentBtnRow",
            },
        ],
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
        initComplete: function (settings, json) {
            $(".dataTables_filter")
                .find("input")
                .attr("placeholder", "Search...");
            $(
                '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddGroup" ><i class="fa fa-plus mr-2"></i>Tambah</button>'
            ).insertBefore(".buttons-refresh");
        },
    });
    var datatableSubGroup = $("").DataTable();
    var datatableDepartemen = $("").DataTable();

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

    options.data = {
        resource:
            "components.master_data.master_bahan_barang.group.modal-create",
        modalId: "modalAddGroup",
        formId: "formAddGroup",
        modalWidth: "md",
    };

    $(document).on("click", ".btnAddGroup", function (e) {
        options.data = {
            ...options.data,
            url: publicURL + "/master-data/master-bahan-barang/group",
            fnData: {
                class: "\\GroupController",
                function: "getGroup",
                params: [
                    $(this).data("group") == undefined
                        ? null
                        : $(this).data("group"),
                ],
            },
        };

        getModal(options);
    });

    $(document).on("click", ".btnEditGroup", function (e) {
        options.data = {
            ...options.data,
            url: $(this).data("url"),
            fnData: {
                class: "\\GroupController",
                function: "getGroup",
                params: [
                    $(this).data("group") == undefined
                        ? null
                        : $(this).data("group"),
                ],
            },
        };
        options.callback = function (response, modal) {
            modal.find('input[name="KODEGRP"]').prop("readonly", true);
        };

        getModal(options);
    });

    $(document).on("click", ".btnSubGroup", function (e) {
        const dataMain = datatableMain.row($(this).parents("tr")).data();
        options.data = {
            resource:
                "components.master_data.master_bahan_barang.group.modal-sub-group",
            modalId: "modalSubGroup",
            formId: "formSubGroup",
            modalWidth: "xl",
        };
        options.callback = function (response, modal) {
            modal
                .find(".modal-title")
                .text(
                    "Sub Group " + dataMain.NAMA + " (" + dataMain.KODEGRP + ")"
                );
            const datatableSubGroupEl = modal.find("#datatableSubGroup");
            const contentPosting = modal.find("#contentForm");
            const form = modal.find("form");
            let serverUrl = datatableSubGroupEl.data("server");
            datatableSubGroupEl.data(
                "server",
                serverUrl.replace("%kode", dataMain.KODEGRP)
            );
            applyPlugins(form.closest(".modal"), [
                {
                    element:
                        "select[name='PerkPers'],select[name='PerkH'],select[name='PerkPPN'],select[name='PerkBiaya']",
                    plugin: "select2-search",
                    ajax: "setSelectAjax",
                    path: "/get-biaya-select",
                },
            ]);

            datatableSubGroup = datatableSubGroupEl.DataTable({
                ...optionDatatable,
                ajax: {
                    url: datatableSubGroupEl.data("server"),
                },
                columns: [
                    { data: "KodeSubGrp" },
                    { data: "NamaSubGrp" },
                    { data: "PerkPers" },
                    { data: "PerkH" },
                    { data: "PerkPPN" },
                    { data: "PerkBiaya" },
                    {
                        data: "action",
                        searchable: false,
                        orderable: false,
                        className: "text-center parentBtnRow",
                    },
                ],
                initComplete: function () {
                    $(
                        '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddSubGroup"><i class="fa fa-plus mr-2"></i>Tambah</button>'
                    ).insertBefore(modal.find(".buttons-refresh"));
                },
            });

            $(document).on("click", ".btnAddSubGroup", function (e) {
                if (contentPosting.hasClass("d-none")) {
                    contentPosting.removeClass("d-none");
                    contentPosting.addClass("d-block");
                }

                $(modal).animate(
                    {
                        scrollTop: $(contentPosting).offset().top,
                    },
                    1000
                );

                form.get(0).reset();
                form.attr(
                    "action",
                    publicURL +
                        `/master-data/master-bahan-barang/${dataMain.KODEGRP}/sub`
                );
                modal.find('select[name="PerkPers"]').empty();
                modal.find('select[name="PerkH"]').empty();
                modal.find('select[name="PerkPPN"]').empty();
                modal.find('select[name="PerkBiaya"]').empty();
                modal.find('input[name="KodeSubGrp"]').removeAttr("readonly");

                applyPlugins(form.closest(".modal"), [
                    {
                        element:
                            "select[name='PerkPers'],select[name='PerkH'],select[name='PerkPPN'],select[name='PerkBiaya']",
                        plugin: "select2-search",
                        ajax: "setSelectAjax",
                        path: "/get-biaya-select",
                    },
                ]);
            });

            $(document).on("click", ".btnEditSubGroup", function (e) {
                let data = datatableSubGroup.row($(this).parents("tr")).data();
                let KodeSubGrp = modal.find('input[name="KodeSubGrp"]');
                let NamaSubGrp = modal.find('input[name="NamaSubGrp"]');
                let PerkPers = modal.find('select[name="PerkPers"]');
                let PerkH = modal.find('select[name="PerkH"]');
                let PerkPPN = modal.find('select[name="PerkPPN"]');
                let PerkBiaya = modal.find('select[name="PerkBiaya"]');

                if (contentPosting.hasClass("d-none")) {
                    contentPosting.removeClass("d-none");
                    contentPosting.addClass("d-block");
                }

                $(modal).animate(
                    {
                        scrollTop: $(contentPosting).offset().top,
                    },
                    1000
                );

                form.attr(
                    "action",
                    publicURL +
                        `/master-data/master-bahan-barang/${dataMain.KODEGRP}/sub/${data.KodeSubGrp}`
                );

                KodeSubGrp.val(data.KodeSubGrp).prop("readonly", true);
                NamaSubGrp.val(data.NamaSubGrp);
                PerkPers.empty()
                    .append(
                        new Option(
                            `${data.PerkPers} - ${data.KeteranganPerkPers}`,
                            data.PerkPers,
                            true,
                            true
                        )
                    )
                    .trigger("change");
                PerkH.empty()
                    .append(
                        new Option(
                            `${data.PerkH} - ${data.KeteranganPerkH}`,
                            data.PerkH,
                            true,
                            true
                        )
                    )
                    .trigger("change");
                PerkPPN.empty()
                    .append(
                        new Option(
                            `${data.PerkPPN} - ${data.KeteranganPerkPPN}`,
                            data.PerkPPN,
                            true,
                            true
                        )
                    )
                    .trigger("change");
                PerkBiaya.empty()
                    .append(
                        new Option(
                            `${data.PerkBiaya} - ${data.KeteranganPerkBiaya}`,
                            data.PerkBiaya,
                            true,
                            true
                        )
                    )
                    .trigger("change");
            });

            modal.on("click", ".btnDepartemen", function (e) {
                let data = datatableSubGroup.row($(this).parents("tr")).data();
                modalDepartemen(data);
            });

            form.on("submit", function (e) {
                e.preventDefault();

                formAjax({
                    form,
                    callbackSuccess: function (data, status, jqxhr) {
                        if (data.status) {
                            baseSwal(
                                "success",
                                "Berhasil",
                                data.message,
                                "success"
                            );
                            contentPosting.removeClass("d-block");
                            contentPosting.addClass("d-none");
                            datatableSubGroup.ajax.reload();
                        } else {
                            baseSwal("danger", "Gagal", data.message, "error");
                        }
                    },
                });
            });
        };

        getModal(options);
    });

    $(document).on("submit", "#formAddGroup", function (e) {
        e.preventDefault();

        formAjax({
            form: $(this),
            modal: $(document).find("#modalAddGroup"),
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
        let $datatable = eval($(this).data("datatable") ?? "datatableMain");
        globalDelete($(this).data("url"), $datatable, $(this).data("key"));
    });

    function modalDepartemen(data) {
        let option = {
            data: {
                resource:
                    "components.master_data.master_bahan_barang.group.modal-departemen",
                modalId: "modalDepartemen",
                formId: "formDepartemen",
                modalWidth: "lg",
            },
            callback: function (response, _modal) {
                _modal
                    .find(".modal-title")
                    .text(
                        "Departemen Sub Group " +
                            data.NamaSubGrp +
                            " (" +
                            data.KodeSubGrp +
                            ")"
                    );
                const datatableDepartemenEl = _modal.find(
                    "#datatableDepartemen"
                );
                const contentForm = _modal.find("#contentFormDepartemen");
                const _form = _modal.find("form");
                let serverUrl = datatableDepartemenEl.data("server");
                serverUrl = serverUrl.replace("%kodegroup", data.KodeGrp);
                serverUrl = serverUrl.replace("%kodesubgroup", data.KodeSubGrp);

                applyPlugins(_form.closest(".modal"), [
                    {
                        element: "select[name='Departemen']",
                        plugin: "select2-search",
                        ajax: "setSelectAjax",
                        path: "/get-departemen-select",
                    },
                ]);

                datatableDepartemen = datatableDepartemenEl.DataTable({
                    ...optionDatatable,
                    ajax: {
                        url: serverUrl,
                    },
                    columns: [
                        { data: "Urut" },
                        { data: "Keterangan" },
                        { data: "NMDEP" },
                        {
                            data: "action",
                            searchable: false,
                            orderable: false,
                            className: "text-center parentBtnRow",
                        },
                    ],
                    initComplete: function () {
                        $(
                            '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddDepartemen"><i class="fa fa-plus mr-2"></i>Tambah</button>'
                        ).insertBefore(_modal.find(".buttons-refresh"));
                    },
                });

                _modal.on("click", ".btnAddDepartemen", function (e) {
                    if (contentForm.hasClass("d-none")) {
                        contentForm.removeClass("d-none");
                        contentForm.addClass("d-block");
                    }

                    $(_modal).animate(
                        {
                            scrollTop: $(contentForm).offset().top,
                        },
                        1000
                    );

                    _form.attr(
                        "action",
                        publicURL +
                            `/master-data/master-bahan-barang/${data.KodeGrp}/sub/${data.KodeSubGrp}/departemen`
                    );

                    _modal
                        .find('select[name="Departemen"]')
                        .val(null)
                        .trigger("change");
                });

                _modal.on("click", ".btnEditDepartemen", function (e) {
                    let dataDep = datatableDepartemen
                        .row($(this).parents("tr"))
                        .data();
                    if (contentForm.hasClass("d-none")) {
                        contentForm.removeClass("d-none");
                        contentForm.addClass("d-block");
                    }

                    $(_modal).animate(
                        {
                            scrollTop: $(contentForm).offset().top,
                        },
                        1000
                    );

                    _form.attr(
                        "action",
                        publicURL +
                            `/master-data/master-bahan-barang/${data.KodeGrp}/sub/${data.KodeSubGrp}/departemen/${dataDep.Keterangan}`
                    );

                    _modal
                        .find('select[name="Departemen"]')
                        .empty()
                        .append(
                            new Option(
                                `${dataDep.Keterangan} - ${dataDep.NMDEP}`,
                                data.Keterangan,
                                true,
                                true
                            )
                        )
                        .trigger("change");
                });

                _form.on("submit", function (e) {
                    e.preventDefault();

                    formAjax({
                        form: _form,
                        callbackSuccess: function (data, status, jqxhr) {
                            if (data.status) {
                                baseSwal(
                                    "success",
                                    "Berhasil",
                                    data.message,
                                    "success"
                                );
                                contentForm.removeClass("d-block");
                                contentForm.addClass("d-none");
                                datatableDepartemen.ajax.reload();
                            } else {
                                baseSwal(
                                    "danger",
                                    "Gagal",
                                    data.message,
                                    "error"
                                );
                            }
                        },
                    });
                });
            },
        };

        getModal(option);
    }
})(jQuery, $globalVariable);
