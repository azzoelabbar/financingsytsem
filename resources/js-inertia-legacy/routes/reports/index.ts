import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
export const balanceSheet = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: balanceSheet.url(options),
    method: 'get',
})

balanceSheet.definition = {
    methods: ["get","head"],
    url: '/reports/balance-sheet',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
balanceSheet.url = (options?: RouteQueryOptions) => {
    return balanceSheet.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
balanceSheet.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: balanceSheet.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
balanceSheet.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: balanceSheet.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
    const balanceSheetForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: balanceSheet.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
        balanceSheetForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: balanceSheet.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/balance-sheet'
 */
        balanceSheetForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: balanceSheet.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    balanceSheet.form = balanceSheetForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
export const profitLoss = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profitLoss.url(options),
    method: 'get',
})

profitLoss.definition = {
    methods: ["get","head"],
    url: '/reports/profit-loss',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
profitLoss.url = (options?: RouteQueryOptions) => {
    return profitLoss.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
profitLoss.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profitLoss.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
profitLoss.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: profitLoss.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
    const profitLossForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: profitLoss.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
        profitLossForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profitLoss.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/profit-loss'
 */
        profitLossForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profitLoss.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    profitLoss.form = profitLossForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
export const cashFlow = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cashFlow.url(options),
    method: 'get',
})

cashFlow.definition = {
    methods: ["get","head"],
    url: '/reports/cash-flow',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
cashFlow.url = (options?: RouteQueryOptions) => {
    return cashFlow.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
cashFlow.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cashFlow.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
cashFlow.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cashFlow.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
    const cashFlowForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: cashFlow.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
        cashFlowForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cashFlow.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/cash-flow'
 */
        cashFlowForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cashFlow.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    cashFlow.form = cashFlowForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
export const managementPack = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: managementPack.url(options),
    method: 'get',
})

managementPack.definition = {
    methods: ["get","head"],
    url: '/reports/management-pack',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
managementPack.url = (options?: RouteQueryOptions) => {
    return managementPack.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
managementPack.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: managementPack.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
managementPack.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: managementPack.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
    const managementPackForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: managementPack.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
        managementPackForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: managementPack.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/reports/management-pack'
 */
        managementPackForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: managementPack.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    managementPack.form = managementPackForm
const reports = {
    balanceSheet: Object.assign(balanceSheet, balanceSheet),
profitLoss: Object.assign(profitLoss, profitLoss),
cashFlow: Object.assign(cashFlow, cashFlow),
managementPack: Object.assign(managementPack, managementPack),
}

export default reports