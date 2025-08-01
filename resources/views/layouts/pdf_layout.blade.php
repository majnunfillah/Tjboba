<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            font-family: 'Times New Roman', Times, serif;
        }

        .table-border,
        .table-border tr,
        .table-border th,
        .table-border td {
            border: 1px solid black;
            border-collapse: collapse;
        }

        th {
            font-weight: bold;
            font-size: 14px;
        }

        td {
            font-size: 12px;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .align-top {
            vertical-align: top;
        }

        .align-bottom {
            vertical-align: bottom;
        }

        .align-middle {
            vertical-align: middle;
        }

        .float-left {
            float: left;
        }

        .float-right {
            float: right;
        }

        .h-5 {
            height: 5% !important
        }

        .h-10 {
            height: 10% !important
        }

        .h-15 {
            height: 15% !important
        }

        .h-20 {
            height: 20% !important
        }

        .h-25 {
            height: 25% !important
        }

        .h-30 {
            height: 30% !important
        }

        .h-35 {
            height: 35% !important
        }

        .h-40 {
            height: 40% !important
        }

        .h-45 {
            height: 45% !important
        }

        .h-50 {
            height: 50% !important
        }

        .h-55 {
            height: 55% !important
        }

        .h-60 {
            height: 60% !important
        }

        .h-65 {
            height: 65% !important
        }

        .h-70 {
            height: 70% !important
        }

        .h-75 {
            height: 75% !important
        }

        .h-80 {
            height: 80% !important
        }

        .h-85 {
            height: 85% !important
        }

        .h-90 {
            height: 90% !important
        }

        .h-95 {
            height: 95% !important
        }

        .h-100 {
            height: 100% !important
        }

        .w-5 {
            width: 5% !important
        }

        .w-10 {
            width: 10% !important
        }

        .w-15 {
            width: 15% !important
        }

        .w-20 {
            width: 20% !important
        }

        .w-25 {
            width: 25% !important
        }

        .w-30 {
            width: 30% !important
        }

        .w-35 {
            width: 35% !important
        }

        .w-40 {
            width: 40% !important
        }

        .w-45 {
            width: 45% !important
        }

        .w-50 {
            width: 50% !important
        }

        .w-55 {
            width: 55% !important
        }

        .w-60 {
            width: 60% !important
        }

        .w-65 {
            width: 65% !important
        }

        .w-70 {
            width: 70% !important
        }

        .w-75 {
            width: 75% !important
        }

        .w-80 {
            width: 80% !important
        }

        .w-85 {
            width: 85% !important
        }

        .w-90 {
            width: 90% !important
        }

        .w-95 {
            width: 95% !important
        }

        .w-100 {
            width: 100% !important
        }

        .w-1--12 {
            width: 8.333333% !important
        }

        .w-2--12 {
            width: 16.666667% !important
        }

        .w-3--12 {
            width: 25% !important
        }

        .w-4--12 {
            width: 33.333333% !important
        }

        .w-5--12 {
            width: 41.666667% !important
        }

        .w-6--12 {
            width: 50% !important
        }

        .w-7--12 {
            width: 58.333333% !important
        }

        .w-8--12 {
            width: 66.666667% !important
        }

        .w-9--12 {
            width: 75% !important
        }

        .w-10--12 {
            width: 83.333333% !important
        }

        .w-11--12 {
            width: 91.666667% !important
        }

        .w-12--12 {
            width: 100% !important
        }


        .fs-10 {
            font-size: 10px;
        }

        .fs-12 {
            font-size: 12px;
        }

        .fs-14 {
            font-size: 14px;
        }

        .fs-16 {
            font-size: 16px;
        }

        .fs-18 {
            font-size: 18px;
        }

        .fs-20 {
            font-size: 20px;
        }

        .fs-22 {
            font-size: 22px;
        }

        .fs-24 {
            font-size: 24px;
        }

        .fs-26 {
            font-size: 26px;
        }

        .fs-28 {
            font-size: 28px;
        }

        .fs-30 {
            font-size: 30px;
        }

        .text-jusify {
            text-align: justify;
        }

        .text-nowrap {
            white-space: nowrap;
        }

        .text-wrap {
            white-space: normal;
        }

        .text-underline {
            text-decoration: underline;
        }

        .text-lowercase {
            text-transform: lowercase;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .text-capitalize {
            text-transform: capitalize;
        }

        .text-italic {
            font-style: italic;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-normal {
            font-weight: normal;
        }

        .m-0 {
            margin: 0 !important
        }

        .m-1 {
            margin: 0.25rem !important
        }

        .m-2 {
            margin: 0.5rem !important
        }

        .m-3 {
            margin: 0.75rem !important
        }

        .m-4 {
            margin: 1rem !important
        }

        .m-5 {
            margin: 1.25rem !important
        }

        .mt-0 {
            margin-top: 0 !important
        }

        .mt-1 {
            margin-top: 0.25rem !important
        }

        .mt-2 {
            margin-top: 0.5rem !important
        }

        .mt-3 {
            margin-top: 0.75rem !important
        }

        .mt-4 {
            margin-top: 1rem !important
        }

        .mt-5 {
            margin-top: 1.25rem !important
        }

        .mb-0 {
            margin-bottom: 0 !important
        }

        .mb-1 {
            margin-bottom: 0.25rem !important
        }

        .mb-2 {
            margin-bottom: 0.5rem !important
        }

        .mb-3 {
            margin-bottom: 0.75rem !important
        }

        .mb-4 {
            margin-bottom: 1rem !important
        }

        .mb-5 {
            margin-bottom: 1.25rem !important
        }

        .ml-0 {
            margin-left: 0 !important
        }

        .ml-1 {
            margin-left: 0.25rem !important
        }

        .ml-2 {
            margin-left: 0.5rem !important
        }

        .ml-3 {
            margin-left: 0.75rem !important
        }

        .ml-4 {
            margin-left: 1rem !important
        }

        .ml-5 {
            margin-left: 1.25rem !important
        }

        .mr-0 {
            margin-right: 0 !important
        }

        .mr-1 {
            margin-right: 0.25rem !important
        }

        .mr-2 {
            margin-right: 0.5rem !important
        }

        .mr-3 {
            margin-right: 0.75rem !important
        }

        .mr-4 {
            margin-right: 1rem !important
        }

        .mr-5 {
            margin-right: 1.25rem !important
        }

        .p-0 {
            padding: 0 !important
        }

        .p-1 {
            padding: 0.25rem !important
        }

        .p-2 {
            padding: 0.5rem !important
        }

        .p-3 {
            padding: 0.75rem !important
        }

        .p-4 {
            padding: 1rem !important
        }

        .p-5 {
            padding: 1.25rem !important
        }

        .pt-0 {
            padding-top: 0 !important
        }

        .pt-1 {
            padding-top: 0.25rem !important
        }

        .pt-2 {
            padding-top: 0.5rem !important
        }

        .pt-3 {
            padding-top: 0.75rem !important
        }

        .pt-4 {
            padding-top: 1rem !important
        }

        .pt-5 {
            padding-top: 1.25rem !important
        }

        .pb-0 {
            padding-bottom: 0 !important
        }

        .pb-1 {
            padding-bottom: 0.25rem !important
        }

        .pb-2 {
            padding-bottom: 0.5rem !important
        }

        .pb-3 {
            padding-bottom: 0.75rem !important
        }

        .pb-4 {
            padding-bottom: 1rem !important
        }

        .pb-5 {
            padding-bottom: 1.25rem !important
        }

        .pl-0 {
            padding-left: 0 !important
        }

        .pl-1 {
            padding-left: 0.25rem !important
        }

        .pl-2 {
            padding-left: 0.5rem !important
        }

        .pl-3 {
            padding-left: 0.75rem !important
        }

        .pl-4 {
            padding-left: 1rem !important
        }

        .pl-5 {
            padding-left: 1.25rem !important
        }

        .pr-0 {
            padding-right: 0 !important
        }

        .pr-1 {
            padding-right: 0.25rem !important
        }

        .pr-2 {
            padding-right: 0.5rem !important
        }

        .pr-3 {
            padding-right: 0.75rem !important
        }

        .pr-4 {
            padding-right: 1rem !important
        }

        .pr-5 {
            padding-right: 1.25rem !important
        }
    </style>

    <style>
        /** Define the margins of your page **/
        @page {
            margin: 100px 25px;
        }

        .preview {
            background: #525151;
        }

        header {
            position: fixed;
            top: -60px;
            left: 0px;
            right: 0px;
            height: 50px;

            /** Extra personal styles **/
            /* font-size: 20px !important; */
            /* background-color: #008B8B; */
            /* color: white; */
            /* text-align: center; */
            /* line-height: 35px; */
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;

            /** Extra personal styles **/
            /* font-size: 20px !important; */
            /* background-color: #008B8B; */
            /* color: white; */
            /* text-align: center; */
            /* line-height: 35px; */
        }

        body.preview footer,
        body.preview header {
            bottom: 0px;
            top: 0px;
            position: relative;
        }

        .break-always {
            page-break-after: always;
        }

        .break-avoid {
            page-break-after: avoid;
        }

        body.preview main[size="A4"] {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
            padding: 100px 25px;
        }

        body.preview header,
        body.preview footer {
            width: 210mm;
            top: 100px;
            left: 113mm;
        }
    </style>
</head>

<body {{ isset($preview) ? 'class=preview' : '' }}>
    @if (isset($header) && $header)
        @include($header, ['data' => $data])
    @endif
    @if (isset($footer) && $footer)
        @include($footer, ['data' => $data])
    @endif

    <main size="A4">
        @if (isset($body) && $body)
            @include($body, ['data' => $data])
        @endif
        {{-- <p class="break-always">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam laborum dolorem,
            minima non deleniti corrupti, earum impedit id explicabo quos voluptas suscipit ratione praesentium sapiente
            nihil aut culpa. Laboriosam, ipsam.
            Aliquam autem doloremque ad, velit magnam temporibus optio debitis! Amet soluta molestiae, earum voluptate
            aliquid, voluptatem fugiat exercitationem officiis reiciendis eos inventore excepturi dolorum nesciunt
            consequuntur, rerum ea! Voluptates, cumque.
            Nostrum porro praesentium omnis aliquam dicta odio, necessitatibus, adipisci nulla veniam quo eveniet vitae
            fugiat harum voluptate cumque velit vero eius nobis id ratione. Minus vel dignissimos praesentium at
            voluptatibus!
            Nostrum accusantium eveniet minima officia tenetur illum enim autem sunt ipsa iste ex necessitatibus eius
            laudantium, tempora sint velit voluptates nisi, error provident harum dicta, vel ullam alias sequi.
            Pariatur.
            Iusto, excepturi? Nostrum rem quae earum saepe optio error architecto? Consectetur, delectus! Dicta
            doloribus sit repellat ipsam aliquid illum porro sed! Autem officiis asperiores et ipsa minus cumque
            repellat provident?
        </p>
        <p class="break-avoid">Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam laborum dolorem, minima
            non deleniti corrupti, earum impedit id explicabo quos voluptas suscipit ratione praesentium sapiente nihil
            aut culpa. Laboriosam, ipsam.
            Aliquam autem doloremque ad, velit magnam temporibus optio debitis! Amet soluta molestiae, earum voluptate
            aliquid, voluptatem fugiat exercitationem officiis reiciendis eos inventore excepturi dolorum nesciunt
            consequuntur, rerum ea! Voluptates, cumque.
            Nostrum porro praesentium omnis aliquam dicta odio, necessitatibus, adipisci nulla veniam quo eveniet vitae
            fugiat harum voluptate cumque velit vero eius nobis id ratione. Minus vel dignissimos praesentium at
            voluptatibus!
            Nostrum accusantium eveniet minima officia tenetur illum enim autem sunt ipsa iste ex necessitatibus eius
            laudantium, tempora sint velit voluptates nisi, error provident harum dicta, vel ullam alias sequi.
            Pariatur.
            Iusto, excepturi? Nostrum rem quae earum saepe optio error architecto? Consectetur, delectus! Dicta
            doloribus sit repellat ipsam aliquid illum porro sed! Autem officiis asperiores et ipsa minus cumque
            repellat provident?
        </p> --}}
    </main>
</body>

</html>
