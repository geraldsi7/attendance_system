<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <!-- Extra details for Live View on GitHub Pages -->
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
</head>

<body>
    <style>
        * {
            font-family: "Helvetica Neue", Arial, sans-serif !important;
            -moz-osx-font-smoothing: grayscale;
            -webkit-font-smoothing: antialiased;
            font-size: 10px;
            line-height: 1.2em !important;
        }

        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin-top: 0;
            margin-bottom: .5rem;
            font-weight: 500;
            line-height: 1.2
        }

        h1,
        .h1 {
            font-size: 2.5rem;
        }

        h2,
        .h2 {
            font-size: 2rem;
        }

        h3,
        .h3 {
            font-size: 1.75rem;
        }

        h4,
        .h4 {
            font-size: 1.5rem;
        }

        h5,
        .h5 {
            font-size: 1.25rem;
        }

        h6,
        .h6 {
            font-size: 1rem;
        }

        .float-start {
            float: start !important;
        }

        .float-end {
            float: right !important;
        }

        .clearfix::after {
            display: block;
            clear: both;
            content: "";
        }

        .text-lowercase {
            text-transform: lowercase !important
        }

        .text-uppercase {
            text-transform: uppercase !important
        }

        .text-capitalize {
            text-transform: capitalize !important
        }

        .text-start {
            text-align: start !important
        }

        .text-end {
            text-align: right !important
        }

        .text-center {
            text-align: center !important
        }

        .fw-bold {
            font-weight: 700 !important
        }

        .mt-1 {
            margin-top: .25rem !important
        }

        .mt-2 {
            margin-top: .5rem !important
        }

        .mt-3 {
            margin-top: 1rem !important
        }

        .p-1 {
            padding: .25rem !important
        }

        .p-2 {
            padding: .5rem !important
        }

        .p-3 {
            padding: 1rem !important
        }

        .small,
        small {
            font-size: .8em
        }

        table {
            caption-side: bottom;
            border-collapse: collapse;
            width: 100%;
        }


        .table {
            background: transparent;
            margin-bottom: 1rem;
            color: #212529;
            vertical-align: top;
            border-color: #dee2e6
        }

        .border {
            border: 1px solid #dee2e6 !important
        }

        .border-1 {
            border-width: 1px !important
        }

        .border-dark {
            border-color: #212529 !important
        }
    </style>
    <div class="float-start">
        <span class="fw-bold text-uppercase h3">monthly payment record</span>
        <br>
        <!-- company's details -->
        <span class="text-uppercase fw-bold h5">smave company limited
        </span>

        <table class="mt-1 table text-capitalize">
            <thead>
                <tr>
                    <td class="p-1">
                        P.O. Box DT 588, Adenta
                    </td>
                    <td class="p-1">
                        +233 24 463 6940
                    </td>
                </tr>
                <tr>
                    <td class="p-1">
                        Accra, Ghana
                    </td>
                    <td class="p-1">
                        +233 30 297 4758
                    </td>
                </tr>
                <tr>
                    <td class="p-1">
                        Dodowa, Opposite Dodowa Market
                    </td>
                    <td class="p-1">
                        +233 30 297 4759
                    </td>
                </tr>
    </thead>
        </table>
    </div>

    <div class="float-end">
        <img src="../public/img/logo.png" class="mt-3" height="50">
        <br>
        <br>
        <small>{{ $date }}</small>
    </div>

    <div class="clearfix"></div>
    <p class="mt-1 text-uppercase fw-bold h6">
        {{ $title }}
    </p>
    <span class="fw-bold">From: </span><span>{{ $from }}</span>
    <br>
    <span class="fw-bold">To: </span><span>{{ $to }}</span>
    
    <div class="mt-3">
        <table class="table">
            <thead style="border: 1px solid #000;" class="text-uppercase">
                <tr>
                    <th scope="col" class="text-sm text-gray-900 p-1 text-start">
                        Date
                    </th>
                    <th scope="col" class="text-sm text-gray-900 p-1 text-start">
                        Reference
                    </th>
                    <th scope="col" class="text-sm text-gray-900 p-1 text-start">
                        Quantity
                    </th>
                    <th scope="col" class="text-sm text-gray-900 p-1 text-start">
                        Amount
                    </th>
                </tr>
            </thead>
            @forelse($data as $row)
            <thead style="border: 1px solid #000;">
                <tr>
                    <td class="p-2 text-start">
                        {{ $row->created_at->format('d/m/Y') }}
                    </td>
                    <td class="p-2 text-start">
                        {{ $row->title }}
                    </td>
                    <td class="p-2 text-end">
                        {{ number_format($row->quantity) }}
                    </td>
                    <td class="p-2 text-end">
                        {{ number_format($row->amount, 2) }}
                    </td>
                </tr>
            </thead>
            @empty
            <thead style="border: 1px solid #000;">
                <tr>
                    <td class="text-center" colspan="4">
                        No data
                    </td>
                </tr>
            </thead>
            @endforelse
            <tfoot style="border: 1px solid #000;">
                <tr>
                    <th scope="col" class="text-start fw-bold p-2 h5">Total</th>
                    <th></th>
                    <th scope="col" class="text-end fw-bold p-2 h5">{{ number_format($data->sum('quantity')) }}</th>
                    <th scope="col" class="text-end fw-bold p-2 h5">{{ $currency }} {{ number_format($data->sum('amount'), 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>