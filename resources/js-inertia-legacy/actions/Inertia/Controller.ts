import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
const Controller980bb49ee7ae63891f1d891d2fbcf1c9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})

Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition = {
    methods: ["get","head"],
    url: '/',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
Controller980bb49ee7ae63891f1d891d2fbcf1c9.url = (options?: RouteQueryOptions) => {
    return Controller980bb49ee7ae63891f1d891d2fbcf1c9.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
Controller980bb49ee7ae63891f1d891d2fbcf1c9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
Controller980bb49ee7ae63891f1d891d2fbcf1c9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
    const Controller980bb49ee7ae63891f1d891d2fbcf1c9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
        Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/'
 */
        Controller980bb49ee7ae63891f1d891d2fbcf1c9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller980bb49ee7ae63891f1d891d2fbcf1c9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller980bb49ee7ae63891f1d891d2fbcf1c9.form = Controller980bb49ee7ae63891f1d891d2fbcf1c9Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
const Controller42a740574ecbfbac32f8cc353fc32db9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})

Controller42a740574ecbfbac32f8cc353fc32db9.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
Controller42a740574ecbfbac32f8cc353fc32db9.url = (options?: RouteQueryOptions) => {
    return Controller42a740574ecbfbac32f8cc353fc32db9.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
Controller42a740574ecbfbac32f8cc353fc32db9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
Controller42a740574ecbfbac32f8cc353fc32db9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
    const Controller42a740574ecbfbac32f8cc353fc32db9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
        Controller42a740574ecbfbac32f8cc353fc32db9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller42a740574ecbfbac32f8cc353fc32db9.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/dashboard'
 */
        Controller42a740574ecbfbac32f8cc353fc32db9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller42a740574ecbfbac32f8cc353fc32db9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller42a740574ecbfbac32f8cc353fc32db9.form = Controller42a740574ecbfbac32f8cc353fc32db9Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
const Controller1118256a56bcf330a5e6aa92c3538a4c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller1118256a56bcf330a5e6aa92c3538a4c.url(options),
    method: 'get',
})

Controller1118256a56bcf330a5e6aa92c3538a4c.definition = {
    methods: ["get","head"],
    url: '/ar/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
Controller1118256a56bcf330a5e6aa92c3538a4c.url = (options?: RouteQueryOptions) => {
    return Controller1118256a56bcf330a5e6aa92c3538a4c.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
Controller1118256a56bcf330a5e6aa92c3538a4c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller1118256a56bcf330a5e6aa92c3538a4c.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
Controller1118256a56bcf330a5e6aa92c3538a4c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller1118256a56bcf330a5e6aa92c3538a4c.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
    const Controller1118256a56bcf330a5e6aa92c3538a4cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller1118256a56bcf330a5e6aa92c3538a4c.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
        Controller1118256a56bcf330a5e6aa92c3538a4cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller1118256a56bcf330a5e6aa92c3538a4c.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
        Controller1118256a56bcf330a5e6aa92c3538a4cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller1118256a56bcf330a5e6aa92c3538a4c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller1118256a56bcf330a5e6aa92c3538a4c.form = Controller1118256a56bcf330a5e6aa92c3538a4cForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/create'
 */
const Controllerf0e55d005a22f74c12a066c89b7a88b2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerf0e55d005a22f74c12a066c89b7a88b2.url(options),
    method: 'get',
})

Controllerf0e55d005a22f74c12a066c89b7a88b2.definition = {
    methods: ["get","head"],
    url: '/ar/customers/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/create'
 */
Controllerf0e55d005a22f74c12a066c89b7a88b2.url = (options?: RouteQueryOptions) => {
    return Controllerf0e55d005a22f74c12a066c89b7a88b2.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/create'
 */
Controllerf0e55d005a22f74c12a066c89b7a88b2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerf0e55d005a22f74c12a066c89b7a88b2.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/create'
 */
Controllerf0e55d005a22f74c12a066c89b7a88b2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerf0e55d005a22f74c12a066c89b7a88b2.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/create'
 */
    const Controllerf0e55d005a22f74c12a066c89b7a88b2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerf0e55d005a22f74c12a066c89b7a88b2.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/create'
 */
        Controllerf0e55d005a22f74c12a066c89b7a88b2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerf0e55d005a22f74c12a066c89b7a88b2.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/create'
 */
        Controllerf0e55d005a22f74c12a066c89b7a88b2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerf0e55d005a22f74c12a066c89b7a88b2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerf0e55d005a22f74c12a066c89b7a88b2.form = Controllerf0e55d005a22f74c12a066c89b7a88b2Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/{id}'
 */
const Controller031ed354cac38f56c524069c66f34ed1 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller031ed354cac38f56c524069c66f34ed1.url(args, options),
    method: 'get',
})

Controller031ed354cac38f56c524069c66f34ed1.definition = {
    methods: ["get","head"],
    url: '/ar/customers/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/{id}'
 */
Controller031ed354cac38f56c524069c66f34ed1.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return Controller031ed354cac38f56c524069c66f34ed1.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/{id}'
 */
Controller031ed354cac38f56c524069c66f34ed1.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller031ed354cac38f56c524069c66f34ed1.url(args, options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/{id}'
 */
Controller031ed354cac38f56c524069c66f34ed1.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller031ed354cac38f56c524069c66f34ed1.url(args, options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/{id}'
 */
    const Controller031ed354cac38f56c524069c66f34ed1Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller031ed354cac38f56c524069c66f34ed1.url(args, options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/{id}'
 */
        Controller031ed354cac38f56c524069c66f34ed1Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller031ed354cac38f56c524069c66f34ed1.url(args, options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers/{id}'
 */
        Controller031ed354cac38f56c524069c66f34ed1Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller031ed354cac38f56c524069c66f34ed1.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller031ed354cac38f56c524069c66f34ed1.form = Controller031ed354cac38f56c524069c66f34ed1Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
const Controller5018e050909b7293873f39c77bae3f7e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller5018e050909b7293873f39c77bae3f7e.url(options),
    method: 'get',
})

Controller5018e050909b7293873f39c77bae3f7e.definition = {
    methods: ["get","head"],
    url: '/ar/sales-invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
Controller5018e050909b7293873f39c77bae3f7e.url = (options?: RouteQueryOptions) => {
    return Controller5018e050909b7293873f39c77bae3f7e.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
Controller5018e050909b7293873f39c77bae3f7e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller5018e050909b7293873f39c77bae3f7e.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
Controller5018e050909b7293873f39c77bae3f7e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller5018e050909b7293873f39c77bae3f7e.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
    const Controller5018e050909b7293873f39c77bae3f7eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller5018e050909b7293873f39c77bae3f7e.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
        Controller5018e050909b7293873f39c77bae3f7eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller5018e050909b7293873f39c77bae3f7e.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
        Controller5018e050909b7293873f39c77bae3f7eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller5018e050909b7293873f39c77bae3f7e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller5018e050909b7293873f39c77bae3f7e.form = Controller5018e050909b7293873f39c77bae3f7eForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/create'
 */
const Controller18b4e39c35661053e4c6797bea4bf153 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller18b4e39c35661053e4c6797bea4bf153.url(options),
    method: 'get',
})

Controller18b4e39c35661053e4c6797bea4bf153.definition = {
    methods: ["get","head"],
    url: '/ar/sales-invoices/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/create'
 */
Controller18b4e39c35661053e4c6797bea4bf153.url = (options?: RouteQueryOptions) => {
    return Controller18b4e39c35661053e4c6797bea4bf153.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/create'
 */
Controller18b4e39c35661053e4c6797bea4bf153.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller18b4e39c35661053e4c6797bea4bf153.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/create'
 */
Controller18b4e39c35661053e4c6797bea4bf153.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller18b4e39c35661053e4c6797bea4bf153.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/create'
 */
    const Controller18b4e39c35661053e4c6797bea4bf153Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller18b4e39c35661053e4c6797bea4bf153.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/create'
 */
        Controller18b4e39c35661053e4c6797bea4bf153Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller18b4e39c35661053e4c6797bea4bf153.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/create'
 */
        Controller18b4e39c35661053e4c6797bea4bf153Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller18b4e39c35661053e4c6797bea4bf153.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller18b4e39c35661053e4c6797bea4bf153.form = Controller18b4e39c35661053e4c6797bea4bf153Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/{id}'
 */
const Controller9b41ecbbc53d3e874d18ff532724501f = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller9b41ecbbc53d3e874d18ff532724501f.url(args, options),
    method: 'get',
})

Controller9b41ecbbc53d3e874d18ff532724501f.definition = {
    methods: ["get","head"],
    url: '/ar/sales-invoices/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/{id}'
 */
Controller9b41ecbbc53d3e874d18ff532724501f.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return Controller9b41ecbbc53d3e874d18ff532724501f.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/{id}'
 */
Controller9b41ecbbc53d3e874d18ff532724501f.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller9b41ecbbc53d3e874d18ff532724501f.url(args, options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/{id}'
 */
Controller9b41ecbbc53d3e874d18ff532724501f.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller9b41ecbbc53d3e874d18ff532724501f.url(args, options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/{id}'
 */
    const Controller9b41ecbbc53d3e874d18ff532724501fForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller9b41ecbbc53d3e874d18ff532724501f.url(args, options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/{id}'
 */
        Controller9b41ecbbc53d3e874d18ff532724501fForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller9b41ecbbc53d3e874d18ff532724501f.url(args, options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices/{id}'
 */
        Controller9b41ecbbc53d3e874d18ff532724501fForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller9b41ecbbc53d3e874d18ff532724501f.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller9b41ecbbc53d3e874d18ff532724501f.form = Controller9b41ecbbc53d3e874d18ff532724501fForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
const Controller35712ad4b783b0c09dba11f00ef7f74f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller35712ad4b783b0c09dba11f00ef7f74f.url(options),
    method: 'get',
})

Controller35712ad4b783b0c09dba11f00ef7f74f.definition = {
    methods: ["get","head"],
    url: '/ar/receipts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
Controller35712ad4b783b0c09dba11f00ef7f74f.url = (options?: RouteQueryOptions) => {
    return Controller35712ad4b783b0c09dba11f00ef7f74f.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
Controller35712ad4b783b0c09dba11f00ef7f74f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller35712ad4b783b0c09dba11f00ef7f74f.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
Controller35712ad4b783b0c09dba11f00ef7f74f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller35712ad4b783b0c09dba11f00ef7f74f.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
    const Controller35712ad4b783b0c09dba11f00ef7f74fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller35712ad4b783b0c09dba11f00ef7f74f.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
        Controller35712ad4b783b0c09dba11f00ef7f74fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller35712ad4b783b0c09dba11f00ef7f74f.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
        Controller35712ad4b783b0c09dba11f00ef7f74fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller35712ad4b783b0c09dba11f00ef7f74f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller35712ad4b783b0c09dba11f00ef7f74f.form = Controller35712ad4b783b0c09dba11f00ef7f74fForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts/create'
 */
const Controller39d6c15ce76ee0419882c140ca7d37ab = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller39d6c15ce76ee0419882c140ca7d37ab.url(options),
    method: 'get',
})

Controller39d6c15ce76ee0419882c140ca7d37ab.definition = {
    methods: ["get","head"],
    url: '/ar/receipts/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts/create'
 */
Controller39d6c15ce76ee0419882c140ca7d37ab.url = (options?: RouteQueryOptions) => {
    return Controller39d6c15ce76ee0419882c140ca7d37ab.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts/create'
 */
Controller39d6c15ce76ee0419882c140ca7d37ab.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller39d6c15ce76ee0419882c140ca7d37ab.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts/create'
 */
Controller39d6c15ce76ee0419882c140ca7d37ab.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller39d6c15ce76ee0419882c140ca7d37ab.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts/create'
 */
    const Controller39d6c15ce76ee0419882c140ca7d37abForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller39d6c15ce76ee0419882c140ca7d37ab.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts/create'
 */
        Controller39d6c15ce76ee0419882c140ca7d37abForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller39d6c15ce76ee0419882c140ca7d37ab.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts/create'
 */
        Controller39d6c15ce76ee0419882c140ca7d37abForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller39d6c15ce76ee0419882c140ca7d37ab.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller39d6c15ce76ee0419882c140ca7d37ab.form = Controller39d6c15ce76ee0419882c140ca7d37abForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
const Controller089251ce1ff4fd7de0094d36e3e53fc2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller089251ce1ff4fd7de0094d36e3e53fc2.url(options),
    method: 'get',
})

Controller089251ce1ff4fd7de0094d36e3e53fc2.definition = {
    methods: ["get","head"],
    url: '/ar/credit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
Controller089251ce1ff4fd7de0094d36e3e53fc2.url = (options?: RouteQueryOptions) => {
    return Controller089251ce1ff4fd7de0094d36e3e53fc2.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
Controller089251ce1ff4fd7de0094d36e3e53fc2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller089251ce1ff4fd7de0094d36e3e53fc2.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
Controller089251ce1ff4fd7de0094d36e3e53fc2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller089251ce1ff4fd7de0094d36e3e53fc2.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
    const Controller089251ce1ff4fd7de0094d36e3e53fc2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller089251ce1ff4fd7de0094d36e3e53fc2.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
        Controller089251ce1ff4fd7de0094d36e3e53fc2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller089251ce1ff4fd7de0094d36e3e53fc2.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
        Controller089251ce1ff4fd7de0094d36e3e53fc2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller089251ce1ff4fd7de0094d36e3e53fc2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller089251ce1ff4fd7de0094d36e3e53fc2.form = Controller089251ce1ff4fd7de0094d36e3e53fc2Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
const Controllera3d34301b06eab863b38252a14a03fc7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllera3d34301b06eab863b38252a14a03fc7.url(options),
    method: 'get',
})

Controllera3d34301b06eab863b38252a14a03fc7.definition = {
    methods: ["get","head"],
    url: '/ar/debit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
Controllera3d34301b06eab863b38252a14a03fc7.url = (options?: RouteQueryOptions) => {
    return Controllera3d34301b06eab863b38252a14a03fc7.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
Controllera3d34301b06eab863b38252a14a03fc7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllera3d34301b06eab863b38252a14a03fc7.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
Controllera3d34301b06eab863b38252a14a03fc7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllera3d34301b06eab863b38252a14a03fc7.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
    const Controllera3d34301b06eab863b38252a14a03fc7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllera3d34301b06eab863b38252a14a03fc7.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
        Controllera3d34301b06eab863b38252a14a03fc7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllera3d34301b06eab863b38252a14a03fc7.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
        Controllera3d34301b06eab863b38252a14a03fc7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllera3d34301b06eab863b38252a14a03fc7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllera3d34301b06eab863b38252a14a03fc7.form = Controllera3d34301b06eab863b38252a14a03fc7Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
const Controllere27462a7e07c45ad0ffa53038f3eb5c4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere27462a7e07c45ad0ffa53038f3eb5c4.url(options),
    method: 'get',
})

Controllere27462a7e07c45ad0ffa53038f3eb5c4.definition = {
    methods: ["get","head"],
    url: '/ar/aging',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
Controllere27462a7e07c45ad0ffa53038f3eb5c4.url = (options?: RouteQueryOptions) => {
    return Controllere27462a7e07c45ad0ffa53038f3eb5c4.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
Controllere27462a7e07c45ad0ffa53038f3eb5c4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere27462a7e07c45ad0ffa53038f3eb5c4.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
Controllere27462a7e07c45ad0ffa53038f3eb5c4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere27462a7e07c45ad0ffa53038f3eb5c4.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
    const Controllere27462a7e07c45ad0ffa53038f3eb5c4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllere27462a7e07c45ad0ffa53038f3eb5c4.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
        Controllere27462a7e07c45ad0ffa53038f3eb5c4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere27462a7e07c45ad0ffa53038f3eb5c4.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
        Controllere27462a7e07c45ad0ffa53038f3eb5c4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere27462a7e07c45ad0ffa53038f3eb5c4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllere27462a7e07c45ad0ffa53038f3eb5c4.form = Controllere27462a7e07c45ad0ffa53038f3eb5c4Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
const Controllerd358fcd5c46fb0eca276b16d01891ae3 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerd358fcd5c46fb0eca276b16d01891ae3.url(options),
    method: 'get',
})

Controllerd358fcd5c46fb0eca276b16d01891ae3.definition = {
    methods: ["get","head"],
    url: '/ar/open-items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
Controllerd358fcd5c46fb0eca276b16d01891ae3.url = (options?: RouteQueryOptions) => {
    return Controllerd358fcd5c46fb0eca276b16d01891ae3.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
Controllerd358fcd5c46fb0eca276b16d01891ae3.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerd358fcd5c46fb0eca276b16d01891ae3.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
Controllerd358fcd5c46fb0eca276b16d01891ae3.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerd358fcd5c46fb0eca276b16d01891ae3.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
    const Controllerd358fcd5c46fb0eca276b16d01891ae3Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerd358fcd5c46fb0eca276b16d01891ae3.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
        Controllerd358fcd5c46fb0eca276b16d01891ae3Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerd358fcd5c46fb0eca276b16d01891ae3.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
        Controllerd358fcd5c46fb0eca276b16d01891ae3Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerd358fcd5c46fb0eca276b16d01891ae3.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerd358fcd5c46fb0eca276b16d01891ae3.form = Controllerd358fcd5c46fb0eca276b16d01891ae3Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
const Controller8f345849b2122401f90a7be9f20a2b67 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller8f345849b2122401f90a7be9f20a2b67.url(options),
    method: 'get',
})

Controller8f345849b2122401f90a7be9f20a2b67.definition = {
    methods: ["get","head"],
    url: '/ar/reconciliation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
Controller8f345849b2122401f90a7be9f20a2b67.url = (options?: RouteQueryOptions) => {
    return Controller8f345849b2122401f90a7be9f20a2b67.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
Controller8f345849b2122401f90a7be9f20a2b67.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller8f345849b2122401f90a7be9f20a2b67.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
Controller8f345849b2122401f90a7be9f20a2b67.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller8f345849b2122401f90a7be9f20a2b67.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
    const Controller8f345849b2122401f90a7be9f20a2b67Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller8f345849b2122401f90a7be9f20a2b67.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
        Controller8f345849b2122401f90a7be9f20a2b67Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller8f345849b2122401f90a7be9f20a2b67.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
        Controller8f345849b2122401f90a7be9f20a2b67Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller8f345849b2122401f90a7be9f20a2b67.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller8f345849b2122401f90a7be9f20a2b67.form = Controller8f345849b2122401f90a7be9f20a2b67Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
const Controller57220b0fd621167c299cab63d571e1b2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller57220b0fd621167c299cab63d571e1b2.url(options),
    method: 'get',
})

Controller57220b0fd621167c299cab63d571e1b2.definition = {
    methods: ["get","head"],
    url: '/ap/suppliers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
Controller57220b0fd621167c299cab63d571e1b2.url = (options?: RouteQueryOptions) => {
    return Controller57220b0fd621167c299cab63d571e1b2.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
Controller57220b0fd621167c299cab63d571e1b2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller57220b0fd621167c299cab63d571e1b2.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
Controller57220b0fd621167c299cab63d571e1b2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller57220b0fd621167c299cab63d571e1b2.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
    const Controller57220b0fd621167c299cab63d571e1b2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller57220b0fd621167c299cab63d571e1b2.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
        Controller57220b0fd621167c299cab63d571e1b2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller57220b0fd621167c299cab63d571e1b2.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
        Controller57220b0fd621167c299cab63d571e1b2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller57220b0fd621167c299cab63d571e1b2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller57220b0fd621167c299cab63d571e1b2.form = Controller57220b0fd621167c299cab63d571e1b2Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers/create'
 */
const Controller412f38bb134bb2663f4b23cfcc2a2d4b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller412f38bb134bb2663f4b23cfcc2a2d4b.url(options),
    method: 'get',
})

Controller412f38bb134bb2663f4b23cfcc2a2d4b.definition = {
    methods: ["get","head"],
    url: '/ap/suppliers/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers/create'
 */
Controller412f38bb134bb2663f4b23cfcc2a2d4b.url = (options?: RouteQueryOptions) => {
    return Controller412f38bb134bb2663f4b23cfcc2a2d4b.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers/create'
 */
Controller412f38bb134bb2663f4b23cfcc2a2d4b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller412f38bb134bb2663f4b23cfcc2a2d4b.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers/create'
 */
Controller412f38bb134bb2663f4b23cfcc2a2d4b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller412f38bb134bb2663f4b23cfcc2a2d4b.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers/create'
 */
    const Controller412f38bb134bb2663f4b23cfcc2a2d4bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller412f38bb134bb2663f4b23cfcc2a2d4b.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers/create'
 */
        Controller412f38bb134bb2663f4b23cfcc2a2d4bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller412f38bb134bb2663f4b23cfcc2a2d4b.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers/create'
 */
        Controller412f38bb134bb2663f4b23cfcc2a2d4bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller412f38bb134bb2663f4b23cfcc2a2d4b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller412f38bb134bb2663f4b23cfcc2a2d4b.form = Controller412f38bb134bb2663f4b23cfcc2a2d4bForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
const Controllerf2f4a7bece28fd4a708d9b537fdc3ff2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.url(options),
    method: 'get',
})

Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.definition = {
    methods: ["get","head"],
    url: '/ap/purchase-invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.url = (options?: RouteQueryOptions) => {
    return Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
    const Controllerf2f4a7bece28fd4a708d9b537fdc3ff2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
        Controllerf2f4a7bece28fd4a708d9b537fdc3ff2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
        Controllerf2f4a7bece28fd4a708d9b537fdc3ff2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerf2f4a7bece28fd4a708d9b537fdc3ff2.form = Controllerf2f4a7bece28fd4a708d9b537fdc3ff2Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices/create'
 */
const Controller8ff07afc862b8f40c63e5e14fd2ff47d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller8ff07afc862b8f40c63e5e14fd2ff47d.url(options),
    method: 'get',
})

Controller8ff07afc862b8f40c63e5e14fd2ff47d.definition = {
    methods: ["get","head"],
    url: '/ap/purchase-invoices/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices/create'
 */
Controller8ff07afc862b8f40c63e5e14fd2ff47d.url = (options?: RouteQueryOptions) => {
    return Controller8ff07afc862b8f40c63e5e14fd2ff47d.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices/create'
 */
Controller8ff07afc862b8f40c63e5e14fd2ff47d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller8ff07afc862b8f40c63e5e14fd2ff47d.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices/create'
 */
Controller8ff07afc862b8f40c63e5e14fd2ff47d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller8ff07afc862b8f40c63e5e14fd2ff47d.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices/create'
 */
    const Controller8ff07afc862b8f40c63e5e14fd2ff47dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller8ff07afc862b8f40c63e5e14fd2ff47d.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices/create'
 */
        Controller8ff07afc862b8f40c63e5e14fd2ff47dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller8ff07afc862b8f40c63e5e14fd2ff47d.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices/create'
 */
        Controller8ff07afc862b8f40c63e5e14fd2ff47dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller8ff07afc862b8f40c63e5e14fd2ff47d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller8ff07afc862b8f40c63e5e14fd2ff47d.form = Controller8ff07afc862b8f40c63e5e14fd2ff47dForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
const Controller6912af11a1213fe8460431fafb31370b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller6912af11a1213fe8460431fafb31370b.url(options),
    method: 'get',
})

Controller6912af11a1213fe8460431fafb31370b.definition = {
    methods: ["get","head"],
    url: '/ap/supplier-payments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
Controller6912af11a1213fe8460431fafb31370b.url = (options?: RouteQueryOptions) => {
    return Controller6912af11a1213fe8460431fafb31370b.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
Controller6912af11a1213fe8460431fafb31370b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller6912af11a1213fe8460431fafb31370b.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
Controller6912af11a1213fe8460431fafb31370b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller6912af11a1213fe8460431fafb31370b.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
    const Controller6912af11a1213fe8460431fafb31370bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller6912af11a1213fe8460431fafb31370b.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
        Controller6912af11a1213fe8460431fafb31370bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller6912af11a1213fe8460431fafb31370b.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
        Controller6912af11a1213fe8460431fafb31370bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller6912af11a1213fe8460431fafb31370b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller6912af11a1213fe8460431fafb31370b.form = Controller6912af11a1213fe8460431fafb31370bForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
const Controllerc1d705b146f1a3c714b1e3aefb53d82c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerc1d705b146f1a3c714b1e3aefb53d82c.url(options),
    method: 'get',
})

Controllerc1d705b146f1a3c714b1e3aefb53d82c.definition = {
    methods: ["get","head"],
    url: '/ap/aging',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
Controllerc1d705b146f1a3c714b1e3aefb53d82c.url = (options?: RouteQueryOptions) => {
    return Controllerc1d705b146f1a3c714b1e3aefb53d82c.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
Controllerc1d705b146f1a3c714b1e3aefb53d82c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerc1d705b146f1a3c714b1e3aefb53d82c.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
Controllerc1d705b146f1a3c714b1e3aefb53d82c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerc1d705b146f1a3c714b1e3aefb53d82c.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
    const Controllerc1d705b146f1a3c714b1e3aefb53d82cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerc1d705b146f1a3c714b1e3aefb53d82c.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
        Controllerc1d705b146f1a3c714b1e3aefb53d82cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerc1d705b146f1a3c714b1e3aefb53d82c.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
        Controllerc1d705b146f1a3c714b1e3aefb53d82cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerc1d705b146f1a3c714b1e3aefb53d82c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerc1d705b146f1a3c714b1e3aefb53d82c.form = Controllerc1d705b146f1a3c714b1e3aefb53d82cForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
const Controller9c802de5a611c892ce508c046f09efd4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller9c802de5a611c892ce508c046f09efd4.url(options),
    method: 'get',
})

Controller9c802de5a611c892ce508c046f09efd4.definition = {
    methods: ["get","head"],
    url: '/ap/open-items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
Controller9c802de5a611c892ce508c046f09efd4.url = (options?: RouteQueryOptions) => {
    return Controller9c802de5a611c892ce508c046f09efd4.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
Controller9c802de5a611c892ce508c046f09efd4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller9c802de5a611c892ce508c046f09efd4.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
Controller9c802de5a611c892ce508c046f09efd4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller9c802de5a611c892ce508c046f09efd4.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
    const Controller9c802de5a611c892ce508c046f09efd4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller9c802de5a611c892ce508c046f09efd4.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
        Controller9c802de5a611c892ce508c046f09efd4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller9c802de5a611c892ce508c046f09efd4.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
        Controller9c802de5a611c892ce508c046f09efd4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller9c802de5a611c892ce508c046f09efd4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller9c802de5a611c892ce508c046f09efd4.form = Controller9c802de5a611c892ce508c046f09efd4Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
const Controller7cb57da946889d72325b7a2a17979dfe = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller7cb57da946889d72325b7a2a17979dfe.url(options),
    method: 'get',
})

Controller7cb57da946889d72325b7a2a17979dfe.definition = {
    methods: ["get","head"],
    url: '/ap/reconciliation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
Controller7cb57da946889d72325b7a2a17979dfe.url = (options?: RouteQueryOptions) => {
    return Controller7cb57da946889d72325b7a2a17979dfe.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
Controller7cb57da946889d72325b7a2a17979dfe.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller7cb57da946889d72325b7a2a17979dfe.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
Controller7cb57da946889d72325b7a2a17979dfe.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller7cb57da946889d72325b7a2a17979dfe.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
    const Controller7cb57da946889d72325b7a2a17979dfeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller7cb57da946889d72325b7a2a17979dfe.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
        Controller7cb57da946889d72325b7a2a17979dfeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller7cb57da946889d72325b7a2a17979dfe.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
        Controller7cb57da946889d72325b7a2a17979dfeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller7cb57da946889d72325b7a2a17979dfe.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller7cb57da946889d72325b7a2a17979dfe.form = Controller7cb57da946889d72325b7a2a17979dfeForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
const Controller2e24e8ace5b02a4f220694af7e81fab4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller2e24e8ace5b02a4f220694af7e81fab4.url(options),
    method: 'get',
})

Controller2e24e8ace5b02a4f220694af7e81fab4.definition = {
    methods: ["get","head"],
    url: '/gl/accounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
Controller2e24e8ace5b02a4f220694af7e81fab4.url = (options?: RouteQueryOptions) => {
    return Controller2e24e8ace5b02a4f220694af7e81fab4.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
Controller2e24e8ace5b02a4f220694af7e81fab4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller2e24e8ace5b02a4f220694af7e81fab4.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
Controller2e24e8ace5b02a4f220694af7e81fab4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller2e24e8ace5b02a4f220694af7e81fab4.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
    const Controller2e24e8ace5b02a4f220694af7e81fab4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller2e24e8ace5b02a4f220694af7e81fab4.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
        Controller2e24e8ace5b02a4f220694af7e81fab4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller2e24e8ace5b02a4f220694af7e81fab4.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
        Controller2e24e8ace5b02a4f220694af7e81fab4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller2e24e8ace5b02a4f220694af7e81fab4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller2e24e8ace5b02a4f220694af7e81fab4.form = Controller2e24e8ace5b02a4f220694af7e81fab4Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
const Controllerbb788701d5a5f46d65a87292db182f2a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbb788701d5a5f46d65a87292db182f2a.url(options),
    method: 'get',
})

Controllerbb788701d5a5f46d65a87292db182f2a.definition = {
    methods: ["get","head"],
    url: '/gl/journals',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
Controllerbb788701d5a5f46d65a87292db182f2a.url = (options?: RouteQueryOptions) => {
    return Controllerbb788701d5a5f46d65a87292db182f2a.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
Controllerbb788701d5a5f46d65a87292db182f2a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbb788701d5a5f46d65a87292db182f2a.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
Controllerbb788701d5a5f46d65a87292db182f2a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerbb788701d5a5f46d65a87292db182f2a.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
    const Controllerbb788701d5a5f46d65a87292db182f2aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerbb788701d5a5f46d65a87292db182f2a.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
        Controllerbb788701d5a5f46d65a87292db182f2aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerbb788701d5a5f46d65a87292db182f2a.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
        Controllerbb788701d5a5f46d65a87292db182f2aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerbb788701d5a5f46d65a87292db182f2a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerbb788701d5a5f46d65a87292db182f2a.form = Controllerbb788701d5a5f46d65a87292db182f2aForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/create'
 */
const Controller6216e976460554f11239fbae669d7aaa = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller6216e976460554f11239fbae669d7aaa.url(options),
    method: 'get',
})

Controller6216e976460554f11239fbae669d7aaa.definition = {
    methods: ["get","head"],
    url: '/gl/journals/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/create'
 */
Controller6216e976460554f11239fbae669d7aaa.url = (options?: RouteQueryOptions) => {
    return Controller6216e976460554f11239fbae669d7aaa.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/create'
 */
Controller6216e976460554f11239fbae669d7aaa.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller6216e976460554f11239fbae669d7aaa.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/create'
 */
Controller6216e976460554f11239fbae669d7aaa.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller6216e976460554f11239fbae669d7aaa.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/create'
 */
    const Controller6216e976460554f11239fbae669d7aaaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller6216e976460554f11239fbae669d7aaa.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/create'
 */
        Controller6216e976460554f11239fbae669d7aaaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller6216e976460554f11239fbae669d7aaa.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/create'
 */
        Controller6216e976460554f11239fbae669d7aaaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller6216e976460554f11239fbae669d7aaa.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller6216e976460554f11239fbae669d7aaa.form = Controller6216e976460554f11239fbae669d7aaaForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/{id}'
 */
const Controller1dc69cc1d7ac69388f5bca8720af7fc9 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller1dc69cc1d7ac69388f5bca8720af7fc9.url(args, options),
    method: 'get',
})

Controller1dc69cc1d7ac69388f5bca8720af7fc9.definition = {
    methods: ["get","head"],
    url: '/gl/journals/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/{id}'
 */
Controller1dc69cc1d7ac69388f5bca8720af7fc9.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return Controller1dc69cc1d7ac69388f5bca8720af7fc9.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/{id}'
 */
Controller1dc69cc1d7ac69388f5bca8720af7fc9.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller1dc69cc1d7ac69388f5bca8720af7fc9.url(args, options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/{id}'
 */
Controller1dc69cc1d7ac69388f5bca8720af7fc9.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller1dc69cc1d7ac69388f5bca8720af7fc9.url(args, options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/{id}'
 */
    const Controller1dc69cc1d7ac69388f5bca8720af7fc9Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller1dc69cc1d7ac69388f5bca8720af7fc9.url(args, options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/{id}'
 */
        Controller1dc69cc1d7ac69388f5bca8720af7fc9Form.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller1dc69cc1d7ac69388f5bca8720af7fc9.url(args, options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals/{id}'
 */
        Controller1dc69cc1d7ac69388f5bca8720af7fc9Form.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller1dc69cc1d7ac69388f5bca8720af7fc9.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller1dc69cc1d7ac69388f5bca8720af7fc9.form = Controller1dc69cc1d7ac69388f5bca8720af7fc9Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
const Controller97d971ee472bf41332c238c51aac6453 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller97d971ee472bf41332c238c51aac6453.url(options),
    method: 'get',
})

Controller97d971ee472bf41332c238c51aac6453.definition = {
    methods: ["get","head"],
    url: '/gl/general-ledger',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
Controller97d971ee472bf41332c238c51aac6453.url = (options?: RouteQueryOptions) => {
    return Controller97d971ee472bf41332c238c51aac6453.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
Controller97d971ee472bf41332c238c51aac6453.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller97d971ee472bf41332c238c51aac6453.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
Controller97d971ee472bf41332c238c51aac6453.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller97d971ee472bf41332c238c51aac6453.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
    const Controller97d971ee472bf41332c238c51aac6453Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller97d971ee472bf41332c238c51aac6453.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
        Controller97d971ee472bf41332c238c51aac6453Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller97d971ee472bf41332c238c51aac6453.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
        Controller97d971ee472bf41332c238c51aac6453Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller97d971ee472bf41332c238c51aac6453.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller97d971ee472bf41332c238c51aac6453.form = Controller97d971ee472bf41332c238c51aac6453Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
const Controller89a6f3eb0efd12392ea21d0c7cdaa01b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller89a6f3eb0efd12392ea21d0c7cdaa01b.url(options),
    method: 'get',
})

Controller89a6f3eb0efd12392ea21d0c7cdaa01b.definition = {
    methods: ["get","head"],
    url: '/gl/trial-balance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
Controller89a6f3eb0efd12392ea21d0c7cdaa01b.url = (options?: RouteQueryOptions) => {
    return Controller89a6f3eb0efd12392ea21d0c7cdaa01b.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
Controller89a6f3eb0efd12392ea21d0c7cdaa01b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller89a6f3eb0efd12392ea21d0c7cdaa01b.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
Controller89a6f3eb0efd12392ea21d0c7cdaa01b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller89a6f3eb0efd12392ea21d0c7cdaa01b.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
    const Controller89a6f3eb0efd12392ea21d0c7cdaa01bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller89a6f3eb0efd12392ea21d0c7cdaa01b.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
        Controller89a6f3eb0efd12392ea21d0c7cdaa01bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller89a6f3eb0efd12392ea21d0c7cdaa01b.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
        Controller89a6f3eb0efd12392ea21d0c7cdaa01bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller89a6f3eb0efd12392ea21d0c7cdaa01b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller89a6f3eb0efd12392ea21d0c7cdaa01b.form = Controller89a6f3eb0efd12392ea21d0c7cdaa01bForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
const Controllerbfe03920bc24e1af2a8c3e2957088299 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbfe03920bc24e1af2a8c3e2957088299.url(options),
    method: 'get',
})

Controllerbfe03920bc24e1af2a8c3e2957088299.definition = {
    methods: ["get","head"],
    url: '/gl/account-balances',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
Controllerbfe03920bc24e1af2a8c3e2957088299.url = (options?: RouteQueryOptions) => {
    return Controllerbfe03920bc24e1af2a8c3e2957088299.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
Controllerbfe03920bc24e1af2a8c3e2957088299.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerbfe03920bc24e1af2a8c3e2957088299.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
Controllerbfe03920bc24e1af2a8c3e2957088299.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerbfe03920bc24e1af2a8c3e2957088299.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
    const Controllerbfe03920bc24e1af2a8c3e2957088299Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerbfe03920bc24e1af2a8c3e2957088299.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
        Controllerbfe03920bc24e1af2a8c3e2957088299Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerbfe03920bc24e1af2a8c3e2957088299.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
        Controllerbfe03920bc24e1af2a8c3e2957088299Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerbfe03920bc24e1af2a8c3e2957088299.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerbfe03920bc24e1af2a8c3e2957088299.form = Controllerbfe03920bc24e1af2a8c3e2957088299Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
const Controller72ff358be9efc347b902aa4812780e8a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller72ff358be9efc347b902aa4812780e8a.url(options),
    method: 'get',
})

Controller72ff358be9efc347b902aa4812780e8a.definition = {
    methods: ["get","head"],
    url: '/gl/periods',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
Controller72ff358be9efc347b902aa4812780e8a.url = (options?: RouteQueryOptions) => {
    return Controller72ff358be9efc347b902aa4812780e8a.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
Controller72ff358be9efc347b902aa4812780e8a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller72ff358be9efc347b902aa4812780e8a.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
Controller72ff358be9efc347b902aa4812780e8a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller72ff358be9efc347b902aa4812780e8a.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
    const Controller72ff358be9efc347b902aa4812780e8aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller72ff358be9efc347b902aa4812780e8a.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
        Controller72ff358be9efc347b902aa4812780e8aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller72ff358be9efc347b902aa4812780e8a.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
        Controller72ff358be9efc347b902aa4812780e8aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller72ff358be9efc347b902aa4812780e8a.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller72ff358be9efc347b902aa4812780e8a.form = Controller72ff358be9efc347b902aa4812780e8aForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/banking'
 */
const Controller029d08be4b0a8fbb1963873b431c5e17 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller029d08be4b0a8fbb1963873b431c5e17.url(options),
    method: 'get',
})

Controller029d08be4b0a8fbb1963873b431c5e17.definition = {
    methods: ["get","head"],
    url: '/banking',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/banking'
 */
Controller029d08be4b0a8fbb1963873b431c5e17.url = (options?: RouteQueryOptions) => {
    return Controller029d08be4b0a8fbb1963873b431c5e17.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/banking'
 */
Controller029d08be4b0a8fbb1963873b431c5e17.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller029d08be4b0a8fbb1963873b431c5e17.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/banking'
 */
Controller029d08be4b0a8fbb1963873b431c5e17.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller029d08be4b0a8fbb1963873b431c5e17.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/banking'
 */
    const Controller029d08be4b0a8fbb1963873b431c5e17Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller029d08be4b0a8fbb1963873b431c5e17.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/banking'
 */
        Controller029d08be4b0a8fbb1963873b431c5e17Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller029d08be4b0a8fbb1963873b431c5e17.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/banking'
 */
        Controller029d08be4b0a8fbb1963873b431c5e17Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller029d08be4b0a8fbb1963873b431c5e17.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller029d08be4b0a8fbb1963873b431c5e17.form = Controller029d08be4b0a8fbb1963873b431c5e17Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses'
 */
const Controllerf15427b4993b94dea5aadc475939b413 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerf15427b4993b94dea5aadc475939b413.url(options),
    method: 'get',
})

Controllerf15427b4993b94dea5aadc475939b413.definition = {
    methods: ["get","head"],
    url: '/expenses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses'
 */
Controllerf15427b4993b94dea5aadc475939b413.url = (options?: RouteQueryOptions) => {
    return Controllerf15427b4993b94dea5aadc475939b413.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses'
 */
Controllerf15427b4993b94dea5aadc475939b413.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllerf15427b4993b94dea5aadc475939b413.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses'
 */
Controllerf15427b4993b94dea5aadc475939b413.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllerf15427b4993b94dea5aadc475939b413.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses'
 */
    const Controllerf15427b4993b94dea5aadc475939b413Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllerf15427b4993b94dea5aadc475939b413.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses'
 */
        Controllerf15427b4993b94dea5aadc475939b413Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerf15427b4993b94dea5aadc475939b413.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses'
 */
        Controllerf15427b4993b94dea5aadc475939b413Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllerf15427b4993b94dea5aadc475939b413.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllerf15427b4993b94dea5aadc475939b413.form = Controllerf15427b4993b94dea5aadc475939b413Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses/create'
 */
const Controller310742548c0d3b77419dae3e4071a424 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller310742548c0d3b77419dae3e4071a424.url(options),
    method: 'get',
})

Controller310742548c0d3b77419dae3e4071a424.definition = {
    methods: ["get","head"],
    url: '/expenses/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses/create'
 */
Controller310742548c0d3b77419dae3e4071a424.url = (options?: RouteQueryOptions) => {
    return Controller310742548c0d3b77419dae3e4071a424.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses/create'
 */
Controller310742548c0d3b77419dae3e4071a424.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller310742548c0d3b77419dae3e4071a424.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses/create'
 */
Controller310742548c0d3b77419dae3e4071a424.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller310742548c0d3b77419dae3e4071a424.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses/create'
 */
    const Controller310742548c0d3b77419dae3e4071a424Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller310742548c0d3b77419dae3e4071a424.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses/create'
 */
        Controller310742548c0d3b77419dae3e4071a424Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller310742548c0d3b77419dae3e4071a424.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/expenses/create'
 */
        Controller310742548c0d3b77419dae3e4071a424Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller310742548c0d3b77419dae3e4071a424.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller310742548c0d3b77419dae3e4071a424.form = Controller310742548c0d3b77419dae3e4071a424Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/projects'
 */
const Controller8f35706c95c06c991312479b995e49d2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller8f35706c95c06c991312479b995e49d2.url(options),
    method: 'get',
})

Controller8f35706c95c06c991312479b995e49d2.definition = {
    methods: ["get","head"],
    url: '/projects',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/projects'
 */
Controller8f35706c95c06c991312479b995e49d2.url = (options?: RouteQueryOptions) => {
    return Controller8f35706c95c06c991312479b995e49d2.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/projects'
 */
Controller8f35706c95c06c991312479b995e49d2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller8f35706c95c06c991312479b995e49d2.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/projects'
 */
Controller8f35706c95c06c991312479b995e49d2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller8f35706c95c06c991312479b995e49d2.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/projects'
 */
    const Controller8f35706c95c06c991312479b995e49d2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller8f35706c95c06c991312479b995e49d2.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/projects'
 */
        Controller8f35706c95c06c991312479b995e49d2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller8f35706c95c06c991312479b995e49d2.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/projects'
 */
        Controller8f35706c95c06c991312479b995e49d2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller8f35706c95c06c991312479b995e49d2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller8f35706c95c06c991312479b995e49d2.form = Controller8f35706c95c06c991312479b995e49d2Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/investments'
 */
const Controller999c82faf299ef7c436f5002558daec2 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller999c82faf299ef7c436f5002558daec2.url(options),
    method: 'get',
})

Controller999c82faf299ef7c436f5002558daec2.definition = {
    methods: ["get","head"],
    url: '/investments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/investments'
 */
Controller999c82faf299ef7c436f5002558daec2.url = (options?: RouteQueryOptions) => {
    return Controller999c82faf299ef7c436f5002558daec2.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/investments'
 */
Controller999c82faf299ef7c436f5002558daec2.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller999c82faf299ef7c436f5002558daec2.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/investments'
 */
Controller999c82faf299ef7c436f5002558daec2.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller999c82faf299ef7c436f5002558daec2.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/investments'
 */
    const Controller999c82faf299ef7c436f5002558daec2Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller999c82faf299ef7c436f5002558daec2.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/investments'
 */
        Controller999c82faf299ef7c436f5002558daec2Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller999c82faf299ef7c436f5002558daec2.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/investments'
 */
        Controller999c82faf299ef7c436f5002558daec2Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller999c82faf299ef7c436f5002558daec2.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller999c82faf299ef7c436f5002558daec2.form = Controller999c82faf299ef7c436f5002558daec2Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/tax'
 */
const Controllercf41de407639b770d1a6321100e80a86 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllercf41de407639b770d1a6321100e80a86.url(options),
    method: 'get',
})

Controllercf41de407639b770d1a6321100e80a86.definition = {
    methods: ["get","head"],
    url: '/tax',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/tax'
 */
Controllercf41de407639b770d1a6321100e80a86.url = (options?: RouteQueryOptions) => {
    return Controllercf41de407639b770d1a6321100e80a86.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/tax'
 */
Controllercf41de407639b770d1a6321100e80a86.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllercf41de407639b770d1a6321100e80a86.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/tax'
 */
Controllercf41de407639b770d1a6321100e80a86.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllercf41de407639b770d1a6321100e80a86.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/tax'
 */
    const Controllercf41de407639b770d1a6321100e80a86Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllercf41de407639b770d1a6321100e80a86.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/tax'
 */
        Controllercf41de407639b770d1a6321100e80a86Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllercf41de407639b770d1a6321100e80a86.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/tax'
 */
        Controllercf41de407639b770d1a6321100e80a86Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllercf41de407639b770d1a6321100e80a86.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllercf41de407639b770d1a6321100e80a86.form = Controllercf41de407639b770d1a6321100e80a86Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/opening-balances'
 */
const Controller88eddc690030858bad5e40ef7c1920b5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller88eddc690030858bad5e40ef7c1920b5.url(options),
    method: 'get',
})

Controller88eddc690030858bad5e40ef7c1920b5.definition = {
    methods: ["get","head"],
    url: '/opening-balances',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/opening-balances'
 */
Controller88eddc690030858bad5e40ef7c1920b5.url = (options?: RouteQueryOptions) => {
    return Controller88eddc690030858bad5e40ef7c1920b5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/opening-balances'
 */
Controller88eddc690030858bad5e40ef7c1920b5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller88eddc690030858bad5e40ef7c1920b5.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/opening-balances'
 */
Controller88eddc690030858bad5e40ef7c1920b5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller88eddc690030858bad5e40ef7c1920b5.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/opening-balances'
 */
    const Controller88eddc690030858bad5e40ef7c1920b5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller88eddc690030858bad5e40ef7c1920b5.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/opening-balances'
 */
        Controller88eddc690030858bad5e40ef7c1920b5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller88eddc690030858bad5e40ef7c1920b5.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/opening-balances'
 */
        Controller88eddc690030858bad5e40ef7c1920b5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller88eddc690030858bad5e40ef7c1920b5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller88eddc690030858bad5e40ef7c1920b5.form = Controller88eddc690030858bad5e40ef7c1920b5Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/books'
 */
const Controller96e5b65c6848771ddbbaeb506ce6ca70 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller96e5b65c6848771ddbbaeb506ce6ca70.url(options),
    method: 'get',
})

Controller96e5b65c6848771ddbbaeb506ce6ca70.definition = {
    methods: ["get","head"],
    url: '/books',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/books'
 */
Controller96e5b65c6848771ddbbaeb506ce6ca70.url = (options?: RouteQueryOptions) => {
    return Controller96e5b65c6848771ddbbaeb506ce6ca70.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/books'
 */
Controller96e5b65c6848771ddbbaeb506ce6ca70.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller96e5b65c6848771ddbbaeb506ce6ca70.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/books'
 */
Controller96e5b65c6848771ddbbaeb506ce6ca70.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller96e5b65c6848771ddbbaeb506ce6ca70.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/books'
 */
    const Controller96e5b65c6848771ddbbaeb506ce6ca70Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller96e5b65c6848771ddbbaeb506ce6ca70.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/books'
 */
        Controller96e5b65c6848771ddbbaeb506ce6ca70Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller96e5b65c6848771ddbbaeb506ce6ca70.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/books'
 */
        Controller96e5b65c6848771ddbbaeb506ce6ca70Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller96e5b65c6848771ddbbaeb506ce6ca70.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller96e5b65c6848771ddbbaeb506ce6ca70.form = Controller96e5b65c6848771ddbbaeb506ce6ca70Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
const Controller68104c39fc50f931d04080ab9bc50796 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller68104c39fc50f931d04080ab9bc50796.url(options),
    method: 'get',
})

Controller68104c39fc50f931d04080ab9bc50796.definition = {
    methods: ["get","head"],
    url: '/reports/balance-sheet',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
Controller68104c39fc50f931d04080ab9bc50796.url = (options?: RouteQueryOptions) => {
    return Controller68104c39fc50f931d04080ab9bc50796.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
Controller68104c39fc50f931d04080ab9bc50796.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller68104c39fc50f931d04080ab9bc50796.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
Controller68104c39fc50f931d04080ab9bc50796.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller68104c39fc50f931d04080ab9bc50796.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
    const Controller68104c39fc50f931d04080ab9bc50796Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller68104c39fc50f931d04080ab9bc50796.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
        Controller68104c39fc50f931d04080ab9bc50796Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller68104c39fc50f931d04080ab9bc50796.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
        Controller68104c39fc50f931d04080ab9bc50796Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller68104c39fc50f931d04080ab9bc50796.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller68104c39fc50f931d04080ab9bc50796.form = Controller68104c39fc50f931d04080ab9bc50796Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
const Controller99242c6f06fd52373c3f22cf26a770b1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller99242c6f06fd52373c3f22cf26a770b1.url(options),
    method: 'get',
})

Controller99242c6f06fd52373c3f22cf26a770b1.definition = {
    methods: ["get","head"],
    url: '/reports/profit-loss',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
Controller99242c6f06fd52373c3f22cf26a770b1.url = (options?: RouteQueryOptions) => {
    return Controller99242c6f06fd52373c3f22cf26a770b1.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
Controller99242c6f06fd52373c3f22cf26a770b1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller99242c6f06fd52373c3f22cf26a770b1.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
Controller99242c6f06fd52373c3f22cf26a770b1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller99242c6f06fd52373c3f22cf26a770b1.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
    const Controller99242c6f06fd52373c3f22cf26a770b1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller99242c6f06fd52373c3f22cf26a770b1.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
        Controller99242c6f06fd52373c3f22cf26a770b1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller99242c6f06fd52373c3f22cf26a770b1.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
        Controller99242c6f06fd52373c3f22cf26a770b1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller99242c6f06fd52373c3f22cf26a770b1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller99242c6f06fd52373c3f22cf26a770b1.form = Controller99242c6f06fd52373c3f22cf26a770b1Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
const Controller183a074ec42e0840ee73f687852777ba = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller183a074ec42e0840ee73f687852777ba.url(options),
    method: 'get',
})

Controller183a074ec42e0840ee73f687852777ba.definition = {
    methods: ["get","head"],
    url: '/reports/cash-flow',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
Controller183a074ec42e0840ee73f687852777ba.url = (options?: RouteQueryOptions) => {
    return Controller183a074ec42e0840ee73f687852777ba.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
Controller183a074ec42e0840ee73f687852777ba.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller183a074ec42e0840ee73f687852777ba.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
Controller183a074ec42e0840ee73f687852777ba.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller183a074ec42e0840ee73f687852777ba.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
    const Controller183a074ec42e0840ee73f687852777baForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller183a074ec42e0840ee73f687852777ba.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
        Controller183a074ec42e0840ee73f687852777baForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller183a074ec42e0840ee73f687852777ba.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
        Controller183a074ec42e0840ee73f687852777baForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller183a074ec42e0840ee73f687852777ba.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller183a074ec42e0840ee73f687852777ba.form = Controller183a074ec42e0840ee73f687852777baForm
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
const Controller50672d89120d00ffd726df5d80920065 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller50672d89120d00ffd726df5d80920065.url(options),
    method: 'get',
})

Controller50672d89120d00ffd726df5d80920065.definition = {
    methods: ["get","head"],
    url: '/reports/management-pack',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
Controller50672d89120d00ffd726df5d80920065.url = (options?: RouteQueryOptions) => {
    return Controller50672d89120d00ffd726df5d80920065.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
Controller50672d89120d00ffd726df5d80920065.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controller50672d89120d00ffd726df5d80920065.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
Controller50672d89120d00ffd726df5d80920065.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controller50672d89120d00ffd726df5d80920065.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
    const Controller50672d89120d00ffd726df5d80920065Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controller50672d89120d00ffd726df5d80920065.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
        Controller50672d89120d00ffd726df5d80920065Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller50672d89120d00ffd726df5d80920065.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
        Controller50672d89120d00ffd726df5d80920065Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controller50672d89120d00ffd726df5d80920065.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controller50672d89120d00ffd726df5d80920065.form = Controller50672d89120d00ffd726df5d80920065Form
    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
const Controllere19ee86e9cf603ce1a59a1ec5d21dec5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})

Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition = {
    methods: ["get","head"],
    url: '/settings/appearance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url = (options?: RouteQueryOptions) => {
    return Controllere19ee86e9cf603ce1a59a1ec5d21dec5.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
Controllere19ee86e9cf603ce1a59a1ec5d21dec5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
    const Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
        Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/settings/appearance'
 */
        Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: Controllere19ee86e9cf603ce1a59a1ec5d21dec5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    Controllere19ee86e9cf603ce1a59a1ec5d21dec5.form = Controllere19ee86e9cf603ce1a59a1ec5d21dec5Form

/**
* Multiple routes resolve to \Inertia\Controller::Controller, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `Controller['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
const Controller = {
    '/': Controller980bb49ee7ae63891f1d891d2fbcf1c9,
    '/dashboard': Controller42a740574ecbfbac32f8cc353fc32db9,
    '/ar/customers': Controller1118256a56bcf330a5e6aa92c3538a4c,
    '/ar/customers/create': Controllerf0e55d005a22f74c12a066c89b7a88b2,
    '/ar/customers/{id}': Controller031ed354cac38f56c524069c66f34ed1,
    '/ar/sales-invoices': Controller5018e050909b7293873f39c77bae3f7e,
    '/ar/sales-invoices/create': Controller18b4e39c35661053e4c6797bea4bf153,
    '/ar/sales-invoices/{id}': Controller9b41ecbbc53d3e874d18ff532724501f,
    '/ar/receipts': Controller35712ad4b783b0c09dba11f00ef7f74f,
    '/ar/receipts/create': Controller39d6c15ce76ee0419882c140ca7d37ab,
    '/ar/credit-notes': Controller089251ce1ff4fd7de0094d36e3e53fc2,
    '/ar/debit-notes': Controllera3d34301b06eab863b38252a14a03fc7,
    '/ar/aging': Controllere27462a7e07c45ad0ffa53038f3eb5c4,
    '/ar/open-items': Controllerd358fcd5c46fb0eca276b16d01891ae3,
    '/ar/reconciliation': Controller8f345849b2122401f90a7be9f20a2b67,
    '/ap/suppliers': Controller57220b0fd621167c299cab63d571e1b2,
    '/ap/suppliers/create': Controller412f38bb134bb2663f4b23cfcc2a2d4b,
    '/ap/purchase-invoices': Controllerf2f4a7bece28fd4a708d9b537fdc3ff2,
    '/ap/purchase-invoices/create': Controller8ff07afc862b8f40c63e5e14fd2ff47d,
    '/ap/supplier-payments': Controller6912af11a1213fe8460431fafb31370b,
    '/ap/aging': Controllerc1d705b146f1a3c714b1e3aefb53d82c,
    '/ap/open-items': Controller9c802de5a611c892ce508c046f09efd4,
    '/ap/reconciliation': Controller7cb57da946889d72325b7a2a17979dfe,
    '/gl/accounts': Controller2e24e8ace5b02a4f220694af7e81fab4,
    '/gl/journals': Controllerbb788701d5a5f46d65a87292db182f2a,
    '/gl/journals/create': Controller6216e976460554f11239fbae669d7aaa,
    '/gl/journals/{id}': Controller1dc69cc1d7ac69388f5bca8720af7fc9,
    '/gl/general-ledger': Controller97d971ee472bf41332c238c51aac6453,
    '/gl/trial-balance': Controller89a6f3eb0efd12392ea21d0c7cdaa01b,
    '/gl/account-balances': Controllerbfe03920bc24e1af2a8c3e2957088299,
    '/gl/periods': Controller72ff358be9efc347b902aa4812780e8a,
    '/banking': Controller029d08be4b0a8fbb1963873b431c5e17,
    '/expenses': Controllerf15427b4993b94dea5aadc475939b413,
    '/expenses/create': Controller310742548c0d3b77419dae3e4071a424,
    '/projects': Controller8f35706c95c06c991312479b995e49d2,
    '/investments': Controller999c82faf299ef7c436f5002558daec2,
    '/tax': Controllercf41de407639b770d1a6321100e80a86,
    '/opening-balances': Controller88eddc690030858bad5e40ef7c1920b5,
    '/books': Controller96e5b65c6848771ddbbaeb506ce6ca70,
    '/reports/balance-sheet': Controller68104c39fc50f931d04080ab9bc50796,
    '/reports/profit-loss': Controller99242c6f06fd52373c3f22cf26a770b1,
    '/reports/cash-flow': Controller183a074ec42e0840ee73f687852777ba,
    '/reports/management-pack': Controller50672d89120d00ffd726df5d80920065,
    '/settings/appearance': Controllere19ee86e9cf603ce1a59a1ec5d21dec5,
}

export default Controller