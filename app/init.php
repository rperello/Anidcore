<?php

// application initialization

Ac::hookRegister("ac.before.router_resolve", array("App", "beforeRouterResolve"));