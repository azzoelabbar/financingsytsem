import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:25
 * @route '/api/v1/ap/suppliers'
 */
export const suppliersIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suppliersIndex.url(options),
    method: 'get',
})

suppliersIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/suppliers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:25
 * @route '/api/v1/ap/suppliers'
 */
suppliersIndex.url = (options?: RouteQueryOptions) => {
    return suppliersIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:25
 * @route '/api/v1/ap/suppliers'
 */
suppliersIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suppliersIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:25
 * @route '/api/v1/ap/suppliers'
 */
suppliersIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: suppliersIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:25
 * @route '/api/v1/ap/suppliers'
 */
    const suppliersIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: suppliersIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:25
 * @route '/api/v1/ap/suppliers'
 */
        suppliersIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suppliersIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:25
 * @route '/api/v1/ap/suppliers'
 */
        suppliersIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suppliersIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    suppliersIndex.form = suppliersIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:32
 * @route '/api/v1/ap/suppliers'
 */
export const suppliersStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suppliersStore.url(options),
    method: 'post',
})

suppliersStore.definition = {
    methods: ["post"],
    url: '/api/v1/ap/suppliers',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:32
 * @route '/api/v1/ap/suppliers'
 */
suppliersStore.url = (options?: RouteQueryOptions) => {
    return suppliersStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:32
 * @route '/api/v1/ap/suppliers'
 */
suppliersStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suppliersStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:32
 * @route '/api/v1/ap/suppliers'
 */
    const suppliersStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: suppliersStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:32
 * @route '/api/v1/ap/suppliers'
 */
        suppliersStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: suppliersStore.url(options),
            method: 'post',
        })
    
    suppliersStore.form = suppliersStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersShow
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:44
 * @route '/api/v1/ap/suppliers/{supplier}'
 */
export const suppliersShow = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suppliersShow.url(args, options),
    method: 'get',
})

suppliersShow.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/suppliers/{supplier}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersShow
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:44
 * @route '/api/v1/ap/suppliers/{supplier}'
 */
suppliersShow.url = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { supplier: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    supplier: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        supplier: args.supplier,
                }

    return suppliersShow.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersShow
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:44
 * @route '/api/v1/ap/suppliers/{supplier}'
 */
suppliersShow.get = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suppliersShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersShow
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:44
 * @route '/api/v1/ap/suppliers/{supplier}'
 */
suppliersShow.head = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: suppliersShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersShow
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:44
 * @route '/api/v1/ap/suppliers/{supplier}'
 */
    const suppliersShowForm = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: suppliersShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersShow
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:44
 * @route '/api/v1/ap/suppliers/{supplier}'
 */
        suppliersShowForm.get = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suppliersShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::suppliersShow
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:44
 * @route '/api/v1/ap/suppliers/{supplier}'
 */
        suppliersShowForm.head = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suppliersShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    suppliersShow.form = suppliersShowForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::supplierStatement
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:211
 * @route '/api/v1/ap/suppliers/{supplier}/statement'
 */
export const supplierStatement = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: supplierStatement.url(args, options),
    method: 'get',
})

supplierStatement.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/suppliers/{supplier}/statement',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::supplierStatement
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:211
 * @route '/api/v1/ap/suppliers/{supplier}/statement'
 */
supplierStatement.url = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { supplier: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    supplier: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        supplier: args.supplier,
                }

    return supplierStatement.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::supplierStatement
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:211
 * @route '/api/v1/ap/suppliers/{supplier}/statement'
 */
supplierStatement.get = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: supplierStatement.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::supplierStatement
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:211
 * @route '/api/v1/ap/suppliers/{supplier}/statement'
 */
supplierStatement.head = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: supplierStatement.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::supplierStatement
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:211
 * @route '/api/v1/ap/suppliers/{supplier}/statement'
 */
    const supplierStatementForm = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: supplierStatement.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::supplierStatement
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:211
 * @route '/api/v1/ap/suppliers/{supplier}/statement'
 */
        supplierStatementForm.get = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: supplierStatement.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::supplierStatement
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:211
 * @route '/api/v1/ap/suppliers/{supplier}/statement'
 */
        supplierStatementForm.head = (args: { supplier: string | number } | [supplier: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: supplierStatement.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    supplierStatement.form = supplierStatementForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:51
 * @route '/api/v1/ap/purchase-invoices'
 */
export const invoicesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesIndex.url(options),
    method: 'get',
})

invoicesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/purchase-invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:51
 * @route '/api/v1/ap/purchase-invoices'
 */
invoicesIndex.url = (options?: RouteQueryOptions) => {
    return invoicesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:51
 * @route '/api/v1/ap/purchase-invoices'
 */
invoicesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoicesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:51
 * @route '/api/v1/ap/purchase-invoices'
 */
invoicesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: invoicesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:51
 * @route '/api/v1/ap/purchase-invoices'
 */
    const invoicesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: invoicesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:51
 * @route '/api/v1/ap/purchase-invoices'
 */
        invoicesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoicesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:51
 * @route '/api/v1/ap/purchase-invoices'
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
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:58
 * @route '/api/v1/ap/purchase-invoices'
 */
export const invoicesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesStore.url(options),
    method: 'post',
})

invoicesStore.definition = {
    methods: ["post"],
    url: '/api/v1/ap/purchase-invoices',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:58
 * @route '/api/v1/ap/purchase-invoices'
 */
invoicesStore.url = (options?: RouteQueryOptions) => {
    return invoicesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:58
 * @route '/api/v1/ap/purchase-invoices'
 */
invoicesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:58
 * @route '/api/v1/ap/purchase-invoices'
 */
    const invoicesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invoicesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:58
 * @route '/api/v1/ap/purchase-invoices'
 */
        invoicesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invoicesStore.url(options),
            method: 'post',
        })
    
    invoicesStore.form = invoicesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:88
 * @route '/api/v1/ap/purchase-invoices/{invoice}/post'
 */
export const invoicesPost = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesPost.url(args, options),
    method: 'post',
})

invoicesPost.definition = {
    methods: ["post"],
    url: '/api/v1/ap/purchase-invoices/{invoice}/post',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:88
 * @route '/api/v1/ap/purchase-invoices/{invoice}/post'
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
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:88
 * @route '/api/v1/ap/purchase-invoices/{invoice}/post'
 */
invoicesPost.post = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: invoicesPost.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:88
 * @route '/api/v1/ap/purchase-invoices/{invoice}/post'
 */
    const invoicesPostForm = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: invoicesPost.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::invoicesPost
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:88
 * @route '/api/v1/ap/purchase-invoices/{invoice}/post'
 */
        invoicesPostForm.post = (args: { invoice: string | number } | [invoice: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: invoicesPost.url(args, options),
            method: 'post',
        })
    
    invoicesPost.form = invoicesPostForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:96
 * @route '/api/v1/ap/supplier-payments'
 */
export const paymentsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: paymentsIndex.url(options),
    method: 'get',
})

paymentsIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/supplier-payments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:96
 * @route '/api/v1/ap/supplier-payments'
 */
paymentsIndex.url = (options?: RouteQueryOptions) => {
    return paymentsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:96
 * @route '/api/v1/ap/supplier-payments'
 */
paymentsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: paymentsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:96
 * @route '/api/v1/ap/supplier-payments'
 */
paymentsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: paymentsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:96
 * @route '/api/v1/ap/supplier-payments'
 */
    const paymentsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: paymentsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:96
 * @route '/api/v1/ap/supplier-payments'
 */
        paymentsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: paymentsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:96
 * @route '/api/v1/ap/supplier-payments'
 */
        paymentsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: paymentsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    paymentsIndex.form = paymentsIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:103
 * @route '/api/v1/ap/supplier-payments'
 */
export const paymentsStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentsStore.url(options),
    method: 'post',
})

paymentsStore.definition = {
    methods: ["post"],
    url: '/api/v1/ap/supplier-payments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:103
 * @route '/api/v1/ap/supplier-payments'
 */
paymentsStore.url = (options?: RouteQueryOptions) => {
    return paymentsStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:103
 * @route '/api/v1/ap/supplier-payments'
 */
paymentsStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentsStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:103
 * @route '/api/v1/ap/supplier-payments'
 */
    const paymentsStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: paymentsStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:103
 * @route '/api/v1/ap/supplier-payments'
 */
        paymentsStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: paymentsStore.url(options),
            method: 'post',
        })
    
    paymentsStore.form = paymentsStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsAllocate
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:128
 * @route '/api/v1/ap/supplier-payments/{payment}/allocate'
 */
export const paymentsAllocate = (args: { payment: string | number } | [payment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentsAllocate.url(args, options),
    method: 'post',
})

paymentsAllocate.definition = {
    methods: ["post"],
    url: '/api/v1/ap/supplier-payments/{payment}/allocate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsAllocate
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:128
 * @route '/api/v1/ap/supplier-payments/{payment}/allocate'
 */
paymentsAllocate.url = (args: { payment: string | number } | [payment: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payment: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    payment: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payment: args.payment,
                }

    return paymentsAllocate.definition.url
            .replace('{payment}', parsedArgs.payment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsAllocate
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:128
 * @route '/api/v1/ap/supplier-payments/{payment}/allocate'
 */
paymentsAllocate.post = (args: { payment: string | number } | [payment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentsAllocate.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsAllocate
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:128
 * @route '/api/v1/ap/supplier-payments/{payment}/allocate'
 */
    const paymentsAllocateForm = (args: { payment: string | number } | [payment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: paymentsAllocate.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::paymentsAllocate
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:128
 * @route '/api/v1/ap/supplier-payments/{payment}/allocate'
 */
        paymentsAllocateForm.post = (args: { payment: string | number } | [payment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: paymentsAllocate.url(args, options),
            method: 'post',
        })
    
    paymentsAllocate.form = paymentsAllocateForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:141
 * @route '/api/v1/ap/supplier-credit-notes'
 */
export const creditNotesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditNotesIndex.url(options),
    method: 'get',
})

creditNotesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/supplier-credit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:141
 * @route '/api/v1/ap/supplier-credit-notes'
 */
creditNotesIndex.url = (options?: RouteQueryOptions) => {
    return creditNotesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:141
 * @route '/api/v1/ap/supplier-credit-notes'
 */
creditNotesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditNotesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:141
 * @route '/api/v1/ap/supplier-credit-notes'
 */
creditNotesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: creditNotesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:141
 * @route '/api/v1/ap/supplier-credit-notes'
 */
    const creditNotesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: creditNotesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:141
 * @route '/api/v1/ap/supplier-credit-notes'
 */
        creditNotesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: creditNotesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:141
 * @route '/api/v1/ap/supplier-credit-notes'
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
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:148
 * @route '/api/v1/ap/supplier-credit-notes'
 */
export const creditNotesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: creditNotesStore.url(options),
    method: 'post',
})

creditNotesStore.definition = {
    methods: ["post"],
    url: '/api/v1/ap/supplier-credit-notes',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:148
 * @route '/api/v1/ap/supplier-credit-notes'
 */
creditNotesStore.url = (options?: RouteQueryOptions) => {
    return creditNotesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:148
 * @route '/api/v1/ap/supplier-credit-notes'
 */
creditNotesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: creditNotesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:148
 * @route '/api/v1/ap/supplier-credit-notes'
 */
    const creditNotesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: creditNotesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::creditNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:148
 * @route '/api/v1/ap/supplier-credit-notes'
 */
        creditNotesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: creditNotesStore.url(options),
            method: 'post',
        })
    
    creditNotesStore.form = creditNotesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:176
 * @route '/api/v1/ap/supplier-debit-notes'
 */
export const debitNotesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: debitNotesIndex.url(options),
    method: 'get',
})

debitNotesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/supplier-debit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:176
 * @route '/api/v1/ap/supplier-debit-notes'
 */
debitNotesIndex.url = (options?: RouteQueryOptions) => {
    return debitNotesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:176
 * @route '/api/v1/ap/supplier-debit-notes'
 */
debitNotesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: debitNotesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:176
 * @route '/api/v1/ap/supplier-debit-notes'
 */
debitNotesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: debitNotesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:176
 * @route '/api/v1/ap/supplier-debit-notes'
 */
    const debitNotesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: debitNotesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:176
 * @route '/api/v1/ap/supplier-debit-notes'
 */
        debitNotesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: debitNotesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesIndex
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:176
 * @route '/api/v1/ap/supplier-debit-notes'
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
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:183
 * @route '/api/v1/ap/supplier-debit-notes'
 */
export const debitNotesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: debitNotesStore.url(options),
    method: 'post',
})

debitNotesStore.definition = {
    methods: ["post"],
    url: '/api/v1/ap/supplier-debit-notes',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:183
 * @route '/api/v1/ap/supplier-debit-notes'
 */
debitNotesStore.url = (options?: RouteQueryOptions) => {
    return debitNotesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:183
 * @route '/api/v1/ap/supplier-debit-notes'
 */
debitNotesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: debitNotesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:183
 * @route '/api/v1/ap/supplier-debit-notes'
 */
    const debitNotesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: debitNotesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::debitNotesStore
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:183
 * @route '/api/v1/ap/supplier-debit-notes'
 */
        debitNotesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: debitNotesStore.url(options),
            method: 'post',
        })
    
    debitNotesStore.form = debitNotesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::aging
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:224
 * @route '/api/v1/ap/aging'
 */
export const aging = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})

aging.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/aging',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::aging
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:224
 * @route '/api/v1/ap/aging'
 */
aging.url = (options?: RouteQueryOptions) => {
    return aging.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::aging
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:224
 * @route '/api/v1/ap/aging'
 */
aging.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::aging
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:224
 * @route '/api/v1/ap/aging'
 */
aging.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: aging.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::aging
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:224
 * @route '/api/v1/ap/aging'
 */
    const agingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: aging.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::aging
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:224
 * @route '/api/v1/ap/aging'
 */
        agingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: aging.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::aging
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:224
 * @route '/api/v1/ap/aging'
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
* @see \App\Http\Controllers\Api\V1\Ap\ApController::openItems
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:235
 * @route '/api/v1/ap/open-items'
 */
export const openItems = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})

openItems.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/open-items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::openItems
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:235
 * @route '/api/v1/ap/open-items'
 */
openItems.url = (options?: RouteQueryOptions) => {
    return openItems.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::openItems
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:235
 * @route '/api/v1/ap/open-items'
 */
openItems.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::openItems
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:235
 * @route '/api/v1/ap/open-items'
 */
openItems.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: openItems.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::openItems
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:235
 * @route '/api/v1/ap/open-items'
 */
    const openItemsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: openItems.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::openItems
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:235
 * @route '/api/v1/ap/open-items'
 */
        openItemsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openItems.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::openItems
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:235
 * @route '/api/v1/ap/open-items'
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
* @see \App\Http\Controllers\Api\V1\Ap\ApController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:247
 * @route '/api/v1/ap/reconciliation'
 */
export const reconciliation = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})

reconciliation.definition = {
    methods: ["get","head"],
    url: '/api/v1/ap/reconciliation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:247
 * @route '/api/v1/ap/reconciliation'
 */
reconciliation.url = (options?: RouteQueryOptions) => {
    return reconciliation.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:247
 * @route '/api/v1/ap/reconciliation'
 */
reconciliation.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:247
 * @route '/api/v1/ap/reconciliation'
 */
reconciliation.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reconciliation.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:247
 * @route '/api/v1/ap/reconciliation'
 */
    const reconciliationForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reconciliation.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:247
 * @route '/api/v1/ap/reconciliation'
 */
        reconciliationForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reconciliation.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Ap\ApController::reconciliation
 * @see app/Http/Controllers/Api/V1/Ap/ApController.php:247
 * @route '/api/v1/ap/reconciliation'
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
const ApController = { suppliersIndex, suppliersStore, suppliersShow, supplierStatement, invoicesIndex, invoicesStore, invoicesPost, paymentsIndex, paymentsStore, paymentsAllocate, creditNotesIndex, creditNotesStore, debitNotesIndex, debitNotesStore, aging, openItems, reconciliation }

export default ApController