import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import customers1682b4 from './customers'
import invoicesD0c47e from './invoices'
import receipts03856a from './receipts'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
export const customers = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})

customers.definition = {
    methods: ["get","head"],
    url: '/ar/customers',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
customers.url = (options?: RouteQueryOptions) => {
    return customers.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
customers.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: customers.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
customers.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: customers.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
    const customersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: customers.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
        customersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customers.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/customers'
 */
        customersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: customers.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    customers.form = customersForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
export const invoices = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoices.url(options),
    method: 'get',
})

invoices.definition = {
    methods: ["get","head"],
    url: '/ar/sales-invoices',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
invoices.url = (options?: RouteQueryOptions) => {
    return invoices.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
invoices.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: invoices.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
invoices.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: invoices.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
    const invoicesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: invoices.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
 */
        invoicesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: invoices.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/sales-invoices'
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
 * @route '/ar/receipts'
 */
export const receipts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receipts.url(options),
    method: 'get',
})

receipts.definition = {
    methods: ["get","head"],
    url: '/ar/receipts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
receipts.url = (options?: RouteQueryOptions) => {
    return receipts.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
receipts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receipts.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
receipts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: receipts.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
    const receiptsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: receipts.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
        receiptsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receipts.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/receipts'
 */
        receiptsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receipts.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    receipts.form = receiptsForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
export const creditNotes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditNotes.url(options),
    method: 'get',
})

creditNotes.definition = {
    methods: ["get","head"],
    url: '/ar/credit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
creditNotes.url = (options?: RouteQueryOptions) => {
    return creditNotes.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
creditNotes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: creditNotes.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
creditNotes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: creditNotes.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
    const creditNotesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: creditNotes.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
        creditNotesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: creditNotes.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/credit-notes'
 */
        creditNotesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: creditNotes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    creditNotes.form = creditNotesForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
export const debitNotes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: debitNotes.url(options),
    method: 'get',
})

debitNotes.definition = {
    methods: ["get","head"],
    url: '/ar/debit-notes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
debitNotes.url = (options?: RouteQueryOptions) => {
    return debitNotes.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
debitNotes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: debitNotes.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
debitNotes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: debitNotes.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
    const debitNotesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: debitNotes.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
        debitNotesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: debitNotes.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/debit-notes'
 */
        debitNotesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: debitNotes.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    debitNotes.form = debitNotesForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
export const aging = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})

aging.definition = {
    methods: ["get","head"],
    url: '/ar/aging',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
aging.url = (options?: RouteQueryOptions) => {
    return aging.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
aging.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aging.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
aging.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: aging.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
    const agingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: aging.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
 */
        agingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: aging.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/aging'
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
 * @route '/ar/open-items'
 */
export const openItems = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})

openItems.definition = {
    methods: ["get","head"],
    url: '/ar/open-items',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
openItems.url = (options?: RouteQueryOptions) => {
    return openItems.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
openItems.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openItems.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
openItems.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: openItems.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
    const openItemsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: openItems.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
 */
        openItemsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openItems.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/open-items'
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
 * @route '/ar/reconciliation'
 */
export const reconciliation = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})

reconciliation.definition = {
    methods: ["get","head"],
    url: '/ar/reconciliation',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
reconciliation.url = (options?: RouteQueryOptions) => {
    return reconciliation.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
reconciliation.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: reconciliation.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
reconciliation.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: reconciliation.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
    const reconciliationForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: reconciliation.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
 */
        reconciliationForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: reconciliation.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/ar/reconciliation'
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
const ar = {
    customers: Object.assign(customers, customers1682b4),
invoices: Object.assign(invoices, invoicesD0c47e),
receipts: Object.assign(receipts, receipts03856a),
creditNotes: Object.assign(creditNotes, creditNotes),
debitNotes: Object.assign(debitNotes, debitNotes),
aging: Object.assign(aging, aging),
openItems: Object.assign(openItems, openItems),
reconciliation: Object.assign(reconciliation, reconciliation),
}

export default ar