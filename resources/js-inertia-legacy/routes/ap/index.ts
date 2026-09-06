import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import suppliers577ad4 from './suppliers'
import invoicesD0c47e from './invoices'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
export const suppliers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suppliers.url(options),
    method: 'get',
})

suppliers.definition = {
    methods: ["get","head"],
    url: '/ap/suppliers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
suppliers.url = (options?: RouteQueryOptions) => {
    return suppliers.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
suppliers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: suppliers.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
suppliers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: suppliers.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
    const suppliersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: suppliers.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
        suppliersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suppliers.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/suppliers'
 */
        suppliersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: suppliers.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    suppliers.form = suppliersForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
export const invoices = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoices.url(options),
    method: 'get',
})

invoices.definition = {
    methods: ["get","head"],
    url: '/ap/purchase-invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
invoices.url = (options?: RouteQueryOptions) => {
    return invoices.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
invoices.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoices.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
invoices.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: invoices.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
    const invoicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: invoices.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
        invoicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoices.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/purchase-invoices'
 */
        invoicesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoices.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    invoices.form = invoicesForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
export const payments = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: payments.url(options),
    method: 'get',
})

payments.definition = {
    methods: ["get","head"],
    url: '/ap/supplier-payments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
payments.url = (options?: RouteQueryOptions) => {
    return payments.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
payments.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: payments.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
payments.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: payments.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
    const paymentsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: payments.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
        paymentsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: payments.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/supplier-payments'
 */
        paymentsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: payments.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    payments.form = paymentsForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
export const aging = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})

aging.definition = {
    methods: ["get","head"],
    url: '/ap/aging',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
aging.url = (options?: RouteQueryOptions) => {
    return aging.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
aging.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
aging.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: aging.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
    const agingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: aging.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
 */
        agingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: aging.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/aging'
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
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
export const openItems = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})

openItems.definition = {
    methods: ["get","head"],
    url: '/ap/open-items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
openItems.url = (options?: RouteQueryOptions) => {
    return openItems.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
openItems.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
openItems.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: openItems.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
    const openItemsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: openItems.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
 */
        openItemsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openItems.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/open-items'
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
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
export const reconciliation = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})

reconciliation.definition = {
    methods: ["get","head"],
    url: '/ap/reconciliation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
reconciliation.url = (options?: RouteQueryOptions) => {
    return reconciliation.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
reconciliation.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
reconciliation.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reconciliation.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
    const reconciliationForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reconciliation.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
 */
        reconciliationForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reconciliation.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ap/reconciliation'
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
const ap = {
    suppliers: Object.assign(suppliers, suppliers577ad4),
invoices: Object.assign(invoices, invoicesD0c47e),
payments: Object.assign(payments, payments),
aging: Object.assign(aging, aging),
openItems: Object.assign(openItems, openItems),
reconciliation: Object.assign(reconciliation, reconciliation),
}

export default ap