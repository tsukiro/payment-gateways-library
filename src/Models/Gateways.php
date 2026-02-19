<?php

namespace Raion\Gateways\Models;

enum Gateways: string
{
    case Flow = 'flow';
    case Webpay = 'webpay';
    case MercadoPago = 'mercadopago';
}
