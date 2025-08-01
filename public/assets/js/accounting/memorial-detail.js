function parentmodalAddEditDetailMemorial(applyPlugins, getModal, formAjax, baseSwal, publicURL) {
    this.applyPlugins = applyPlugins;
    this.getModal = getModal;
    this.publicURL = publicURL;
    this.formAjax = formAjax;
    this.baseSwal = baseSwal;
}

parentmodalAddEditDetailMemorial.prototype.modalAddEditDetailMemorial = function (
    row,
    child,
    datatableExpand,
    button
) {
    const options = {};
    const { applyPlugins, getModal, formAjax, baseSwal, publicURL } = this;
    options.data = {
        resource: "components.accounting.memorial.modal-insert-detail",
        modalId: "modalAddMemorialDetail",
        formId: "formAddMemorialDetail",
        modalWidth: "lg",
        url: publicURL + "/accounting/transaksi-memorial/memorial-detail",
        fnData: {
            class: "\\MemorialController",
            function: "getDetailMemorialByNoBukti",
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
        let data = response.res || {};
        modal.find('input[name="Kurs_val"]').val(1.00).maskMoney('mask').trigger("keyup");

        // Populate form fields jika ada data (untuk edit)
        if (Object.keys(data).length !== 0) {
            // Populate Keterangan
            modal.find('input[name="Keterangan"]').val(data.Keterangan || '');
            
            // Populate No SPK (KodeBag)
            modal.find('input[name="KodeBag"]').val(data.KodeBag || '');
            
            // Populate Jumlah (Debet_val)
            if (data.Debet) {
                modal.find('input[name="Debet_val"]').val(parseFloat(data.Debet || 0).toFixed(2)).maskMoney('mask');
                modal.find('input[name="Debet"]').val(data.Debet || 0);
            }
            
            // Populate Kurs jika ada
            if (data.Kurs) {
                modal.find('input[name="Kurs_val"]').val(parseFloat(data.Kurs || 1).toFixed(2)).maskMoney('mask');
                modal.find('input[name="Kurs"]').val(data.Kurs || 1);
            }
                 }

        // 1. Pastikan applyPlugins dieksekusi terlebih dahulu
        applyPlugins(modal, [
            {
                element: "select[name='Valas']",
                plugin: "select2-search", 
                ajax: "setSelectAjax",
                path: "/get-valas-select", // Hapus publicURL
                formatResult: function(item) { // Tambahkan format
                    return item.id + " - " + item.Description;
                },
                formatSelection: function(item) {
                    return item.id; 
                },
                defaultData:
                    Object.keys(response.res || {}).length !== 0
                        ? [
                              {
                                  id: data.Valas || "IDR",
                                  Description: data.Kurs || "1.00",
                                  text: (data.Valas || "IDR") + " - " + (data.Kurs || "1.00")
                              },
                          ]
                        : [
                            {
                                id: "IDR",
                                Description: "1.00",
                                text: "IDR - 1.00"
                            }
                        ],
            },
            {
                element: "select[name='Perkiraan']",
                plugin: "select2-search",
                ajax: "setSelectAjax",
                path: "/get-biaya-select?posthutpiut=true",
                defaultData:
                    Object.keys(response.res || {}).length !== 0
                        ? [
                              {
                                  id: data.Perkiraan || '',
                                  Description: data.KeteranganPerkiraan || '',
                                  Kode: data.KodeP || ''
                              }
                          ]
                        : undefined
            },
            {
                element: ".mask-money",
                plugin: "maskMoney",
            },
        ]);

        // Then add event handler for Perkiraan change BEFORE applying Lawan plugin
        modal.on("change", 'select[name="Perkiraan"]', function() {
            // Get current value
            let perkiraanVal = $(this).val() || '';
            
            // Destroy and rebuild Lawan
            if (modal.find('select[name="Lawan"]').data('select2')) {
                modal.find('select[name="Lawan"]').select2('destroy');
            }
            
            // Apply new configuration with current Perkiraan value
            applyPlugins(modal, [{
                element: "select[name='Lawan']",
                plugin: "select2-search",
                ajax: "setSelectAjax", 
                path: "/get-biaya-select?posthutpiut=true&without=" + perkiraanVal,
                defaultData: [] // Start with empty selection when Perkiraan changes
            }]);
        });

        // Finally, initialize Lawan with current Perkiraan value
        let initialPerkiraanVal = modal.find('select[name="Perkiraan"]').val() || '';
        applyPlugins(modal, [{
            element: "select[name='Lawan']",
            plugin: "select2-search",
            ajax: "setSelectAjax", 
            path: "/get-biaya-select?posthutpiut=true&without=" + initialPerkiraanVal,
            defaultData:
                Object.keys(response.res || {}).length !== 0
                ? [
                    {
                        id: data.Lawan || '',
                        Description: data.KeteranganLawan || '',
                        Kode: data.KodeL || ''
                    }
                ]
                : undefined
        }]);

        // 2. Baru kemudian pasang event handler setelah plugin diterapkan
        modal.on("change", 'select[name="Valas"]', function () {
            let selectedData = $(this).select2("data")[0];
            if (selectedData) {
                modal
                    .find('input[name="Kurs_val"]')
                    .val(selectedData.Description || selectedData.Kurs || "1.00")
                    .maskMoney("mask")
                    .trigger("keyup");
            }
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

                    // Validasi untuk perkiraan HT/PT
                    let select = $($form)
                        .find('select[name="Perkiraan"]')
                        .select2("data")[0];
                    if (select && select.Kode == "HT") {
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

                    // Validasi untuk lawan HT/PT
                    let selectLawan = $($form)
                        .find('select[name="Lawan"]')
                        .select2("data")[0];
                    if (selectLawan && selectLawan.Kode == "HT") {
                        let pelunasan = $($form)
                            .find('input[name="pelunasan"]')
                            .val();
                        let Kredit = $($form).find('input[name="Kredit"]').val();

                        if (
                            pelunasan == "" ||
                            pelunasan == null ||
                            pelunasan == undefined
                        ) {
                            alert("Lawan Hutang harus melunasi hutang");
                            return false;
                        }

                        if (pelunasan < Kredit) {
                            alert(
                                "Jumlah pelunasan tidak boleh kurang dari jumlah Kredit"
                            );
                            return false;
                        }

                        if (pelunasan > Kredit) {
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

            let data = $(this).select2("data")[0];
            if (data.Kode == "HT") {
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
                        url: "accounting/memorial-modal-hutang.js",
                        fn: "modalHutang",
                        args: [
                            KodeCustSupp,
                            response.res.NamaCustSupp,
                            modal.find('input[name="Debet"]').val(),
                            data,
                            row.data().NoBukti,
                            Urut,
                            row.data().Tanggal,
                            modal,
                        ],
                    });
                    return false;
                }
                $injectScript({
                    url: "accounting/memorial-modal-customer.js",
                    fn: "modalCustomer",
                    args: [
                        modal,
                        data,
                        row.data().NoBukti,
                        Urut,
                        row.data().Tanggal,
                    ],
                    id: "memorial-detail-modal",
                });
            } else if (data.Kode == "PT") {
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
                        url: "accounting/memorial-modal-hutang.js",
                        fn: "modalHutang",
                        args: [
                            KodeCustSupp,
                            response.res.NamaCustSupp,
                            modal.find('input[name="Debet"]').val(),
                            data,
                            row.data().NoBukti,
                            Urut,
                            row.data().Tanggal,
                            modal,
                        ],
                    });
                    return false;
                }
                $injectScript({
                    url: "accounting/memorial-modal-customer.js",
                    fn: "modalCustomer",
                    args: [
                        modal,
                        data,
                        row.data().NoBukti,
                        Urut,
                        row.data().Tanggal,
                    ],
                    id: "memorial-detail-modal",
                });
            }
        });

        modal.on("hide.bs.modal", function () {
            $removeScript("accounting/memorial-detail.js");

            parentmodalAddEditDetailMemorial = undefined;
        });

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
            .find("#modalAddMemorialDetailLabel")
            .text("Detail Transaksi Memorial - " + row.data().NoBukti);
        
        // Pastikan nilai Kurs_val terisi benar berdasarkan nilai default Valas
        let valasData = modal.find('select[name="Valas"]').select2("data")[0];
        if (valasData) {
            modal
                .find('input[name="Kurs_val"]')
                .val(valasData.Description || "1.00")
                .maskMoney("mask")
                .trigger("keyup");
        }
    };
    getModal(options);
};
