var _isRequest = false;
const publicURL = $( 'meta[name="base_url"]' ).attr( "content" ),
    csfr_token = $( 'meta[name="csrf-token"]' ).attr( "content" );

const defaultOptionDatatable = {
    dom:
        "<'row'<'col button-table'B><'col-auto row'lf>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col'i><'col'p>>",
    scrollX: "100%",
    processing: true,
    serverSide: true,
    searchDelay: 1000,
    destroy: true,
    // stateSave: true,
    // stateSaveCallback: (settings, data) => {
    //     let tableId = settings.nTable.id;
    //     if(!tableId) {
    //         // console.warn(`DataTable is missing an ID; cannot save its state`);
    //         return;
    //     }
    //     history.replaceState(_.set(['datatables', tableId], data, history.state), '');
    // },
    // stateLoadCallback: settings => {
    //     let tableId = settings.nTable.id;
    //     if(!tableId) {
    //         console.warn(`DataTable is missing an ID; cannot load its state`);
    //         return;
    //     }
    //     return _.get(['datatables', tableId], history.state) || null;
    // },
    fixedColumns: {
        leftColumns: 1,
        rightColumns: 1,
    },
    columnDefs: [
        {
            targets: "_all",
            defaultContent: '<div class="text-center align-middle">-</div>',
        },
    ],
    buttons: [
        {
            html: '<button class="btn btn-success btn-sm mr-2 buttons-add" ><i class="fa fa-plus mr-2"></i>Tambah 1</button>',
            $keyButton: "tambah",
            onRender: function ( options = {} ) {
                const { id, className } = options;
                return `<button class="btn btn-success btn-sm mr-2 buttons-add ${ className || ""
                    }" ${ id ? 'id="' + id + "'" : ""
                    } ><i class="fa fa-plus mr-2"></i>Tambah 2</button>`;
            },
        },
        {
            extend: "colvis",
            className: "btn btn-secondary btn-sm mr-2",
            title: "Show/Hide Column",
            text: "<i class='fa fa-eye mr-2'></i>Column",
            $keyButton: "colvis",
        },
        {
            html: `<div><input type="number" min="0" max="2" value="1" class="left-fixed-input"style="width:50px" /><input type="number" min="0" max="2" value="1" class="right-fixed-input" style="width:50px" /></div>`,
            $keyButton: "flexiblefixed",
        },
        {
            text: "<i class='fa fa-sync mr-2'></i>Refresh",
            className: "btn btn-primary btn-sm mr-2 buttons-refresh",
            action: function () {
                this.ajax.reload();
            },
            init: function ( api, node, config ) {
                $( node ).removeClass( "btn-secondary" );
            },
            $keyButton: "refresh",
        },
        {
            html: `
                <button type="button" class="btn btn-default btn-sm">Export</button>
                <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon mr-2" data-toggle="dropdown">
                <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu" role="menu">
                    <a class="dropdown-item btn-export-excel" href="#"><i class='fa fa-file-excel mr-2'></i>Expot Excel</a>
                    <a class="dropdown-item btn-export-pdf" href="#"><i class='fa fa-file-pdf mr-2'></i>Expot PDF</a>
                </div>
            `,
            $keyButton: "excel-pdf",
            onRender: function ( options = {} ) {
                const { className } = options;
                return `
                <button type="button" class="btn btn-default btn-sm">Export</button>
                <button type="button" class="btn btn-default btn-sm dropdown-toggle dropdown-icon mr-2" data-toggle="dropdown">
                <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu" role="menu">
                    <a class="dropdown-item btn-export-excel ${ className || ""
                    }" href="#"><i class='fa fa-file-excel mr-2'></i>Expot Excel</a>
                    <a class="dropdown-item btn-export-pdf ${ className || ""
                    }" href="#"><i class='fa fa-file-pdf mr-2'></i>Expot PDF</a>
                </div>
            `;
            },
            init: function ( api, node, config ) {
                $( document ).on( "click", ".btn-export-excel", function ( e ) {
                    e.preventDefault();
                    let dtButtons = $( this ).closest( ".dt-buttons.btn-group" );
                    dtButtons
                        .find( ".buttons-excel.buttons-html5" )
                        .trigger( "click" );
                } );

                $( document ).on( "click", ".btn-export-pdf", function ( e ) {
                    e.preventDefault();
                    let dtButtons = $( this ).closest( ".dt-buttons.btn-group" );
                    dtButtons
                        .find( ".buttons-pdf.buttons-html5" )
                        .trigger( "click" );
                } );
            },
        },
        {
            extend: "excel",
            className: "btn btn-success btn-sm mr-2 d-none",
            title: "Expot Excel",
            text: "<i class='fa fa-file-excel mr-2'></i>Expot Excel",
            exportOptions: {
                columns: [ 1, 2 ],
            },
            action: function ( e, dt, node, config ) {
                $globalVariable.newExportAction( this, e, dt, node, config );
                return;
            },
            $keyButton: "excel",
        },
        {
            extend: "print",
            className: "btn btn-success btn-sm mr-2 d-none",
            title: "Expot PDF",
            text: "<i class='fa fa-file-print mr-2'></i>Expot PDF",
            exportOptions: {
                columns: [ 1, 2 ],
            },
            action: function ( e, dt, node, config ) {
                $globalVariable.newExportAction( this, e, dt, node, config );
                return;
            },
            $keyButton: "print",
        },
        {
            extend: "pdf",
            className: "btn btn-success btn-sm mr-2 d-none",
            title: "Expot PDF",
            text: "<i class='fa fa-file-pdf mr-2'></i>Expot PDF",
            exportOptions: {
                columns: [ 1, 2 ],
            },
            action: function ( e, dt, node, config ) {
                $globalVariable.newExportAction( this, e, dt, node, config );
                return;
            },
            $keyButton: "pdf",
        },
    ],
    initComplete: function ( settings, json ) {
        var api = new $.fn.dataTable.Api( settings );
        let tableID = api.table().node().id;
        let parentLength = $( `#${ tableID }_length` ).closest( ".col-auto" );
        parentLength
            .find( 'input[type="search"]' )
            .attr( "placeholder", "Search..." );
        if ( !parentLength.hasClass( "row" ) ) {
            parentLength.addClass( "row" );
        }
        parentLength.find( ".dataTables_filter" ).addClass( "col-xs-6" );
        parentLength.find( ".dataTables_length" ).addClass( "col-xs-6" );
        parentLength
            .find( ".dataTables_filter input" )
            .appendTo( parentLength.find( ".dataTables_filter" ) );
        parentLength.find( ".dataTables_filter label" ).remove();
    },
};

const $globalVariable = {
    /**
     * @description
     * this function is used to request with ajax and handle error response by default
     * and will show loader when request is running. while request is done, loader will be hidden
     * if request run more than 50 seconds, request will be aborted and show alert
     *
     * @param {Object} options
     * @param {String} options.url                          url to request
     * @param {String} options.type                         Request type (GET, POST, PUT, DELETE)
     * @param {Boolean} options.contentType
     * @param {Boolean} options.processData
     * @param {Boolean} options.xhrFields
     * @param {function(response)|undefined} options.successCallback  callback function when request succesa
     * @param {function(response)|undefined} options.errorCallback  callback function when request succesa
     * @param {Object|null} options.headers                 headers to request
     * @param {Object|null} options.param                   data of request POST, PUT, DELETE
     * @param {Object|null} options.params                  query parameter url GET
     *
     */
    baseAjax: function ( options ) {
        let {
            headers = {},
            url,
            type,
            param,
            params,
            contentType,
            processData,
            xhrFields,
            successCallback,
            errorCallback,
        } = options;
        let _intervalAjax;
        let _requestAjax;
        headers = { ...headers, "X-CSRF-TOKEN": csfr_token };
        try {
            $globalVariable.loader( true );
            _intervalAjax = setInterval( function runAjax() {
                if ( _requestAjax != undefined && _requestAjax != null ) {
                    if (
                        _requestAjax.readyState != 0 &&
                        _requestAjax.readyState != 4
                    ) {
                        /**
                         * The xhr object also contains a readyState which contains the state of the
                         * request(UNSENT-0, OPENED-1, HEADERS_RECEIVED-2, LOADING-3 and DONE-4).
                         * we can use this to check whether the previous request was completed.
                         */
                        alert(
                            "Request dibatalkan, karna suatu alasan. Coba lagi dan pastikan internet Anda stabil!"
                        );
                        _requestAjax.abort();
                        _isRequest = false;
                        clearInterval( _intervalAjax );
                        $globalVariable.loader( false );
                    }
                    if ( _requestAjax.readyState == 4 ) {
                        clearInterval( _intervalAjax );
                        _isRequest = false;
                    }
                }
            }, 300 * 1000 );

            _requestAjax = $.ajax( {
                headers: headers,
                url: url,
                type: type,
                contentType,
                processData,
                xhrFields,
                data: param,
                params: params,
                error: function ( xhr ) {
                    if ( _intervalAjax != undefined && _intervalAjax != null ) {
                        clearInterval( _intervalAjax );
                    }
                    if ( typeof errorCallback == "function" ) {
                        error( xhr, errorCallback );
                    } else {
                        error( xhr );
                    }
                },
                success: function ( data ) {
                    $globalVariable.loader( false );
                    if ( _intervalAjax != undefined && _intervalAjax != null ) {
                        clearInterval( _intervalAjax );
                        _isRequest = false;
                    }
                    if ( typeof successCallback == "function" ) {
                        successCallback( data );
                    }
                },
            } );
            _isRequest = true;
        } catch ( error ) {
            alert(
                "Terjadi kesalahan saat menjalankan fitur ini, mohon coba lagi"
            );
            console.error( error );
            $globalVariable.loader( false );
            if ( _intervalAjax != undefined && _intervalAjax != null ) {
                clearInterval( _intervalAjax );
            }
            if ( _requestAjax != undefined && _requestAjax != null ) {
                _requestAjax.abort();
            }
        }
    },

    /**
     * @description
     * this function is used to submit form with ajax and handle error response by default
     * and will show loader when request is running. while request is done, loader will be hidden
     * if request run more than 50 seconds, request will be aborted and show alert
     *
     * @param {Object} options
     * @param {JQuery|Element} options.form
     * @param {Object|null} options.param
     * @param {JQuery|Element} options.modal
     * @param {function($form,option)|undefined} options.callbackSerialize
     * @param {function(data,status,jqxhr,form,modal)|undefined} options.callbackSuccess
     * @param {function(xhr)|undefined} options.callbackError
     *
     */
    formAjax: function ( options ) {
        let {
            form,
            modal,
            callbackSerialize,
            callbackSuccess,
            callbackError,
            param,
            dataType,
            contentType,
            processData,
            xhrFields,
        } = options;
        let _requestFormAjax;
        let _xhr;
        let intervalFormAjax;
        try {
            $globalVariable.loader( true );

            intervalFormAjax = setInterval( function () {
                _xhr = _requestFormAjax.data( "jqxhr" );

                if ( _xhr != undefined && _xhr != null ) {
                    if ( _xhr.readyState != 0 && _xhr.readyState != 4 ) {
                        /**
                         * The _xhr object also contains a readyState which contains the state of the
                         * request(UNSENT-0, OPENED-1, HEADERS_RECEIVED-2, LOADING-3 and DONE-4).
                         * we can use this to check whether the previous request was completed.
                         */
                        alert(
                            "Request dibatalkan, karna suatu alasan. Coba lagi dan pastikan internet Anda stabil!"
                        );
                        _xhr.abort();
                        clearInterval( intervalFormAjax );
                        $globalVariable.loader( false );
                    }
                    if ( _xhr.readyState == 4 ) {
                        clearInterval( intervalFormAjax );
                    }
                }
            }, 300 * 1000 );
            return ( _requestFormAjax = form.ajaxSubmit( {
                data: param,
                dataType,
                contentType,
                processData,
                xhrFields,
                beforeSerialize: function ( $form, option ) {
                    if ( typeof callbackSerialize == "function" ) {
                        if ( !callbackSerialize( $form, option ) ) {
                            $globalVariable.loader( false );
                            return false;
                        }
                    }
                    return true;
                },
                error: function ( xhr ) {
                    if (
                        intervalFormAjax != undefined &&
                        intervalFormAjax != null
                    ) {
                        clearInterval( intervalFormAjax );
                    }
                    if ( typeof callbackError == "function" ) {
                        console.log( xhr );

                        error( xhr, callbackError );
                    } else {
                        error( xhr );
                    }
                },
                success: function ( data, status, jqxhr ) {
                    $globalVariable.loader( false );
                    if (
                        intervalFormAjax != undefined &&
                        intervalFormAjax != null
                    ) {
                        clearInterval( intervalFormAjax );
                    }
                    if ( typeof callbackSuccess == "function" ) {
                        if ( modal == undefined ) {
                            callbackSuccess( data, status, jqxhr, form );
                        } else {
                            callbackSuccess( data, status, jqxhr, form, modal );
                        }
                    }
                },
            } ) );
        } catch ( error ) {
            alert(
                "Terjadi kesalahan saat menjalankan fitur ini, mohon coba lagi"
            );
            console.error( error );
            $globalVariable.loader( false );
            if ( intervalFormAjax != undefined && intervalFormAjax != null ) {
                clearInterval( intervalFormAjax );
            }
            if ( _xhr != undefined && _xhr != null ) {
                _xhr.abort();
            }
        }
    },

    /**
     * @description
     * this function is used to show alert notification in top right corner
     *
     * @param {String|null} type            type of alert (success, info, warning, danger)
     * @param {String|null} title           Title of alert
     * @param {String|null} message         message of alert
     * @param {String|null} subtitle        subtitle of alert
     * @param {Int32Array} timer            timer by default is 8 second (8000)
     *
     */
    baseSwal: function (
        type = "warning",
        title = "title",
        message = "",
        subtitle = "",
        timer = 8000
    ) {
        var html =
            $( `<div class="toast bg-${ type } fade show" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header"><strong class="mr-auto">${ title }</strong><small>${ subtitle }</small><button data-dismiss="toast" type="button" class="ml-2 mb-1 close" aria-label="Close"><span aria-hidden="true">×</span></button></div>
    <div class="toast-body">${ message }</div>
    </div>`);

        var body = $( document ).find( "#toastsContainerTopRight" );
        body.append( html );
        setTimeout( function () {
            html.remove();
        }, timer );
    },

    /**
     * @description
     * this function is used to alert dialog delete with callback ajax when confirmed
     *
     * @param {Object} options
     * @param {String} options.url              url Request when confirmed
     * @param {String} options.title            item to be deleted
     * @param {String} options.successCallback  callback function when request success
     *
     */
    swalConfirmDelete: function ( options ) {
        let { title, url, successCallback, data = undefined } = options;
        new swal( {
            title: `Anda yakin ingin menghapus ${ title }?`,
            text: "Anda tidak akan bisa mengembalikan data ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes, delete it!",
        } ).then( ( result ) => {
            if ( result.isConfirmed ) {
                $globalVariable.baseAjax( {
                    url,
                    headers: {
                        "X-CSRF-TOKEN": csfr_token,
                    },
                    param: data,
                    type: "DELETE",
                    successCallback,
                } );
            }
        } );
    },

    oldExportAction: function ( self, e, dt, button, config ) {
        if ( button[ 0 ].className.indexOf( "buttons-excel" ) >= 0 ) {
            if ( $.fn.dataTable.ext.buttons.excelHtml5.available( dt, config ) ) {
                $.fn.dataTable.ext.buttons.excelHtml5.action.call(
                    self,
                    e,
                    dt,
                    button,
                    config
                );
            } else {
                $.fn.dataTable.ext.buttons.excelFlash.action.call(
                    self,
                    e,
                    dt,
                    button,
                    config
                );
            }
        } else if ( button[ 0 ].className.indexOf( "buttons-pdf" ) >= 0 ) {
            if ( $.fn.dataTable.ext.buttons.pdfHtml5.available( dt, config ) ) {
                $.fn.dataTable.ext.buttons.pdfHtml5.action.call(
                    self,
                    e,
                    dt,
                    button,
                    config
                );
            } else {
                $.fn.dataTable.ext.buttons.pdfFlash.action.call(
                    self,
                    e,
                    dt,
                    button,
                    config
                );
            }
        } else if ( button[ 0 ].className.indexOf( "buttons-print" ) >= 0 ) {
            $.fn.dataTable.ext.buttons.print.action( e, dt, button, config );
        }
    },

    newExportAction: function ( self, e, dt, button, config ) {
        var oldStart = dt.settings()[ 0 ]._iDisplayStart;
        dt.one( "preXhr", function ( e, s, data ) {
            // Just this once, load all data from the server...
            data.start = 0;
            data.length = 2147483647;
            data.download = true;

            dt.one( "preDraw", function ( e, settings ) {
                // Call the original action function
                $globalVariable.oldExportAction( self, e, dt, button, config );

                dt.one( "preXhr", function ( e, s, data ) {
                    // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                    // Set the property to what it was before exporting.
                    settings._iDisplayStart = oldStart;
                    data.start = oldStart;
                    data.download = false;
                } );

                // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
                setTimeout( dt.ajax.reload, 0 );

                // Prevent rendering of the full data to the DOM
                return false;
            } );
        } );

        // Requery the server with the new one-time export settings
        dt.ajax.reload();
    },

    loader: function ( isShow, percentComplete = 0 ) {
        if ( isShow ) {
            let html = `<div class="loader-ajax" style="position: fixed;z-index:99999;background: transparent;width:100%;height:100%;top:0;left:0;">
            <div class="text-center" style="width:300px;position: absolute;left: 50%;top: 35%;-webkit-transform: translate(-50%, -50%);transform: translate(-50%, -50%);">
                <img src="${ publicURL }/assets/img/Double Ring-1s-200px.svg" alt="" style="width:150px;">
                </div>
                </div>`;
            // <div class="progress mt-2" style="position: relative">
            //     <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" aria-valuenow="${percentComplete}" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
            //         <span style="position: absolute;color: black;font-weight: bold; left:2px; font-size:16px">${percentComplete}%</span>
            //     </div>
            // </div>
            $( "section.content" ).append( html );
        } else {
            $( "section.content .loader-ajax" ).remove();
        }
    },

    /**
     * @description
     * this function is used to alert dialog delete with callback when confirmed
     *
     * @param {Object} options
     * @param {String} options.text             text to be shown
     * @param {String} options.title            title dialog
     * @param {function()} options.callback         callback function when request success
     * @param {function()} options.callbackDismiss         callback function when request success
     *
     */
    swalConfirm: function ( options ) {
        const { title, text = "", callback, callbackDismiss } = options;
        new swal( {
            title: title,
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Ya, Lanjutkan!",
        } ).then( ( result ) => {
            if ( result.isConfirmed ) {
                if ( typeof callback == "function" ) {
                    callback();
                }
            } else if ( result.dismiss ) {
                if ( typeof callbackDismiss == "function" ) {
                    callbackDismiss();
                }
            }
        } );
    },

    fillForm: function ( $parent = undefined, index = [] ) {
        if ( $parent != undefined ) {
            let triggerTimer;
            let lastTrigger;
            $.each( index, function ( index, value ) {
                if ( value.type == "input" && value.withTrigger == undefined ) {
                    /* [{type:'input',data:value,content:element}] */
                    $parent.find( value.content ).val( value.data );
                } else if (
                    value.type == "input" &&
                    value.withTrigger &&
                    value.mustTrigger
                ) {
                    $globalVariable.loader( true );
                    let timer = setTimeout( function run() {
                        if (
                            triggerTimer == undefined &&
                            !_isRequest &&
                            lastTrigger == undefined
                        ) {
                            $globalVariable.loader( false );
                            $parent
                                .find( value.content )
                                .val( value.data )
                                .trigger( "change" );
                            clearTimeout( timer );
                        } else {
                            timer = setTimeout( run, 500 );
                        }
                    }, 500 );
                } else if ( value.type == "input" && value.withTrigger ) {
                    $globalVariable.loader( true );
                    let timer = setTimeout( function run() {
                        if (
                            triggerTimer == undefined &&
                            !_isRequest &&
                            lastTrigger == undefined
                        ) {
                            $globalVariable.loader( false );
                            $parent.find( value.content ).val( value.data );
                            clearTimeout( timer );
                        } else {
                            timer = setTimeout( run, 500 );
                        }
                    }, 500 );
                } else if ( value.type == "select" && value.timer == undefined ) {
                    /* [{type:'select',data:value,content:element}] */
                    $parent
                        .find( value.content )
                        .val( value.data )
                        .trigger( "change" );
                } else if (
                    value.type == "select" &&
                    value.timer != undefined &&
                    value.withTrigger == undefined
                ) {
                    /* [{type:'select',data:value,content:element,stop:true,timer:true}] */
                    $globalVariable.loader( true );
                    let timer = setTimeout( function run() {
                        if (
                            $parent.find( value.content + " option" ).length > 1
                        ) {
                            $parent
                                .find( value.content )
                                .val( value.data )
                                .trigger( "change" );
                            clearTimeout( timer );
                            $globalVariable.loader( false );
                            triggerTimer = undefined;
                            lastTrigger = undefined;
                        } else {
                            triggerTimer = "running";
                            lastTrigger = "running";
                            timer = setTimeout( run, 500 );
                        }
                    }, 500 );
                } else if (
                    value.type == "select" &&
                    value.timer != undefined &&
                    value.withTrigger
                ) {
                    $globalVariable.loader( true );
                    let timer = setTimeout( function run() {
                        if ( triggerTimer == undefined && !_isRequest ) {
                            if (
                                $parent.find( value.content + " option" ).length >
                                1
                            ) {
                                $parent
                                    .find( value.content )
                                    .val( value.data )
                                    .trigger( "change" );
                                clearTimeout( timer );
                                lastTrigger = undefined;
                                $globalVariable.loader( false );
                            } else {
                                lastTrigger = "running";
                                timer = setTimeout( run, 500 );
                            }
                        } else {
                            lastTrigger = "running";
                            timer = setTimeout( run, 500 );
                        }
                    }, 500 );
                } else if (
                    value.type == "file" &&
                    value.addButton != undefined
                ) {
                    if ( value.data != null && value.data != "" ) {
                        $parent
                            .find( value.content )
                            .append(
                                `<button type="button" class="ms-2 btn btn-sm btn-primary view-file" data-src="${ value.data }" title="Lihat File"><i class="fa fa-file-pdf"></i></button>`
                            );
                    }
                } else if ( value.type == "file" && value.plugins != undefined ) {
                    /* [{type:'file',data:value, content:'element',fname:'file name'}] */
                    if ( value.data != null && value.data != "" ) {
                        let src = publicURL + "storage/" + value.data;
                        resetPreview(
                            $parent.find( value.content ),
                            src,
                            value.fname
                        );
                    } else {
                        resetPreview( $parent.find( value.content ), "", "" );
                    }
                } else if (
                    value.type == "textarea" &&
                    value.wysihtml5 == undefined
                ) {
                    /* [{type:'textarea',data:value,content:element}] */
                    $parent.find( value.content ).text( value.data );
                } else if (
                    value.type == "textarea" &&
                    value.wysihtml5 != undefined
                ) {
                    /* [{type:'textarea',data:value,content:element,wysihtml5:true}] */
                    $parent
                        .find( value.content )
                        .data( "wysihtml5" )
                        .editor.setValue( value.data );
                } else if ( value.type == "checkbox" ) {
                    /* [{type:'checkbox',data:value,content:element}] */
                    if ( value.data ) {
                        $parent
                            .find( value.content )
                            .prop( "checked", true )
                            .trigger( "change" );
                    } else {
                        $parent.find( value.content ).removeAttr( "checked" );
                    }
                } else if ( value.type == "text" ) {
                    $parent.find( value.content ).val( value.data );
                }
            } );
        } else {
            if ( container != undefined && content != undefined ) {
                $globalVariable.loader( false );
            }
            console.log( "undefined parent" );
        }
    },

    resetForm: function ( parent, index = [] ) {
        if ( parent != undefined ) {
            $.each( index, function ( index, value ) {
                if (
                    value.type == "select" &&
                    value.append == undefined &&
                    !value.isRemove
                ) {
                    parent
                        .find( value.content )
                        .prop( "selectedIndex", 0 )
                        .trigger( "change" );
                } else if (
                    value.type == "select" &&
                    value.append != undefined
                ) {
                    parent
                        .find( value.content )
                        .empty()
                        .append( value.append )
                        .trigger( "change" );
                } else if ( value.type == "select" && value.isRemove ) {
                    let select = parent.find( value.content );
                    select
                        .parent( value.group )
                        .find( ".select2-container--default" )
                        .remove();
                } else if ( value.type == "file" ) {
                    resetPreview( parent.find( value.content ), "", "" );
                } else if ( value.type == "input" && value.isRemove ) {
                    parent.find( value.content ).remove();
                } else if ( value.type == "input" ) {
                    parent.find( value.content ).val( value.data );
                }
            } );
        } else {
            console.log( "undefined parent" );
        }
    },

    /**
     *
     * @param object data property of option is required for post data ajax
     * @param function callback property of options is is opsional
     *
     * @return modal bootstrap
     */
    getModal: function ( options ) {
        const { data, callback } = options;
        $globalVariable.baseAjax( {
            url: publicURL + `/get-modal`,
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": csfr_token,
            },
            param: data,
            successCallback: function ( response ) {
                if (
                    response.message != undefined &&
                    response.view == undefined
                ) {
                    $globalVariable.baseSwal(
                        "danger",
                        "Error",
                        response.message,
                        "error"
                    );
                    return false;
                }

                $( document ).find( "#contentBody" ).append( response.view );
                let modal = $( document ).find(
                    "#contentBody #" + response.modalId
                );

                if ( modal !== undefined ) {
                    modal.modal( "show" );
                    if ( response.plugins !== undefined ) {
                        $globalVariable.applyPlugins( modal, response.plugins );
                    }

                    if (
                        callback !== undefined &&
                        typeof callback === "function"
                    ) {
                        callback( response, modal );
                    }
                }
            },
        } );
    },

    applyPlugins: function ( modal, plugins, isModal = true ) {
        let dropdownParent = undefined;
        if ( isModal ) {
            dropdownParent = modal.find( ".modal-body" );
        }
        plugins.forEach( ( plugin, key ) => {
            if ( plugin.plugin === "select2" ) {
                modal.find( plugin.element ).select2( {
                    placeholder: "Pilih Opsi...",
                    dropdownParent: plugin.dropdownParent ? plugin.dropdownParent : dropdownParent,
                    width: plugin.width || "100%",
                } );
            } else if ( plugin.plugin === "select2-search" ) {
                modal.find( plugin.element ).select2( {
                    placeholder: "Cari....",
                    dropdownParent: plugin.dropdownParent ? plugin.dropdownParent : dropdownParent,
                    // minimumInputLength: 1,
                    cache: true,
                    width: plugin.width || "100%",
                    ajax: window[ plugin.ajax ]( plugin.path ),
                    data:
                        plugin.defaultData != undefined
                            ? processData( plugin.defaultData ).results
                            : undefined,
                    templateResult: myCustomTemplate,
                    templateSelection: myCustomTemplate,
                    selectOnClose: false,
                } );
            } else if ( plugin.plugin === "select2-search-tags" ) {
                modal.find( plugin.element ).select2( {
                    placeholder: "Cari....",
                    dropdownParent: plugin.dropdownParent ? plugin.dropdownParent : dropdownParent,
                    // minimumInputLength: 1,
                    cache: true,
                    width: plugin.width || "100%",
                    tags: true,
                    ajax: window[ plugin.ajax ]( plugin.path ),
                    data:
                        plugin.defaultData != undefined
                            ? processData( plugin.defaultData ).results
                            : undefined,
                    templateResult: myCustomTemplate,
                    templateSelection: myCustomTemplate,
                    selectOnClose: false,
                } );
            } else if ( plugin.plugin === "duallistbox" ) {
                modal.find( plugin.element ).bootstrapDualListbox( {
                    moveOnSelect: false,
                    nonSelectedListLabel: "Non-selected",
                    selectedListLabel: "Selected",
                    preserveSelectionOnMove: "moved",
                } );
            } else if ( plugin.plugin === "maskMoney" ) {
                if ( plugin.options != undefined ) {
                    modal.find( plugin.element ).maskMoney( plugin.options );
                } else {
                    modal.find( plugin.element ).maskMoney( {
                        prefix: "Rp.",
                        allowNegative: false,
                    } );
                }
            }
        } );
    },

    Logout: function () {
        $globalVariable.swalConfirm( {
            title: "Keluar",
            text: "Apakah anda yakin ingin keluar?",
            callback: function () {
                $( "#formLogout" ).submit();
            },
        } );
    },

    globalDelete: function (
        url,
        datatdable = undefined,
        key = "data ini",
        data = undefined
    ) {
        $globalVariable.swalConfirmDelete( {
            url: url,
            data: data,
            title: key,
            successCallback: function () {
                if ( datatdable != undefined ) {
                    alert( "Berhasil!", "Data berhasil dihapus", "success" );
                    datatdable.ajax.reload();
                }
            },
        } );
    },
    mergeWithDefaultOptions: function ( options = {} ) {
        options.dom = options.dom || defaultOptionDatatable.dom;
        options.scrollX = options.scrollX || defaultOptionDatatable.scrollX;
        options.processing =
            options.processing || defaultOptionDatatable.processing;
        options.serverSide =
            options.serverSide || defaultOptionDatatable.serverSide;
        options.searchDelay =
            options.searchDelay || defaultOptionDatatable.searchDelay;
        options.destroy = options.destroy || defaultOptionDatatable.destroy;
        options.columnDefs =
            options.columnDefs || defaultOptionDatatable.columnDefs;
        options.fixedColumns =
            options.fixedColumns || defaultOptionDatatable.fixedColumns;
        options.buttons = options.buttons || [];
        // options.initComplete =
        //     options.initComplete || defaultOptionDatatable.initComplete;
        if ( typeof options.initComplete === "function" ) {
            let temp = options.initComplete;
            options.initComplete = function ( settings, json ) {
                if ( temp( settings, json ) !== false ) {
                    defaultOptionDatatable.initComplete( settings, json );
                }
            };
        } else {
            options.initComplete = defaultOptionDatatable.initComplete;
        }
        const buttons = options.$defaultOpt.buttons;
        for ( const button of buttons ) {
            if ( typeof button === "string" ) {
                let buttonDefault = defaultOptionDatatable.buttons.find(
                    ( item ) => item.$keyButton === button
                );
                if ( buttonDefault ) {
                    options.buttons.push( buttonDefault );
                }
                // if (button === "excel-pdf") {
                //     let buttonExcel = defaultOptionDatatable.buttons.find(
                //         (item) => item.$keyButton === "excel"
                //     );
                //     let buttonPdf = defaultOptionDatatable.buttons.find(
                //         (item) => item.$keyButton === "pdf"
                //     );
                //     options.buttons.push(buttonExcel);
                //     options.buttons.push(buttonPdf);
                // }
            } else if ( typeof button === "object" ) {
                let buttonDefault = defaultOptionDatatable.buttons.find(
                    ( item ) => item.$keyButton === button.$keyButton
                );
                if ( !buttonDefault ) {
                    buttonDefault = {};
                }

                if ( buttonDefault.html && ( button.className || button.id ) ) {
                    buttonDefault.html = buttonDefault.onRender( {
                        id: button.id,
                        className: button.className,
                    } );
                }
                let newButton = $.extend( true, {}, buttonDefault, button );
                options.buttons.push( newButton );
            }
        }
        return options || {};
    },
};

function error( xhr, callbackError ) {
    $globalVariable.loader( false );
    if ( typeof callbackError === "function" ) {
        if ( callbackError( xhr ) ) {
            return;
        }
    }

    if ( xhr.status == 500 ) {
        $globalVariable.baseSwal( "danger", "Error!", "Query error" );
    } else if ( xhr.status == 422 ) {
        let json = xhr.responseJSON;
        let message = "";
        if ( json.errors != null ) {
            message = '<ul class="text-left">';
            $.each( json.errors, function ( index, value ) {
                console.log( value );
                message += "<li>" + value + "</li>";
            } );
            message += "</ul>";
        } else {
            message = json.message;
        }
        $globalVariable.baseSwal( "danger", "Error!", message );
    } else if ( xhr.status == 404 ) {
        $globalVariable.baseSwal(
            "danger",
            "Error!",
            $.parseJSON( xhr.responseText ).message
        );
    } else if ( xhr.status == 400 ) {
        $globalVariable.baseSwal( "danger", "Error!", xhr.responseText );
        // $globalVariable.baseSwal("danger", "Error!", $.parseJSON(xhr.responseText).message);
    } else if ( xhr.status == 501 ) {
        $globalVariable.baseSwal(
            "danger",
            "Error!",
            $.parseJSON( xhr.responseText ).message
        );
    } else {
        $globalVariable.baseSwal(
            "danger",
            "Error!",
            xhr.responseText
                ? $.parseJSON( xhr.responseText ).message
                : "Mohon Periksa Koneksi Internet Anda"
        );
    }
}

window.alert = function (
    title = "Peringatan!",
    message = "",
    icon = "warning"
) {
    return new Promise( ( resolve, reject ) => {
        new swal( {
            title: title,
            text: message,
            icon: icon,
            showCancelButton: false,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "OK",
        } ).then( ( result ) => {
            if ( result.isConfirmed ) {
                resolve( true );
            }
        } );
    } );
};

function processData( data ) {
    var mapdata = $.map( data, function ( obj ) {
        //   obj.id = obj.Id;
        obj.text = "[" + obj.id + "] " + obj.Description;
        return obj;
    } );
    return { results: mapdata };
}

function myCustomTemplate( item ) {
    if ( !item.id ) {
        return item.Description || item.text;
    }
    return $(
        "<span><strong>" +
        item.id +
        "</strong> - " +
        ( item.Description || item.text ) +
        "</span>"
    );
}

window.confirm = function (
    message,
    callback = undefined,
    callbackDismiss = undefined
) {
    $globalVariable.swalConfirm( {
        title: "Peringatan!",
        text: message,
        callback,
        callbackDismiss,
    } );
};

window.setSelectAjax = function ( path ) {
    return {
        url: publicURL + path,
        dataType: "json",
        delay: 500,
        // processResults: function (data) {
        //     return {
        //         results: data,
        //     };
        // },
        processResults: processData,
        cache: true,
    };
};

window.formatDateInput = function ( date, splitter = "/" ) {
    var dateParts = date.split( splitter );
    var d = new Date( +dateParts[ 2 ], dateParts[ 1 ] - 1, +dateParts[ 0 ] ),
        month = "" + ( d.getMonth() + 1 ),
        day = "" + d.getDate(),
        year = d.getFullYear();

    if ( month.length < 2 ) month = "0" + month;
    if ( day.length < 2 ) day = "0" + day;

    return [ year, month, day ].join( "-" );
};

window.momentDateInput = function (
    date,
    formatBefore = "DD/MM/YYYY",
    formatAfter = "YYYY-MM-DD"
) {
    return moment( date, formatBefore ).format( formatAfter );
};

$( document ).on( "init.dt", function ( e, settings, json ) {
    window.dispatchEvent( new Event( "resize" ) );
} );

window.$injectScript = function ( {
    url = "",
    fn = undefined,
    args = [],
    callback = undefined,
    id = "",
} ) {
    if ( $( document ).find( "#script_" + id ).length > 0 ) {
        let argss = getVariablesFromString( getArgs( eval( "parent" + fn ) ) );
        if ( typeof eval( "parent" + fn ) == "function" ) {
            eval( "parent" + fn + "(...argss)" );
            return;
        }

        let newClass = eval( "new parent" + fn + "(...argss)" );

        eval( "newClass." + fn + "(...args)" );

        if ( typeof callback == "function" ) {
            callback();
        }
        return;
    }
    var script = document.createElement( "script" );
    script.type = "text/javascript";
    // script.id = "script_" + id;
    // script.type = "module";

    if ( script.readyState ) {
        //IE
        script.onreadystatechange = function () {
            if (
                script.readyState == "loaded" ||
                script.readyState == "complete"
            ) {
                script.onreadystatechange = null;
                let argss = getVariablesFromString(
                    getArgs( eval( "parent" + fn ) )
                );
                let newClass = eval( "new parent" + fn + "(...argss)" );

                eval( "newClass." + fn + "(...args)" );

                if ( typeof callback == "function" ) {
                    callback();
                }
            }
        };
    } else {
        //Others
        script.onload = function () {
            let argss = getVariablesFromString( getArgs( eval( "parent" + fn ) ) );
            let newClass = eval( "new parent" + fn + "(...argss)" );

            eval( "newClass." + fn + "(...args)" );

            if ( typeof callback == "function" ) {
                callback();
            }
        };
    }
    script.setAttribute( "src", publicURL + "/assets/js/" + url );
    document.getElementById( "$scriptFile" ).appendChild( script );
};

window.$removeScript = function ( url ) {
    let script = $( document ).find(
        '[src="' + publicURL + "/assets/js/" + url + '"]'
    );
    script.remove();
};

window.$injectCss = function ( url ) {
    var link = document.createElement( "link" );
    link.type = "text/css";
    link.rel = "stylesheet";
    link.href = publicURL + "/assets/css/" + url;
    document.getElementsByTagName( "head" )[ 0 ].appendChild( link );
};

window.$removeCss = function ( url ) {
    var link = document.getElementsByTagName( "link" );
    for ( var i = 0; i < link.length; i++ ) {
        if ( link[ i ].getAttribute( "href" ) == url ) {
            link[ i ].parentNode.removeChild( link[ i ] );
        }
    }
};

function getArgs( func ) {
    // First match everything inside the function argument parens.
    var args = func.toString().match( /function\s.*?\(([^)]*)\)/ )[ 1 ];

    // Split the arguments string into an array comma delimited.
    return args
        .split( "," )
        .map( function ( arg ) {
            // Ensure no inline comments are parsed and trim the whitespace.
            arg = arg.replace( /(?:\\[rn]|[\r\n]+)+/g, "" ).trim();
            return arg.replace( /\/\*.*\*\//, "" ).trim();
        } )
        .filter( function ( arg ) {
            // Ensure no undefined values are added.
            return arg;
        } );
}

function getVariablesFromString( variables = [] ) {
    var result = [];
    variables.forEach( ( variable ) => {
        if ( variable == undefined ) {
            return false;
        }

        if ( variable == "csfr_token" ) {
            // result += "csfr_token" + (i == length ? "" : ", ");
            result.push( csfr_token );
        } else if ( variable == "publicURL" ) {
            // result += "publicURL" + (i == length ? "" : ", ");
            result.push( publicURL );
        } else if ( $globalVariable.hasOwnProperty( variable ) ) {
            // result += "$globalVariable." + variable + (i == length ? "" : ", ");
            result.push( $globalVariable[ variable ] );
        }
    } );
    return result;
}
export { _isRequest, publicURL, csfr_token, defaultOptionDatatable };

export default $globalVariable;
