import $globalVariable, {
    publicURL,
    defaultOptionDatatable,
} from "./base-function.js";

// Export $globalVariable to global scope for non-module scripts
window.$globalVariable = $globalVariable;
window.publicURL = publicURL;

// Create global Logout function for onclick compatibility
window.Logout = function() {
    if (typeof $globalVariable !== 'undefined' && $globalVariable.Logout) {
        $globalVariable.Logout();
    } else {
        console.error('$globalVariable.Logout not available');
    }
};

(function ($) {
    /* start modal setting */
    $(document).on("show.bs.modal", ".modal", function () {
        const zIndex = 1040 + 10 * $(".modal:visible").length;
        $(this).css("z-index", zIndex);
        let modalDialog = $(this).find(".modal-dialog");
        setTimeout(() => {
            $(".modal-backdrop")
                .not(".modal-stack")
                .css("z-index", zIndex - 1)
                .addClass("modal-stack")
                .addClass("" + (zIndex - 1));

            if ($(".modal-backdrop").length > 1) {
                $(".modal-backdrop." + (zIndex - 1)).remove();
            }
        }, 500);

        $(modalDialog).draggable({
            handle: ".header-draggable, .modal-footer",
        });
    });

    $(document).on(
        "hidden.bs.modal",
        ".modal",
        () =>
            $(".modal:visible").length &&
            $(document.body).addClass("modal-open")
    );

    $(document).on(
        "hidden.bs.modal",
        '#contentBody .modal[data-destroy="true"]',
        function () {
            $(this).remove();
        }
    );

    $(document).on("click", 'a[href="#gantiPassword"]', function (e) {
        e.preventDefault();
        $("#modalGantiPassword").modal("show");
    });

    $(document).on("submit", "#formGantiPassword", function (e) {
        e.preventDefault();
        $globalVariable.formAjax({
            form: $(this),
            modal: $("#modalGantiPassword"),
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                modal.modal("hide");
                form.get(0).reset();
                $globalVariable.baseSwal(
                    "success",
                    "Berhasil",
                    data.message,
                    "success"
                );
            },
        });
    });

    $(document).on("click", 'a[href="#setPeriode"]', function (e) {
        e.preventDefault();
        let modal = $("#modalSetPeriode");
        $globalVariable.baseAjax({
            url: publicURL + "/berkas/get-periode",
            type: "GET",
            successCallback: function (response) {
                modal.modal("show");
                modal.find('input[name="BULAN"]').val(response.BULAN);
                modal.find('input[name="TAHUN"]').val(response.TAHUN);
            },
        });
    });

    $(document).on("submit", "#formSetPeriode", function (e) {
        e.preventDefault();
        $globalVariable.formAjax({
            form: $(this),
            modal: $("#modalSetPeriode"),
            callbackSuccess: function (data, status, jqxhr, form, modal) {
                $globalVariable.baseSwal(
                    "success",
                    "Berhasil",
                    data.message,
                    "success"
                );
                modal.modal("hide");
                window.location.reload();
            },
        });
    });

    $(document).on("click", 'a[href="#logOut"]', function (e) {
        e.preventDefault();
        $globalVariable.Logout();
    });

    $(document).on("input", "input[type='number-text']", function () {
        if (this.hasAttribute("min-length")) {
            if (this.value.length < this.getAttribute("min-length")) {
                this.value = this.value.padStart(
                    this.getAttribute("min-length"),
                    "0"
                );
            }
        }
        if (this.hasAttribute("max-length")) {
            if (this.value.length > this.getAttribute("max-length")) {
                this.value = this.value.slice(
                    0,
                    this.getAttribute("max-length")
                );
            }
        }

        if (this.hasAttribute("decimal")) {
            this.value = this.value
                .replace(/[^0-9.]/g, "")
                .replace(/(\..*)\./g, "$1");
        } else {
            this.value = this.value.replace(/[^0-9]/g, "");
        }

        this.value = this.value.replace(/[^0-9,.-]/g, "");
    });

    $(document).on("click", "button[data-dismiss='toast']", function () {
        $(this).closest(".toast.fade.show").remove();
    });

    $(document).on("keyup", "input.mask-money", function (e) {
        let input = $(this);
        let form = input.closest("form");
        let row = input.closest(".row");
        let val = input.maskMoney("unmasked")[0];
        let name_input = input.attr("name");
        if(name_input == undefined){
            console.log(name_input, input);
        }
        name_input = name_input.replace("_val", "");
        if(form.length > 0){
            form.find(`input[name="${name_input}"]`).val(val).trigger("change");
        }else{
            row.find(`input[name="${name_input}"]`).val(val).trigger("change");
        }
    });

    $(document).on("select2:open", ".select2", function (e) {
        let select2 = $(this).data("select2");
        let dropdown = select2.dropdown.$dropdown;
        let search = dropdown.find(".select2-search__field");
        search.prop("placeholder", "Cari disini");
        document.querySelector(".select2-search__field").focus();
    });

    /** Event Datatable */
    $(document).on("click", ".showButton", function (event) {
        let parent = $(this).parent(".parentBtnRow");
        let row = parent.parent("tr");
        if (
            parent
                .find(".notification-container")
                .hasClass("close-button-container")
        ) {
            $(".closeButton").click();
            parent
                .find(".notification-container")
                .removeClass("close-button-container")
                .addClass("open-button-container")
                .show();
            $(this).removeClass("showButton").addClass("closeButton");
            $(this)
                .find("i")
                .removeClass("fa-arrow-alt-circle-left")
                .addClass("fa-arrow-alt-circle-right");
            row.addClass("row-opened-button");

            let rect = row[0].getBoundingClientRect();
            // if mouse position outside of row, then close button
            row[0].onmousemove = (e) => {
                if (
                    e.clientX < rect.left ||
                    e.clientX > rect.right ||
                    e.clientY < rect.top ||
                    e.clientY > rect.bottom
                ) {
                    $(".closeButton").click();
                }
            };
        }
        event.preventDefault();
    });

    $(document).on("click", ".closeButton", function (event) {
        let parent = $(this).parent(".parentBtnRow");
        let row = parent.parent("tr");
        if (
            parent
                .find(".notification-container")
                .hasClass("open-button-container")
        ) {
            parent
                .find(".notification-container")
                .removeClass("open-button-container")
                .addClass("close-button-container");
            let interval = setInterval(() => {
                parent.find(".notification-container").removeAttr("style");
                clearInterval(interval);
            }, 450);
            $(this).removeClass("closeButton").addClass("showButton");
            $(this)
                .find("i")
                .removeClass("fa-arrow-alt-circle-right")
                .addClass("fa-arrow-alt-circle-left");
            row.removeClass("row-opened-button");
        }
        event.preventDefault();
    });

    $(document).on("init.dt", function (e, settings) {
        var api = new $.fn.dataTable.Api(settings);
        let options = api.init();
        let tableID = api.table().node().id;
        let table = $(document).find(
            `#${tableID}_wrapper .dataTables_scrollBody`
        );
        let head = $(document).find(
            `#${tableID}_wrapper .dataTables_scrollHead`
        );
        let scrollTop = $(
            `<div class="wrapper_scroll_top ${tableID}_wrapper" style="width:100%"><div style="width:${table
                .find("table")
                .width()}px;zoom:${table
                .find("table")
                .css("zoom")}"></div></div>`
        );
        scrollTop.insertBefore(head);

        scrollTop.scroll(function () {
            table.scrollLeft(scrollTop.scrollLeft());
        });
        table.scroll(function () {
            scrollTop.scrollLeft(table.scrollLeft());
        });
    });

    $(document).on("change", ".left-fixed-input", function () {
        let elDataTable = $(this).closest(".dataTables_wrapper");
        let elHeader = elDataTable.find(".dataTables_scrollHead");
        let elBody = elDataTable.find(".dataTables_scrollBody");

        if ($(this).val() == 0) {
            elHeader.find("tr th:nth-child(1)").removeClass("dtfc-fixed-left");
            elHeader.find("tr th:nth-child(1)").css({ left: "", position: "" });
            elBody.find("tr td:nth-child(1)").removeClass("dtfc-fixed-left");
            elBody.find("tr td:nth-child(1)").css({ left: "", position: "" });
        }
        if ($(this).val() == 1) {
            if (
                !elHeader.find("tr th:nth-child(1)").hasClass("dtfc-fixed-left")
            ) {
                elHeader.find("tr th:nth-child(1)").addClass("dtfc-fixed-left");
                elBody.find("tr td:nth-child(1)").addClass("dtfc-fixed-left");
            }

            elHeader
                .find("tr th:nth-child(1)")
                .css({ left: 0, position: "sticky" });
            elBody
                .find("tr td:nth-child(1)")
                .css({ left: 0, position: "sticky" });

            if (
                elHeader.find("tr th:nth-child(2)").hasClass("dtfc-fixed-left")
            ) {
                elHeader
                    .find("tr th:nth-child(2)")
                    .removeClass("dtfc-fixed-left");
                elBody
                    .find("tr td:nth-child(2)")
                    .removeClass("dtfc-fixed-left");

                elHeader
                    .find("tr th:nth-child(2)")
                    .css({ left: 0, position: "" });
                elBody
                    .find("tr td:nth-child(2)")
                    .css({ left: 0, position: "" });
            }
        }
        if ($(this).val() == 2) {
            if (
                !elHeader.find("tr th:nth-child(1)").hasClass("dtfc-fixed-left")
            ) {
                elHeader.find("tr th:nth-child(1)").addClass("dtfc-fixed-left");
                elBody.find("tr td:nth-child(1)").addClass("dtfc-fixed-left");
            }
            if (
                !elHeader.find("tr th:nth-child(2)").hasClass("dtfc-fixed-left")
            ) {
                elHeader.find("tr th:nth-child(2)").addClass("dtfc-fixed-left");
                elBody.find("tr td:nth-child(2)").addClass("dtfc-fixed-left");
            }

            let headerColumnWidth = elHeader
                .find("tr th:nth-child(1)")
                .outerWidth();
            let bodyColumnWidth = elBody
                .find("tr td:nth-child(1)")
                .outerWidth();
            elHeader
                .find("tr th:nth-child(2)")
                .css({ left: headerColumnWidth, position: "sticky" });
            elBody
                .find("tr td:nth-child(2)")
                .css({ left: bodyColumnWidth, position: "sticky" });
        }
    });

    $(document).on("change", ".right-fixed-input", function () {
        let elDataTable = $(this).closest(".dataTables_wrapper");
        let elHeader = elDataTable.find(".dataTables_scrollHead");
        let elBody = elDataTable.find(".dataTables_scrollBody");

        if ($(this).val() == 0) {
            elHeader.find("tr th:last-child").removeClass("dtfc-fixed-right");
            elHeader.find("tr th:last-child").css({ right: "", position: "" });
            elBody.find("tr td:last-child").removeClass("dtfc-fixed-right");
            elBody.find("tr td:last-child").css({ right: "", position: "" });
        }
        if ($(this).val() == 1) {
            if (
                !elHeader.find("tr th:last-child").hasClass("dtfc-fixed-right")
            ) {
                elHeader.find("tr th:last-child").addClass("dtfc-fixed-right");
                elBody.find("tr td:last-child").addClass("dtfc-fixed-right");
            }

            elHeader
                .find("tr th:last-child")
                .css({ right: 0, position: "sticky" });
            elBody
                .find("tr td:last-child")
                .css({ right: 0, position: "sticky" });

            if (
                elHeader
                    .find("tr th:nth-last-child(2)")
                    .hasClass("dtfc-fixed-right")
            ) {
                elHeader
                    .find("tr th:nth-last-child(2)")
                    .removeClass("dtfc-fixed-right");
                elBody
                    .find("tr td:nth-last-child(2)")
                    .removeClass("dtfc-fixed-right");

                elHeader
                    .find("tr th:nth-last-child(2)")
                    .css({ right: 0, position: "" });
                elBody
                    .find("tr td:nth-last-child(2)")
                    .css({ right: 0, position: "" });
            }
        }
        if ($(this).val() == 2) {
            if (
                !elHeader.find("tr th:last-child").hasClass("dtfc-fixed-right")
            ) {
                elHeader.find("tr th:last-child").addClass("dtfc-fixed-right");
                elBody.find("tr td:last-child").addClass("dtfc-fixed-right");
            }
            if (
                !elHeader
                    .find("tr th:nth-last-child(2)")
                    .hasClass("dtfc-fixed-right")
            ) {
                elHeader
                    .find("tr th:nth-last-child(2)")
                    .addClass("dtfc-fixed-right");
                elBody
                    .find("tr td:nth-last-child(2)")
                    .addClass("dtfc-fixed-right");
            }

            let headerColumnWidth = elHeader
                .find("tr th:last-child")
                .outerWidth();
            let bodyColumnWidth = elBody.find("tr td:last-child").outerWidth();
            elHeader
                .find("tr th:nth-last-child(2)")
                .css({ right: headerColumnWidth, position: "sticky" });
            elBody
                .find("tr td:nth-last-child(2)")
                .css({ right: bodyColumnWidth, position: "sticky" });
        }
    });
})(jQuery);
