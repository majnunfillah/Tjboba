function parentmodalHutang(
    baseSwal,
    baseAjax,
    formAjax,
    getModal,
    globalDelete,
    applyPlugins,
    mergeWithDefaultOptions,
    swalConfirm,
    publicURL,
    csfr_token
) {
    this.baseSwal = baseSwal;
    this.baseAjax = baseAjax;
    this.formAjax = formAjax;
    this.getModal = getModal;
    this.globalDelete = globalDelete;
    this.applyPlugins = applyPlugins;
    this.mergeWithDefaultOptions = mergeWithDefaultOptions;
    this.swalConfirm = swalConfirm;
    this.publicURL = publicURL;
    this.csfr_token = csfr_token;
}

parentmodalHutang.prototype.modalHutang = function (
    kode, // kode customer
    nama, // nama customer
    Debet,
    dataSelect,
    NoBukti,
    Urut,
    Tanggal,
    modalKasbank // modal kasbank
) {
    const {
        baseSwal,
        baseAjax,
        formAjax,
        getModal,
        globalDelete,
        applyPlugins,
        mergeWithDefaultOptions,
        swalConfirm,
        publicURL,
        csfr_token,
    } = this;
    
    const options = {};
    options.data = {
        resource: "components.accounting.kasbank.modal-hutang",
        modalId: "modalPelunasanHutang",
        modalTitle: dataSelect.Kode == 'HT' ? "Pelunasan Hutang" : "Penambahan Piutang",
        modalWidth: "fullscreen",
    };

    options.callback = function (response, modal) {
        modal.find("h4.kodeCustomer").text(kode);
        modal.find("h4.namaCustomer").text(nama);
        modal.find("h4.NoBukti").text(NoBukti);
        modal.find("h4.Perkiraan").text(`${dataSelect.text}`);
        modal
            .find('button[data-dismiss="modal"]')
            .attr("data-dismiss", "modal-dismiss");
        applyPlugins(modal, [
            {
                element: ".mask-money",
                plugin: "maskMoney",
            },
        ]);
        modal
            .find("input[name='DebetPelunasan_val']")
            .val(`${Debet}`)
            .maskMoney("mask")
            .trigger("keyup");
    
        let datatableHutang = modal.find("table").DataTable({
            ...mergeWithDefaultOptions({
                $defaultOpt: {
                    buttons: ["refresh"],
                },
            }),
            scrollY: "500px",
            ajax: {
                url: modal.find("table").data("server"),
                type: "POST",
                data: {
                    _token: csfr_token,
                    kode: kode,
                    NoBukti: NoBukti,
                    Urut: Urut,
                    Lawan: dataSelect.Kode,
                },
            },
            paging: false,
            columns: [
                { data: "NoFaktur" },
                { data: "NoRetur" },
                { data: "Tanggal" },
                { data: "JatuhTempo" },
                { data: "NOSO" },
                { data: "DebetRp", className: "text-right" },
                { data: "KreditRp", className: "text-right" },
                { data: "SaldoRp", className: "text-right" },
                { data: "Valas" },
                { data: "KursRp", className: "text-right" },
                { data: "DebetDRp", className: "text-right" },
                { data: "KreditDRp", className: "text-right" },
                { data: "JumlahSaldoRp", className: "text-right" },
                {
                    data: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center parentBtnRow",
                    render: function (data, type, row, meta) {
                        if(data != 'only-read') {
                            return data;
                        }else{
                            return '';
                        }
                    }
                },
            ],
            fnRowCallback: function (
                nRow,
                aData,
                iDisplayIndex,
                iDisplayIndexFull
            ) {
                if (aData.NoBukti == NoBukti  && aData.action != 'only-read') {
                    $(nRow).addClass("redClass");
                }
            },
            footerCallback: function (tfoot, data, start, end, display) {
                var api = this.api();
                let foot5 = 0.0;
                for (let i = 0; i < api.column(5).data().length; i++) {
                    let text = api.column(5).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot5 += parseFloat(text);
                }
    
                // format ro currency
                foot5 = foot5.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
                let foot6 = 0.0;
                for (let i = 0; i < api.column(6).data().length; i++) {
                    let text = api.column(6).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot6 += parseFloat(text);
                }
    
                // format ro currency
                foot6 = foot6.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
    
                let foot7 = 0.0;
                for (let i = 0; i < api.column(7).data().length; i++) {
                    let text = api.column(7).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot7 += parseFloat(text);
                }
    
                // format ro currency
                foot7 = foot7.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
    
                let foot10 = 0.0;
                for (let i = 0; i < api.column(10).data().length; i++) {
                    let text = api.column(10).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot10 += parseFloat(text);
                }
    
                // format ro currency
                foot10 = foot10.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
    
                let foot11 = 0.0;
                for (let i = 0; i < api.column(11).data().length; i++) {
                    let text = api.column(11).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot11 += parseFloat(text);
                }
    
                // format ro currency
                foot11 = foot11.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
    
                let foot12 = 0.0;
                for (let i = 0; i < api.column(12).data().length; i++) {
                    let text = api.column(12).data()[i];
                    text = text.replaceAll(".", "").replaceAll(",", ".");
                    foot12 += parseFloat(text);
                }
    
                // format ro currency
                foot12 = foot12.toLocaleString("id-ID", {
                    style: "decimal",
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
    
                $(tfoot).find("th").eq(1).html(foot5);
                $(tfoot).find("th").eq(2).html(foot6);
                $(tfoot).find("th").eq(3).html(foot7);
    
                $(tfoot).find("th").eq(5).html(foot10);
                $(tfoot).find("th").eq(6).html(foot11);
                $(tfoot).find("th").eq(7).html(foot12);
            },
        });
    
        modal.on("xhr.dt", function (e, settings, json, xhr) {
            hitungSisaSaldo(json, modal, NoBukti, dataSelect);
        });
    
        modal.on("click", ".btnBayarHutang", function (e) {
            let btn = $(this);
            let data = datatableHutang.row(btn.closest("tr")).data();
            let tanggal_ = formatDateInput(Tanggal);
            let tanggalTempo = formatDateInput(data.JatuhTempo);
            let sisaSaldo = modal.find('input[name="SisaPelunasan"]').val();
    
            if (sisaSaldo == 0 || sisaSaldo == "" || sisaSaldo == 0.0) {
                alert("Anda tidak bisa melakukan pelunasan karena saldo sudah 0");
                return false;
            }
    
            if (modal.find("#contentPelunasan").hasClass("d-none")) {
                modal.find("#contentPelunasan").removeClass("d-none");
            }
            modal.find("input[name=NoFaktur]").val(data.NoFaktur);
            modal.find("input[name=Tanggal]").val(tanggal_);
            modal.find("input[name=JatuhTempo]").val(tanggalTempo);
    
            if (data.sisa <= sisaSaldo) {
                modal
                    .find("input[name=Debet_val]")
                    .val(data.sisa)
                    .maskMoney("mask")
                    .trigger("keyup");
            } else {
                modal
                    .find("input[name=Debet_val]")
                    .val(sisaSaldo)
                    .maskMoney("mask")
                    .trigger("keyup");
            }
    
            modal.find("input[name=Catatan]").val("");
    
            applyPlugins(modal, [
                {
                    element: ".mask-money",
                    plugin: "maskMoney",
                },
            ]);
    
            $(modal)
                .stop()
                .animate(
                    {
                        scrollTop:
                            modal.find("#contentPelunasan").offset().top +
                            $(modal).scrollTop() -
                            $(modal).offset().top,
                    },
                    1000
                );
        });
    
        modal.on("click", ".btnHapusHutang", function (e) {
            let btn = $(this);
            let data = datatableHutang.row(btn.closest("tr")).data();
            swalConfirm({
                title: "Apakah Anda yakin?",
                text: "ingin menghapus pelunasan hutang " + data.DebetRp + " ?",
                callback: function () {
                    baseAjax({
                        url:
                            publicURL +
                            "/accounting/transaksi-bank-or-kas/hapus-pelunasan",
                        type: "POST",
                        param: {
                            NoBukti: NoBukti,
                            NoFaktur: data.NoFaktur,
                            NoMsk: Urut,
                            kode: kode,
                            Urut: data.Urut,
                        },
                        successCallback: function (res) {
                            alert("Data berhasil dihapus");
                            datatableHutang.ajax.reload();
                        },
                    });
                },
                callbackDismiss: function () {
                    return false;
                },
            });
        });
    
        modal.on("click", ".batal-hutang", function (e) {
            // smootscroll
            if (!modal.find("#contentPelunasan").hasClass("d-none")) {
                modal.find("#contentPelunasan").addClass("d-none");
            }
            $(modal).animate(
                {
                    scrollTop: modal.find(".card-body").offset().top,
                },
                1000
            );
        });
    
        modal.on("change", 'input[name="KreditPelunasan"]', function (e) {
            let DebetPelunasan = modal.find('input[name="DebetPelunasan"]').val();
            let KreditPelunasan = modal.find('input[name="KreditPelunasan"]').val();
            let sisa = parseFloat(DebetPelunasan) - parseFloat(KreditPelunasan);
    
            modal
                .find('input[name="SisaPelunasan_val"]')
                .val(sisa)
                .maskMoney("mask")
                .trigger("keyup");
        });
    
        modal.on("click", 'button[data-dismiss="modal-dismiss"]', function (e) {
            let DebetPelunasan = modal.find('input[name="DebetPelunasan"]').val();
            let KreditPelunasan = modal.find('input[name="KreditPelunasan"]').val();
    
            let sisa = parseFloat(DebetPelunasan) - parseFloat(KreditPelunasan);
    
            if (sisa != 0 && sisa != 0.0 && sisa != parseFloat(DebetPelunasan)) {
                swalConfirm({
                    title: "Apakah Anda yakin?",
                    text: "Sisa saldo masih tersedia. Jika Anda keluar, data yang sudah diinput akan hilang",
                    callback: function () {
                        baseAjax({
                            url:
                                publicURL +
                                "/accounting/transaksi-bank-or-kas/hapus-pelunasan",
                            type: "POST",
                            param: {
                                NoBukti: NoBukti,
                                NoMsk: Urut,
                                kode: kode,
                                deleteAll: true,
                            },
                            successCallback: function (res) {
                                modal.modal("hide");
                                $removeScript("accounting/kasbank-modal-hutang.js");
    
                                if (typeof parentmodalHutang !== "undefined") {
                                    parentmodalHutang = undefined;
                                }
    
                                alert("Data berhasil dihapus");
                            },
                        });
                    },
                    callbackDismiss: function () {
                        return false;
                    },
                });
            } else {
                if (modalKasbank.find('form input[name="pelunasan"]').length > 0) {
                    modalKasbank
                        .find('form input[name="pelunasan"]')
                        .val(KreditPelunasan);

                    if(sisa != parseFloat(DebetPelunasan)) {
                        modalKasbank.find('form input[name="KodeCustSupp"]').val(kode);
                    } else {
                        modalKasbank.find('form input[name="KodeCustSupp"]').val('');
                    }
                } else {
                    console.log(sisa, DebetPelunasan, 'asda');
                    modalKasbank
                        .find("form")
                        .append(
                            '<input type="hidden" name="pelunasan" value="' +
                                KreditPelunasan +
                                '">'
                        );
                    if(sisa != parseFloat(DebetPelunasan)) {
                        modalKasbank
                            .find("form")
                            .append(
                                '<input type="hidden" name="KodeCustSupp" value="' +
                                    kode +
                                    '">'
                            );
                    } else {
                        modalKasbank
                            .find("form")
                            .append(
                                '<input type="hidden" name="KodeCustSupp" value="">'
                            );
                    }
                }
    
                modal.modal("hide");
                $removeScript("accounting/kasbank-modal-hutang.js");
    
                if (typeof parentmodalHutang !== "undefined") {
                    parentmodalHutang = undefined;
                }
            }
        });
    
        modal.on("submit", "#formPelunasan", function (e) {
            e.preventDefault();
            $(this).attr(
                "action",
                publicURL + "/accounting/transaksi-bank-or-kas/pelunasan-hutang"
            );
            formAjax({
                form: $(this),
                param: {
                    NoBukti: NoBukti,
                    NoMsk: Urut,
                    kode: kode,
                    perkiraan: dataSelect.id,
                    KodePerkiraan: dataSelect.Kode,
                },
                callbackSerialize: function ($form, option) {
                    $form.attr(
                        "action",
                        publicURL +
                            "/accounting/transaksi-bank-or-kas/pelunasan-hutang"
                    );
    
                    let sisaSaldo = modal.find('input[name="SisaPelunasan"]').val();
                    let jumlah = modal.find("input[name=Debet]").val();
    
                    if (parseFloat(jumlah) > parseFloat(sisaSaldo)) {
                        alert("Jumlah tidak boleh melebihi sisa saldo");
                        return false;
                    } else if (
                        parseFloat(jumlah) == 0 ||
                        parseFloat(jumlah) == 0.0
                    ) {
                        alert("Jumlah Harus Diisi");
                        return false;
                    } else if (modal.find("input[name=Debet]").val() < 0) {
                        alert("Jumlah tidak boleh kurang dari 0");
                        return false;
                    }
                    return true;
                },
                callbackSuccess: function (response) {
                    if (!modal.find("#contentPelunasan").hasClass("d-none")) {
                        modal.find("#contentPelunasan").addClass("d-none");
                    }
                    $(modal).animate(
                        {
                            scrollTop: modal.find(".card-body").offset().top,
                        },
                        1000
                    );
    
                    datatableHutang.ajax.reload();
                },
            });
        });

    };

    getModal(options);

};

hitungSisaSaldo = function (json, modal, NoBukti, dataSelect) {
    let KreditPelunasan = 0;
    if(json == null){
        return false;
    }
    for (const data of json.data) {
        if (data.NoBukti == NoBukti && data.action != 'only-read') {
            if(dataSelect.Kode == 'HT') {
                KreditPelunasan += parseFloat(data.Debet);
            } else if (dataSelect.Kode == 'PT') {
                console.log(data);
                KreditPelunasan += parseFloat(data.Debet);
            }
        }
    }
    modal
        .find("input[name='KreditPelunasan_val']")
        .val(KreditPelunasan)
        .maskMoney("mask")
        .trigger("keyup");

    return true;
};
