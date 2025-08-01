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
    modalMemorial // modal memorial
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
        resource: "components.accounting.memorial.modal-hutang",
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
    
                $(api.column(5).footer()).html(foot5);
                $(api.column(6).footer()).html(foot6);
                $(api.column(7).footer()).html(foot7);
                $(api.column(10).footer()).html(foot10);
                $(api.column(11).footer()).html(foot11);
                $(api.column(12).footer()).html(foot12);
            },
            initComplete: function (settings, json) {
                // do nothing
            },
        });
    
        // pelunasan form submit
        modal.on("submit", ".formPelunasan", function (e) {
            e.preventDefault();
            formAjax({
                form: $(this),
                callbackSerialize: function ($form, option) {
                    // validation pelunasan must not be empty
                    let pelunasan = $form.find('input[name="pelunasan"]').val();
                    let Debet = $form
                        .find('input[name="DebetPelunasan_val"]')
                        .val();
                    // remove dots and replace comma with dots
                    pelunasan = pelunasan.replaceAll(".", "").replaceAll(",", ".");
                    Debet = Debet.replaceAll(".", "").replaceAll(",", ".");
                    if (pelunasan == "" || pelunasan == null || pelunasan == undefined) {
                        alert("Pelunasan harus diisi");
                        return false;
                    }
    
                    if (parseFloat(pelunasan) <= 0) {
                        alert("Pelunasan harus lebih dari 0");
                        return false;
                    }
    
                    if (parseFloat(pelunasan) > parseFloat(Debet)) {
                        alert("Pelunasan tidak boleh lebih dari debet");
                        return false;
                    }
    
                    return true;
                },
                callbackSuccess: function (response, status, xhr) {
                    datatableHutang.ajax.reload();
                    baseSwal("success", "Berhasil", response.message);
                },
            });
        });
    
        // hapus pelunasan
        modal.on("click", ".deleteHutang", function (e) {
            e.preventDefault();
    
            let btn = $(this);
            let data = datatableHutang.row(btn.closest("tr")).data();
            let deleteAll = btn.data("all");
    
            swalConfirm().then((isDelete) => {
                if (isDelete) {
                    baseAjax({
                        url: publicURL + "/accounting/transaksi-memorial/hapus-pelunasan",
                        type: "POST",
                        data: {
                            _token: csfr_token,
                            NoBukti: data.NoBukti,
                            NoFaktur: data.NoFaktur,
                            kode: data.KodeCustSupp,
                            NoMsk: data.NoMsk,
                            Urut: data.Urut,
                            deleteAll: deleteAll,
                        },
                        callbackSuccess: function (response) {
                            datatableHutang.ajax.reload();
                            baseSwal("success", "Berhasil", response.message);
                        },
                    });
                }
            });
        });
    
        modal.on("click", ".batalPelunasan", function (e) {
            modal.find(".formPelunasan")[0].reset();
            modal.find(".formPelunasan").find("input").val("");
        });
    
        modal.on("click", ".modalHutangPelunasan", function (e) {
            e.preventDefault();
            let datarow = datatableHutang.row($(this).closest("tr")).data();
            modal.find('input[name="NoFaktur"]').val(datarow.NoFaktur);
            modal.find('input[name="kode"]').val(datarow.KodeCustSupp);
            modal.find('input[name="NoMsk"]').val(Urut);
            modal.find('input[name="NoBukti"]').val(NoBukti);
            modal.find('input[name="Tanggal"]').val(Tanggal);
            modal.find('input[name="perkiraan"]').val(datarow.Perkiraan);
            modal.find('input[name="KodePerkiraan"]').val(dataSelect.Kode);
            modal.find("h6.JatuhTempo").text(datarow.JatuhTempo);
            modal.find("h6.NoInvoice").text(datarow.NoInvoice);
            modal.find("h6.Catatan").text(datarow.Catatan);
            modal.find("h6.TotalSaldoRp").text(datarow.SaldoRp);
            modal.find('textarea[name="Catatan"]').val(`Pembayaran ${datarow.NoInvoice}`);
        });
    
        modal.on("keyup", 'input[name="pelunasan"]', function (e) {
            let val = $(this).val().replaceAll(".", "").replaceAll(",", ".");
            let Debet = modal
                .find('input[name="DebetPelunasan_val"]')
                .val()
                .replaceAll(".", "")
                .replaceAll(",", ".");
            
            // validation
            if (parseFloat(val) > parseFloat(Debet)) {
                $(this).val("");
                $(this).maskMoney("mask");
                alert("Pelunasan tidak boleh lebih dari debet");
                return false;
            }
        });
    
        modal.on("hide.bs.modal", function () {
            modalMemorial.find('select[name="Perkiraan"]').val("").trigger("change");
            modalMemorial.find('input[name="KodeCustSupp"]').val("");
            modalMemorial.find('input[name="NamaCustSupp"]').val("");
        });

        modal.on("modal-dismiss", function () {
            $removeScript("accounting/memorial-modal-hutang.js");
            parentmodalHutang = undefined;
        });
    };
    
    getModal(options);
}; 