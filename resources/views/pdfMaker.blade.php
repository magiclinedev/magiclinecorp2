<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PDF</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
</head>
<style>
    @page {
        size: 14in 8.5in;
    }
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .border{
        border-style: solid;
        border-width: 3px;
    }

    .container {
        width: 100%;
        height: auto;
        margin: 0 auto;
        padding: 0;
        text-align: center;
    }

    table {
        width: 100%;
        height: 100% auto;
    }

    .two-column {

    }

    .column {
        flex: 1;
    }

    .product-image {
        width: 93%;
    }

    .additional-images {
        display: flex;
        flex-wrap: wrap;
    }

    .additional-image {
        width: 47%;

    }

    .logo-images {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 10px;

    }

    .logo-image {
        max-width: 100px;
        max-height: 100px;
        width: 100%;

    }

    .info-container {
        flex: 1;
    }

    .item-reference {
        font-weight: bold;
        text-align: center;
        margin: 10px 0;
        font-size: 30px;
    }

    .company-label,
    .category-label,
    .type-label,
    .description-label {
        font-weight: bold;
        margin-left: 10px;
    }

    .company-name,
    .category-name,
    .type-name,
    .description {
        flex: 1;
        margin-left: 10px;
        font-size: 16px;
    }
    .content {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }
    /* tr
    td
    {
        border: 1px solid black;
    } */
</style>
    {{-- FOR IMAGES --}}
    @php
        $imageLogo = Storage::disk('dropbox')->url($companyLogo);
    @endphp

    {{-- <div class="container"> --}}
        <table class="border">
            <tr>
                <!-- Column 1 -->
                <td style="width: 30%;" rowspan="3">
                    <div class="content">
                    <center>
                        <img id="mainImage" src="{{ $imageUrls[0] }}" alt="Product Image" class="product-image ">
                    </center>
                </div>
                </td>

                <td style="width: 25%;" rowspan="3">
                    <div class="additional-images">
                        <center>
                        @foreach ($imageUrls as $index => $imagePath)
                            @if ($index > 1 && $index <= 5)  {{-- Load images 3 to 7 (up to 5 images) --}}
                                <img src="{{ $imagePath }}" class="additional-image" loading="lazy">
                            @endif
                        @endforeach
                        </center>
                    </div>
                </td>

                <!-- Column 2 -->
                <td class="two-column border" style="width: 25%; margin-top: 0; text-align: left; -top: none;" rowspan="4">
                    <div class="description-label">Description:</div>
                    <div class="description">{!! $mannequin->description !!}</div>
                </td>

                <td style="width: 10%; margin-top: 0; text-align: left;">
                    <center>
                        <img id="mainImage" src="{{ $imageLogo }}" alt="Product Image" class="logo-image">
                    </center>
                </td>
            </tr>
            <tr>
                <td class="border">
                    <div>address</div>
                </td>
            </tr>
            <tr>
                <td rowspan="2" ><div class="item-reference" style="transform: rotate(90deg);">Sample Collection</div></td>
            </tr>
            {{-- <tr>
            </tr> --}}
            <tr>
                <td colspan="2">
                    <div class="item-reference">{{ $mannequin->itemref }}</div>
                </td>
            </tr>
        </table>
    {{-- </div> --}}
</body>
</html>
