<?php

namespace FacturaScripts\Plugins\PrePagosPOS\Extension\Controller;

use Closure;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\ReciboCliente;
use FacturaScripts\Plugins\POS\Extension\Model\Base\SalesDocument;
use FacturaScripts\Plugins\POS\Model\PagoPuntoVenta;
use FacturaScripts\Plugins\PrePagos\Model\PrePago;

class POS
{
    public function save(): Closure
    {
        /**
         * @param SalesDocument $document
         * @param PagoPuntoVenta[] $payments
         */
        return function ($document, array $payments) {
            Tools::log('POS')->warning('Model Class' . $document->modelClassName());
            /** @var SalesDocument $document */
            if ($document->modelClassName() === 'FacturaCliente') {
                /** @var FacturaCliente $document */
                foreach ($document->getReceipts() as $receipt) {
                    $receipt->delete();
                }

                $count = 1;
                foreach ($payments as $payment) {
                    $receipt = new ReciboCliente();

                    $receipt->codcliente = $document->codcliente;
                    $receipt->coddivisa = $document->coddivisa;
                    $receipt->idempresa = $document->idempresa;
                    $receipt->idfactura = $document->primaryColumnValue();
                    $receipt->importe = $document->pagoNeto();
                    $receipt->nick = $document->nick;
                    $receipt->numero = $count;
                    $receipt->fecha = $document->fecha;
                    $receipt->setPaymentMethod($payment->codpago);

                    $receipt->save();
                    $count++;
                }
            }

            foreach ($payments as $payment) {
                $prepago = new PrePago();
                $prepago->amount = $payment->pagoNeto();
                $prepago->codcliente = $document->codcliente;
                $prepago->codpago = $payment->codpago;
                $prepago->modelid = $document->primaryColumnValue();
                $prepago->modelname = $document->modelClassName();
                $prepago->notes = 'POS: ' . Tools::lang()->trans($document->modelClassName()) . ' ' . $document->codigo;

                if (false === $prepago->save()) {
                    Tools::log('POS')->warning('Error al registrar el pago');
                }
            }
        };
    }
}
