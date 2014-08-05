<?php

class AdminMyModuleRedirectConfigureController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $href = $this->context->link->getAdminLink('AdminModules')
            .'&configure=mymodule&token='.Tools::getAdminTokenLite('AdminModules');

        Tools::redirectAdmin($href);
    }
}