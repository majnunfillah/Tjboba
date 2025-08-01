function parentmodalCustomer(getModal, applyPlugins, mergeWithDefaultOptions) {
    this.getModal = getModal;
    this.applyPlugins = applyPlugins;
    this.mergeWithDefaultOptions = mergeWithDefaultOptions;
}

parentmodalCustomer.prototype.modalCustomer = function (
    parentModal,
    dataSelect,
    NoBukti,
    Urut,
    Tanggal
) {
    let Debet = parentModal.find('input[name="Debet"]').val();

    const options = {};
    const { getModal, applyPlugins, mergeWithDefaultOptions } = this;
    options.data = {
        resource: "components.global.modal-customer",
        modalId: "modalPilihCustomer",
        modalWidth: "xl",
        modalParams: {
            JENIS: dataSelect.Kode,
        },
    };
    options.callback = function (response, modal) {
        var datatableCustomer = modal.find("table").DataTable({
            ...mergeWithDefaultOptions({
                $defaultOpt: {
                    buttons: ["refresh"],
                },
            }),
            ajax: {
                url: modal.find("table").data("server"),
            },
            columns: [
                { data: "KODECUSTSUPP" },
                { data: "NAMACUSTSUPP" },
                { data: "ALAMAT1" },
                { data: "Kota" },
                {
                    data: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center parentBtnRow",
                },
            ],
        });

        modal.on("click", ".btnPilihCustomer", function (e) {
            let btn = $(this);
            let data = datatableCustomer.row(btn.closest("tr")).data();
            let kode = data.KODECUSTSUPP;
            let nama = data.NAMACUSTSUPP;

            $injectScript({
                url: "accounting/kasbank-modal-hutang.js",
                fn: "modalHutang",
                args: [
                    kode,
                    nama,
                    Debet,
                    dataSelect,
                    NoBukti,
                    Urut,
                    Tanggal,
                    parentModal,
                ],
            });

            modal.modal("hide");
        });

        modal.on("hide.bs.modal", function () {
            $removeScript("accounting/kasbank-modal-customer.js");
            parentmodalCustomer = undefined;
        });
        
        modal.on('xhr.dt', function (e, settings, json, xhr) {
            setTimeout(() => {
                modal.find('.dataTables_filter input').focus();
            }, 500);
                
        });
    };
    getModal(options);
};
