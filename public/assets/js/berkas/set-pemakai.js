import $globalVariable, { publicURL } from '../base-function.js';

(function ($, {baseSwal, formAjax, getModal, globalDelete}) {
    // "use strict";

    const options = {};
    var permissions = [];
    options.data = {
        resource: "components.berkas.set_pemakai.modal-create",
        modalId: "modalAddKaryawan",
        formId: "formAddKaryawan",
        modalWidth: "md",
        plugins: [
            {
                element: '.select2[name="keynik"]',
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-karyawan-select",
            },
            {
                element: '.select2[name="kodeBag"]',
                plugin: "select2-search-tags",
                ajax: "setSelectAjax",
                path: "/get-departemen-select",
            },
            {
                element: '.select2[name="KodeJab"]',
                plugin: "select2-search-tags",
                ajax: "setSelectAjax",
                path: "/get-jabatan-select",
            },
            { element: '.select2[name="TINGKAT"]', plugin: "select2" },
            { element: '.select2[name="STATUS"]', plugin: "select2" },
        ],
    };

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
            { data: "USERID" },
            { data: "FullName" },
            { data: "NMDEP" },
            { data: "NamaJab" },
            { data: "TINGKAT" },
            { data: "STATUS" },
            { data: "online_from", searchable: false, orderable: false },
            { data: "KodeKasir", searchable: false, orderable: false },
            { data: "Kodegdg", searchable: false, orderable: false },
            { data: "action", searchable: false, orderable: false, className: "text-center parentBtnRow" },
        ],
        columnDefs: [
            {
                targets: "_all",
                defaultContent: '<div class="text-center align-middle">-</div>',
            },
        ],
        fixedColumns: {
            leftColumns: 1,
            rightColumns: 1,
        },
        buttons: [
            {
                extend: "colvis",
                className: "btn btn-secondary btn-sm mr-2",
                title: "Show/Hide Column",
                text: "<i class='fa fa-eye mr-2'></i>Column",
            },
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
                    '<button class="btn btn-success btn-sm mr-2 buttons-add btnAddKaryawan " ><i class="fa fa-plus mr-2"></i>Tambah</button>'
                ).insertBefore(".buttons-colvis");
            if (json.data.length > 0) {
                if (json.data[0].canAdd === true) {
                }
            }
            $(`<div><input type="number" min="0" max="2" value="1" class="left-fixed-input"style="width:50px" /><input type="number" min="0" max="2" value="1" class="right-fixed-input" style="width:50px" /></div>`).insertAfter(".buttons-refresh");
        },
    });

    $(document).on("click", ".btnAddKaryawan", function (e) {
        e.preventDefault();
        options.data = {
            ...options.data,
            url: publicURL + "/berkas/set-pemakai-karyawan",
            fnData: {
                class: "\\ModalController",
                function: "getUserPermission",
                params: [
                    $(this).data("userid") == undefined
                        ? null
                        : $(this).data("userid"),
                ],
            },
            checkPermission: true,
            codeAccess: '0004',
            access: 'ISTAMBAH',

        };
        getModal(options);
    });

    $(document).on("click", ".btnEditKaryawan", function (e) {
        e.preventDefault();
        options.data = $.extend(options.data, {
            fnData: {
                class: "\\ModalController",
                function: "getUserPermission",
                params: [
                    $(this).data("userid") == undefined
                        ? null
                        : $(this).data("userid"),
                ],
            },
            url: $(this).data("url"),
            checkPermission: true,
            codeAccess: '0004',
            access: 'ISKOREKSI',
        });
        getModal(options);
    });

    $(document).on("click", ".btnEditPermission", function (e) {
        e.preventDefault();
        let option = {};
        option.data = {
            resource: "components.berkas.set_pemakai.modal-permission",
            modalId: "modalEditPermission",
            formId: "formEditPermission",
            modalWidth: "lg",
            fnData: {
                class: "\\ModalController",
                function: "getAllMenu",
                params: [
                    $(this).data("userid") == undefined
                        ? null
                        : $(this).data("userid"),
                ],
            },
            url: $(this).data("url"),
            checkPermission: true,
            codeAccess: '0004',
            access: 'ISKOREKSI',
        };
        option.callback = function (response, modal) {
            let cols = [];
            let data = [];
            let values = [];
            let len = parseInt((response.res.length - 1) / 3 + 1);
            for (let i = 0; i < response.res.length; i++) {
                let basic = 0;
                data.push({
                    id: response.res[i].KODEMENU,
                    text: response.res[i].Keterangan,
                    children: [
                        {
                            id: `basic-${response.res[i].KODEMENU}`,
                            text: "Basic Permission",
                            children: [
                                {
                                    id: "HASACCESS-" + response.res[i].KODEMENU,
                                    text: "HASACCESS",
                                },
                                {
                                    id: "ISTAMBAH-" + response.res[i].KODEMENU,
                                    text: "ISTAMBAH",
                                },
                                {
                                    id: "ISKOREKSI-" + response.res[i].KODEMENU,
                                    text: "ISKOREKSI",
                                },
                                {
                                    id: "ISHAPUS-" + response.res[i].KODEMENU,
                                    text: "ISHAPUS",
                                },
                            ],
                        },
                        {
                            id: "ISCETAK-" + response.res[i].KODEMENU,
                            text: "ISCETAK",
                        },
                        {
                            id: "ISEXPORT-" + response.res[i].KODEMENU,
                            text: "ISEXPORT",
                        },
                        {
                            id: "IsOtorisasi1-" + response.res[i].KODEMENU,
                            text: "IsOtorisasi1",
                        },
                        {
                            id: "IsOtorisasi2-" + response.res[i].KODEMENU,
                            text: "IsOtorisasi2",
                        },
                        {
                            id: "IsOtorisasi3-" + response.res[i].KODEMENU,
                            text: "IsOtorisasi3",
                        },
                        {
                            id: "IsOtorisasi4-" + response.res[i].KODEMENU,
                            text: "IsOtorisasi4",
                        },
                        {
                            id: "IsOtorisasi5-" + response.res[i].KODEMENU,
                            text: "IsOtorisasi5",
                        },
                        {
                            id: "IsBatal-" + response.res[i].KODEMENU,
                            text: "IsBatal",
                        },
                    ],
                });

                if (response.res[i].HASACCESS == "1") {
                    values.push(`HASACCESS-${response.res[i].KODEMENU}`);
                    basic++;
                }
                if (response.res[i].ISTAMBAH == "1") {
                    values.push(`ISTAMBAH-${response.res[i].KODEMENU}`);
                    basic++;
                }
                if (response.res[i].ISKOREKSI == "1") {
                    values.push(`ISKOREKSI-${response.res[i].KODEMENU}`);
                    basic++;
                }
                if (response.res[i].ISHAPUS == "1") {
                    values.push(`ISHAPUS-${response.res[i].KODEMENU}`);
                    basic++;
                }
                if (response.res[i].IsOtorisasi1 == "1") {
                    values.push(`IsOtorisasi1-${response.res[i].KODEMENU}`);
                }
                if (response.res[i].IsOtorisasi2 == "1") {
                    values.push(`IsOtorisasi2-${response.res[i].KODEMENU}`);
                }
                if (response.res[i].IsOtorisasi3 == "1") {
                    values.push(`IsOtorisasi3-${response.res[i].KODEMENU}`);
                }
                if (response.res[i].IsOtorisasi4 == "1") {
                    values.push(`IsOtorisasi4-${response.res[i].KODEMENU}`);
                }
                if (response.res[i].IsOtorisasi5 == "1") {
                    values.push(`IsOtorisasi5-${response.res[i].KODEMENU}`);
                }
                if (response.res[i].IsBatal == "1") {
                    values.push(`IsBatal-${response.res[i].KODEMENU}`);
                }

                if (basic === 4) {
                    values.push(`basic-${response.res[i].KODEMENU}`);
                }

                if (i % len == 0 && i > 0) {
                    cols.push({ data: data, value: values });
                    data = [];
                    values = [];
                }
            }
            cols.push({ data: data, value: values });
            cols.forEach((value, index) => {
                const el = $(`<div clas="col-sm-4 tree-${index}"></div>`);
                $(document).find("#modalEditPermission .row").append(el);
                new Tree(el.get(0), {
                    data: value.data,
                    closeDepth: 1,
                    loaded: function () {
                        this.values = value.value;
                        // permissions += [this.values];
                    },
                    onChange: function () {
                        permissions.push(...this.values);
                    },
                });
            });
        };
        getModal(option);
    });

    $(document).on("click", ".btnEditAccessCOA", function (e) {
        e.preventDefault();
        let option = {};
        option.data = {
            resource: "components.berkas.set_pemakai.modal-permission",
            modalId: "modalAccessCOA",
            formId: "formAccessCOA",
            modalWidth: "lg",
            plugins: [{ element: ".duallistbox", plugin: "duallistbox" }],
            fnData: {
                class: "\\PerkiraanController",
                function: "getAccessCOA",
                // repository: "perkiraanRepository",
                params: [
                    $(this).data("userid") == undefined
                        ? null
                        : $(this).data("userid"),
                ],
            },
            url: $(this).data("url"),
            checkPermission: true,
            codeAccess: '0004',
            access: 'ISKOREKSI',
        };
        getModal(option);
    });

    $(document).on("submit", "#formAddKaryawan", function (e) {
        e.preventDefault();
        formAjax({
            form: $(this),
            modal: $(document).find("#modalAddKaryawan"),
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

    $(document).on("submit", "#formEditPermission", function (e) {
        e.preventDefault();
        formAjax({
            form: $(this),
            modal: $(document).find("#modalEditPermission"),
            param: { data: [...new Set(permissions)] },
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                if (data.status) {
                    baseSwal("success", "Berhasil", data.message, "success");
                    modal.modal("hide");
                } else {
                    baseSwal("danger", "Gagal", data.message, "error");
                }
            },
        });
    });

    $(document).on("submit", "#formAccessCOA", function (e) {
        e.preventDefault();
        formAjax({
            form: $(this),
            modal: $(document).find("#modalAccessCOA"),
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                if (data.status) {
                    baseSwal("success", "Berhasil", data.message, "success");
                    modal.modal("hide");
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
