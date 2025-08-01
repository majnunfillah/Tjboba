/// <reference path="../../plugins/jquery/jquery.js" />

import $globalVariable, { publicURL, csfr_token } from "../base-function.js";
const { pdfjsLib } = globalThis

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = `${ publicURL }/assets/plugins/pdf-js/build/pdf.worker.mjs`;
const elemContainer = $( '.container-fluid.pt-4' )

const elemFilterNeracaLajur = $( document ).find( 'div[data-access="20501|20502"]' )
const elemFilterBukuTambahan = $( document ).find( 'div[data-access="202021"]' )

$( '#nama-laporan' ).on( 'change', function ( e ) {
    const val = $( this ).val();
    $( '#form-filter-laporan' ).trigger( 'reset' )
    switch ( val ) {
        case '20501':
        case '20502':
            elemFilterNeracaLajur.show()
            elemFilterBukuTambahan.hide()
            break;
        case '202021':
            elemFilterNeracaLajur.hide()
            elemFilterBukuTambahan.show()
            break;
        default:
            elemFilterNeracaLajur.hide()
            elemFilterBukuTambahan.hide()
            break;
    }

    let access = $( '#form-filter-laporan' ).find( 'input[name="access"]' );

    if ( !access.length ) {
        access = $( '<input type="hidden" name="access">' )
        $( '#form-filter-laporan' ).append( access );
    }

    access.val( val )
} )
$( '#nama-laporan' ).trigger( 'change' )

$globalVariable.applyPlugins( elemContainer, [
    {
        element: "#nama-laporan",
        plugin: "select2",
        dropdownParent: $( '#form-filter-laporan' )
    },
    {
        element: "select[name='awal']",
        plugin: "select2-search",
        ajax: "setSelectAjax",
        path: "/get-biaya-select",
        dropdownParent: $( '#form-filter-laporan' )
    },
    {
        element: "select[name='akhir']",
        plugin: "select2-search",
        ajax: "setSelectAjax",
        path: "/get-biaya-select",
        dropdownParent: $( '#form-filter-laporan' )
    },
], false )

$( document ).on( 'submit', '#form-filter-laporan', function ( e ) {
    e.preventDefault();
    let formCtx = this;

    let url = $( '#preview-new-tab' ).data( 'url' )
    url += "&" + serializeDivInputs( formCtx, $( '#nama-laporan' ).val() )
    // url += $.param( $( formCtx ).serializeArray()
    //     .filter( ( item ) => {
    //         console.log( item );

    //         return item.name !== '_method' && item.name !== '_token'
    //     } ) )

    $globalVariable.formAjax( {
        form: $( formCtx ),
        callbackSuccess: ( data, status, jqxhr, form ) => {
            $( '#button-container' ).removeClass( 'sr-only' )
            if ( data.status == 200 && data.html ) {
                $( '#laporanViewer' ).empty().append( data.html )
                $( '#preview-new-tab' ).attr( 'href', url )
            } else {
                // $( '#preview-new-tab' ).hide
            }
        },
        callbackError: ( xhr ) => {
            $( '#button-container' ).addClass( 'sr-only' )
            if ( xhr.status == 500 && xhr.responseText.includes( "window.Sfdump" ) ) {
                $( '#laporanViewer' ).empty().append( xhr.responseText )
            }
            return true;
        }
    } )

    /* const formData = new FormData( this )

    $globalVariable.baseAjax( {
        url: $( formCtx ).attr( 'action' ),
        type: 'POST',
        param: formData,
        contentType: false,
        processData: false,
        xhrFields: {
            responseType: 'blob'
        },
        successCallback: ( response ) => {
            console.log( response );

            if ( typeof response.arrayBuffer == 'function' ) {
                $( '#button-container' ).removeClass( 'sr-only' )

                let blob = new Blob( [ response ], { type: "application/pdf" } );
                let url = URL.createObjectURL( blob );
                $( '#preview-new-tab' ).attr( 'href', url )

                var pdfDoc = null,
                    pageNum = 1,
                    pageRendering = false,
                    pageNumPending = null,
                    scale = 1,
                    canvas = document.getElementById( 'pdfCanvas' ),
                    canvasContext = canvas.getContext( '2d' ),
                    translatePos = {
                        x: 0,
                        y: canvas.height / 2
                    },
                    scaleMultiplier = 0.8,
                    startDragOffset = {},
                    mouseDown = false;

                // Render page
                function renderPage( num ) {
                    pageRendering = true;
                    // Using promise to fetch the page
                    pdfDoc.getPage( num ).then( function ( page ) {
                        var viewport = page.getViewport( { scale: scale } );
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        // Clear the canvas before rendering 
                        canvasContext.clearRect( 0, 0, canvas.width, canvas.height );
                        canvasContext.save()
                        canvasContext.translate( translatePos.x, translatePos.y );
                        canvasContext.scale( scale, scale );
                        // Render PDF page into canvas context
                        var renderContext = {
                            canvasContext: canvasContext,
                            viewport: viewport
                        };
                        var renderTask = page.render( renderContext );

                        // Wait for rendering to finish
                        renderTask.promise.then( function () {
                            pageRendering = false;
                            if ( pageNumPending !== null ) {
                                // New page rendering is pending
                                renderPage( pageNumPending );
                                pageNumPending = null;
                            }
                        } );
                    } );

                    // Update page counters
                    document.getElementById( 'page_num' ).textContent = num;
                }

                // Zoom in
                document.getElementById( 'zoom-in' ).addEventListener( 'click', () => {
                    if ( !pageRendering ) {
                        scale /= scaleMultiplier;
                        renderPage( pageNum );
                    }
                } );

                // Zoom out
                document.getElementById( 'zoom-out' ).addEventListener( 'click', () => {
                    if ( !pageRendering ) {
                        scale *= scaleMultiplier;
                        renderPage( pageNum );
                    }
                } );

                // Reset
                document.getElementById( 'reset-canvas' ).addEventListener( 'click', () => {
                    if ( !pageRendering ) {
                        scale = 1;
                        translatePos = {
                            x: 0,
                            y: canvas.height / 2
                        };
                        scaleMultiplier = 0.8;
                        startDragOffset = {};
                        mouseDown = false;
                        renderPage( pageNum );
                    }
                } );

                // add event listeners to handle screen drag
                canvas.addEventListener( "mousedown", function ( evt ) {
                    if ( !pageRendering ) {
                        mouseDown = true;
                        startDragOffset.x = evt.clientX - translatePos.x;
                        startDragOffset.y = evt.clientY - translatePos.y;
                    }
                } );

                canvas.addEventListener( "mouseup", function ( evt ) {
                    mouseDown = false;
                } );

                canvas.addEventListener( "mouseover", function ( evt ) {
                    mouseDown = false;
                } );

                canvas.addEventListener( "mouseout", function ( evt ) {
                    mouseDown = false;
                } );

                canvas.addEventListener( "mousemove", function ( evt ) {
                    if ( mouseDown && !pageRendering ) {
                        translatePos.x = evt.clientX - startDragOffset.x;
                        translatePos.y = evt.clientY - startDragOffset.y;
                        renderPage( pageNum );
                    }
                } );

                function queueRenderPage( num ) {
                    if ( pageRendering ) {
                        pageNumPending = num;
                    } else {
                        renderPage( num );
                    }
                }

                function onPrevPage() {
                    if ( pageNum <= 1 ) {
                        return;
                    }
                    pageNum--;
                    queueRenderPage( pageNum );
                }
                document.getElementById( 'prev' ).addEventListener( 'click', onPrevPage );

                function onNextPage() {
                    if ( pageNum >= pdfDoc.numPages ) {
                        return;
                    }
                    pageNum++;
                    queueRenderPage( pageNum );
                }
                document.getElementById( 'next' ).addEventListener( 'click', onNextPage );

                pdfjsLib.getDocument( url ).promise.then( function ( pdfDoc_ ) {
                    pdfDoc = pdfDoc_;
                    document.getElementById( 'page_count' ).textContent = pdfDoc.numPages;

                    // Initial/first page rendering
                    renderPage( pageNum );
                } );
            } else {
                console.log( "not blob" );

            }
        },
        errorCallback: ( err ) => {
            console.log( err );
        }
    } ) */
} )

function serializeDivInputs( formCtx, access ) {
    // Find the div with the specified data-access attribute
    // let div = $(formCtx).find('div[data-access="' + dataAccess + '"]');
    let filteredDiv = $( formCtx ).find( 'div[data-access]' ).filter( function () {
        let dataAccess = $( this ).attr( 'data-access' );
        let accessList = dataAccess.split( '|' );
        // console.log(access, accessList, $(this), accessList.includes( access ));
        
        return accessList.includes( access )
    } );

    if ( filteredDiv.length > 0 ) {
        // console.log(filteredDiv);
        
        // Directly serialize the inputs within the found div and filter unwanted fields
        let serializedData = $(filteredDiv[0]).find( 'input, select, textarea' ).serializeArray().filter( ( item ) => {
            // Filter out unwanted fields
            return item.name !== '_method' && item.name !== '_token';
        } );
        
        serializedData.unshift({name: 'access', value: access})
        
        // Return the serialized data as a query string
        return $.param( serializedData );
    }
    return '';
}
