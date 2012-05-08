<?php

// application initialization

Ac::hookRegister(Ac::HOOK_BEFORE_ROUTER_RESOLVE, array("App", "beforeRouterResolve"));