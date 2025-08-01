function parentmodalAddEditDetailKasbank(applyPlugins, getModal, formAjax, baseSwal, publicURL) {
    this.applyPlugins = applyPlugins;
    this.getModal = getModal;
    this.publicURL = publicURL;
    this.formAjax = formAjax;
    this.baseSwal = baseSwal;
}

parentmodalAddEditDetailKasbank.prototype.modalAddEditDetailKasbank = function (
    row,
    child,
    datatableExpand,
    button
) {
    const options = {};
    const { applyPlugins, getModal, formAjax, baseSwal, publicURL } = this;
    options.data = {
        resource: "components.accounting.kasbank.modal-insert-detail",
        modalId: "modalAddKasBankDetail",
        formId: "formAddKasBankDetail",
        modalWidth: "lg",
        url: publicURL + "/accounting/transaksi-bank-or-kas/kas-bank-detail",
        fnData: {
            class: "\\BankOrKasController",
            function: "getDetailKasBankByNoBukti",
            params: [
                button.data("bukti") == undefined ? null : button.data("bukti"),
                button.data("tanggal") == undefined
                    ? null
                    : button.data("tanggal"),
                button.data("urut") == undefined ? null : button.data("urut"),
            ],
        },
        checkPermission: true,
        codeAccess: "02001",
        access: "ISTAMBAH",
    };
    options.callback = function (response, modal) {
        let data = response.res;
        modal.find('input[name="Kurs_val"]').val(1.00).maskMoney('mask').trigger("keyup");

        modal.on("change", 'select[name="Valas"]', function () {
            let data = $(this).select2("data")[0];
            modal
                .find('input[name="Kurs_val"]')
                .val(data.Kurs)
                .maskMoney("mask");
            modal.find('input[name="Kurs_val"]').maskMoney('mask').trigger("keyup");
        });

        modal.on("change", 'input[name="Tanggal"]', function (e) {
            let tahunPeriode = $(this).data("tahun");
            let bulanPeriode = $(this).data("bulan");
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
            let data = row.data();
            formAjax({
                form: $(this),
                callbackSerialize: function ($form, option) {
                    if (
                        $($form).find('input[type="hidden"][name="NoBukti"]')
                            .length == 0
                    ) {
                        $($form).append(
                            `<input type="hidden" name="NoBukti" value="${data.NoBukti}">`
                        );
                    } else {
                        $($form)
                            .find('input[type="hidden"][name="Urut"]')
                            .val(data.NoBukti);
                    }
                    if (
                        $($form).find('input[type="hidden"][name="Urut"]')
                            .length == 0
                    ) {
                        $($form).append(
                            `<input type="hidden" name="Urut" value="${button.data(
                                "urut"
                            )}">`
                        );
                    } else {
                        $($form)
                            .find('input[type="hidden"][name="Urut"]')
                            .val(button.data("urut"));
                    }

                    if (
                        $($form).find('select[name="Perkiraan"]').val() == "" ||
                        $($form).find('select[name="Perkiraan"]').val() == null
                    ) {
                        alert("Perkiraan harus diisi");
                        return false;
                    }

                    let select = $($form)
                        .find('select[name="Perkiraan"]')
                        .select2("data")[0];
                    if (select.Kode == "HT") {
                        let pelunasan = $($form)
                            .find('input[name="pelunasan"]')
                            .val();
                        let Debet = $($form).find('input[name="Debet"]').val();

                        if (
                            pelunasan == "" ||
                            pelunasan == null ||
                            pelunasan == undefined
                        ) {
                            alert("Lawan Hutang harus melunasi hutang");
                            return false;
                        }

                        if (pelunasan < Debet) {
                            alert(
                                "Jumlah pelunasan tidak boleh kurang dari jumlah Debet"
                            );
                            return false;
                        }

                        if (pelunasan > Debet) {
                            alert(
                                "Saldo tidak mencukupi untuk melunasi hutang"
                            );
                            return false;
                        }
                    }
                    return true;
                },
                callbackSuccess: function (data, status, jqxhr, form) {
                    modal.modal("hide");
                    datatableExpand.ajax.reload();
                    baseSwal("success", "Berhasil", "Data berhasil disimpan");
                },
            });
        });

        modal.on("select2:close", 'select[name="Perkiraan"]', function (e) {
            if ($(this).val() == null) {
                return false;
            }

            let selectData = $(this).select2("data")[0];
            if (selectData.Kode == "HT") {
                if (modal.find('input[name="Debet"]').val() == 0) {
                    alert("Jumlah tidak boleh 0").then((result) => {
                        $(this).val("").trigger("change");
                    });
                    return false;
                }
                let Urut = button.hasClass("buttons-add")
                    ? null
                    : datatableExpand.row(button.closest("tr")).data().Urut;
                
                let KodeCustSupp = modal.find('input[name="KodeCustSupp"]').val();
                

                if(KodeCustSupp != undefined && KodeCustSupp != null && KodeCustSupp != ''){
                    $injectScript({
                        url: "accounting/kasbank-modal-hutang.js",
                        fn: "modalHutang",
                        args: [
                            KodeCustSupp,
                            response.res.NamaCustSupp,
                            modal.find('input[name="Debet"]').val(),
                            selectData,
                            row.data().NoBukti,
                            Urut,
                            row.data().Tanggal,
                            modal,
                        ],
                    });
                    return false;
                }
                $injectScript({
                    url: "accounting/kasbank-modal-customer.js",
                    fn: "modalCustomer",
                    args: [
                        modal,
                        selectData,
                        row.data().NoBukti,
                        Urut,
                        row.data().Tanggal,
                    ],
                    id: "kasbank-detail-modal",
                });
            } else if (selectData.Kode == "PT") {
                if (modal.find('input[name="Debet"]').val() == 0) {
                    alert("Jumlah tidak boleh 0").then((result) => {
                        $(this).val("").trigger("change");
                    });
                    return false;
                }
                let Urut = button.hasClass("buttons-add")
                    ? null
                    : datatableExpand.row(button.closest("tr")).data().Urut;
                
                let KodeCustSupp = modal.find('input[name="KodeCustSupp"]').val();
                

                if(KodeCustSupp != undefined && KodeCustSupp != null && KodeCustSupp != ''){
                    $injectScript({
                        url: "accounting/kasbank-modal-hutang.js",
                        fn: "modalHutang",
                        args: [
                            KodeCustSupp,
                            response.res.NamaCustSupp,
                            modal.find('input[name="Debet"]').val(),
                            selectData,
                            row.data().NoBukti,
                            Urut,
                            row.data().Tanggal,
                            modal,
                        ],
                    });
                    return false;
                }
                $injectScript({
                    url: "accounting/kasbank-modal-customer.js",
                    fn: "modalCustomer",
                    args: [
                        modal,
                        selectData,
                        row.data().NoBukti,
                        Urut,
                        row.data().Tanggal,
                    ],
                    id: "kasbank-detail-modal",
                });
            }
        });

        modal.on("hide.bs.modal", function () {
            $removeScript("accounting/kasbank-detail.js");

            parentmodalAddEditDetailKasbank = undefined;
        });

        applyPlugins(modal, [
            {
                element: "select[name='Valas']",
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-valas-select",
                defaultData:
                    Object.keys(response.res).length !== 0
                        ? [
                              {
                                  id: data.Valas,
                                  Description: data.Kurs,
                              },
                          ]
                        : [
                            {
                                id: "IDR",
                                Description: "1.00",
                            }
                        ],
            },
            {
                element: "select[name='Perkiraan']",
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path:
                    "/get-biaya-select?without=" +
                    row.data().PerkiraanHd +
                    "&posthutpiut=true" + 
                    (Object.keys(response.res).length !== 0 ? ("&perkiraan=" + data.Perkiraan) : ""),
                defaultData:
                    Object.keys(response.res).length !== 0
                        ? [
                              {
                                  id: data.Perkiraan,
                                  Description: data.KeteranganPerkiraan,
                                  Kode: data.KodeP,
                              },
                          ]
                        : undefined,
            },
            {
                element: ".mask-money",
                plugin: "maskMoney",
            },
        ]);

        if (Object.keys(response.res).length !== 0) {
            modal.find('select[name="TPHC"]').val(data.TPHC);
            modal.find('input[name="TipeTrans"]').val(data.TipeTrans);
            modal
                .find('input[name="Kurs_val"]')
                .val(data.Kurs)
                .maskMoney('mask').trigger("keyup");
            modal
                .find('input[name="Debet_val"]')
                .val(data.Debet)
                .maskMoney('mask').trigger("keyup");
            modal.find('input[name="Keterangan"]').val(data.Keterangan);
            modal.find('input[name="KodeBag"]').val(data.KodeBag);
            modal
                .find("form")
                .append(
                    `<input type="hidden" name="pelunasan" value="${modal
                        .find('input[name="Debet"]')
                        .val()}">`
                );
            modal
                .find("form")
                .append(
                    `<input type="hidden" name="KodeCustSupp" value="${data.CustSuppP}">`
                );
        }
        modal.find(".mask-money").maskMoney("mask").trigger("keyup");
        modal.find('input[name="Debet_val"]').focus();
        modal
            .find("#modalAddKasBankDetailLabel")
            .text("Detail Transaksi Kas/Bank - " + row.data().NoBukti);
    };
    getModal(options);
};
