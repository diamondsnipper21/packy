<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=0.8">
        <title>Facture</title>
        <style>
            html { background-color: white; line-height: 22px; color: #333 }
            body { font-family: Arial, sans-serif; margin: 20px; }
            .invoice-container { max-width: 800px; margin: auto; padding: 20px; }
            img.logo { width: 120px; padding-top: 10px;  }
            .section { margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f4f4f4; }
            .total-row td { font-weight: bold; }
            .total-row td:last-child { text-align: right; }
            .legal { font-size: 12px; margin-top: 20px; font-style: italic; }
            .blue { color: #000 }
            .no-border { border: none; background: transparent; }
            .border-top { border-top: 1px solid #b8c3cf; }
            .border-bottom { border-bottom: 1px solid #b8c3cf; }
            table tr th, table tr td { padding-top: 20px; padding-bottom: 20px }
        </style>
    </head>

    <body>
    <div class="invoice-container">
        <table style="margin-bottom: 40px;">
            <tr>
                <th class="no-border" style="padding: 0px; font-weight: 400">
                    <img class="logo" src="<?php echo env('APP_URL'); ?>/assets/logo/packie-logo.png" style="margin-bottom: 20px;"/><br/>
                    Wolfeo<br />
                    8 Clanwilliam Square<br />
                    DUBLIN 2<br />
                    Co. Dublin<br />
                    D02PF75<br />
                    Ireland<br />
                </th>
                <th class="no-border" style="text-align: right; padding: 0px; vertical-align: top">
                    <p style="font-size: 30px; font-weight: 700">INVOICE</p>
                </th>
            </tr>
        </table>

        <div style="background-color: #f5f8fb; padding: 10px 20px 20px 20px; border-radius: 5px; margin-bottom: 10px;">
            <table>
                <tr>
                    <th class="no-border" style="padding: 0px; font-weight: 400; vertical-align: top;">
                        <strong class="blue">Bill to:</strong>
                    </th>
                    <th class="no-border" style="padding: 0px; font-weight: 400; vertical-align: top">
                        {{ $customer['business_name'] }}<br />
                        @if($customer['address'])
                            {{ $customer['address'] }}<br />
                        @endif
                        @if($customer['zipcode'] || $customer['city'])
                            @if($customer['zipcode'])
                                {{ $customer['zipcode'] }}
                            @endif
                            @if($customer['city'])
                                {{ $customer['city'] }}
                            @endif
                            <br />
                        @endif
                        {{ $customer['country'] }}<br />
                        @if($customer['vat_number'])
                            {{ $customer['vat_number'] }}<br />
                        @endif
                    </th>
                    <th class="no-border" style="padding: 0px; font-weight: 400; vertical-align: top; text-align: right">
                        <strong class="blue">Invoice No:</strong><br/>
                        <strong class="blue">Issue Date:</strong><br/>
                        <strong class="blue">Period:</strong>
                    </th>
                    <th class="no-border" style="padding: 0px; font-weight: 400; vertical-align: top; text-align: right">
                        {{ $number }}<br/>
                        {{ $date }}<br/>
                        {{ $period_start }}<br/>
                        to {{ $period_end }}
                    </th>
                </tr>
            </table>
        </div>

        <div>
            <table>
                <tr>
                    <th class="no-border border-bottom blue" style="padding: 10px; vertical-align: middle; padding-bottom: 12px; padding-left: 0px;">Service</th>
                    <th class="no-border border-bottom blue" style="text-align:left; width: 20%">Qty</th>
                    <th class="no-border border-bottom blue" style="text-align:left; width: 20%">Rate</th>
                    <th class="no-border border-bottom blue" style="text-align:right; width: 20%">Amount</th>
                </tr>
                @foreach($products as $product)
                    <tr>
                        <td class="no-border" style="padding-left: 0px; padding-right: 20px">{{ $product['name'] }}</td>
                        <td class="no-border">{{ $product['quantity'] }}</td>
                        <td class="no-border" style="text-align: left">{{ $currency }} {{ number_format($product['unit_price'], 2) }}</td>
                        <td class="no-border" style="text-align: right">{{ $currency }} {{ number_format($product['total'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="no-border border-top"></td>
                    <td class="no-border border-top"></td>
                    <td class="no-border border-top" style="text-align:left; padding-top: 10px; padding-bottom: 0px;">Subtotal</td>
                    <td class="no-border border-top" style="padding-top: 10px; padding-bottom: 0px; font-weight: 400; text-align: right">{{ $currency }} {{ number_format($total_vat_excl, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="no-border"></td>
                    <td class="no-border"></td>
                    <td class="no-border border-bottom" style="text-align:left; padding-top: 10px;">VAT (23%)</td>
                    <td class="no-border border-bottom" style="padding-top: 10px; font-weight: 400; text-align: right">{{ $currency }} {{ number_format($vat, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="no-border"></td>
                    <td class="no-border"></td>
                    <td class="no-border" style="text-align:left;">Total</td>
                    <td class="no-border" style="font-weight: 400; text-align: right">{{ $currency }} {{ number_format($total_vat_incl, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="legal"></div>
    </div>
    </body>
</html>