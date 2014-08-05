<?php

class AdminRequestAModuleConfigurationController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $href = $this->context->link->getAdminLink('AdminModules')
            .'&configure=requestamodule&token='.Tools::getAdminTokenLite('AdminModules');
        Tools::redirectAdmin($href);
    }
}