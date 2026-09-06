import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:25
 * @route '/api/v1/ar/customers'
 */
export const customersIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersIndex.url(options),
    method: 'get',
})

customersIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:25
 * @route '/api/v1/ar/customers'
 */
customersIndex.url = (options?: RouteQueryOptions) => {
    return customersIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:25
 * @route '/api/v1/ar/customers'
 */
customersIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:25
 * @route '/api/v1/ar/customers'
 */
customersIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customersIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:25
 * @route '/api/v1/ar/customers'
 */
    const customersIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customersIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:25
 * @route '/api/v1/ar/customers'
 */
        customersIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:25
 * @route '/api/v1/ar/customers'
 */
        customersIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    customersIndex.form = customersIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:32
 * @route '/api/v1/ar/customers'
 */
export const customersStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customersStore.url(options),
    method: 'post',
})

customersStore.definition = {
    methods: ["post"],
    url: '/api/v1/ar/customers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:32
 * @route '/api/v1/ar/customers'
 */
customersStore.url = (options?: RouteQueryOptions) => {
    return customersStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:32
 * @route '/api/v1/ar/customers'
 */
customersStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: customersStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:32
 * @route '/api/v1/ar/customers'
 */
    const customersStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: customersStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:32
 * @route '/api/v1/ar/customers'
 */
        customersStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: customersStore.url(options),
            method: 'post',
        })
    
    customersStore.form = customersStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:44
 * @route '/api/v1/ar/customers/{customer}'
 */
export const customersShow = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersShow.url(args, options),
    method: 'get',
})

customersShow.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/customers/{customer}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:44
 * @route '/api/v1/ar/customers/{customer}'
 */
customersShow.url = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { customer: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    customer: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        customer: args.customer,
                }

    return customersShow.definition.url
            .replace('{customer}', parsedArgs.customer.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:44
 * @route '/api/v1/ar/customers/{customer}'
 */
customersShow.get = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customersShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:44
 * @route '/api/v1/ar/customers/{customer}'
 */
customersShow.head = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customersShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:44
 * @route '/api/v1/ar/customers/{customer}'
 */
    const customersShowForm = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customersShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:44
 * @route '/api/v1/ar/customers/{customer}'
 */
        customersShowForm.get = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customersShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:44
 * @route '/api/v1/ar/customers/{customer}'
 */
        customersShowForm.head = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customersShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    customersShow.form = customersShowForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customerStatement
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:224
 * @route '/api/v1/ar/customers/{customer}/statement'
 */
export const customerStatement = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerStatement.url(args, options),
    method: 'get',
})

customerStatement.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/customers/{customer}/statement',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customerStatement
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:224
 * @route '/api/v1/ar/customers/{customer}/statement'
 */
customerStatement.url = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { customer: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    customer: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        customer: args.customer,
                }

    return customerStatement.definition.url
            .replace('{customer}', parsedArgs.customer.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customerStatement
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:224
 * @route '/api/v1/ar/customers/{customer}/statement'
 */
customerStatement.get = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customerStatement.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customerStatement
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:224
 * @route '/api/v1/ar/customers/{customer}/statement'
 */
customerStatement.head = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customerStatement.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customerStatement
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:224
 * @route '/api/v1/ar/customers/{customer}/statement'
 */
    const customerStatementForm = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customerStatement.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customerStatement
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:224
 * @route '/api/v1/ar/customers/{customer}/statement'
 */
        customerStatementForm.get = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerStatement.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::customerStatement
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:224
 * @route '/api/v1/ar/customers/{customer}/statement'
 */
        customerStatementForm.head = (args: { customer: string | number } | [customer: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customerStatement.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    customerStatement.form = customerStatementForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:51
 * @route '/api/v1/ar/sales-invoices'
 */
export const invoicesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesIndex.url(options),
    method: 'get',
})

invoicesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/sales-invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:51
 * @route '/api/v1/ar/sales-invoices'
 */
invoicesIndex.url = (options?: RouteQueryOptions) => {
    return invoicesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:51
 * @route '/api/v1/ar/sales-invoices'
 */
invoicesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:51
 * @route '/api/v1/ar/sales-invoices'
 */
invoicesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: invoicesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:51
 * @route '/api/v1/ar/sales-invoices'
 */
    const invoicesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: invoicesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:51
 * @route '/api/v1/ar/sales-invoices'
 */
        invoicesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoicesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:51
 * @route '/api/v1/ar/sales-invoices'
 */
        invoicesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoicesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    invoicesIndex.form = invoicesIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:58
 * @route '/api/v1/ar/sales-invoices'
 */
export const invoicesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesStore.url(options),
    method: 'post',
})

invoicesStore.definition = {
    methods: ["post"],
    url: '/api/v1/ar/sales-invoices',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:58
 * @route '/api/v1/ar/sales-invoices'
 */
invoicesStore.url = (options?: RouteQueryOptions) => {
    return invoicesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:58
 * @route '/api/v1/ar/sales-invoices'
 */
invoicesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:58
 * @route '/api/v1/ar/sales-invoices'
 */
    const invoicesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invoicesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:58
 * @route '/api/v1/ar/sales-invoices'
 */
        invoicesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invoicesStore.url(options),
            method: 'post',
        })
    
    invoicesStore.form = invoicesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:88
 * @route '/api/v1/ar/sales-invoices/{invoice}'
 */
export const invoicesShow = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesShow.url(args, options),
    method: 'get',
})

invoicesShow.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/sales-invoices/{invoice}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:88
 * @route '/api/v1/ar/sales-invoices/{invoice}'
 */
invoicesShow.url = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: args.invoice,
                }

    return invoicesShow.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:88
 * @route '/api/v1/ar/sales-invoices/{invoice}'
 */
invoicesShow.get = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:88
 * @route '/api/v1/ar/sales-invoices/{invoice}'
 */
invoicesShow.head = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: invoicesShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:88
 * @route '/api/v1/ar/sales-invoices/{invoice}'
 */
    const invoicesShowForm = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: invoicesShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:88
 * @route '/api/v1/ar/sales-invoices/{invoice}'
 */
        invoicesShowForm.get = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoicesShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesShow
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:88
 * @route '/api/v1/ar/sales-invoices/{invoice}'
 */
        invoicesShowForm.head = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoicesShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    invoicesShow.form = invoicesShowForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:95
 * @route '/api/v1/ar/sales-invoices/{invoice}/post'
 */
export const invoicesPost = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesPost.url(args, options),
    method: 'post',
})

invoicesPost.definition = {
    methods: ["post"],
    url: '/api/v1/ar/sales-invoices/{invoice}/post',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:95
 * @route '/api/v1/ar/sales-invoices/{invoice}/post'
 */
invoicesPost.url = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invoice: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    invoice: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        invoice: args.invoice,
                }

    return invoicesPost.definition.url
            .replace('{invoice}', parsedArgs.invoice.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:95
 * @route '/api/v1/ar/sales-invoices/{invoice}/post'
 */
invoicesPost.post = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesPost.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:95
 * @route '/api/v1/ar/sales-invoices/{invoice}/post'
 */
    const invoicesPostForm = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invoicesPost.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:95
 * @route '/api/v1/ar/sales-invoices/{invoice}/post'
 */
        invoicesPostForm.post = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invoicesPost.url(args, options),
            method: 'post',
        })
    
    invoicesPost.form = invoicesPostForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:103
 * @route '/api/v1/ar/receipts'
 */
export const receiptsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receiptsIndex.url(options),
    method: 'get',
})

receiptsIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/receipts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:103
 * @route '/api/v1/ar/receipts'
 */
receiptsIndex.url = (options?: RouteQueryOptions) => {
    return receiptsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:103
 * @route '/api/v1/ar/receipts'
 */
receiptsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receiptsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:103
 * @route '/api/v1/ar/receipts'
 */
receiptsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: receiptsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:103
 * @route '/api/v1/ar/receipts'
 */
    const receiptsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: receiptsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:103
 * @route '/api/v1/ar/receipts'
 */
        receiptsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receiptsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:103
 * @route '/api/v1/ar/receipts'
 */
        receiptsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receiptsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    receiptsIndex.form = receiptsIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:110
 * @route '/api/v1/ar/receipts'
 */
export const receiptsStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receiptsStore.url(options),
    method: 'post',
})

receiptsStore.definition = {
    methods: ["post"],
    url: '/api/v1/ar/receipts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:110
 * @route '/api/v1/ar/receipts'
 */
receiptsStore.url = (options?: RouteQueryOptions) => {
    return receiptsStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:110
 * @route '/api/v1/ar/receipts'
 */
receiptsStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receiptsStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:110
 * @route '/api/v1/ar/receipts'
 */
    const receiptsStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: receiptsStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:110
 * @route '/api/v1/ar/receipts'
 */
        receiptsStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: receiptsStore.url(options),
            method: 'post',
        })
    
    receiptsStore.form = receiptsStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsAllocate
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:137
 * @route '/api/v1/ar/receipts/{receipt}/allocate'
 */
export const receiptsAllocate = (args: { receipt: string | number } | [receipt: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receiptsAllocate.url(args, options),
    method: 'post',
})

receiptsAllocate.definition = {
    methods: ["post"],
    url: '/api/v1/ar/receipts/{receipt}/allocate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsAllocate
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:137
 * @route '/api/v1/ar/receipts/{receipt}/allocate'
 */
receiptsAllocate.url = (args: { receipt: string | number } | [receipt: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { receipt: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    receipt: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        receipt: args.receipt,
                }

    return receiptsAllocate.definition.url
            .replace('{receipt}', parsedArgs.receipt.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsAllocate
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:137
 * @route '/api/v1/ar/receipts/{receipt}/allocate'
 */
receiptsAllocate.post = (args: { receipt: string | number } | [receipt: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: receiptsAllocate.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsAllocate
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:137
 * @route '/api/v1/ar/receipts/{receipt}/allocate'
 */
    const receiptsAllocateForm = (args: { receipt: string | number } | [receipt: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: receiptsAllocate.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::receiptsAllocate
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:137
 * @route '/api/v1/ar/receipts/{receipt}/allocate'
 */
        receiptsAllocateForm.post = (args: { receipt: string | number } | [receipt: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: receiptsAllocate.url(args, options),
            method: 'post',
        })
    
    receiptsAllocate.form = receiptsAllocateForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:150
 * @route '/api/v1/ar/credit-notes'
 */
export const creditNotesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditNotesIndex.url(options),
    method: 'get',
})

creditNotesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/credit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:150
 * @route '/api/v1/ar/credit-notes'
 */
creditNotesIndex.url = (options?: RouteQueryOptions) => {
    return creditNotesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:150
 * @route '/api/v1/ar/credit-notes'
 */
creditNotesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditNotesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:150
 * @route '/api/v1/ar/credit-notes'
 */
creditNotesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: creditNotesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:150
 * @route '/api/v1/ar/credit-notes'
 */
    const creditNotesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: creditNotesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:150
 * @route '/api/v1/ar/credit-notes'
 */
        creditNotesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: creditNotesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:150
 * @route '/api/v1/ar/credit-notes'
 */
        creditNotesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: creditNotesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    creditNotesIndex.form = creditNotesIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:157
 * @route '/api/v1/ar/credit-notes'
 */
export const creditNotesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: creditNotesStore.url(options),
    method: 'post',
})

creditNotesStore.definition = {
    methods: ["post"],
    url: '/api/v1/ar/credit-notes',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:157
 * @route '/api/v1/ar/credit-notes'
 */
creditNotesStore.url = (options?: RouteQueryOptions) => {
    return creditNotesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:157
 * @route '/api/v1/ar/credit-notes'
 */
creditNotesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: creditNotesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:157
 * @route '/api/v1/ar/credit-notes'
 */
    const creditNotesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: creditNotesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:157
 * @route '/api/v1/ar/credit-notes'
 */
        creditNotesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: creditNotesStore.url(options),
            method: 'post',
        })
    
    creditNotesStore.form = creditNotesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:187
 * @route '/api/v1/ar/debit-notes'
 */
export const debitNotesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: debitNotesIndex.url(options),
    method: 'get',
})

debitNotesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/debit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:187
 * @route '/api/v1/ar/debit-notes'
 */
debitNotesIndex.url = (options?: RouteQueryOptions) => {
    return debitNotesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:187
 * @route '/api/v1/ar/debit-notes'
 */
debitNotesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: debitNotesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:187
 * @route '/api/v1/ar/debit-notes'
 */
debitNotesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: debitNotesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:187
 * @route '/api/v1/ar/debit-notes'
 */
    const debitNotesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: debitNotesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:187
 * @route '/api/v1/ar/debit-notes'
 */
        debitNotesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: debitNotesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:187
 * @route '/api/v1/ar/debit-notes'
 */
        debitNotesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: debitNotesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    debitNotesIndex.form = debitNotesIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:194
 * @route '/api/v1/ar/debit-notes'
 */
export const debitNotesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: debitNotesStore.url(options),
    method: 'post',
})

debitNotesStore.definition = {
    methods: ["post"],
    url: '/api/v1/ar/debit-notes',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:194
 * @route '/api/v1/ar/debit-notes'
 */
debitNotesStore.url = (options?: RouteQueryOptions) => {
    return debitNotesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:194
 * @route '/api/v1/ar/debit-notes'
 */
debitNotesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: debitNotesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:194
 * @route '/api/v1/ar/debit-notes'
 */
    const debitNotesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: debitNotesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:194
 * @route '/api/v1/ar/debit-notes'
 */
        debitNotesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: debitNotesStore.url(options),
            method: 'post',
        })
    
    debitNotesStore.form = debitNotesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::aging
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:235
 * @route '/api/v1/ar/aging'
 */
export const aging = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})

aging.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/aging',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::aging
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:235
 * @route '/api/v1/ar/aging'
 */
aging.url = (options?: RouteQueryOptions) => {
    return aging.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::aging
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:235
 * @route '/api/v1/ar/aging'
 */
aging.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::aging
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:235
 * @route '/api/v1/ar/aging'
 */
aging.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: aging.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::aging
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:235
 * @route '/api/v1/ar/aging'
 */
    const agingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: aging.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::aging
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:235
 * @route '/api/v1/ar/aging'
 */
        agingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: aging.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::aging
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:235
 * @route '/api/v1/ar/aging'
 */
        agingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: aging.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    aging.form = agingForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::openItems
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:246
 * @route '/api/v1/ar/open-items'
 */
export const openItems = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})

openItems.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/open-items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::openItems
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:246
 * @route '/api/v1/ar/open-items'
 */
openItems.url = (options?: RouteQueryOptions) => {
    return openItems.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::openItems
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:246
 * @route '/api/v1/ar/open-items'
 */
openItems.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::openItems
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:246
 * @route '/api/v1/ar/open-items'
 */
openItems.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: openItems.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::openItems
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:246
 * @route '/api/v1/ar/open-items'
 */
    const openItemsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: openItems.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::openItems
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:246
 * @route '/api/v1/ar/open-items'
 */
        openItemsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openItems.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::openItems
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:246
 * @route '/api/v1/ar/open-items'
 */
        openItemsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openItems.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    openItems.form = openItemsForm
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:258
 * @route '/api/v1/ar/reconciliation'
 */
export const reconciliation = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})

reconciliation.definition = {
    methods: ["get","head"],
    url: '/api/v1/ar/reconciliation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:258
 * @route '/api/v1/ar/reconciliation'
 */
reconciliation.url = (options?: RouteQueryOptions) => {
    return reconciliation.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:258
 * @route '/api/v1/ar/reconciliation'
 */
reconciliation.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:258
 * @route '/api/v1/ar/reconciliation'
 */
reconciliation.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reconciliation.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:258
 * @route '/api/v1/ar/reconciliation'
 */
    const reconciliationForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reconciliation.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:258
 * @route '/api/v1/ar/reconciliation'
 */
        reconciliationForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reconciliation.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ar\ArController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ar/ArController.php:258
 * @route '/api/v1/ar/reconciliation'
 */
        reconciliationForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reconciliation.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    reconciliation.form = reconciliationForm
const ArController = { customersIndex, customersStore, customersShow, customerStatement, invoicesIndex, invoicesStore, invoicesShow, invoicesPost, receiptsIndex, receiptsStore, receiptsAllocate, creditNotesIndex, creditNotesStore, debitNotesIndex, debitNotesStore, aging, openItems, reconciliation }

export default ArController