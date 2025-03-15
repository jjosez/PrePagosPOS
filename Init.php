<?php

namespace FacturaScripts\Plugins\PrePagosPOS;

use FacturaScripts\Core\Template\InitClass;
use FacturaScripts\Plugins\PrePagosPOS\Extension\Controller\POS;

class Init extends InitClass
{
    public function init(): void
    {
        $this->loadExtension(new POS());
    }

    public function uninstall(): void
    {
    }

    public function update(): void
    {
    }
}
