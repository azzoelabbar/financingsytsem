import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import journals637b90 from './journals'
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
export const accounts = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accounts.url(options),
    method: 'get',
})

accounts.definition = {
    methods: ["get","head"],
    url: '/gl/accounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
accounts.url = (options?: RouteQueryOptions) => {
    return accounts.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
accounts.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accounts.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
accounts.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: accounts.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
    const accountsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: accounts.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
        accountsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accounts.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/accounts'
 */
        accountsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accounts.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    accounts.form = accountsForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
export const journals = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journals.url(options),
    method: 'get',
})

journals.definition = {
    methods: ["get","head"],
    url: '/gl/journals',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
journals.url = (options?: RouteQueryOptions) => {
    return journals.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
journals.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journals.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
journals.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: journals.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
    const journalsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: journals.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
        journalsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journals.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/journals'
 */
        journalsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journals.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    journals.form = journalsForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
export const ledger = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ledger.url(options),
    method: 'get',
})

ledger.definition = {
    methods: ["get","head"],
    url: '/gl/general-ledger',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
ledger.url = (options?: RouteQueryOptions) => {
    return ledger.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
ledger.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ledger.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
ledger.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ledger.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
    const ledgerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: ledger.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
        ledgerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: ledger.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/general-ledger'
 */
        ledgerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: ledger.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    ledger.form = ledgerForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
export const trialBalance = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trialBalance.url(options),
    method: 'get',
})

trialBalance.definition = {
    methods: ["get","head"],
    url: '/gl/trial-balance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
trialBalance.url = (options?: RouteQueryOptions) => {
    return trialBalance.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
trialBalance.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trialBalance.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
trialBalance.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: trialBalance.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
    const trialBalanceForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: trialBalance.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
        trialBalanceForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: trialBalance.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/trial-balance'
 */
        trialBalanceForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: trialBalance.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    trialBalance.form = trialBalanceForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
export const balances = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: balances.url(options),
    method: 'get',
})

balances.definition = {
    methods: ["get","head"],
    url: '/gl/account-balances',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
balances.url = (options?: RouteQueryOptions) => {
    return balances.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
balances.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: balances.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
balances.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: balances.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
    const balancesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: balances.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
        balancesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: balances.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/account-balances'
 */
        balancesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: balances.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    balances.form = balancesForm
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
export const periods = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: periods.url(options),
    method: 'get',
})

periods.definition = {
    methods: ["get","head"],
    url: '/gl/periods',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
periods.url = (options?: RouteQueryOptions) => {
    return periods.definition.url + queryParams(options)
}

/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
periods.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: periods.url(options),
    method: 'get',
})
/**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
periods.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: periods.url(options),
    method: 'head',
})

    /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
    const periodsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: periods.url(options),
        method: 'get',
    })

            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
        periodsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: periods.url(options),
            method: 'get',
        })
            /**
* @see \Inertia\Controller::__invoke
 * @see vendor/inertiajs/inertia-laravel/src/Controller.php:13
 * @route '/gl/periods'
 */
        periodsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: periods.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    periods.form = periodsForm
const gl = {
    accounts: Object.assign(accounts, accounts),
journals: Object.assign(journals, journals637b90),
ledger: Object.assign(ledger, ledger),
trialBalance: Object.assign(trialBalance, trialBalance),
balances: Object.assign(balances, balances),
periods: Object.assign(periods, periods),
}

export default gl